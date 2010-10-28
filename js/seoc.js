$(function(){
	$(".datatable a.del").click(function(){
		if(confirm("Are you sure you want to delete this item? This cannot be undone.")){
			return true;
		}
		return false;
	});
	
	$("select[name=year]").change(function(){
		var query = document.location.search;
		if(query.indexOf("year=") != -1){
			query = query.replace(/year=\d+/, "year="+$(this).val());
		} else if(query == "") {
			query = "?year="+$(this).val();
		} else {
			query += "&year="+$(this).val();
		}
		document.location.search = query;
	});
	
	
	//table inline edit panel
	var doEdit = function(focus){
		var parent = (focus instanceof jQuery) ? focus.parent() : $(this).closest("tr");
		
		//check we aren't already editing
		if(parent.is(".lock"))
			return false;
			
		parent.addClass("lock");
		//parent.find("td").unbind('click');
		
		//get all values if we must restore
		var state = [];
		parent.find("td:not(:has(.edit)):not(:has(.del))").each(function(){
			state.push($(this).html());
		});
		
		//functions for multiple reference points
		var save = function(){
			var inputs = {
				action: "update",
				club: parent.attr("id").split('-')[1],
				ajax: true
			};
			var data = [];
			parent.find("td :input:not([type=button])").each(function(){
				var val = $(this).val();
				var name = $(this).attr("name");
				//check if exists, then append
				if(inputs[name]){
					if(typeof(inputs[name]) == "object")
						inputs[name].push(val);
					else
						inputs[name] = [inputs[name], val]
				} else {
					inputs[name] = val;
				}
				if($(this).is("select"))
					val = $(this).find(":selected").text();
				data.push(val);
			});
			
			//ajax
			$.get(
				'seoc.php',
				inputs,
				function(data){
					if(!data || !data.success){
						alert(data.message || "Error!");
					}
				},
				'json'
			);
			
			//quickly restore the contact merged data
			data[1] = [data[1], data.splice(2, 1)[0]];
			restore(data);
		}
		var cancel = function(){
			restore(state);
		}
		var restore = function(data){
			//get things back to text
			parent.find("td").each(function(){
				if(data.length > 0){
					var seg = data.splice(0, 1)[0];
					if(typeof(seg) == "object"){
						//array means we need to wrap it inside divs
						var i = 0;
						seg = $(this).html().replace(/(<div.*?>)(.+?)(<\/div>)/g, function(match, op, mid, end){
							return op+seg[i++]+end;
						});
					}
					$(this).html(seg);
				}
			});
			
			//restore edit and del
			parent.find(".edit").show().next().remove();
			parent.find(".del").show().next().remove();
			
			parent.removeClass("lock"); //clear lock
			parent.find("td").mouseleave(); // clear hovers
			//restore clickability after a timer because if we're clicking cancel it will re-register immediately
			//dont need to, we just don't bind click to the last two cells
			/*setTimeout(function(){
				parent.find("td").click(function(){
					//doEdit($(this));
					alert("faggotry");
				});
			}, 1);*/
		}
		var makeSelect = function(name, source, cur){
			var out = '<select name="' + name + '">';
			for(var i in source){
				out += "\n\t<option value=\"" + source[i].id + "\"" + (source[i].name==cur ? ' selected="selected"' : '') + ">" + source[i].name + "</option>";
			}
			out += "\n</option>";
			return out;
		}
		var makeText = function(name, cur){
			return '<input type="text" name="'+name+'" value="'+cur+'">';
		}
		
		//re-write Edit and Delete with Save and Cancel
		parent.find(".edit").hide().after('<input type="button" value="Save">').next().click(save);
		parent.find(".del").hide().after('<input type="button" value="Cancel">').next().click(cancel);
		
		
		//change each item into an input element
		//hard coding these because there are so many exceptions it's not worth doing something intelligent
		var trans = [
			//club name
			function(data){
				return makeText('club_name', data);
			},
			//contact
			function(data){
				return data.replace(/(<div.*?>)(.+?)(<\/div>)/g, '$1<input type="text" name="club_contact[]" value="$2">$3');
			},
			//status
			function(data){
				return makeSelect('club_active', [ {id:0, name:"Inactive"}, {id:1, name:"Active"} ], data);
			},
			//liaison
			function(data){
				return makeSelect('club_liaison', liaisons, data);
			},
			//space
			function(data){
				return makeText('club_space', data);
			},
			//locker
			function(data){
				return makeText('club_locker', data);
			}
		];
		var i = 0;
		parent.find("td").each(function(){
			//transform
			if(trans[i])
				$(this).html(trans[i++]($(this).html()));
			//bind keyboard event
			$(this).keydown(function(e){
				if(e.keyCode == 13){
					save();
				} else if(e.keyCode == 27) {
					cancel();
				}			
			});
		});
		
		//focus important one
		if(focus instanceof jQuery)
			focus.find(":input:first").focus();
			
		return false;
	}
	
	$("#club-edit a.edit").click(doEdit);
	//dont make the edit/delete cells clickable to edit
	$("#club-edit td:not(:has(a))").addClass("edit-item").click(function(){
		doEdit($(this));
	}).hover(function(){
		$(this).addClass("edit-hover-main");
		$(this).parent().addClass("edit-hover-row");
	}, function(){
		$(this).removeClass("edit-hover-main");
		$(this).parent().removeClass("edit-hover-row");
	});
});