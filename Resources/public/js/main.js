/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require.config({
    paths: {
        sulucomment: '../../sulucomment/js'
    }
});

define({

    name: "SuluCommentBundle",

    initialize: function(app) {

        'use strict';

        var sandbox = app.sandbox;

        app.components.addSource('sulucomment', '/bundles/sulucomment/js/components');

        // Example: list all contacts
        // sandbox.mvc.routes.push({
        //     route: 'contacts/contacts',
        //    callback: function(){
        //         this.html('<div data-aura-component="contacts@sulucontact" data-aura-display="list"/>');
        //     }
        // });
    }
});
