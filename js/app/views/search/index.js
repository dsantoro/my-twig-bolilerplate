define(function(require){
	var Backbone = require('backbone');
	var Twig = require('twig');
	var tpl = require('text!./index.twig');
	var template = Twig.twig({data:tpl});

	var Playlist = require('app/collections/playlist');
	var PlaylistView = require('./playlist');

	var View = Backbone.View.extend({
		initialize: function(config) {
			var that = this;
			that.data = {};

			that.collection = Playlist.search(config.keywords);
			that.pagination = that.collection.pagination();
			that.listenTo(that.collection,'add',that.addPlaylist);

			that.render();			
			that.$el.css({
				width: "100%",
				height: "100%",
				'overflow-x': 'hidden',
				'overflow-y': 'auto'
			})
		},
		events: {
			'scroll': 'scroll'
		},
		scroll: function(e) {
			var that = this;
			var scrollHeight = that.$el[0].scrollHeight;
			var scrollPos = that.$el.scrollTop()+that.$el.height();
			if (scrollHeight - scrollPos < scrollHeight/2) {
				that.loadMore();
			}
		},
		addPlaylist: function(playlist) {
			var that = this;
			var view = new PlaylistView({
				model: playlist
			});

			that.$el.find('ul.playlists').append(view.$el); 
		},
		loadMore: function() {
			var that = this;
			if (that.pagination.get('page') >= that.pagination.get('pages')) return;
			that.collection.more();			
		},
		render: function() {
			var that = this;

			var data = $.extend(that.data,{
			});

			that.$el.html(template.render(data));
			that.collection.each(function(playlist){
				that.addPlaylist(playlist);
			});
		}
	});

	return View;
});