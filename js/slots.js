function getTimeframe(id)
{
	$.ajax({
		cache: false,
		url: "json",
		type: "POST",
		data: { "type": "timeframes", "id": id },
		success: function(data) {
			$("#slotIcao").val(data.airportIcao);
			$("#timeframeId").val(data.id);

			if (data.eventAirport && data.eventAirport.airport)
				$("#timeframeTitle").html("Private slots at " + data.timeHuman + " @ " + data.eventAirport.airport.countryFlag24 + data.eventAirport.icao);
			else
				$("#timeframeTitle").html("Private slots at " + data.timeHuman + " @ " + data.aircraftIcao);

			var tbl = "";
			var haveBooking = false;
			if (data.slots)
			{
				for (var i = 0; i < data.slots.length; i++)
				{
					var row = data.slots[i];
					tbl += '<tr>';

					if (airline = row.airline)
						tbl += '<td data-toggle="tooltip" title="' + airline.name  + '">' + airline.logoSmall + row.callsign + '</td>';
					else
						tbl += '<td>' + row.callsign + '</td>';

					tbl += '<td data-toggle="tooltip" title="' + (row.aircraftName ? row.aircraftName : "") + '">' + row.aircraftIcao + (row.aircraftFreighter ? "/F" : "") + '</td>';

					if (orig = row.originAirport)
						tbl += '<td>' + orig.countryFlag24 + '<span data-toggle="tooltip" title="' + orig.name + '">' + orig.icao + '</span></td>';
					else
						tbl += '<td>' + row.originIcao + '</td>';

					if (dest = row.destinationAirport)
						tbl += '<td>' + dest.countryFlag24 + '<span data-toggle="tooltip" title="' + dest.name + '">' + dest.icao + '</span></td>';
					else
						tbl += '<td>' + row.destinationIcao + '</td>';

					if (row.booked == "requested")
						tbl += '<td><button class="btn btn-warning btn-sm btn-block" onclick="getSlot(' + row.id + ')"><i class="far fa-hand-paper"></i> Requested by <strong>' + row.bookedBy + '</strong></button></td>';
					else
						tbl += '<td><button class="btn btn-danger btn-sm btn-block" onclick="getSlot(' + row.id + ')"><i class="far fa-handshake"></i> Granted to <strong>' + row.bookedBy + '</strong></button></td>';
					tbl += '</tr>';

					if (data.sessionUser && row.bookedBy == data.sessionUser.vid)
						haveBooking = true;
				}
			}
			else
				tbl += '<tr><td colspan="5" style="font-style: italic; text-align: center">(no booked slots yet)</td></tr>';			
			$("#tblSlots tbody").html(tbl);

			if (haveBooking)
			{
				$("#slotRequest").hide();
				$("#timeframeStatus").prop("class", "alert alert-warning")
					.html("You already have a slot (request) at this timeline, thus you're not able to book a new one.");
			}
			else
			{
				if (data.statistics.free > 0)
				{
					if (data.statistics.free == 1)
						$("#timeframeStatus").html("Currently <strong>1</strong> slot is available at this timeframe.");
					else
						$("#timeframeStatus").html("Currently <strong>" + data.statistics.free + "</strong> slots are available at this timeframe.");

					$("#timeframeStatus").prop("class", "alert alert-success");
					$("#slotRequest").show();
				}
				else
				{
					$("#slotRequest").hide();
					$("#timeframeStatus").prop("class", "alert alert-danger")
						.html("Unfortunately there are no slots are available at this timeframe.");
				}
			}

			$("#timeframe").collapse("show");
			scroll("#timeframe");
		}
	});
}

function closeTimeframe()
{
	$("#timeframe").collapse("hide");
}

