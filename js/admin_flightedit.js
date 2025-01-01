$('#flight').on('hidden.bs.modal', function (e) {
	if (pageToBeReloaded)
		window.location.reload();
});

$("#selFltStatus").on("change", function() {
	var val = $("#selFltStatus").val();
	if (val > 0) {
		$("#numFltBookedBy").prop("disabled", false)
			.prop("required", true);
	} else {
		$("#numFltBookedBy").val(null)
			.prop("disabled", true)
			.prop("required", false);
	}
});

$("#dtpFltDeparture").on("change.datetimepicker", function(e) {
	$('#dtpFltArrival').datetimepicker('minDate', e.date.set({ 
		hour: 0, 
		minute: 0, 
		second: 0, 
		millisecond: 0 
	}));
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

async function editFlight() {
	const id = $("#fltId").val();
	const flight_number = $("#txtFltFlightNumber").val().toUpperCase();
	const callsign = $("#txtFltCallsign").val().toUpperCase();
	const origin_icao = $("#txtFltOriginIcao").val().toUpperCase();
	const destination_icao = $("#txtFltDestinationIcao").val().toUpperCase();
	const aircraft_icao = $("#txtFltAircraftIcao").val().toUpperCase();
	const aircraft_freighter = $("#chkFltFreighter").is(":checked");
	const terminal = $("#txtFltTerminal").val();
	const gate = $("#txtFltGate").val();
	const route = $("#txtFltRoute").val().toUpperCase();
	const booked = $("#selFltStatus").val();
	const booked_by = Number($("#numFltBookedBy").val());
	const departure_time = $("#dtpFltDeparture").datetimepicker("viewDate").format("YYYY-MM-DD HH:mm:00");
	const arrival_time = $("#dtpFltArrival").datetimepicker("viewDate").format("YYYY-MM-DD HH:mm:00");
	const departure_estimated = $("#chkFltDepartureAuto").is(":checked");
	const arrival_estimated = $("#chkFltArrivalAuto").is(":checked");

	if (departure_estimated && arrival_estimated) {
		swal2({
			title: "At least one slot time must be given!",
			text: "It is not possible for both times be automatically estimated.",
			type: "error",
			confirmButtonText: "OK",
		});
		return;
	}

	if (id == -1) {
		const response = await $.post("json", {
			"type": "flights", 
			"action": "create", 
			"flight_number": flight_number, 
			"callsign": callsign, 
			"origin_icao": origin_icao, 
			"destination_icao": destination_icao, 
			"aircraft_icao": aircraft_icao, 
			"aircraft_freighter": aircraft_freighter, 
			"terminal": terminal, 
			"gate": gate, 
			"route": route, 
			"booked": booked, 
			"booked_by": booked_by, 
			"departure_time": departure_time, 
			"arrival_time": arrival_time, 
			"departure_estimated": departure_estimated,
			"arrival_estimated": arrival_estimated
		});

		if (response?.error == 0)
		{
			toast({
				title: "The flight has been added!",
				type: "success",
			});
			pageToBeReloaded = true;
			aNewFlight();
		}
		else if (response?.error == 1)
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
	else
	{
		const response = await $.post("json", {
			type: "flights", 
			action: "update", 
			id: id, 
			flight_number: flight_number, 
			callsign: callsign, 
			origin_icao: origin_icao, 
			destination_icao: destination_icao, 
			aircraft_icao: aircraft_icao, 
			aircraft_freighter: aircraft_freighter, 
			terminal: terminal, 
			gate: gate, 
			route: route, 
			booked: booked, 
			booked_by: booked_by, 
			departure_time: departure_time, 
			arrival_time: arrival_time, 
			departure_estimated: departure_estimated, 
			arrival_estimated: arrival_estimated
		});

		if (response?.error == 0)
		{
			toast({
				title: "The flight has been modified!",
				type: "success",
			});
			pageToBeReloaded = true;
			getFlight(id);
		}
		else if (response?.error == 1)
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
}