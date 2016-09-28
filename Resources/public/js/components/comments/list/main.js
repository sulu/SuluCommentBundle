/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'services/sulucomment/comment-manager',
    'services/sulucomment/comment-router',
    'text!./list.html'
], function(Manager, Router, list) {

    var defaults = {
        templates: {
            list: list
        },

        translations: {
            title: 'sulu_comment.comments'
        }
    };

    return {

        defaults: defaults,

        header: function() {
            return {
                title: this.translations.title,
                underline: false,

                noBack: true,

                toolbar: {
                    buttons: {
                        deleteSelected: {
                            options: {
                                callback: function() {
                                    this.sandbox.emit(
                                        'husky.datagrid.comments.items.get-selected',
                                        this.deleteItems.bind(this)
                                    );
                                }.bind(this)
                            }
                        }
                    }
                }
            };
        },

        layout: {
            content: {
                width: 'max'
            }
        },

        initialize: function() {
            this.render();

            this.bindCustomEvents();
        },

        render: function() {
            this.$el.html(this.templates.list());

            this.sandbox.sulu.initListToolbarAndList.call(this,
                'comments',
                Manager.url() + '/fields',
                {
                    el: this.$find('#list-toolbar-container'),
                    instanceName: 'comments',
                    template: this.sandbox.sulu.buttons.get({
                        settings: {
                            options: {
                                dropdownItems: [
                                    {
                                        type: 'columnOptions'
                                    }
                                ]
                            }
                        }
                    })
                },
                {
                    el: this.sandbox.dom.find('#comments-list'),
                    url: Manager.url() + '?sortBy=created&sortOrder=desc',
                    searchInstanceName: 'comments',
                    searchFields: ['title'],
                    resultKey: 'comments',
                    instanceName: 'comments',
                    actionCallback: function(id) {
                        Router.toEdit(id);
                    },
                    contentFilters: {
                        message: function(content) {
                            var tmp = document.createElement('div');
                            tmp.innerHTML = content;

                            content = tmp.textContent || tmp.innerText;

                            return this.sandbox.util.cropMiddle(content, 300);
                        }.bind(this)
                    },
                    viewOptions: {
                        table: {
                            selectItem: {
                                type: 'checkbox',
                                inFirstCell: false
                            }
                        }
                    }
                }
            );
        },

        deleteItems: function(ids) {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'deleteSelected');

            Manager.deleteMultiple(ids).then(function() {
                for (var i in ids) {
                    this.sandbox.emit('husky.datagrid.comments.record.remove', ids[i]);
                }

                this.sandbox.emit('sulu.header.toolbar.item.disable', 'deleteSelected');
            }.bind(this));
        },

        bindCustomEvents: function() {
            this.sandbox.on('husky.datagrid.comments.number.selections', function(number) {
                var postfix = number > 0 ? 'enable' : 'disable';
                this.sandbox.emit('sulu.header.toolbar.item.' + postfix, 'deleteSelected', false);
            }.bind(this));
        }
    };
});
