$(function(){
	// node clicked
	$("#nav a.drop").hover(function(){
		$(this).parent().find("ul.drop").fadeIn(100).show();
		$(this).parent().hover(function(){}, function(){
			$(this).parent().find("ul.drop").fadeOut(100).hide();
		});
		$(this).addClass("hover");
	}, function(){
		$(this).removeClass("hover");
	});
	$("#nav a").hover(function(){
		$(this).animate({
			backgroundColor: '#0f2954',
		}, 100);
	}, function(){
		$(this).animate({
			backgroundColor: '#0f2954',
		}, 100);
	});
});
/*TODO: delete this file? */