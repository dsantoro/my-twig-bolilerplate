define(function(require){
	var $ = require('jquery');
	var Backbone = require('backbone');
	var Youtube = require('youtube');
	var Model = require('app/models/playlist');

	var search = function(keywords) {

		var pageToken = null;		
		var loading = false;
		var page = 0;
		var Pagination = new Backbone.Model({
			total: 0,
			pages: 0,
			page: 0
		});

		var Collection = Backbone.Collection.extend({
			model: Model,
			pagination: function() {
				return Pagination;
			},
			more: function() {
				var that = this;

				if (loading) return;
				loading = true;
				Youtube.done(function(err,youtube){
					youtube.search.list({
						q: keywords,
						part: 'id,snippet',
						maxResults: 50,
						type: 'playlist',
						pageToken: pageToken,
						order: 'relevance'
					}).execute(function(resp){
						var itens = resp.items.map(function(item){
							
							var row = {
								id: item.id.playlistId,
								description: item.snippet.description,
								title: item.snippet.title,
								thumbnails: item.snippet.thumbnails
							};
							return row;
						});
						that.add(itens);
						pageToken = resp.nextPageToken;		
						loading = false;	
						var total = resp.pageInfo.totalResults;
						var pages = Math.ceil(total/50);
						page++;
						Pagination.set({
							total: total,
							pages: pages,
							page: page
						});
					});
				});
			}
		});

		var collection = new Collection();
		collection.more();

		return collection;
	};

	return {
		search: search
	};
});