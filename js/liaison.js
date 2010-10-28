$(function(){
	// set anchors to top of page
	$("a[name]").css({
		position: 'absolute',
		top: '0px',
	});
	
	// index
	if (document.location.hash) {
		$("fieldset.club-details:not(.c-"+document.location.hash.substr(1)+")").hide();
	} else {
		$("fieldset.club-details:not(:first)").hide();
	}
	$("#jump-menu a").click(function(){
		var id = "fieldset.c-"+$(this).attr("hash").substr(1);
		if (!$(id).is(":visible")) {
			$("fieldset.club-details").slideUp('fast');
			$(id).slideDown('fast');
		}
		document.location.hash = $(this).attr("hash").substr(1);
		return false;
	});
	
	
	// editing
	$(".input-amt input").each(function(){
		if($(this).attr("name") == "total_amt"){
			$(this).attr("disabled", "disabled");
		} else {
			$(this).click(function(){
				$(this).select();
			}).change(function(){
				//set fixed point on this
				$(this).val(parseFloat($(this).val(), 10).toFixed(2));
				
				//get total
				var total = 0;
				$(".input-amt input[name!=total_amt]").each(function(){ // $(".input-amt input[name!=total_amt]").each(function(){
					total += parseFloat($(this).val(), 10);
				});
				
				if($(this).attr("name") != "total_amt"){
					//update total
					$("input[name=total_amt]").val(total.toFixed(2));
				} else {
					//check total
				}
			});
		}
	});
	$("form").submit(function(){
		$("input[disabled]").attr("disabled", "");

	});
});