$("#frmSlotRequest").submit(function(e) {
	e.preventDefault();
	var timeframeId = $("#timeframeId").val();
	var slotIcao = $("#slotIcao").val();
	var callsign = $("#txtSrCallsign").val().toUpperCase();
	var originIcao = $("#txtSrOriginIcao").val().toUpperCase();
	var destinationIcao = $("#txtSrDestinationIcao").val().toUpperCase();
	var aircraftIcao = $("#txtSrAircraftIcao").val().toUpperCase();
	var aircraftFreighter = $("#chkSrFreighter").is(":checked");
	var route = $("#txtSrRoute").val().toUpperCase();

	if ((originIcao == slotIcao && destinationIcao != slotIcao) || (destinationIcao == slotIcao && originIcao != slotIcao))
	{
		$.ajax({
			cache: false,
			url: "json",
			type: "POST",
			data: { "type": "slots", "action": "create", "timeframe_id": timeframeId, "callsign": callsign, "origin_icao": originIcao, "destination_icao": destinationIcao, "aircraft_icao": aircraftIcao, "aircraft_freighter": aircraftFreighter, "route": route },
			success: function(data) {
				if (data && data.error == 0)
				{
					toast({
						title: "You have successfully sent the slot request!",
						type: "success"
					});
					getTimeframe(timeframeId);
					
					$("#txtSrCallsign").val(null);
					$("#txtSrOriginIcao").val(null);
					$("#txtSrDestinationIcao").val(null);
					$("#txtSrAircraftIcao").val(null);
					$("#txtSrRoute").val(null);
					$("#chkSrFreighter").prop("checked", false);
				}
				else
					notification(data);
			}
		});
	}
	else
	{
		swal2({
			title: "Wrong airport input!",
			text: "Either origin or destination airport must be " + slotIcao + "!",
			type: "error",
			confirmButtonText: "OK",
		});
	}
});

