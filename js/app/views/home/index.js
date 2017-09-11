define(function(require){
	var Backbone = require('backbone');
	var Twig = require('twig');
	var tpl = require('text!./index.twig');
	var template = Twig.twig({data:tpl});

	//var Playlist = require('app/collections/playlist');

	var View = Backbone.View.extend({
		initialize: function() {
			var that = this;
			that.data = {};

			//that.collection = new Backbone.Collection();
			//that.pagination = new Backbone.Model();

			//that.listenTo(that.collection,'all',that.render);
			//that.listenTo(that.pagination,'all',that.render);
			that.render();			
		},
		events: {
			'submit form': 'search',
			//'click .LoadMore': 'loadMore'
		},
		loadMore: function() {
			/*
			var that = this;
			if (that.Collection != undefined) {
				that.Collection.more();
			}
			*/
		},
		search: function(e) {
			
			e.preventDefault();
			e.stopPropagation();
			var that = this;
			var $form = $(e.currentTarget);
			var q = $form.find('[name=keywords]').val();
			
			/*
			if (search == '') return false;



			that.collection.reset([]);
			that.pagination.set({
				pages: 0,
				page: 0,
				total: 0,
				search: search
			});

			var Collection = Playlist.search(search);
			Collection.on('add',function(model){
				that.collection.add(model);
			});
			that.Collection = Collection;
			var pagination = Collection.pagination();
			pagination.on('change',function(){
				that.pagination.set(pagination.toJSON());
			});
			*/

			Backbone.history.navigate('search?'+$.param({q:q}));

			return false;
		},
		render: function() {
			var that = this;

			var data = $.extend(that.data,{
				//playlists: that.collection.toJSON(),
				//pagination: that.pagination.toJSON()
			});

			that.$el.html(template.render(data));
		}
	});

	return View;
});