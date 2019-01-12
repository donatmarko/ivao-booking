$('#flight').on('hidden.bs.modal', function (e) {
	if (pageToBeReloaded)
		window.location.reload();
});

$("#selFltStatus").on("change", function()
{
	var val = $("#selFltStatus").val();
	if (val > 0)
	{
		$("#numFltBookedBy").prop("disabled", false)
			.prop("required", true);
	}
	else
	{
		$("#numFltBookedBy").val(null)
			.prop("disabled", true)
			.prop("required", false);
	}
});

$("#dtpFltDeparture").on("change.datetimepicker", function(e) {
	$('#dtpFltArrival').datetimepicker('minDate', e.date.set({ hour: 0, minute: 0, second: 0, millisecond: 0 }));
});

$("#frmFlightNew").submit(function(e) {
	e.preventDefault();	
	e.stopImmediatePropagation();	
	editFlight();	
});
$("#frmFlightEdit").submit(function(e) {
	e.preventDefault();
	e.stopImmediatePropagation();	
	editFlight();	
});

function editFlight()
{
	var id = $("#fltId").val();
	var fltno = $("#txtFltFlightNumber").val().toUpperCase();
	var callsign = $("#txtFltCallsign").val().toUpperCase();
	var origin = $("#txtFltOriginIcao").val().toUpperCase();
	var destination = $("#txtFltDestinationIcao").val().toUpperCase();
	var aircraft = $("#txtFltAircraftIcao").val().toUpperCase();
	var isFreighter = $("#chkFltFreighter").is(":checked");
	var terminal = $("#txtFltTerminal").val().toUpperCase();
	var gate = $("#txtFltGate").val().toUpperCase();
	var route = $("#txtFltRoute").val().toUpperCase();
	var booked = $("#selFltStatus").val();
	var bookedBy = Number($("#numFltBookedBy").val());
	var departure = $("#dtpFltDeparture").datetimepicker("viewDate").format("YYYY-MM-DD HH:mm:00");
	var arrival = $("#dtpFltArrival").datetimepicker("viewDate").format("YYYY-MM-DD HH:mm:00");
	var depAuto = $("#chkFltDepartureAuto").is(":checked");
	var arrAuto = $("#chkFltArrivalAuto").is(":checked");

	if (depAuto && arrAuto)
	{
		swal2({
			title: "At least one slot time must be given!",
			text: "It is not possible to be both times automatically estimated.",
			type: "error",
			confirmButtonText: "OK",
		});
		return;
	}

	if (id == -1)
	{
		$.ajax({
			cache: false,
			type: "POST",
			url: "json",
			data: { "type": "flights", "action": "create", "flight_number": fltno, "callsign": callsign, "origin_icao": origin, "destination_icao": destination, "aircraft_icao": aircraft, "aircraft_freighter": isFreighter, "terminal": terminal, "gate": gate, "route": route, "booked": booked, "booked_by": bookedBy, "departure_time": departure, "arrival_time": arrival, "departure_estimated": depAuto, "arrival_estimated": arrAuto },
			success: function(data) {
				if (data && data.error == 0)
				{
					toast({
						title: "The flight has been added!",
						type: "success",
					});
					pageToBeReloaded = true;
					aNewFlight();
				}					
				else if (data && data.error == 1)
				{
					swal2({
						title: "The user doesn't exist!",
						text: "Please check the supplied VID!",
						type: "error",
						confirmButtonText: "RIP",
					});
				}
				else
					notification(data);
			}
		});	
	}
	else
	{
		$.ajax({
			cache: false,
			type: "POST",
			url: "json",
			data: { "type": "flights", "action": "update", "id": id, "flight_number": fltno, "callsign": callsign, "origin_icao": origin, "destination_icao": destination, "aircraft_icao": aircraft, "aircraft_freighter": isFreighter, "terminal": terminal, "gate": gate, "route": route, "booked": booked, "booked_by": bookedBy, "departure_time": departure, "arrival_time": arrival, "departure_estimated": depAuto, "arrival_estimated": arrAuto },
			success: function(data) {
				if (data && data.error == 0)
				{
					toast({
						title: "The flight has been modified!",
						type: "success",
					});
					pageToBeReloaded = true;
					getFlight(id);
				}
				else if (data && data.error == 1)
				{
					swal2({
						title: "The user doesn't exist!",
						text: "Please check the supplied VID!",
						type: "error",
						confirmButtonText: "RIP",
					});
				}
				else
					notification(data);
			}
		});	
	}
}