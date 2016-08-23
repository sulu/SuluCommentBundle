require.config({
    paths: {
        sulucomment: '../../sulucomment/js',
        sulucommentcss: '../../sulucomment/css',

        'services/sulucomment/comment-manager': '../../sulucomment/js/services/comments/manager',
        'services/sulucomment/comment-router': '../../sulucomment/js/services/comments/router',

        'services/sulucomment/thread-manager': '../../sulucomment/js/services/threads/manager',
        'services/sulucomment/thread-router': '../../sulucomment/js/services/threads/router'
    }
});

define(function() {

    'use strict';

    return {

        name: 'Sulu Comment Bundle',

        initialize: function(app) {

            app.components.addSource('sulucomment', '/bundles/sulucomment/js/components');

            app.sandbox.mvc.routes.push({
                route: 'comments',
                callback: function() {
                    return '<div data-aura-component="comments/list@sulucomment"/>';
                }
            });
            app.sandbox.mvc.routes.push({
                route: 'comments/edit::id/:content',
                callback: function(id, content) {
                    return '<div data-aura-component="comments/edit@sulucomment" data-aura-id="' + id + '"/>';
                }
            });

            app.sandbox.mvc.routes.push({
                route: 'threads',
                callback: function() {
                    return '<div data-aura-component="threads/list@sulucomment"/>';
                }
            });
            app.sandbox.mvc.routes.push({
                route: 'threads/edit::id/:content',
                callback: function(id, content) {
                    return '<div data-aura-component="threads/edit@sulucomment" data-aura-id="' + id + '"/>';
                }
            });
        }
    };
});
