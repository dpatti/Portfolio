$(function(){
	var speed = 'fast';
	var auto_load = function(unique, t){
		//need to use t instead of this because it's an external function
		
		//first, return true if the anchor is flagged for follows
		if ($(t).is(".follow")){
			return true;
		}
		
		//if it's flagged to open as a dialog, do that
		if ($(t).is(".dialog")){
			var href = $(t).attr('href');
			var part = href.split('=')[1];
			$.loadDialog(href+"&ajax", part, { width: '750px', height: '450px', overflow: 'auto' });
			return false;
		}
	
		//if we're open, close
		if ($(t).is(".active")) {
			//close self
			$(t).closest("li").find(".virtual").slideUp(speed);
			$(t).removeClass("active");				
			return false;
		}
		
		//hide all other windows (if unique)
		if (unique) {
			$(t).closest("ul").find(".virtual").slideUp(speed);
			$(t).closest("ul").find(".active").removeClass("active");
		}
		$(t).addClass("active");
		
		//check if there is already active content;
		if($(t).closest("li").find(".virtual").first().slideDown(speed).length == 0){
			$(t).closest("li").append("<div></div>").find(":last").addClass("virtual").load($(t).attr("href")+"&ajax", function(){
				//show loaded content
				$(this).slideDown(speed);
				
				//anchor
				$('html,body').animate({scrollTop: $(this).offset().top-60}, 'medium');
				
				//ajax children
				$(this).find("ul a:not(.year)").add("td a").click(function(){
					return auto_load(false, this);
				});
			});
		} else {
			$('html,body').animate({scrollTop: $(t).closest("li").find(".virtual").first().offset().top-60}, 'medium');
		}
		
		
		return false;
	}
	
	//meeting anchor clicks
	$("#main_content ul a:not(.year)").click(function(){
		return auto_load($(this).closest("ul").is(".meetings"), this);
	});
	
	//year hide/expansion
	$(".years li:not(:first) .meetings").hide();
	$(".years li:first").addClass("active");
	$(".years li .year").click(function(){
		//check if this was active
		var active = $(this).parent().is(".active");
		$(".years li.active .meetings").slideUp(speed);
		$(".years li.active").removeClass("active");
		if(!active){
			//now we can expand
			$(this).parent().addClass("active").find(".meetings").slideDown(speed);
		}
		return false;
	});
});