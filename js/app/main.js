define(function(require){
	var App = {
		init: function() {
			require("app/views/layout/index");
			require('app/router');
			Backbone.history.start();
		}
	};
	return App;
});