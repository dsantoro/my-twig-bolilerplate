define(function(require){
	var Backbone = require('backbone');
	var Twig = require('twig');
	var tpl = require('text!./playlist.twig');
	var template = Twig.twig({data:tpl});

	
	var View = Backbone.View.extend({
		tagName: 'li',
		initialize: function() {
			var that = this;
			that.data = {};

			
			that.listenTo(that.model,'change',that.render);
			that.listenTo(that.model,'remove',that.remove);

			that.render();			
		},
		events: {
		},
		
		render: function() {
			var that = this;

			var data = $.extend(that.data,that.model.toJSON());

			that.$el.html(template.render(data));
		}
	});

	return View;
});