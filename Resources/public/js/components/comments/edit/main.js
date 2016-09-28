/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'jquery',
    'services/sulucomment/comment-manager',
    'services/sulucomment/comment-router'
], function($, CommentManager, CommentRouter) {

    'use strict';

    return {

        defaults: {
            translations: {
                headline: 'sulu_comment.comments',
                published: 'sulu_comment.comment.published',
                unpublished: 'sulu_comment.comment.unpublished'
            }
        },

        header: function() {
            var buttons = {
                save: {},
                edit: {
                    options: {
                        dropdownItems: {
                            delete: {
                                options: {
                                    callback: this.delete.bind(this)
                                }
                            }
                        }
                    }
                },
                state: {
                    parent: 'toggler',
                    options: {
                        title: this.translations.unpublished
                    }
                }
            };

            // unsafe check is used because rest-api returns as string
            if (this.data.state == 1) {
                buttons.state = {
                    parent: 'toggler-on',
                    options: {
                        title: this.translations.published,
                        callback: this.toggleState.bind(this)
                    }
                };
            }

            return {
                title: function() {
                    return this.translations.headline;
                }.bind(this),

                tabs: {
                    url: '/admin/content-navigations?alias=comments',
                    options: {
                        data: function() {
                            return this.sandbox.util.extend(false, {}, this.data);
                        }.bind(this)
                    },
                    componentOptions: {
                        values: this.data
                    }
                },

                toolbar: {
                    buttons: buttons
                }
            };
        },

        loadComponentData: function() {
            var promise = $.Deferred();

            if (!this.options.id) {
                promise.resolve({});

                return promise;
            }
            CommentManager.load(this.options.id).done(function(data) {
                promise.resolve(data);
            });

            return promise;
        },

        initialize: function() {
            this.bindCustomEvents();
        },

        bindCustomEvents: function() {
            this.sandbox.on('sulu.header.back', this.toList.bind(this));
            this.sandbox.on('sulu.tab.dirty', this.enableSave.bind(this));
            this.sandbox.on('sulu.toolbar.save', this.save.bind(this));
            this.sandbox.on('sulu.tab.data-changed', this.setData.bind(this));

            // state-toggler
            this.sandbox.on('husky.toggler.sulu-toolbar.changed', this.toggleState.bind(this));
        },

        toList: function() {
            CommentRouter.toList();
        },

        toggleState: function() {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'save');

            // unsafe check is used because rest-api returns as string
            if (this.data.state == 0) {
                this.sandbox.emit('sulu.header.toolbar.button.set', 'state', {title: this.translations.published});

                return CommentManager.publish(this.data.id).done(function(data) {
                    this.data.state = data.state;
                    this.sandbox.emit('sulu.header.toolbar.item.enable', 'save', false);
                }.bind(this));
            }

            this.sandbox.emit('sulu.header.toolbar.button.set', 'state', {title: this.translations.unpublished});

            return CommentManager.unpublish(this.data.id).done(function(data) {
                this.data.state = data.state;
                this.sandbox.emit('sulu.header.toolbar.item.enable', 'save', false);
            }.bind(this));
        },

        delete: function() {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'edit');

            CommentManager.delete(this.data.id).done(function() {
                this.sandbox.emit('sulu.header.toolbar.item.enable', 'edit', false);
                CommentRouter.toList();
            }.bind(this));
        },

        save: function(action) {
            this.loadingSave();

            this.saveTab().then(function(data) {
                this.afterSave(action, data);
            }.bind(this));
        },

        setData: function(data) {
            this.data = data;
        },

        saveTab: function() {
            var promise = $.Deferred();

            this.sandbox.once('sulu.tab.saved', function(savedData) {
                this.setData(savedData);

                promise.resolve(savedData);
            }.bind(this));

            this.sandbox.emit('sulu.tab.save');

            return promise;
        },

        enableSave: function() {
            this.sandbox.emit('sulu.header.toolbar.item.enable', 'save', false);
        },

        loadingSave: function() {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'save');
        },

        afterSave: function(action, data) {
            this.sandbox.emit('sulu.header.toolbar.item.disable', 'save', true);
            this.sandbox.emit('sulu.header.saved', data);
        }
    };
});
