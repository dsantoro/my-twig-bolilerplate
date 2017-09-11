define(function(require){
	var $ = require('jquery');
    var gapi = require('gapi');
    var Config = require('app/config');

	var dfd = $.Deferred();
    
    gapi.load('client',function(){
        gapi.client.setApiKey(Config.google.youtube.api_key);        
        dfd.resolve(null,gapi.client);
    });

	return dfd.promise();
});