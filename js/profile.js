$("#frmProfile").submit(function(e) {
	e.preventDefault();
	
	var email = $("#txtEmail").val();
	var privacy = $("#chkPrivacy").is(":checked");
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "profile", "action": "update", "email": email, "privacy": privacy },
		success: function(data) { 
			if (data && data.error == 0)
			{
				toast({
					title: "Your profile has been saved!",
					type: "success",
				});
			}
			else
				notification(data);
		}
	});	
}); 