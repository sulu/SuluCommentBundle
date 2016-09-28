/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'underscore',
    'jquery',
    'services/sulucomment/thread-manager',
    'text!/admin/comments/thread-template/details.html'
], function(_, $, Manager, form) {

    return {

        defaults: {
            templates: {
                form: form
            },
            translations: {
                title: 'public.title'
            }
        },

        type: 'form-tab',

        tabInitialize: function() {
            this.sandbox.on('sulu.content.changed', this.setDirty.bind(this));
        },

        parseData: function(data) {
            return data;
        },

        save: function(data) {
            Manager.save(data).then(function(data) {
                this.saved(data);
            }.bind(this));
        },

        getTemplate: function() {
            return this.templates.form({translations: this.translations, translate: this.sandbox.translate});
        },

        getFormId: function() {
            return '#thread-form';
        }
    };
});
