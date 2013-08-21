function S4() {
    return ((1 + Math.random()) * 65536 | 0).toString(16).substring(1);
}

function guid() {
    return S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4();
}

function InitAdapter(config) {
    return {};
}

function apiCall(_options, _callback) {
    var xhr = Ti.Network.createHTTPClient({
        timeout: 5000
    });
    Ti.API.info("Connecting to service at: " + _options.url);
    //Prepare the request
    xhr.open(_options.type, _options.url);

    xhr.onload = function() {
        _callback({
            success: true,
            status: xhr.status == 200 ? "ok" : xhr.status,
            code: xhr.status,
            responseText: xhr.responseText || null,
            responseData: xhr.responseData || null
        });
    };

    //Handle error
    xhr.onerror = function(e) {
        _callback({
            success: false,
            status: "error",
            code: xhr.status,
            data: e.error,
            responseText: xhr.responseText
        });
        Ti.API.error('[REST API] apiCall ERROR: ' + xhr.responseText)
    }
    for (var header in _options.headers) {
        xhr.setRequestHeader(header, _options.headers[header]);
    }

    if (_options.beforeSend) {
        _options.beforeSend(xhr);
    }

    xhr.send(_options.data || null);
}

function Sync(method, model, opts) {
    var DEBUG = model.config.debug;
    model.idAttribute = model.config.adapter.idAttribute || "id";
    var methodMap = {
        'create': 'POST',
        'read': 'GET',
        'update': 'PUT',
        'delete': 'DELETE'
    };

    var type = methodMap[method];
    var params = _.extend({}, opts);
    params.type = type;

    //set default headers
    params.headers = params.headers || {};

    // We need to ensure that we have a base url.
    if (!params.url) {
        params.url = (model.config.URL || model.url());
        if (!params.url) {
            Ti.API.error("[REST API] ERROR: NO BASE URL");
            return;
        }
    }

    // For older servers, emulate JSON by encoding the request into an HTML-form.
    if (Alloy.Backbone.emulateJSON) {
        params.contentType = 'application/x-www-form-urlencoded';
        params.processData = true;
        params.data = params.data ? {
            model: params.data
        } : {};
    }

    // For older servers, emulate HTTP by mimicking the HTTP method with `_method`
    // And an `X-HTTP-Method-Override` header.
    if (Alloy.Backbone.emulateHTTP) {
        if (type === 'PUT' || type === 'DELETE') {
            if (Alloy.Backbone.emulateJSON)
                params.data._method = type;
            params.type = 'POST';
            params.beforeSend = function(xhr) {
                params.headers['X-HTTP-Method-Override'] = type
            };
        }
    }

    //json data transfers
    params.headers['Content-Type'] = 'application/json';

    if (DEBUG) {
        Ti.API.debug("[REST API] REST METHOD: " + method);
    }
    switch (method) {

        case 'delete' :
            if (!model[model.idAttribute]) {
                params.error(null, "MISSING MODEL ID");
                Ti.API.error("[REST API] ERROR: MISSING MODEL ID");
                return;
            }
            params.url = params.url + '/' + model[model.idAttribute];

            if (DEBUG) {
                Ti.API.info("[REST API] options: ");
                Ti.API.info(params);
            }
            apiCall(params, function(_response) {
                if (_response.success) {
                    var data = JSON.parse(_response.responseText);
                    if (DEBUG) {
                        Ti.API.info("[REST API] server delete response: ");
                        Ti.API.info(data)
                    }
                    params.success(null, _response.responseText);
                    model.trigger("fetch");
                    // fire event
                } else {
                    params.error(JSON.parse(_response.responseText), _response.responseText);
                    Ti.API.error('[REST API] DELETE ERROR: ');
                    Ti.API.error(_response);
                }
            });
            break;
        case 'create' :
            // convert to string for API call
            params.data = JSON.stringify(model.toJSON());
            if (DEBUG) {
                Ti.API.info("[REST API] options: ");
                Ti.API.info(params);
            }
            apiCall(params, function(_response) {
                if (_response.success) {
                    var data = JSON.parse(_response.responseText);
                    if (DEBUG) {
                        Ti.API.info("[REST API] server create response: ");
                        Ti.API.info(data)
                    }
                    //Rest API should return a new model id.
                    if (data[model.idAttribute] == undefined) {
                        data[model.idAttribute] = guid();
                        //if not - create one
                    }
                    params.success(data, JSON.stringify(data));
                    model.trigger("fetch");
                    // fire event
                } else {
                    params.error(JSON.parse(_response.responseText), _response.responseText);
                    Ti.API.error('[REST API] CREATE ERROR: ');
                    Ti.API.error(_response);
                }
            });
            break;
        case 'update' :
            if (!model[model.idAttribute]) {
                params.error(null, "MISSING MODEL ID");
                Ti.API.error("[REST API] ERROR: MISSING MODEL ID");
                return;
            }

            // setup the url & data
            params.url = params.url + '/' + model[model.idAttribute];
            params.data = JSON.stringify(model.toJSON());
            if (DEBUG) {
                Ti.API.info("[REST API] options: ");
                Ti.API.info(params);
            }
            apiCall(params, function(_response) {
                if (_response.success) {
                    var data = JSON.parse(_response.responseText);
                    if (DEBUG) {
                        Ti.API.info("[REST API] server update response: ");
                        Ti.API.info(data)
                    }
                    params.success(data, JSON.stringify(data));
                    model.trigger("fetch");
                } else {
                    params.error(JSON.parse(_response.responseText), _response.responseText);
                    Ti.API.error('[REST API] UPDATE ERROR: ');
                    Ti.API.error(_response);
                }
            });
            break;

        case 'read':
            if (params[model.idAttribute]) {
                params.url = params.url + '/' + params[model.idAttribute];
            }

            if (params.urlparams) {
                params.url += "?" + encodeData(params.urlparams);
            }
            if (DEBUG) {
                Ti.API.info("[REST API] options: ");
                Ti.API.info(params);
                Ti.API.info("[REST API] url: " + params.url);
            }
            apiCall(params, function(_response) {
                if (_response.success) {
                    var data = JSON.parse(_response.responseText);
                    if (DEBUG) {
                        Ti.API.info("[REST API] server read response: ");
                        Ti.API.info(data)
                    }
                    var values = [];
                    model.length = 0;
                    for (var i in data) {
                        var item = {};
                        item = data[i];
                        if (item[model.idAttribute] == undefined) {
                            item[model.idAttribute] = guid();
                        }
                        values.push(item);
                        model.length++;
                    }

                    params.success((model.length === 1) ? values[0] : values, _response.responseText);
                    model.trigger("fetch");
                } else {
                    params.error(JSON.parse(_response.responseText), _response.responseText);
                    Ti.API.error('[REST API] READ ERROR: ');
                    //Ti.API.error(JSON.parse(_response.responseText));
                }
            })
            break;
    }

}
;
var encodeData = function(obj) {
    var str = [];
    for (var p in obj)
        str.push(Ti.Network.encodeURIComponent(p) + "=" + Ti.Network.encodeURIComponent(obj[p]));
    return str.join("&");
}
//we need underscore
var _ = require("alloy/underscore")._;

//until this issue is fixed: https://jira.appcelerator.org/browse/TIMOB-11752
var Alloy = require("alloy"), Backbone = Alloy.Backbone;

module.exports.sync = Sync;

module.exports.apiCall = Sync;

module.exports.beforeModelCreate = function(config, name) {
    config = config || {};
    InitAdapter(config);
    return config;
};

module.exports.afterModelCreate = function(Model, name) {
    Model = Model || {};
    Model.prototype.config.Model = Model;
    Model.prototype.idAttribute = Model.prototype.config.adapter.idAttribute;
    return Model;
};