function getSlot(id)
{
	$.ajax({
		cache: false,
		url: "json",
		type: "POST",
		data: { "type": "slots", "id": id },
		success: function(data) {
			$("#uiSlotMap").html("<div id='leafletSlotMap' style='width: 100%; height: 100%'></div>");
			var map = L.map('leafletSlotMap').setView([51.505, -0.09], 13);
			map.scrollWheelZoom.disable();
			L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png', {
				maxZoom: 18,
 				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
			}).addTo(map);		
			$("#slotMap").on('shown.bs.collapse', function(e){ map.invalidateSize(true); map.fitBounds(polyline.getBounds()); });
			$("#slot").on('shown.bs.modal', function(e){ map.invalidateSize(true); map.fitBounds(polyline.getBounds()); });

			if (data.airline)
			{
				$("#slotTitle").html("Private slot for " + data.airline.logo + data.airline.name);
				$("#slotRadioCallsignHuman").html('"' + data.airline.callsign + '"');
			}
			else
			{
				$("#slotTitle").html("Private slot");
				$("#radioCallsignHuman").html(null);
			}
			$("#slotRadioCallsign").html(data.callsign);			
			
			if (data.originAirport)
			{
				$("#slotOriginIcao").html(data.originAirport.countryFlag32 + data.originAirport.icao);
				$("#slotOriginHuman").html(data.originAirport.name);
				L.marker([data.originAirport.latitude, data.originAirport.longitude]).addTo(map).bindPopup("<b>" + data.originIcao + "</b><br>" + data.originAirport.name);
			}
			else
				$("#slotOriginIcao").html(data.originIcao);
			
			if (data.destinationAirport)
			{
				$("#slotDestinationIcao").html(data.destinationAirport.countryFlag32 + data.destinationAirport.icao);
				$("#slotDestinationHuman").html(data.destinationAirport.name);
				L.marker([data.destinationAirport.latitude, data.destinationAirport.longitude]).addTo(map).bindPopup("<b>" + data.destinationIcao + "</b><br>" + data.destinationAirport.name);
			}
			else
				$("#slotDestinationIcao").html(data.destinationIcao);

			if (data.originAirport && data.destinationAirport)
			{
				$("#slotGcd").html("- <i>Great circle distance: <b>" + Number(data.greatCircleDistanceNm.toFixed(1)) + " nm</b></i>");

				if (!(data.originAirport.latitude == data.destinationAirport.latitude && data.originAirport.longitude == data.destinationAirport.longitude))
				{
					var polyline = L.Polyline.Arc([data.originAirport.latitude, data.originAirport.longitude], [data.destinationAirport.latitude, data.destinationAirport.longitude], { color: 'red', vertices: 200 }).addTo(map);
					map.fitBounds(polyline.getBounds());
				}
			}

			$("#slotAircraftIcao").html(data.aircraftIcao);
			$("#slotAircraftHuman").html(data.aircraftName);
			$("#slotPosition").html(data.position);
			$("#slotRoute").html(data.route);
			$("#slotDepartureTimeHuman").html(data.departureTimeHuman);
			$("#slotArrivalTimeHuman").html(data.arrivalTimeHuman);

			if (data.isArrivalEstimated)
				$("#slotArrivalTimeAuto").show();
			else
				$("#slotArrivalTimeAuto").hide();

			if (data.isDepartureEstimated)
				$("#slotDepartureTimeAuto").show();
			else
				$("#slotDepartureTimeAuto").hide();
			
			if (data.sessionUser)
			{
				$("#slotBtnsLoggedOut").hide();
				$("#slotBtnsDefault").show();				
			}
			else
			{
				$("#slotBtnsLoggedOut").show();
				$("#slotBtnsDefault").hide();
			}

			$("#slotEdit").collapse("hide");
			if (data.sessionUser && data.sessionUser.permission > 1)
			{
				// populating timeframe selector
				$.ajax({
					cache: false,
					type: "POST",
					url: "json",
					data: { "type": "timeframes", "action": "getall" },
					success: function(tfData) {
						var content = "";
						$.each(tfData, function() {
							if (this.eventAirport)
								content += '<option value="' + this.id + '">' + this.airportIcao + ' (' + this.eventAirport.name + ') - ' + this.timeHuman + '</option>';
							else
								content += '<option value="' + this.id + '">' + this.airportIcao + ' - ' + this.timeHuman + '</option>';
						});
						$("#selSlotTimeframe").html(content);
						$("#selSlotTimeframe").val(data.timeframeId);
					}
				});

				$("#slotAdminButtons").show();
				$("#btnSlotAdminDelete").attr("onclick", "deleteSlot(" + data.id + ")");
				$("#slotId").val(data.id);
				$("#txtSlotCallsign").val(data.callsign);
				$("#txtSlotOriginIcao").val(data.originIcao);
				$("#txtSlotDestinationIcao").val(data.destinationIcao);
				$("#txtSlotAircraftIcao").val(data.aircraftIcao);
				$("#chkSlotFreighter").prop("checked", data.aircraftFreighter);
				$("#txtSlotTerminal").val(data.terminal);
				$("#txtSlotGate").val(data.gate);
				$("#txtSlotRoute").val(data.route);	
				$("#numSlotBookedBy").val(data.bookedBy);
				$("#txtSlotRejectMessage").val(null);

				if (data.booked == "requested")
					$("#selSlotStatus").val(1);
				if (data.booked == "granted")
					$("#selSlotStatus").val(2);

				$("#selSlotStatus").trigger("change");
			}
			else
			{
				$("#slotAdminButtons").hide();
				$("#slotEdit").html(null);
			}

			if (data.booked == "requested")
			{
				$("#slotInfobox").html("This slot has not yet been granted to the requester.")
					.attr("class", "alert alert-warning")
				$("#lblBookedByName").html("Slot has been requested by");
			}
			if (data.booked == "granted")
			{
				$("#slotInfobox").html("This slot has been granted to the requester.")
					.attr("class", "alert alert-danger")
				$("#lblBookedByName").html("Slot has been granted to");
			}
			$("#slotBookedAt").html(data.bookedAtHuman);
			$("#slotBookedByVid").html(data.bookedByUser.vid);
			$("#slotBookedByName").html(data.bookedByUser.fullname);
			$("#slotBookedByRating").html(data.bookedByUser.pilotBadge);
			$("#slotBookedByDivision").html(data.bookedByUser.divisionBadge);

			if (data.sessionUser && (data.sessionUser.vid == data.bookedByUser.vid || data.sessionUser.permission >= 2))
			{
				$("#btnSlotDelete").show()
					.attr("onclick", "deleteSlot(" + id + ")");
			}
			else
				$("#btnSlotDelete").hide();

			// show flight briefing only when logged in to admins, or if we're the bookers
			if (data.sessionUser && ((data.bookedByUser && data.bookedByUser.vid == data.sessionUser.vid) || (data.sessionUser.permission >= 2)))
			{
				$("#btnSlotBriefing").show();
				$("#slotMetarOrigin").html("METAR " + data.originIcao)
					.attr("onclick", "getWx('#slotWxResult', '" + data.wxUrl + "?icao=" + data.originIcao + "&type=metar')");
				$("#slotTafOrigin").html("TAF " + data.originIcao)
					.attr("onclick", "getWx('#slotWxResult', '" + data.wxUrl + "?icao=" + data.originIcao + "&type=taf')");
				$("#slotMetarDestination").html("METAR " + data.destinationIcao)
					.attr("onclick", "getWx('#slotWxResult', '" + data.wxUrl + "?icao=" + data.destinationIcao + "&type=metar')");
				$("#slotTafDestination").html("TAF " + data.destinationIcao)
					.attr("onclick", "getWx('#slotWxResult', '" + data.wxUrl + "?icao=" + data.destinationIcao + "&type=taf')");
				$("#lnkSlotIvaoRte").prop("href", "https://www.ivao.aero/db/route/route.asp?start=" + data.originIcao + "&end=" + data.destinationIcao);
			}
			else
				$("#btnSlotBriefing").hide();

			$("#slot").modal("show");
		}
	});
}

