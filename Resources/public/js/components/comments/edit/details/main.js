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
    'services/sulucomment/comment-manager',
    'text!/admin/comments/comment-template/details.html'
], function(_, $, CommentManager, form) {

    return {

        defaults: {
            templates: {
                form: form
            },
            translations: {
                title: 'public.title',
                message: 'sulu_comment.list.message'
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
            CommentManager.save(data).then(function(data) {
                this.saved(data);
            }.bind(this));
        },

        getTemplate: function() {
            return this.templates.form({translations: this.translations, translate: this.sandbox.translate});
        },

        getFormId: function() {
            return '#comment-form';
        }
    };
});
