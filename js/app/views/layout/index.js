define(function(require){
	var Backbone = require('backbone');
	var Twig = require('twig');
	var tpl = require('text!./index.twig');
	var template = Twig.twig({data:tpl});

	var View = Backbone.View.extend({
		el: 'body',
		initialize: function() {
			var that = this;
			that.data = {};
			that.render();
		},
		render: function() {
			var that = this;

			var data = $.extend(that.data,{

			});

			that.$el.html(template.render(data));
		}
	});

	return new View();
});