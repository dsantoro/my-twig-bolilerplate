define(function(require){
	var Backbone = require('backbone');

	var Router = Backbone.Router.extend({
		routes: {
			'': 'home',
			'home': 'home',
			'search?q=:keywords': 'search',
			'playlist/:id': 'playlist',
			'player/:id': 'player',
		},
		execute: function(callback, args,rota) {
			var that = this;
			if (that.view != undefined) {
				that.view.$el.remove();
				that.view.remove();
			}
			window.setTimeout(function(){
				callback.apply(that,args);				
			})
		},
		home: function() {
			var that = this;
			var View = require('app/views/home/index');
			that.view = new View();
			that.view.$el.appendTo('.content');
		},
		search: function(keywords) {
			var that = this;
			var View = require('app/views/search/index');
			that.view = new View({
				keywords: keywords
			});
			that.view.$el.appendTo('.content');
		},
		playlist: function(id) {
			var that = this;
			var View = require('app/views/playlist/index');
			that.view = new View({
				id: id
			});
			that.view.$el.appendTo('.content');
		},
		player: function(id) {
			var that = this;
			var View = require('app/views/player/index');
			that.view = new View({
				id: id
			});
			that.view.$el.appendTo('.content');
		}
	});
	return new Router();
});