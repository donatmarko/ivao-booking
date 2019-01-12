$("#frmContact").submit(function(e) {
	e.preventDefault();
	var subject = $("#selCfType").val();
	var message = $("#txtCfMessage").val();

	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "contact", "subject": subject, "message": message },
		success: function(data) {
			console.log(data);
			if (data && data.error == 0)
			{
				swal2({
					title: "Your message has been sent!",
					text: "We're going to reply shortly.",
					type: "success",
				}).then(() => { window.location.href = "banner"; });
			}
			else
				notification(data);
		}
	});	
}); 

$("#selCfType").on("change", function() {
	var text = "";
	switch (Number($(this).val()))
	{
		case 1:
			text = "";
			break;
		case 2:
			text = "Please describe the issue as in detail as you can, also mention the version of your browser and operating system!";
			break;
		case 3:
			text = "Please don't forget to mention the callsign/flight number precisely!";
			break;
		case 4:
			text = "<strong>Do not</strong> write us if you'd like to request a private slot to a timeframe that is already full!";
			break;
		case 5:
			text = "Both compliment and criticism are appreciated. Don't forget to mention your callsign and the time when the situation happened!<br>Thank you for your honesty!";
			break;
	}

	if (text.length > 0)
	{
		$("#cfAlert").html(text)
			.show();
	}
	else
		$("#cfAlert").hide();
});

$(document).ready(function() {
	$("#selCfType").trigger("change");
});