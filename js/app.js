requirejs.config({
	baseUrl: 'js/lib',
	paths: {
		'app': '../app',
		'text': 'https://cdn.jsdelivr.net/requirejs.text/2.0.12/text.min',
		'underscore': 'https://cdn.jsdelivr.net/underscorejs/1.8.3/underscore-min',
		'backbone': 'https://cdn.jsdelivr.net/backbonejs/1.3.3/backbone-min',
		'jquery': 'https://cdn.jsdelivr.net/jquery/3.1.1/jquery.min'
	},
	shim: {
		backbone: ['jquery','underscore']
	}
});

require(['app/main'],function(App){
	App.init();
});