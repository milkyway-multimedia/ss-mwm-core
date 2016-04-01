<?php namespace Milkyway\SS\Core\Overrides;

/**
 * Milkyway Multimedia
 * UploadField_SelectHandler.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use UploadField_SelectHandler as Original;
use DataList;
use Permission;
use Folder;

class UploadField_SelectHandler extends Original
{
    protected function getListField($folderID)
    {
        $field = parent::getListField($folderID);

        if ($this->parent && $this->parent->hasMethod('getConfig') && ($files = $field->fieldByName('Files'))) {
            // Set data class of list (eg. display only images)
            if ($this->parent->getConfig('listDataClass')) {
                $files->setList(DataList::create($this->parent->getConfig('listDataClass'))->filter('ParentID',
                    $folderID));
            }

            $uploadField = $this->parent;
            $callbacks = [
                'UploadField' => function ($keyParts, $key) use ($uploadField) {
                    if ($uploadField->class !== 'UploadField') {
                        return $uploadField->config()->{implode('.', $keyParts)};
                    }

                    return null;
                }
            ];

            // Limit the number of results in the file list (defaults to 10)
            $limit = $this->parent->getConfig('listLimit') ?: singleton('env')->get('UploadField.file_list_limit', 10, [
                'beforeConfigNamespaceCheckCallbacks' => $callbacks,
            ]);

            $files
                ->Config
                ->getComponentByType('GridFieldPaginator')
                ->setItemsPerPage($limit);

            // Add the ability to upload files in the file list
            if (Permission::check('CMS_ACCESS_AssetAdmin') && singleton('env')->get('UploadField.allow_file_list_uploads',
                    true, [
                        'beforeConfigNamespaceCheckCallbacks' => $callbacks,
                    ])
            ) {
                $field->insertAfter($uploader = $this->parent->create('File_Uploader', ''), 'Files');

                $uploader->setConfig('canAttachExisting', false);
                $uploader->addExtraClass('ss-upload-to-folder');

                if ($folderID && ($folder = Folder::get()->byID($folderID))) {
                    $path = strpos($folder->RelativePath, ASSETS_DIR . '/') === 0 ? substr($folder->RelativePath,
                        strlen(ASSETS_DIR . '/')) : $folder->RelativePath;
                    $uploader->setFolderName($path);
                }
            }
        }

        parent::extend('updateListField', $field, $folderID);

        return $field;
    }
} 