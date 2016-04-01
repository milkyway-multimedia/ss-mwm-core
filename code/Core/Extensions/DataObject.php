<?php namespace Milkyway\SS\Core\Extensions;

/**
 * Milkyway Multimedia
 * DataObject.php
 *
 * @package milkyway-multimedia/ss-mwm-core
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use DataExtension;
use DataObjectInterface;

class DataObject extends DataExtension
{
    // cache inherited objects to lessen load on system
    private $inheritedObjCache = [];

    /**
     * Add a nice description of @DataObject if you want to
     * @return mixed
     */
    function i18n_description()
    {
        return _t(get_class($this->owner) . '.DESCRIPTION', $this->owner->config()->description);
    }

    /**
     * Find a record using a filter, or create it if it doesn't exist
     *
     * @param array $filter
     * @param array $additionalData
     * @param bool|true $write
     * @return static
     */
    public function firstOrMake($filter = [], $additionalData = [], $write = true)
    {
        if (!($record = $this->owner->get()->filter($filter)->first())) {
            $record = $this->owner->create(array_merge($filter, $additionalData));

            if ($write) {
                $record->write();
                $record->isNew = true;
            }
        }

        return $record;
    }

    /**
     * Alias of ->firstOrMake
     *
     * @param array $filter
     * @param array $additionalData
     * @param bool|true $write
     * @return static
     */
    public function firstOrCreate($filter = [], $additionalData = [], $write = true)
    {
        return $this->owner->firstOrMake($filter, $additionalData, $write);
    }

    /**
     * Check if dataobject is instance of class
     *
     * @param $class
     * @return bool
     */
    public function is_a($class)
    {
        return (bool)singleton('mwm')->is_instanceof($class, $this->owner);
    }

    /**
     * Check if dataobject is not instance of class
     *
     * @param $class
     * @return bool
     */
    public function is_not_a($class)
    {
        return !$this->is_a($class);
    }

    /**
     * Get an inherited object (dot notation allowed), it will check in order:
     * 1. cache
     * 2. method on current object
     * 3. if object has parent method (or extends @Hierarchy), it will check the parents
     * 4. check home page
     * 5. check @SiteConfig if it exists
     *
     * @param $fieldName
     * @param null $arguments
     * @param bool|true $cache
     * @param null $cacheName
     * @param string $includePrefix
     * @param Callable $parentCallback
     * @return mixed|null
     */
    public function InheritedObj($fieldName, $arguments = null, $cache = true, $cacheName = null, $includePrefix = '')
    {
        $value = null;
        $firstVal = null;

        if (!$cacheName) {
            $cacheName = $arguments ? $fieldName . implode(',', $arguments) : $fieldName;
        }

        $cacheName = get_class($this->owner) . '__' . $cacheName;

        if (!isset($this->inheritedObjCache[$cacheName]) || !$cache) {
            $keyParts = explode('.', $fieldName);
            $fieldName = array_shift($keyParts);

            $value = $this->owner->obj($fieldName, $arguments, $cache, $cacheName);

            if (!empty($keyParts) && $value instanceof DataObjectInterface) {
                foreach ($keyParts as $keyPart) {
                    $value = $value->obj($keyPart, $arguments, $cache, $cacheName);
                }
            }

            if ($value && $value->hasMethod('exists') && !$value->exists()) {
                if ($firstVal === null) {
                    $firstVal = $value;
                }
                $value = null;
            }

            if (!$value) {
                $page = $this->owner;

                while ($page != null && $page->ID) {
                    $value = $page->obj($fieldName, $arguments, $cache, $cacheName);

                    if (!empty($keyParts) && $value instanceof DataObjectInterface) {
                        foreach ($keyParts as $keyPart) {
                            $value = $value->obj($keyPart, $arguments, $cache, $cacheName);
                        }
                    }

                    if ($value && $value->hasMethod('exists') && !$value->exists()) {
                        if ($firstVal === null) {
                            $firstVal = $value;
                        }
                        $value = null;
                    } else {
                        if ($value) {
                            $value->__inheritedFrom = $page;
                        }
                    }

                    if ($value) {
                        break;
                    }

                    if($this->owner->hasMethod('Parent')) {
                        $page = $page->Parent();
                    }
                    else {
                        $page = null;
                    }
                }
            }

            if (!$value && ($home = singleton('director')->homePage()) && $home !== $this->owner) {
                $value = $home->obj($fieldName, $arguments, $cache, $cacheName);

                if (!empty($keyParts) && $value instanceof DataObjectInterface) {
                    foreach ($keyParts as $keyPart) {
                        $value = $value->obj($keyPart, $arguments, $cache, $cacheName);
                    }
                }
            }

            if ($value && $value->hasMethod('exists') && !$value->exists()) {
                if ($firstVal === null) {
                    $firstVal = $value;
                }
                $value = null;
            } else {
                if ($value && isset($home)) {
                    $value->__inheritedFrom = $home;
                }
            }

            if (!$value && class_exists('SiteConfig') && ($siteConfig = \SiteConfig::current_site_config()) && $siteConfig !== $this) {
                $value = $siteConfig->obj($includePrefix . $fieldName, $arguments, $cache, $cacheName);

                if (!empty($keyParts) && $value instanceof DataObjectInterface) {
                    foreach ($keyParts as $keyPart) {
                        $value = $value->obj($keyPart, $arguments, $cache, $cacheName);
                    }
                }
            }

            if ($value && $value->hasMethod('exists') && !$value->exists()) {
                if ($firstVal === null) {
                    $firstVal = $value;
                }
                $value = null;
            } else {
                if ($value && isset($siteConfig)) {
                    $value->__inheritedFrom = $siteConfig;
                }
            }

            if ($value === null) {
                $value = $firstVal;
            }

            if ($cache) {
                $this->inheritedObjCache[$cacheName] = $value;
            }
        } else {
            $value = $this->inheritedObjCache[$cacheName];
        }

        return $value;
    }
}
