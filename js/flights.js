$(document).ready(function() {
	$.fn.dataTableExt.afnFiltering.push(function(oSettings, aData, iDataIndex) {
		var checked = $('#fltOnlyFree').is(':checked');
		return !checked || aData[6] == "free";
	});

	var dataTables = [];
	$(".tblFlights").each(function() {
		var tbl = $(this).dataTable({
			"responsive": true,
			"pageLength": -1,
			"lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "all"]],
			"language": {
				"info":           "Showing _START_ to _END_ of _TOTAL_ flights",
				"infoEmpty":      "Showing 0 to 0 of 0 flights",
				"infoFiltered":   "(filtered from _MAX_ total flights)",
				"lengthMenu":     "Show _MENU_ flights",
				"zeroRecords":    "No matching flights found",
			},
			"order": [[ 4, "asc" ]]
		});
		dataTables.push(tbl);
	});

	$('#fltOnlyFree').on("click", function(e) {
		$.each(dataTables, function(index, item) {
			item.fnDraw();
		});
	});
});

function getFlight(id)
{
	$.ajax({
		cache: false,
		url: "json",
		type: "POST",
		data: { "type": "flights", "id": id	},
		success: function(data) {
			$("#uiFltMap").html("<div id='leafletFltMap' style='width: 100%; height: 100%'></div>");
			var map = L.map('leafletFltMap').setView([51.505, -0.09], 13);
			map.scrollWheelZoom.disable();
			L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png', {
				maxZoom: 18,
 				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
			}).addTo(map);	
			
			$("#fltMap").collapse("hide");
			$("#fltMap").on('shown.bs.collapse', function(e) { map.invalidateSize(true); map.fitBounds(polyline.getBounds()); });
			$("#flight").on('shown.bs.modal', function(e) { map.invalidateSize(true); map.fitBounds(polyline.getBounds()); });

			if (data.airline)
			{
				$("#fltTitle").html(data.airline.logo + data.airline.name + " flight " + data.flightNumber);
				$("#fltRadioCallsignHuman").html('"' + data.airline.callsign + '"');
			}
			else
			{
				$("#fltTitle").html("Flight " + data.flightNumber);
				$("#fltRadioCallsignHuman").html(null);
			}
			$("#fltRadioCallsign").html(data.callsign);
			
			if (data.originAirport)
			{
				$("#fltOriginIcao").html(data.originAirport.countryFlag32 + data.originAirport.icao);
				$("#fltOriginHuman").html(data.originAirport.name);
				L.marker([data.originAirport.latitude, data.originAirport.longitude]).addTo(map).bindPopup("<b>" + data.originIcao + "</b><br>" + data.originAirport.name);
			}
			else
				$("#fltOriginIcao").html(data.originIcao);
			
			if (data.destinationAirport)
			{
				$("#fltDestinationIcao").html(data.destinationAirport.countryFlag32 + data.destinationAirport.icao);
				$("#fltDestinationHuman").html(data.destinationAirport.name);
				L.marker([data.destinationAirport.latitude, data.destinationAirport.longitude]).addTo(map).bindPopup("<b>" + data.destinationIcao + "</b><br>" + data.destinationAirport.name);
			}
			else
				$("#fltDestinationIcao").html(data.destinationIcao);

			if (data.originAirport && data.destinationAirport)
			{
				$("#fltGcd").html("- <i>Great circle distance: <b>" + Number(data.greatCircleDistanceNm.toFixed(1)) + " nm</b></i>");

				if (!(data.originAirport.latitude == data.destinationAirport.latitude && data.originAirport.longitude == data.destinationAirport.longitude))
				{
					var polyline = L.Polyline.Arc([data.originAirport.latitude, data.originAirport.longitude], [data.destinationAirport.latitude, data.destinationAirport.longitude], { color: 'red', vertices: 200 }).addTo(map);
					map.fitBounds(polyline.getBounds());				
				}
			}			

			$("#fltAircraftIcao").html(data.aircraftIcao);
			$("#fltAircraftHuman").html(data.aircraftName);
			$("#fltPosition").html(data.position);
			$("#fltRoute").html(data.route);
			$("#fltDepartureTimeHuman").html(data.departureTimeHuman);
			$("#fltArrivalTimeHuman").html(data.arrivalTimeHuman);

			if (data.isArrivalEstimated)
				$("#fltArrivalTimeAuto").show();
			else
				$("#fltArrivalTimeAuto").hide();

			if (data.isDepartureEstimated)
				$("#fltDepartureTimeAuto").show();
			else
				$("#fltDepartureTimeAuto").hide();
			
			if (data.sessionUser)
			{
				$("#fltBtnsLoggedOut").hide();
				$("#fltBtnsDefault").show();
			}
			else
			{
				$("#fltBtnsLoggedOut").show();
				$("#fltBtnsDefault").hide();
			}

			$("#fltEdit").collapse("hide");
			if (data.sessionUser && data.sessionUser.permission > 1)
			{
				$("#fltBtnsAdmin").show();
				$("#btnFltAdminDelete").attr("onclick", "deleteFlight(" + data.id + ")");
				$("#fltId").val(data.id);
				$("#txtFltCallsign").val(data.callsign);
				$("#txtFltFlightNumber").val(data.flightNumber);
				$("#txtFltOriginIcao").val(data.originIcao);
				$("#txtFltDestinationIcao").val(data.destinationIcao);
				$("#txtFltAircraftIcao").val(data.aircraftIcao);
				$("#chkFltFreighter").prop("checked", data.aircraftFreighter);
				$("#txtFltTerminal").val(data.terminal);
				$("#txtFltGate").val(data.gate);
				$("#txtFltRoute").val(data.route);	
				$("#dtpFltDeparture").datetimepicker("date", moment(data.departureTime));
				$("#dtpFltArrival").datetimepicker("date", moment(data.arrivalTime));
				$("#chkFltDepartureAuto").prop("checked", data.isDepartureEstimated).trigger("change");
				$("#chkFltArrivalAuto").prop("checked", data.isArrivalEstimated).trigger("change");

				if (data.booked == "free")
				{
					$("#selFltStatus").val(0);
					$("#numFltBookedBy").val(null);
				}
				if (data.booked == "prebooked")
				{
					$("#selFltStatus").val(1);
					$("#numFltBookedBy").val(data.bookedBy);
				}
				if (data.booked == "booked")
				{
					$("#selFltStatus").val(2);
					$("#numFltBookedBy").val(data.bookedBy);
				}
				$("#selFltStatus").trigger("change");
			}
			else
			{
				$("#fltBtnsAdmin").hide();
				$("#fltEdit").html(null);
			}

			$("#fltBtnsConfirm").hide();
			if (data.booked == "free")
			{
				$("#fltInfobox").html("This flight is available.")
					.attr("class", "alert alert-success");
				$("#fltBookedBy").hide();
				$("#btnFltBook").show();					
				$("#btnFltFree").hide();
				$("#btnFltSendEmail").hide();				
				
				$("#btnFltBook").click(function() {
					$("#fltBtnsDefault").hide();
					$("#fltBtnsConfirm").show();
					$("#fltInfobox").attr("class", "alert alert-warning")
						.html("Please reserve <strong>only</strong> if you have genuine intentions to complete this flight!<div>Do not abuse the event by reserving flights solely to block them from other members! Thank you.</div>");
					$("#btnFltConfirm").attr("onclick", "bookFlight(" + id + ")");
				});
			}
			else
			{
				if (data.booked == "prebooked")
				{
					$("#fltInfobox").html("This flight has been reserved.")
						.attr("class", "alert alert-warning")
				}
				if (data.booked == "booked")
				{
					$("#fltInfobox").html("This flight has already been booked.")
						.attr("class", "alert alert-danger")
				}
				$("#fltInfobox").show();
				$("#fltBookedBy").show();
				$("#fltBookedAt").html(data.bookedAtHuman);
				$("#fltBookedByVid").html(data.bookedByUser.vid);
				$("#fltBookedByName").html(data.bookedByUser.fullname);
				$("#fltBookedByRating").html(data.bookedByUser.pilotBadge);
				$("#fltBookedByDivision").html(data.bookedByUser.divisionBadge);
				$("#btnFltSendEmail").hide();
				$("#btnFltBook").hide();

				if (data.sessionUser && (data.sessionUser.vid == data.bookedByUser.vid || data.sessionUser.permission >= 2))
				{
					$("#btnFltFree").show()
						.attr("onclick", "freeFlight(" + id + ")");

					if (data.booked == "prebooked" && data.bookedByUser.emailGiven)
					{
						$("#btnFltSendEmail").attr("onclick", "sendEmailFlight(" + id + ")")
							.show();
					}
				}
				else
					$("#btnFltFree").hide();
			}

			// turnover flights
			$("#fltTurnovers").collapse("hide");
			if (data.turnoverFlights.length > 0)
			{
				var content = '<div class="list-group">';
				$.each(data.turnoverFlights, function() {
					content += '<a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="getFlight(' + this.id + ')">';
					content += (this.airline ? this.airline.logo : "") + '<strong>' + this.callsign + '</strong> ' + this.originIcao + ' – ' + this.destinationIcao + '<span class="float-right">';
					switch (this.booked)
					{
						case 'free':
							content += '<span class="badge badge-success">Available</span>';
							break;
						case 'prebooked':
							content += '<span class="badge badge-warning">Reserved by ' + this.bookedBy + '</span>';
							break;
						case 'booked':
							content += '<span class="badge badge-danger">Booked by ' + this.bookedBy + '</span>';
							break;
					}
					content += '</span></a>';
				});
				content += '</div>';
				$("#fltTurnovers").html(content);
				$("#btnFltTurnovers").show();
			}
			else
				$("#btnFltTurnovers").hide();

			// show WX and Simbrief buttons only when logged in to admins, or if we're the bookers
			$("#fltBriefing").collapse("hide");
			if (data.briefing !== null) {
				$("#btnFltBriefing").html('<i class="fas fa-exclamation-triangle"></i> Flight Briefing &ndash; MUST READ! <i class="fas fa-exclamation-triangle"></i>');
				$("#fltBriefingText").html(data.briefing).show();
			} else {
				$("#btnFltBriefing").html("Flight Briefing");
				$("#fltBriefingText").html("").hide();
			}
			if (data.sessionUser && ((data.bookedByUser && data.bookedByUser.vid == data.sessionUser.vid) || (data.sessionUser.permission >= 2))) {
				$("#fltMetarOrigin").html(`METAR ${data.originIcao}`)
					.attr("onclick", `getWx(0, ${data?.id}, 0)`)
					.show();
				$("#fltTafOrigin").html(`TAF ${data.originIcao}`)
					.attr("onclick", `getWx(0, ${data?.id}, 1)`)
					.show();
				$("#fltMetarDestination").html(`METAR ${data.destinationIcao}`)
					.attr("onclick", `getWx(0, ${data?.id}, 2)`)
					.show();
				$("#fltTafDestination").html(`TAF ${data.destinationIcao}`)
					.attr("onclick", `getWx(0, ${data?.id}, 3)`)
					.show();
				$("#fltSimbrief").html("Generate OFP via SimBrief")
					.attr("href", data.simbriefLink);
			} else {
				$("#fltMetarOrigin").hide();
				$("#fltTafOrigin").hide();
				$("#fltMetarDestination").hide();
				$("#fltTafDestination").hide();
				$("#fltSimbrief").hide();
			}

			$("#flight").modal("show");
		}
	});
}

