/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['underscore', 'jquery', 'services/husky/util'], function(_, $, Util) {

    'use strict';

    var instance = null,

        getInstance = function() {
            if (instance === null) {
                instance = new ThreadManager();
            }
            return instance;
        },

        url = _.template('/admin/api/threads<% if (typeof id !== "undefined") { %>/<%= id %><% } %>');

    /** @constructor **/
    function ThreadManager() {
    }

    ThreadManager.prototype = {
        load: function(id) {
            return Util.load(url({id: id}));
        },
        save: function(data) {
            return Util.save(url({id: data.id}), !!data.id ? 'PUT' : 'POST', data);
        },
        delete: function(id) {
            return Util.save(url({id: id}), 'DELETE');
        },
        deleteMultiple: function(ids) {
            return Util.save(url() + '?ids=' + ids.join(','), 'DELETE');
        },
        url: url
    };

    return getInstance();
});
