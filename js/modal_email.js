$("#frmEmail").submit(function(e)
{
	e.preventDefault();
	var email = $("#txtEmail").val();
	
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "profile", "action": "updateEmail", "email": email },
		success: function(data) { 
			if (data && data.error == 0)
			{
				toast({
					title: "Your email has been saved!",
					type: "success",
				});
				$("#enterEmail").modal("hide");
			}
			else
				notification(data);
		},
	});	
});  

$(document).ready(function() {
	$("#enterEmail").modal("show");
});