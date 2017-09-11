define(function(require){
	var $ = require('jquery');
	var dfd = $.Deferred();
	require('gapi.client').done(function(err,client){
	    client.load('youtube', 'v3', function(){
	        dfd.resolve(null,client.youtube);
	    });
	});
	return dfd.promise();
});