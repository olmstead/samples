// Copyright (c) 2013 John Olmstead, All Rights Reserved
// <author>john olmstead</author>
// <email>john.olmstead@gmail.com/email>
// <date>2013</date>
var Alloy = require('alloy');

exports.definition = {
    config: {
    	//"debug": 1,
        "columns": {
            "source": "TEXT",
            "status": "Integer",
            "id": "TEXT",
            "points": "Integer",
            "name": "TEXT",
            "created": "TEXT",
            "width": "Integer",
            "height": "Integer",
            "content_type": "TEXT",
            "url": "TEXT",
            "user_id": "TEXT",
            "user_name": "TEXT",
            "days": "Integer",
            "guesses": "Integer",
            "points": "Integer"
        },
        "defaults": {
            "source": "direct",
            "status": 0,
            "id": null,
            "points": 1,
            "created": null,
            "width": 320,
            "height": 320,
            "content_id": null,
            "url": "",
            "user_id": null,
            "user_name": "",
            "days": 0,
            "guesses": 0,
            "points": 10
        },
        "adapter": {
            "type": "restapi",
            "collection_name": "puzzle",
            "idAttribute": "id"
        },
        "URL": Alloy.Globals.puzlsrvr + "/json/puzzle"
	},
    extendModel: function(Model) {
        _.extend(Model.prototype, {});
        return Model;
    },
    extendCollection: function(Collection) {
        _.extend(Collection.prototype, {});
        return Collection;
    }
}