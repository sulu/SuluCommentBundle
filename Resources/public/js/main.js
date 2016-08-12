require.config({
    paths: {
        sulucomment: '../../sulucomment/js',
        sulucommentcss: '../../sulucomment/css'
    }
});

define(function() {

    'use strict';

    return {

        name: 'Sulu Comment Bundle',

        initialize: function(app) {

            app.components.addSource('sulucomment', '/bundles/sulucomment/js/components');

        }
    };
});
