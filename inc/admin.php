<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

global $config;
$sesUser = Session::User();
$fullname = $sesUser->firstname . " " . $sesUser->lastname;

// determining count of booking confirmation emails
$flts = Flight::GetAll();
$prebookeds = 0;
$prebookedEmails = 0;

foreach ($flts as $flt)
{
	if ($flt->booked == "prebooked")
	{
		$prebookeds++;
		if ($user = User::Find($flt->bookedBy))
		{
			if (!empty($user->email))
				$prebookedEmails++;
		}
	}
}

?>

<main role="main" class="container">
	<h1>Admin area</h1>

	<div class="row">
		<div class="col-lg-3">
			<div class="nav flex-column nav-pills mb-5" id="adminPills-tab" role="tablist">
				<a class="nav-link active" id="tabAboutLink" data-toggle="pill" href="#tabAbout" role="tab">About the system</a>
				<hr>
				<a class="nav-link" id="tabGeneralLink" data-toggle="pill" href="#tabGeneral" role="tab">General settings</a>
				<a class="nav-link" id="tabAirportsLink" data-toggle="pill" href="#tabAirports" role="tab">Event airports</a>
				<a class="nav-link" id="tabUsersLink" data-toggle="pill" href="#tabUsers" role="tab">Users & permissions</a>
				<hr>
				<a class="nav-link" id="tabNewFlightLink" data-toggle="pill" href="#tabNewFlight" role="tab">Add new flight</a>
				<a class="nav-link" id="tabTimeframesLink" data-toggle="pill" href="#tabTimeframes" role="tab">Private slot management</a>
				<a class="nav-link" id="tabEmailLink" data-toggle="pill" href="#tabEmail" role="tab">Email</a>
			</div>
		</div> 

		<div class="col-lg-9">
			<div class="tab-content" id="adminPills-tabContent">
				<div class="tab-pane fade" id="tabGeneral" role="tabpanel">
					<h2>General settings</h2>
					<form id="frmGeneral">
						<div class="table-responsive">
							<table class="table">
								<tr>
									<th>Name of event</th>
									<td><input class="form-control" id="txtEventName" type="text" placeholder="e.g. RFE Vatican" required value="<?=$config["event_name"]?>"></td>
								</tr>
								<tr>
									<th>Booking status</th>
									<td>
										<select class="form-control" id="selMode">
											<option value="0"<?=($config["mode"] == 0 ? " selected" : "")?>>not opened yet</option>
											<option value="1"<?=($config["mode"] == 1 ? " selected" : "")?>>open</option>
											<option value="2"<?=($config["mode"] == 2 ? " selected" : "")?>>closed</option>
										</select>
									</td>
								</tr>
								<tr>
									<th>
										Event date & time<br>
										<small>from - to</small>
									</th>
									<td>
										<div class="form-row">
											<div class="col">
												<div class="input-group date dtp" id="dtpEventStart" data-target-input="nearest">
													<input type="text" class="form-control datetimepicker-input" data-target="#dtpEventStart" value="<?=Config::getDateStart()?>">
													<div class="input-group-append" data-target="#dtpEventStart" data-toggle="datetimepicker">
														<span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
													</div>
												</div>
											</div>
											<div class="col">
												<div class="input-group date dtp" id="dtpEventEnd" data-target-input="nearest">
													<input type="text" class="form-control datetimepicker-input" data-target="#dtpEventEnd" value="<?=Config::getDateEnd()?>">
													<div class="input-group-append" data-target="#dtpEventEnd" data-toggle="datetimepicker">
														<span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
													</div>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th>
										Division<br>
										<small>name, website, email</small>
									</th>
									<td>
										<div class="form-row">									
											<div class="col">
												<input class="form-control" id="txtDivisionName" type="text" placeholder="e.g. IVAO Hungary" required value="<?=$config["division_name"]?>">
											</div>
											<div class="col">
												<div class="input-group">
													<div class="input-group-prepend">
														<div class="input-group-text"><i class="fas fa-globe-americas"></i></div>
													</div>
													<input class="form-control" id="txtDivisionWeb" type="text" placeholder="e.g. www.ivao.hu" required value="<?=$config["division_web"]?>">																								
												</div>											
											</div>	
											<div class="col">
												<div class="input-group">
													<div class="input-group-prepend">
														<div class="input-group-text"><i class="fas fa-at"></i></div>
													</div>
													<input class="form-control" id="txtDivisionEmail" type="email" placeholder="e.g. events@ivao.hu" required value="<?=$config["division_email"]?>">																								
												</div>											
											</div>									
										</div>
										<div class="text-muted"><small>These data will be shown in the page title, emails and footer</small></div>
									</td>
								</tr>
								<tr>
									<th>
										Social media<br>
										<small>full URLs are needed</small>
									</th>
									<td>
										<div class="form-row">									
											<div class="col">
												<div class="input-group">
													<div class="input-group-prepend">
														<div class="input-group-text"><i class="fab fa-facebook-f"></i></div>
													</div>
													<input class="form-control" id="txtDivisionFacebook" type="text" placeholder="Facebook link" value="<?=$config["division_facebook"]?>">																								
												</div>											
											</div>	
											<div class="col">
												<div class="input-group">
													<div class="input-group-prepend">
														<div class="input-group-text"><i class="fab fa-twitter"></i></div>
													</div>
													<input class="form-control" id="txtDivisionTwitter" type="text" placeholder="Twitter link" value="<?=$config["division_twitter"]?>">																								
												</div>											
											</div>									
										</div>
										<div class="text-muted"><small>Leave empty if the division doesn't have any of them</small></div>
									</td>
								</tr>
								<tr>
									<th>
										URL of weather API<br>
										<small>do not modify without reason</small>
									</th>
									<td>
										<input class="form-control" id="txtWxUrl" type="text" value="<?=$config["wx_url"]?>">
										<div class="text-muted"><small>To disable the weather request feature at the flight briefing, simply remove the URL above</small></div>
									</td>
								</tr>							
								<tr>
									<th></th>
									<td><button class="btn btn-success btn-lg" type="submit">Save settings</button></td>
								</tr>
							</table>
						</div>
					</form>
				</div>


				<div class="tab-pane fade" id="tabAirports" role="tabpanel">
					<h2>Participating airports on the event <button class="btn btn-secondary btn-sm float-right" onclick="aNewAirport()">New airport</button></h2>

					<div id="editAirport" class="collapse card">
						<h5 class="card-header">
							<span id="lblAirport"></span>
							<button type="button" class="close" aria-label="Close" onclick="aCloseAirport()"><span aria-hidden="true">&times;</span></button>
						</h5>
						<div class="card-body">
							<form id="frmAirportEdit">
								<input type="hidden" id="aptId">
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">ICAO:</label>
									<div class="col-sm-10">
										<input class="form-control input-uppercase" type="text" id="txtAirportIcao" maxlength="4" required placeholder="e.g. LHBP">
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Airport name:</label>
									<div class="col-sm-10">
										<input class="form-control" type="text" id="txtAirportName" required placeholder="e.g. Vatican International">
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Order:</label>
									<div class="col-sm-10">
										<input class="form-control" type="number" id="numAirportOrder" required placeholder="the weight of the airport in every list, table, etc.">
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2">Active:</label>
									<div class="col-sm-10">
										<div class="form-check">
											<input class="form-check-input" type="checkbox" id="chkAirportEnabled">
											<label class="form-check-label" for="chkAirportEnabled">by unchecking this, airport won't be present in the flight list and statistics</label>
										</div>
									</div>
								</div>
								<button class="btn btn-info" type="submit">Save airport</button>
								<button class="btn btn-danger" type="button" id="btnAirportDelete">Delete airport</button>
							</form>
						</div>
					</div>

					<div class="table-responsive">
						<table class="table table-hover table-sm table-striped" id="tblAirports">
							<thead>
								<tr>
									<th>ICAO</th>
									<th>Airport name</th>
									<th>Order</th>
									<th>Active</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>					
					</div>					
				</div>


				<div class="tab-pane fade" id="tabNewFlight" role="tabpanel">
					<h2>Add new flight</h2>
					<div class="alert alert-info">
						Flight deletion and modification are possible on the <a href="flights">flight booking</a> page.
					</div>
					<form id="frmFlightNew">
						<?php include_once("inc/admin_flightedit.php"); ?>
					</form>
				</div>

				<div class="tab-pane fade" id="tabUsers" role="tabpanel">
					<h2>Users & permissions <button class="btn btn-secondary btn-sm float-right" onclick="aNewUser()">New user</button></h2>

					<div id="editUser" class="collapse card">
						<h5 class="card-header">
							<span id="lblUser"></span>
							<button type="button" class="close" aria-label="Close" onclick="aCloseUser()"><span aria-hidden="true">&times;</span></button>
						</h5>
						<div class="card-body">
							<form id="frmUserEdit">
								<input type="hidden" id="userId">
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">VID:</label>
									<div class="col-sm-10">
										<input class="form-control" type="number" id="numUserVid" maxlength="6" required placeholder="e.g. 540147">
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Real name:</label>
									<div class="col-sm-10">
										<div class="form-row">
											<div class="col">
												<input class="form-control" type="text" id="txtUserFirstname" required placeholder="first name">
											</div>
											<div class="col">
												<input class="form-control" type="text" id="txtUserLastname" required placeholder="last name">
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Division:</label>
									<div class="col-sm-10">
										<input class="form-control input-uppercase" type="text" id="txtUserDivision" maxlength="2" required placeholder="ISO code, e.g. HU">
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Email address:</label>
									<div class="col-sm-10">
										<input class="form-control" type="email" id="txtUserEmail" placeholder="leave empty if don't want to disclose">
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Permission:</label>
									<div class="col-sm-10">
										<select class="form-control" id="selUserPermission">
											<option value="0">banned</option>
											<option value="1">normal user</option>
											<option value="2">administrator</option>
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2">Privacy setting:</label>
									<div class="col-sm-10">
										<div class="form-check">
											<input class="form-check-input" type="checkbox" id="chkUserPrivacy">
											<label class="form-check-label" for="chkUserPrivacy">show his/her full name to other pilots at the booked flights when they're logged in</label>
										</div>
									</div>
								</div>
								<button class="btn btn-info" type="submit">Save user</button>
								<button class="btn btn-danger" type="button" id="btnUserDelete">Delete user</button>

								<div class="flightCollapses" id="userCollapses">
									<button class="btn btn-light btn-block collapsed" data-toggle="collapse" data-target="#userFlights" type="button">Show booked flights</button>
									<div id="userFlights" class="collapse card card-body" data-parent="#userCollapses"></div>

									<button class="btn btn-light btn-block collapsed" data-toggle="collapse" data-target="#userSlots" type="button">Show private slots</button>
									<div id="userSlots" class="collapse card card-body" data-parent="#userCollapses"></div>
								</div>
							</form>
						</div>
					</div>

					<div class="table-responsive">
						<table class="table table-hover table-sm table-striped" id="tblUsers">
							<thead>
								<tr>
									<th>VID</th>
									<th>Name</th>
									<th>Division</th>
									<th>Email</th>
									<th>Permission</th>
								</tr>
							</thead>
							<tbody>
							</tbody>						
						</table>
					</div>

					<div class="bd-callout bd-callout-info">
						<h4>Important info regarding privacy</h4>
						<p>Do not modify/add email address and change the privacy setting (name visibility) without the consent of the member due to privacy (GDPR) regulations!</p>
					</div>
				</div>


				<div class="tab-pane fade" id="tabTimeframes" role="tabpanel">
					<h2>Private slot management <button class="btn btn-secondary btn-sm float-right" onclick="aNewTimeframe()">New timeframe</button></h2>
					<div class="alert alert-warning" style="display: none" id="slotAlert"></div>
					<input type="hidden" id="timeframeId">
					
					<div id="timeframeNew" class="collapse card">
						<h5 class="card-header">
							Add new slot timeframes
							<button type="button" class="close" aria-label="Close" onclick="aCloseTimeframeNew()"><span aria-hidden="true">&times;</span></button>
						</h5>
						<div class="card-body">
							<form id="frmTimeframeNew">
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Airport:</label>
									<div class="col-sm-10">
										<select class="form-control" id="selTfNewAirport"></select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Date:</label>
									<div class="col-sm-10">
										<div class="input-group date dtpDate" id="dtpTfNewDate" data-target-input="nearest">
											<input type="text" class="form-control datetimepicker-input" required data-target="#dtpTfNewDate">
											<div class="input-group-append" data-target="#dtpTfNewDate" data-toggle="datetimepicker">
												<span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Time (range):</label>
									<div class="col-sm-10">
										<div class="form-row">
											<div class="col">
												<input class="form-control" type="number" id="numTfNewHourFrom" required placeholder="hour FROM (0-24)" min="0" max="23">
											</div>
											<div class="col">
												<input class="form-control" type="number" id="numTfNewHourTo" required placeholder="hour TO (0-24)" min="0" max="23">
											</div>
											<div class="col">
												<input class="form-control" type="number" id="numTfNewMinute" required placeholder="minute" min="0" max="59">
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">
										Available slots:
										<small>in each hour</small>
									</label>
									<div class="col-sm-10">
										<input class="form-control" type="number" id="numTfNewCount" required value="1" min="1">
									</div>
								</div>
								<button class="btn btn-info" type="submit">Save timeframe</button>
							</form>
						</div>
					</div>

					<div id="timeframe" class="collapse card">
						<h5 class="card-header">
							<span id="timeframeTitle"></span>
							<button type="button" class="close" aria-label="Close" onclick="aCloseTimeframe()"><span aria-hidden="true">&times;</span></button>
						</h5>
						<div class="table-responsive card-body">
							<table class="table table-hover table-sm table-striped" id="tblSlots">
								<thead>
									<tr>
										<th>Callsign</th>
										<th>Aircraft</th>
										<th>Origin</th>
										<th>Destination</th>
										<th>Requested at</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
								</tbody>						
							</table>
						</div>
						<div><button class="btn btn-light btn-block" data-toggle="collapse" data-target="#timeframeEdit">Modify timeframe</button></div>

						<div class="collapse card card-body" id="timeframeEdit">
							<form id="frmTimeframeEdit">
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Airport:</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" readonly id="txtTfEditAirport">
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">Date:</label>
									<div class="col-sm-10">
										<div class="input-group date dtp" id="dtpTfEditDate" data-target-input="nearest">
											<input type="text" class="form-control datetimepicker-input" required data-target="#dtpTfEditDate">
											<div class="input-group-append" data-target="#dtpTfEditDate" data-toggle="datetimepicker">
												<span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2 col-form-label">
										Available slots:
										<small>in each hour</small>
									</label>
									<div class="col-sm-10">
										<input class="form-control" type="number" id="numTfEditCount" required min="1">
									</div>
								</div>
								<button class="btn btn-info" type="submit">Save timeframe</button>
								<button class="btn btn-danger" type="button" id="btnTfDelete">Delete timeframe</button>
							</form>
						</div>
					</div>

					<div class="table-responsive">
						<table class="table table-hover table-sm table-striped" id="tblTimeframes">
							<thead>
								<tr>
									<th>Airport</th>
									<th>Date & time</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
							</tbody>						
						</table>
					</div>

					<?php include_once("inc/modal_slot.php"); ?>
				</div>

				<div class="tab-pane fade" id="tabEmail" role="tabpanel">
					<h2>Email</h2>

					<form id="frmAdminEmail">
						<div class="row mb-4">
							<div class="col-lg-6">
								<div class="card">
									<h5 class="card-header">Flight confirmation reminder</h5>
									<div class="card-body">
										<table class="table table-sm">
											<tr>
												<td>Unconfirmed flights</td>
												<th><?=$prebookeds?></th>
											</tr>
											<tr>
												<td>Emails can be send</td>
												<th><?=$prebookedEmails?></th>
											</tr>
										</table>
										<button type="button" class="btn btn-primary btn-sm btn-block" id="btnAdminEmailFltConfirmation">Re-send confirmation mails</button>
									</div>
								</div>
							</div>
						</div>
						<div class="row mb-4">
							<div class="col-lg-12">
								<div class="card">
									<h5 class="card-header">Free-text circular</h5>
									<div class="card-body">
										<div class="form-group">
											<label for="txtAdminEmailFrom">From:</label>
											<input type="text" id="txtAdminEmailFrom" readonly class="form-control" value="<?=$fullname . " <" . $config["division_email"] . ">"?>">
										</div>
										<div class="form-group">
											<label for="selAdminEmailRecipients">Recipients:</label>
											<select id="selAdminEmailRecipients" class="form-control">
												<option selected disabled>Choose...</option>
												<option value="1">All members (with at least one scheduled flight or private slot)</option>
												<option value="2">Members with flight booking</option>
												<option value="3">Members with UNCONFIRMED flight booking</option>
												<option value="4">Members with private slot</option>
											</select>
										</div>
										<div class="alert alert-info">Message will be delivered only to users who had set their email on their profile.</div>
										<div class="form-group">
											<label for="txtAdminEmailSubject">Subject:</label>
											<input type="text" id="txtAdminEmailSubject" class="form-control" required>
										</div>
										<div class="form-group">
											<label for="txtAdminEmailMessage">Message:</label>
											<textarea id="txtAdminEmailMessage" class="form-control ckeditor" required></textarea>
										</div>
										<button type="submit" class="btn btn-primary btn-sm">Send messages</button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>

				<div class="tab-pane fade active show text-justify" id="tabAbout" role="tabpanel">
					<h2>About the system</h2>
					<p>This web application has been developed since 2018 to the IVAO community. Not coincidentally, the influence was given by <a href="https://ivao.aero/Member.aspx?Id=205631" target="_blank">Filipe Fonseca</a>'s RFE system which is widely used on the network.</p>
					<p>The system has been developed as an "universal" solution (not specifically to one division), and I've always strived for keeping the system easy-to-use on both user and staff side.</p>
					<p>
						Backend is PHP using object-oriented concept that ensures the application is easily expandable and modifiable, also new features can be implemented with ease.<br>
						The frontend design is unified, complies with the <a href="https://brand.ivao.aero/" target="_blank">IVAO Brand Guidelines</a>, responsive, mobile-first and gives a comfy user experience.
					</p>

					<div class="flightCollapses" id="collapses">
						<button class="btn btn-light text-left btn-block collapsed" data-target="#credits" data-toggle="collapse">Credits (i.e. list of people who contributed)</button>
						<div class="text-center card card-body collapse" id="credits" data-parent="#collapses">
							<p>
								Developed by:<br>
								<strong>Donat Marko</strong> (<a href="https://ivao.aero/Member.aspx?Id=540147" target="_blank">540147</a>)
							</p>
							<p>
								Ideas, recommendations:<br>
								<strong>-/-</strong> (<a href="https://ivao.aero/Member.aspx?Id=100000" target="_blank">100000</a>)
							</p>
							<p>
								Testing <small>(booking random flights and tolerating dozens of spams in their mailbox):</small><br>
								<strong>Keve Kovacs</strong> (<a href="https://ivao.aero/Member.aspx?Id=492790" target="_blank">492790</a>)<br>
								<strong>Philip Bölcskei</strong> (<a href="https://ivao.aero/Member.aspx?Id=527188" target="_blank">527188</a>)
							</p>
							<p>
								Background noise for coding:<br>
								<a href="https://www.spotify.com" target="_blank"><strong>Spotify</strong></a>
							</p>
							<p>
								Catering:<br>
								<a href="https://www.nescafe.com/" target="_blank"><strong>Nescafé</strong></a>
							</p>
						</div>

						<button class="btn btn-light text-left btn-block collapsed" data-target="#features" data-toggle="collapse">Feature list</button>
						<div class="card card-body collapse" id="features" data-parent="#collapses">
							<ul>
								<li>flight booking</li>
								<li>supporting multi-airport events</li>
								<li>private slot management</li>
								<li>email sending either via API or direct SMTP</li>
								<li>automatic turnover flight detection</li>
								<li>conflicting flight detection</li>
								<li>GDPR compliance with optional full name publishing</li>
								<li>free-text circular email sending to various groups of people</li>
							</ul>
						</div>

						<button class="btn btn-light text-left btn-block collapsed" data-target="#plugins" data-toggle="collapse">Used plugins, classes, modules</button>
						<div class="card card-body collapse" id="plugins" data-parent="#collapses">
							<ul>
								<li><a href="https://github.com/PHPMailer/PHPMailer" target="_blank">PHPMailer</a></li>
								<li><a href="https://getbootstrap.com/" target="_blank">Bootstrap 4</a></li>
								<li><a href="https://jquery.com/" target="_blank">jQuery 3</a></li>
								<li><a href="https://tempusdominus.github.io/bootstrap-4/" target="_blank">Tempus Dominus 5</a></li>
								<li><a href="https://momentjs.com/" target="_blank">Moment.js</a></li>
								<li><a href="https://popper.js.org/" target="_blank">Popper.js</a></li>
								<li><a href="https://leafletjs.com/" target="_blank">Leaflet</a></li>
								<li><a href="https://datatables.net/" target="_blank">DataTables</a></li>
								<li><a href="https://ckeditor.com/" target="_blank">CKEditor 4</a></li>
								<li><a href="https://sweetalert2.github.io/" target="_blank">SweetAlert 2</a></li>
								<li><a href="https://fontawesome.com/?from=io" target="_blank">Font Awesome</a></li>
								<li><a href="https://github.com/donatmarko/my-smtp-api" target="_blank">My SMTP API by donatmarko</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
include_once("inc/modal_flight.php");
Pages::AddJS("admin_flightedit");
Pages::AddJS("flights");
Pages::AddJS("slots");
?>