$(function(){
	var dir = 1; // increment in files array
	var timeout = 8000; // time to rotate in ms
	var throttle = 800; // time in ms before you can scroll again
	var lastScroll = 0;
	var cur = 0;
	var files = [
		"img/slideshow.jpg",	
		"img/slideshow2.jpg",	
		"img/slideshow3.jpg"		// can't end this with a comma because IE is so pro
	];
	var fileObjs = [];
	
	// no need to run if we don't have anything to showcase
	if (files.length < 2) {
		return;
	}

	var scrollShow = function(dir){
		// check scroll status
		var now = (new Date()).getTime();
		if (now-lastScroll < throttle) {
			return;
		}
		lastScroll = now;
		
		var next, open = fileObjs[cur];
		cur = (cur + dir) % files.length;
		if (cur < 0)
			cur = files.length + cur;
		next = fileObjs[cur];
		
		// is our current the stock slideshow image?
		if ($("#pres-default").is(":visible")) {
			open = $("#pres-default");
		}
		
		// start animation on current
		open.stop();
		open.clearQueue();
		open.css({
			left: 0,
			opacity: 1,
		});
		open.fadeOut(1000);
		
		// set position and start animation on next
		next.stop();
		next.clearQueue();
		next.css({
			left: 960*dir,
			opacity: 0,
		});
		next.show();
		next.animate({
			left: 0,
			opacity: 1,
		}, 800);
		
		// restart timer
		timer.start();
	};
	
	// pre-load images
	for(var i=0;i<files.length;i++){
		fileObjs[i] = $("#presentation").append("<img id='pres-"+i+"' src='"+files[i]+"'>").find("#pres-"+i).hide();
	}
	
	// attach arrow buttons
	var leftButton = $("#presentation").append("<div id='pres-left'>").find("#pres-left").click(function(){
		scrollShow(dir=-1);
	});
	var rightButton = $("#presentation").append("<div id='pres-right'>").find("#pres-right").click(function(){
		scrollShow(dir=1);
	});
		
	// timer control
	var timer = {
		ref: null,
		start: function(callback){
			if (this.ref)
				clearTimeout(this.ref);
			
			this.ref = setTimeout(this.callback, timeout);
		},
		callback: function(){
			scrollShow(dir);
			timer.start();
		},
	};
	timer.start();
});