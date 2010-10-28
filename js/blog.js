$(function(){
	var highlight = function(hash){
		hash = hash.substr(1);
		if(hash>0){
			$("#comments .highlighted").removeClass("highlighted");
			$("a[name="+hash+"]").next().addClass("highlighted");
		}
	};
	
	$("#comments .comment").hover(function(){
		$(this).children(".actions").fadeIn(150);
	}, function(){
		$(this).children(".actions").fadeOut(150);	
	});
	
	$("#comments .actions").hide();
	$("#comments .actions a:first-child").click(function(){
		//Permalink
		var hash = this.hash;
		$('html,body').animate({scrollTop: $("[name="+this.hash.substr(1)+"]").offset().top}, 'medium', function(){
			document.location.hash = hash;
		});
		highlight(this.hash);
		return false;
	}).next().click(function(){
		//Quote
		var name = $(this).closest(".comment").find(".author").text();
		var id = $(this).closest(".comment").prev().attr("name");
		var contents = $(this).closest(".comment").children(".post-guts").text();
		$("[name=contents]").val($("[name=contents]").val() + "[quote="+name+";"+id+"]"+contents+"[/quote]\n\n");
	}).next().click(function(){
		//Report
		var clicked = this;
		$.get(
			'blog.php',
			{
				action: 'report',
				cid: $(this).parent().children(":first").attr('hash').substr(1),
				ajax: true,
			},
			function(data){
				if(!data){
					alert("Unexpected AJAX error!");
				} else {
					if(data.success){
						$(clicked).hide().after("<span>Reported</span>");
					} else {
						alert("Error: "+data.message);
					}
				}
			},
			'json'
		);
		return false;
	});
	$("a").click(function(){
		if(this.hash){
			highlight(this.hash);
		}
	});
	
	highlight(document.location.hash);
});