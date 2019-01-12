var pageToBeReloaded = false;

function pageReload()
{
	if (pageToBeReloaded)
		window.location.reload();
}

function notification(data)
{
	if (data && data.error == 419)
	{
		swal2({
			title: "Invalid session",
			text: "Session might be expired. Log out and back in and try again!",
			type: "error",
			confirmButtonText: "OK",
		});
	}
	else
	{
		swal2({
			title: "Error during the request!",
			text: "An unknown error has happened. Please try again and/or notify the developer!",
			type: "error",
			confirmButtonText: "OK",
		});
	}
}

function scroll(elem)
{
	elem = $(elem);
    var offset = elem.offset().top;
    if(!elem.is(":visible")) {
        elem.css({"visibility":"hidden"}).show();
        var offset = elem.offset().top;
        elem.css({"visibility":"", "display":""});
    }

    var visible_area_start = $(window).scrollTop();
    var visible_area_end = visible_area_start + window.innerHeight;

    if(offset < visible_area_start || offset > visible_area_end){
         // Not in view so scroll to it
         $('html,body').animate({scrollTop: offset - window.innerHeight/3}, 1000);
         return false;
    }
    return true;
}

function getWx(elem, uri)
{
	$.ajax({
		url: uri,
		success: function(data) {
			if (data)
				$(elem).html(data);
			else
				$(elem).html("(no data available)");
			$(elem).show();
		},
	});	
} 

const toast = swal.mixin({
	toast: true,
	position: 'top-end',
	showConfirmButton: false,
	timer: 3000
});
const swal2 = swal.mixin({
	confirmButtonClass: 'btn btn-primary',
	cancelButtonClass: 'btn btn-secondary',
	buttonsStyling: false,
});

$(document).ready(function () {
	$('[data-toggle="tooltip"]').tooltip();
	CKEDITOR.replaceClass = 'ckeditor';
	CKEDITOR.config.allowedContent = true;
	CKEDITOR.config.contentsCss = [CKEDITOR.basePath + 'contents.css', 'css/bootstrap.min.css'];

	$(".dtp").datetimepicker({
		"format": "DD/MM/YYYY HH:mm",
		"ignoreReadonly": true,
		"allowInputToggle": true,
		"locale": "en-gb",
		"icons": {
			"time": "far fa-clock",
			"date": "fas fa-calendar-alt",
			"up": "fas fa-angle-up",
			"down": "fas fa-angle-down",
		},
	});
	$(".dtpDate").datetimepicker({
		"format": "DD/MM/YYYY",
		"ignoreReadonly": true,
		"allowInputToggle": true,
		"locale": "en-gb",
		"icons": {
			"time": "far fa-clock",
			"date": "fas fa-calendar-alt",
			"up": "fas fa-angle-up",
			"down": "fas fa-angle-down",
		}
	});
});
$.ajaxSetup({
	data: { "xsrfToken": XSRF_TOKEN }
});
$(document).ajaxStart(function() {
	$(".loader").show();
});
$(document).ajaxComplete(function() {
	$(".loader").hide();
	$('[data-toggle="tooltip"]').tooltip();
});
$(document).ajaxError(function(xhr, error, thrownError) {
	console.log(xhr, error, thrownError);
	notification(null);
});