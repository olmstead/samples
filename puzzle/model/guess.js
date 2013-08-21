// Copyright (c) 2013 John Olmstead, All Rights Reserved
// <author>john olmstead</author>
// <email>john.olmstead@gmail.com/email>
// <date>2013</date>
var Alloy = require('alloy');
exports.definition = {
    config: {
    	"debug": 1,
        "columns": {
        	"id": "TEXT",
        	"puzzle": "TEXT",
        	"answer": "TEXT",
            "source": "TEXT",
            "user_id": "TEXT",
            "user_name": "TEXT",
            "created": "TEXT",     
        },
        "defaults": {
        	"id": null,
        	"puzzle": null,
        	"answer": "",
            "source": "puzlr",
            "user_id": null,
            "user_name": "TEXT",
            "created": null,  
        },
        "adapter": {
            "type": "restapi",
            "collection_name": "guess",
            "idAttribute": "id"
        },
        "URL": Alloy.Globals.puzlsrvr + "/json/guess"
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