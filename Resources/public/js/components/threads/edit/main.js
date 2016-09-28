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
    'services/sulucomment/thread-manager',
    'services/sulucomment/thread-router'
], function($, Manager, Router) {

    'use strict';

    return {

        defaults: {
            translations: {
                headline: 'sulu_comment.threads'
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
                }
            };

            return {
                title: function() {
                    return this.translations.headline;
                }.bind(this),

                tabs: {
                    url: '/admin/content-navigations?alias=threads',
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
            Manager.load(this.options.id).done(function(data) {
                promise.resolve(data);
            });

            return promise;
        },

        initialize: function() {
            this.bindCustomEvents();
        },

        bindCustomEvents: function() {
            this.sandbox.on('sulu.header.back', Router.toList);
            this.sandbox.on('sulu.tab.dirty', this.enableSave.bind(this));
            this.sandbox.on('sulu.toolbar.save', this.save.bind(this));
            this.sandbox.on('sulu.tab.data-changed', this.setData.bind(this));
        },

        delete: function() {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'edit');

            Manager.delete(this.data.id).done(function() {
                this.sandbox.emit('sulu.header.toolbar.item.enable', 'edit', false);
                Router.toList();
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