function bookFlight(id)
{
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "flights", "id": id, "action": "book" },
		success: function(data) {
			if (data && data.error == 0)
			{
				swal2({
					title: "Flight has been reserved!",
					text: "If you have provided your email address earlier, you will receive a confirmation email very soon.",
					type: "success",
					confirmButtonText: "YAY!",
					timer: 5000,
				}).then((value) => { $("#flight").modal("hide"); window.location.reload(); });
			}
			else if (data && data.error == 1)
			{
				swal2({
					title: "Someone else was faster :-(",
					text: "Another member has already reserved this flight. You will be redirected back to the flight list to look for another one!",
					type: "error",
					confirmButtonText: "OK",
				}).then((value) => {  window.location.reload(); });
			}
			else if (data && data.error == 2)
			{
				swal2({
					title: "You have an other reserved flight in this interval!",
					html: "If you would rather reserve the present flight, please delete the previous reservation.<br>Conflicting flight(s): " + data.callsigns,
					type: "error",
					confirmButtonText: "OK",
				}).then((value) => { $("#flight").modal("hide"); });
			}
			else
				notification(data);
		},
	});	
}

function freeFlight(id)
{
	swal2({
		title: "Are you sure you want to delete this reservation?",
		type: "warning",
		showCancelButton: true,
		cancelButtonText: "No, don't delete",
		confirmButtonText: "Yes, delete it"
	}).then((result) =>
	{
		if (result.value)
		{
			$.ajax({
				cache: false,
				type: "POST",
				url: "json",
				data: { "type": "flights", "id": id, "action": "free" },
				success: function(data) {
					if (data && data.error == 0)
					{
						swal2({
							title: "Reservation has been deleted!",
							text: "Thank you for giving others the chance to fly this flight :-)",
							type: "success",
							confirmButtonText: "^^",
							timer: 3000,
						}).then(() => { $("#flight").modal("hide"); window.location.reload(); });
					}
					else
					{
						swal2({
							title: "Error while deleting the reservation!",
							text: "I am not sure what happened. Please notify the staff to delete the reservation manually. The page will be reloaded.",
							type: "error",
							confirmButtonText: "RIP",
						}).then(() => { $("#flight").modal("hide"); window.location.reload(); });
					}
				},
			});	
		}
	});
}

