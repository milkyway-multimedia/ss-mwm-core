(function ($) {
    $.entwine('ss', function ($) {
        // Reload GridField when item is uploaded to folder
        $('div.ss-upload-to-folder').entwine({
            onfileuploaddone:    function (e, data) {
                this.reloadGridField();
            },
            onfileuploaddestroy: function (e, data) {
                this.reloadGridField();
            },
            reloadGridField:     function () {
                var $self = this;

                setTimeout(function () {
                    var fileList = $self.closest('form').find('.ss-gridfield');
                    fileList.reload();
                }, 10);
            }
        });

        // Reload the upload field when the parent folder is changed
        $('form.uploadfield-form #ParentID .TreeDropdownField').entwine({
            onchange:                function () {
                var $uploader = this.closest('form').find('input.ss-upload-to-folder:first'),
                    $this = this;

                if ($uploader.length) {
                    var $uploaderParent = $uploader.parents('.ss-upload-to-folder:first');
                    config = $uploader.data('config');

                    if (config.url) {
                        $.get(config.url.substring(0, config.url.indexOf('/upload')) + '?folder=' + this.getValue(), function (html) {
                            $uploaderParent.replaceWith($this.addFolderIdToUrlsInHTML(html));
                        });
                    }
                }
            },
            addFolderIdToUrlsInHTML: function (html) {
                var folder = this.getValue(),
                    replacements = {
                        '\\/upload&quot;':     '\\/upload?folder=' + folder + '&quot;',
                        '\\/attach&quot;':     '\\/attach?folder=' + folder + '&quot;',
                        '\\/select&quot;':     '\\/select?folder=' + folder + '&quot;',
                        '\\/fileexists&quot;': '\\/fileexists?folder=' + folder + '&quot;'
                    };

                for (var replace in replacements) {
                    if (replacements.hasOwnProperty(replace)) {
                        html = html.replace(new RegExp(this.escapeRegExp(replace), 'g'), replacements[replace]);
                    }
                }

                return html;
            },
            escapeRegExp:            function (expression) {
                return expression.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
            }
        });

        // Load a tab that matches the hash in current url
        $('.ss-tabset').entwine({
            onadd: function () {
                this._super();

                if (window.location.hash && this.data('tabs')) {
                    $('[aria-controls=' + window.location.hash.replace(/^(#tab\-)/,'') + ']').find('a:first').click();
                }
            }
        });

        // Allow links with ss-tabset-goto to link to a tab
        $('.ss-tabset-goto').entwine({
            onclick: function () {
                if (window.location.hash) {
                    $('[aria-controls=' + window.location.hash.replace(/^(#tab\-)/,'') + ']').find('a:first').click();
                }
            }
        });
    });

    $.entwine('ss.tree', function ($) {
        // Add new actions to the context menu for the site tree
        $('.cms .cms-tree').entwine({
            getTreeConfig: function () {
                var self = this,
                    config = this._super(),
                    cms = $('.cms-container');

                if (config.hasOwnProperty('contextmenu')) {
                    var _items = config.contextmenu.items;

                    config.contextmenu.items = function (node) {
                        var menu = _items(node),
                            url = self.data('urlDuplicate');

                        if (url) {
                            // Allow publishing a page via right click
                            menu.publish = {
                                'label':  ss.i18n._t('Tree.Publish', 'Publish'),
                                'action': function (obj) {
                                    var id = obj.data('id'),
                                        ids = [id];

                                    cms.entwine('.ss').loadFragment(
                                        $.path.addSearchParams(
                                            ss.i18n.sprintf(url.replace('/duplicate', '/publish-record'), id),
                                            self.data('extraParams')
                                        ), 'SiteTree'
                                    ).success(function () {
                                            self.updateNodesFromServer(ids);
                                        });
                                }
                            };

                            // Allow unpublishing a page via right click
                            menu.unpublish = {
                                'label':  ss.i18n._t('Tree.Unpublish', 'Unpublish'),
                                'action': function (obj) {
                                    var id = obj.data('id'),
                                        ids = [id];

                                    cms.entwine('.ss').loadFragment(
                                        $.path.addSearchParams(
                                            ss.i18n.sprintf(url.replace('/duplicate', '/unpublish-record'), id),
                                            self.data('extraParams')
                                        ), 'SiteTree'
                                    ).success(function () {
                                            self.updateNodesFromServer(ids);
                                        });
                                }
                            };

                            // Allow permanent deletion via right click
                            if (!node.hasClass('nodelete')) {
                                menu.delete = {
                                    'label':  ss.i18n._t('Tree.Delete_Permanently', 'Delete permanently'),
                                    'action': function (obj) {
                                        if (confirm(ss.i18n._t('CMSMAIN.DELETE_PERMANENTLY', 'Are you sure you want to delete this page permanently (aka no going back)?'))) {
                                            var id = obj.data('id');

                                            cms.entwine('.ss').loadFragment(
                                                $.path.addSearchParams(
                                                    ss.i18n.sprintf(url.replace('/duplicate', '/annihilate'), id),
                                                    self.data('extraParams')
                                                ), 'SiteTree'
                                            ).success(function () {
                                                    var node = self.getNodeByID(id);
                                                    if (node.length) {
                                                        self.jstree('delete_node', node);
                                                    }

                                                    cms.entwine('.ss').reloadCurrentPanel();
                                                });
                                        }
                                    }
                                };
                            }
                        }

                        return menu;
                    };
                }

                return config;
            }
        });
    });
})(jQuery);
