define(function(require){
	var Backbone = require('backbone');
	var Twig = require('twig');
	var tpl = require('text!./index.twig');
	var template = Twig.twig({data:tpl});


	var View = Backbone.View.extend({
		initialize: function(config) {
			var that = this;
			that.data = {
				videoId: config.id
			};

			

			that.render();			
			
		},
		events: {
			
		},
		
		render: function() {
			var that = this;

			var data = $.extend(that.data,{

			});

			that.$el.html(template.render(data));
		}
	});

	return View;
});