function deleteFlight(id)
{
	swal2({
		title: "Are you sure you want to delete this flight?",
		type: "warning",
		showCancelButton: true,
		cancelButtonText: "No, don't delete",
		confirmButtonText: "Yes, delete it"
	}).then((result) =>
	{		
		if (result.value)
		{
			$.ajax({
				cache: false,
				type: "POST",
				url: "json",
				data: { "type": "flights", "id": id, "action": "delete" },
				success: function(data) {
					if (data && data.error == 0)
					{
						toast({
							title: "Flight has been deleted!",
							type: "success",
						});
						$("#flight").modal("hide");
						window.location.reload();
					}
					else
						notification(data);
				},
			});	
		}
	});
}

function sendEmailFlight(id)
{
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "flights", "id": id, "action": "sendconfirmation" },
		success: function(data) {
			if (data && data.error == 0)
			{
				toast({
					title: "Confirmation mail has been re-sent!",
					type: "success",
				});
				$("#flight").modal("hide");
			}
			else
				notification(data);
		},
	});	
}

$("#btnFltAdminEdit").click(function() {
	$("#fltEdit").collapse("show");
});

$("#chkFltArrivalAuto").on("change", function() {
	$("#dtpFltArrival input").prop("disabled", $(this).is(":checked"));
});

$("#chkFltDepartureAuto").on("change", function() {
	$("#dtpFltDeparture input").prop("disabled", $(this).is(":checked"));
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
	setTimeout(function() {
		$(".tblFlights").css("width", "100%");
		$(".tblFlights").DataTable().columns.adjust();
	}, 50); 
});