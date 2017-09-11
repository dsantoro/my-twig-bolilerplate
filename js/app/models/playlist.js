define(function(require){
	var $ = require('jquery');
	var Backbone = require('backbone');
	var Youtube = require('youtube');

	var Model = Backbone.Model.extend({
		videos: function() {
			var model = this;
			
			var pageToken = null;		
			var loading = false;
			var page = 0;
			var Pagination = new Backbone.Model({
				total: 0,
				pages: 0,
				page: 0
			});

			var Collection = Backbone.Collection.extend({				
				pagination: function() {
					return Pagination;
				},
				more: function() {
					var that = this;

					if (loading) return;
					loading = true;
					Youtube.done(function(err,youtube){
						youtube.playlistItems.list({
							playlistId: model.get('id'),
							part: 'id,snippet,contentDetails,status',
							maxResults: 50,
							pageToken: pageToken
						}).execute(function(resp){
							var itens = resp.items.map(function(item){
								
								var row = {
									id: item.id,
									description: item.snippet.description,
									title: item.snippet.title,
									thumbnails: item.snippet.thumbnails,
									playlistId: item.snippet.playlistId,
									videoId: item.contentDetails.videoId

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
		}
	});
	return Model;
});