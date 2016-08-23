/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['services/husky/util', 'services/husky/mediator'], function(Util, Mediator) {

    'use strict';

    var instance = null,

        getInstance = function() {
            if (instance === null) {
                instance = new CommentRouter();
            }

            return instance;
        },

        navigate = function(route) {
            Mediator.emit('sulu.router.navigate', route, true, true);
        };

    /** @constructor **/
    function CommentRouter() {
    }

    CommentRouter.prototype = {
        toList: function() {
            navigate('comments');
        },
        toEdit: function(id, content) {
            navigate('comments/edit:' + id + '/' + (content || 'details'));
        }
    };

    return getInstance();
});