$("#slot").on("hidden.bs.modal", function() { pageReload(); });

$("#selSlotStatus").on("change", function() {
	if ($(this).val() == 0)
		$("#slotRejectMessage").show();
	else
		$("#slotRejectMessage").hide();
});

function deleteSlot(id)
{
	swal2({
		title: "Are you sure you want to delete this slot?",
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
				data: { "type": "slots", "id": id, "action": "delete" },
				success: function(data) {
					if (data && data.error == 0)
					{
						toast({
							title: "Slot has been deleted!",
							type: "success",
						});

						if (typeof aGetTimeframe === "function")
							aGetTimeframe($("#timeframeId").val());
						else
						{
							if ($("#timeframe").length)
								getTimeframe($("#timeframeId").val());
							else
								pageToBeReloaded = true;
						}

						$("#slot").modal("hide");
					}
					else
						notification(data);
				},
			});	
		}
	});
}

$("#frmSlotEdit").submit(function(e) {
	e.preventDefault();	
	var id = $("#slotId").val();
	var callsign = $("#txtSlotCallsign").val().toUpperCase();
	var origin = $("#txtSlotOriginIcao").val().toUpperCase();
	var destination = $("#txtSlotDestinationIcao").val().toUpperCase();
	var aircraft = $("#txtSlotAircraftIcao").val().toUpperCase();
	var isFreighter = $("#chkSlotFreighter").is(":checked");
	var terminal = $("#txtSlotTerminal").val().toUpperCase();
	var gate = $("#txtSlotGate").val().toUpperCase();
	var route = $("#txtSlotRoute").val().toUpperCase();
	var rejectMessage = $("#txtSlotRejectMessage").val();
	var timeframeId = $("#selSlotTimeframe").val();
	var booked = $("#selSlotStatus").val();
	var bookedBy = Number($("#numSlotBookedBy").val());

	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "slots", "action": "update", "id": id, "callsign": callsign, "timeframe_id": timeframeId, "origin_icao": origin, "destination_icao": destination, "aircraft_icao": aircraft, "aircraft_freighter": isFreighter, "terminal": terminal, "gate": gate, "route": route, "booked": booked, "booked_by": bookedBy, "reject_message": rejectMessage },
		success: function(data) {
			if (data && data.error == 0)
			{
				toast({
					title: "Slot has been modified!",
					type: "success",
				});

				if (booked > 0)
					getSlot(id);
				else
					$("#slot").modal("hide");
					
				if (typeof aGetTimeframe === "function")
				{
					aGetTimeframe(timeframeId);
					aGetTimeframes();
				}
				else
				{
					if ($("#timeframe").length)
						getTimeframe(timeframeId);
					else
						pageToBeReloaded = true;
				}
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
});