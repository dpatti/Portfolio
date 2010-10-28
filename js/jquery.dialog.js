/*
 * jQuery Dialog Boxes
 *
 */
(function($){
 	var MAX_WIDTH = 950;
	var MAX_HEIGHT = 575;
	//inserts content into a dialog box of specified width and height (or tries to get content area's)
	$.fn.showDialog = function(){
		if(!$('.underlay').length){
			$("body").append("<div class='underlay'></div>").find('.underlay').click(function(){
				$(".overlay").hideDialog();			
			});
		}
		if(!$('.overlay').length){
			$("body").appendDialog();
		}
		
		//hide any old dialogs
		$(".overlay .container").children(":not(.close)").hide();
		
		//move in new box, try default width/height, then try parameter
		$(this).show();
		$(".overlay .container").append($(this));
		$(".overlay").css({
			width: $(this).css('width'),
			height: $(this).css('height'),
			marginLeft: -parseInt($(this).css('width'), 10)/2,
			marginTop: -parseInt($(this).css('height'), 10)/2,
			left: '50%',
			top: $(window).scrollTop()+$(window).height()/2,
		}).each(function(){
			$(document).scroll();
			if($.browser.msie){
				$(this).show();
			} else {
				$(this).fadeIn('fast');
			}
		});
		$('.underlay').css({top: $(window).scrollTop()}).show();	
	};
	$.fn.hideDialog = function(){
		$('.overlay').each(function(){
			if($.browser.msie){
				$(this).hide();
			} else {
				$(this).fadeOut('fast');
			}
		});
		$('.underlay').hide();
	}
	$.fn.appendDialog = function(){
		//takes a node (body) and appends the required elements to wrap a dialog
		$(this).append("<div class='overlay'><div class='container'><div class='close'><a href='javascript:;'>Close this window</a></div></div></div>");
		$(this).find(".close a").click(function(){
			$(this).hideDialog();
		});		
	}
	
	//scroll event
	$(document).scroll(function(){
		$('.overlay').css({top: $(window).scrollTop()+$(window).height()/2, left: $(window).scrollLeft()+$(window).width()/2});
		$('.underlay').css({top: $(window).scrollTop(), left: $(window).scrollLeft()});
	});
	
	//ajax registration
	$.extend({
		//url : url of the dialog to load
		//name : identifier so we don't reload each time (id: ajax-[name])
		//[css] : width and height information, or anything else
		//[callback] : function executed every display
		//[firstRun] : function executed on load
		loadDialog: function(url, name, css, callback, firstRun){
			name = "ajax-"+name;
			if (css && typeof(css) == "function") {
				firstRun = callback;
				callback = css;
				css = {};
			}
			css = css || {};
			css.width = css.width || '500px';
			css.height = css.height || '250px';
			if($("#"+name).length == 0){
				$.addDialog(name).load(url, function(){
					if(firstRun)
						firstRun();
					if(callback)
						callback();
					$(this).showDialog();
				}).css(css);
			} else {
				if(callback)
					callback();
				$("#"+name).showDialog();
			}
		},
		addDialog: function(name, innerHTML){
			return $("body").append('<div id="'+name+'">'+(innerHTML||"")+'</div>').children().last().hide()
		},
		loadImage: function(url, name, css, callback, firstRun){
			name = "img-"+name;
			if (css && typeof(css) == "function") {
				firstRun = callback;
				callback = css;
				css = {};
			}
			css = css || {};
			if($("#"+name).length == 0){
				$("body").append('<div id="'+name+'"></div>').children().last().hide().append('<img src="'+url+'">').css(css).find("img").load(function(){
					var tImg = this;
					setTimeout(function(){
						// adjust our max values if the browser is tiny
						var max_w = $(window).width()*.8<MAX_WIDTH ? $(window).width()*.8 : MAX_WIDTH;
						var max_h = $(window).height()*.8<MAX_HEIGHT ? $(window).height()*.8 : MAX_HEIGHT;
						//get current values
						var width = $("#"+name).width();
						var height = $("#"+name).height();
						//check max/min
						var w_ratio = width / max_w;
						var h_ratio = height / max_h;
						//alert("values: "+width+", "+height);
						//alert("ratios: "+w_ratio+", "+h_ratio);
						if(width > max_w && w_ratio > h_ratio){
							//alert("max width; "+max_w+", "+height/w_ratio);
							$(tImg).width(max_w);
							$(tImg).height(height/w_ratio);
						} else if(height > max_h && h_ratio > w_ratio){
							//alert("max height; "+width/h_ratio+", "+max_h);
							$(tImg).width(width/h_ratio);
							$(tImg).height(max_h);							
						}
						$("#"+name).css({
							width: $("#"+name).width(),
							height: $("#"+name).height()+15,
							cursor: 'pointer',
						}).click(function(){
							$(tImg).hideDialog();
						});
						if(firstRun)
							firstRun();
						if(callback)
							callback();
						$("#"+name).showDialog();
						if($("#rawImg").length == 0){
							$(".overlay .container").append('<div id="rawImg"><a href="javascript:;" target="_blank">View plain image</a></div>');
						}
						$(".overlay #rawImg").show().find("a").attr("href", url);
					}, 1);
				});
			} else {
				if(callback)
					callback();
				$("#"+name).showDialog();
				if($("#rawImg").length == 0){
					$(".overlay .container").append('<div id="rawImg"><a href="javascript:;" target="_blank">View plain image</a></div>');
				}
				$(".overlay #rawImg").show().find("a").attr("href", url);
			}
		},
	});
	
})(jQuery);