<?php

/** Authors: Jon Scherdin, Scott Pearson, Andrew Poppe */

# verify user access
if (!isset($_COOKIE['grant_repo'])) {
	header("Location: ".$module->getUrl("index.php"));
}

require_once("base.php");

# update user role
$role = updateRole($userid);

# make sure role is not empty
if ($role == "") {
	header("Location: ".$module->getUrl("index.php"));
}

# log visit
$module->log("Visited Grants Page", array("user"=>$userid, "role"=>$role));

$awards = array(
	"k_awards" => "K Awards",
	"r_awards" => "R Awards",
	"misc_awards" => "Misc. Awards",
	"lrp_awards" => "LRP Awards",
	"va_merit_awards" => "VA Merit Awards",
	"f_awards" => "F Awards",
);

# get metadata
$metadata = json_decode(\REDCap::getDataDictionary($grantsProjectId, "json"), true);
$choices = getChoices($metadata);

# get event_id
$eventId = $module->getEventId($grantsProjectId);

# get grants instrument name
$grantsInstrument = getGrantsInstrument($metadata, 'grants_number');

// Pull all data in grants project
// Except do not include records not marked as "Complete" on grants instrument
$grants = json_decode(\REDCap::getData(array(
	"project_id"=>$grantsProjectId, 
	"return_format"=>"json", 
	"combine_checkbox_values"=>true,
	"exportAsLabels"=>true,
	"filterLogic"=>"[".$grantsInstrument."_complete] = 2"
)), true);

// get award options
$awardOptions = getAllChoices($choices, array_keys($awards));

// get award option values
$awardOptionValues = combineValues($grants, array_keys($awards));
?>

<html>
	<head>
		<title><?php echo \REDCap::escapeHtml($databaseTitle) ?></title>
		<link rel="shortcut icon" type="image" href="<?php echo \REDCap::escapeHtml($faviconImage) ?>"/> 
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.24/af-2.3.5/b-1.7.0/b-colvis-1.7.0/b-html5-1.7.0/b-print-1.7.0/rg-1.1.2/sb-1.0.1/sp-1.2.2/sl-1.3.3/datatables.min.css"/>
 		<link rel="stylesheet" type="text/css" href="<?php echo $module->getUrl("css/basic.css") ?>">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.24/af-2.3.5/b-1.7.0/b-colvis-1.7.0/b-html5-1.7.0/b-print-1.7.0/rg-1.1.2/sb-1.0.1/sp-1.2.2/sl-1.3.3/datatables.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<style>
			table.dataTable tr.dtrg-group.dtrg-level-0 td { 
				background-color: <?php echo \REDCap::escapeHtml($accentColor); ?>; 
				color: <?php echo \REDCap::escapeHtml($accentTextColor); ?>;
			}
			div.dtsp-panesContainer tr.selected {
				background-color: <?php echo \REDCap::escapeHtml($secondaryAccentColor); ?> !important;
				color: <?php echo \REDCap::escapeHtml($secondaryTextColor); ?>;
			}
			div.dtsp-panesContainer tr.selected:hover {
				background-color: <?php echo adjustBrightness($secondaryAccentColor, -0.25); ?> !important;
				color: <?php
					$newColor = adjustBrightness($secondaryAccentColor, -0.25);
					echo adjustBrightness($secondaryTextColor, getBrightness($newColor) >= 0.50 ? -0.50 : 0.50); 
				?>;
				cursor: pointer;
			}
		</style>	
	</head>
	<body>
		<br/>
		<div id="container" style="padding-left:8%;  padding-right:10%; margin-left:auto; margin-right:auto; ">
			<div id="header">
				<?php createHeaderAndTaskBar($role);?>
				<h3><?php echo \REDCap::escapeHtml($databaseTitle) ?></h3>
				<i>You may download grant documents by clicking "download" links below. The use of the grants document database is strictly limited to authorized individuals and you are not permitted to share files or any embedded content with other individuals. All file downloads are logged.</i>
				<hr/>
			</div>

			<div id="grants" class="dataTableParentHidden">
				<br/>
				<table id="grantsTable" class="dataTable">
				<thead>
					<tr>
						<th>PI</th>
						<th>Grant Title</th>
						<th>NIH Format</th>
						<th>Award Type</th>
						<th>Award Option</th>
						<th style="width: 150px;">Grant Date</th>
						<th>Grant #</th>
						<th>Department</th>
						<th>Acquire</th>
						<th>Thesaurus</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($grants as $id=>$row) {
						$url = $module->getUrl("src/download.php?p=$grantsProjectId&id=" .
							$row['grants_file'] . "&s=&page=register_grants&record=" . $row['record_id'] . "&event_id=" .
							$eventId . "&field_name=grants_file");

						echo "<tr>";
							echo "<td style='white-space:nowrap;'>" . \REDCap::escapeHtml($row['grants_pi']) . "</td>";				// 0 - PI
							echo "<td>" . \REDCap::escapeHtml($row['grants_title']) . "</td>";										// 1 - Title
							echo "<td style='text-align: center;'></td>";															// 2 - NIH Format
							echo "<td style='text-align: center;'>" . \REDCap::escapeHtml($row['grants_type']) . "</td>";			// 3 - Award Type
							echo "<td style='text-align: center;'>" . \REDCap::escapeHtml($awardOptionValues[$id]) . "</td>";		// 4 - Award Option
							echo "<td style='text-align: center;'>" . \REDCap::escapeHtml($row['grants_date']) ."</td>";			// 5 - Grant Date
							echo "<td style='text-align: center;'>" . \REDCap::escapeHtml($row['grants_number']) . "</td>";			// 6 - Grant Number
							echo "<td style='text-align: center;'>" . \REDCap::escapeHtml($row['grants_department']) . "</td>";		// 7 - Department
							echo "<td style='text-align: center;'><a href='".\REDCap::escapeHtml($url)."'>Download</a></td>";		// 8 - Acquire
							echo "<td style='text-align: center;'>" . \REDCap::escapeHtml($row["grants_thesaurus"]) . "</td>";		// 9 - Thesaurus (keywords)
						echo "</tr>";
					}
					?>
				</tbody>
				</table>
			</div>
		</div>
		<script>
			function createOption(option, column) {
				return {
					label: option,
					value: function(rowData, rowIdx) {return rowData[column].includes(`--${option}--`);}
				}
			}
			function createPane(options, column, header) {
				return {
					header: header,
					options: options.map(option => createOption(option, column))
				}
			}
			let awardOptions = <?php 
				echo '[';
				foreach ($awardOptions as $awardOption) {
					echo '"'.$awardOption.'",';
				}
				echo ']'; ?>;
			let awardOptionValues = <?php
				echo '[';
				foreach ($awardOptionValues as $awardOptionValue) {
					echo '"'.$awardOptionValue.'",';
				}
				echo ']'; ?>;
			let awardOptionsCombined = awardOptionValues.reduce((acc, val)=> acc+val, "");
			let awardOptionDropdownValues = awardOptions.filter(option => awardOptionsCombined.includes(`--${option}--`));


			$(document).ready( function () {
				$('#grantsTable').DataTable({
					
					
					columns: [
						{"data": "pi"},
						{"data": "title"},
						{"data": "format", "visible": false},
						{"data": "awardType", "visible": false},
						{"data": "awardOption", 
							"visible": false, 
							"type": "awardOption", 
							"render": function(data,type,row) {
								if (type === 'display') {
									return data.replace(/--/g, ', ').replace(/^(, )(, )*|(, )*(, )$/g, '');
								}
								return data;
							} 
						},
						{"data": "date"},
						{"data": "number"},
						{"data": "department", "visible": false},
						{"data": "acquire", "searchable": false},
						{"data": "thesaurus", "visible": false}
					],
					columnDefs: [
						{
							searchPanes: {
								show: true
							},
							targets: [0,1,3]
						}
					],
					pageLength: 1000,
					dom: 'Bfrtip',
					buttons: [
						{
							extend: 'searchPanes',
							config: {
								cascadePanes: true,
								panes: [
									createPane(awardOptions, "awardOption", 'Award Option')
								]
							}
							
						},
						{
							extend: 'searchBuilder',
							config: {
								conditions: {
									awardOption: {
										contains: {
											conditionName: 'Contains',
											init: function (that, fn, preDefined = null) {
												let el = $('<select/>').on('input', function() { fn(that, this) });
												awardOptionDropdownValues.forEach(option => {
													el[0].options.add($(`<option value="${option}" label="${option}"></option`)[0]);
												});

												if (preDefined !== null) {
													$(el).val(preDefined[0]);
												}

												return el;
											},
											inputValue: function (el) {
												return $(el[0]).val();
											},
											isInputValid: function (el, that) {
												return $(el[0]).val().length !== 0;
											},
											search: function(value, comparison) {
												console.log('value:', value, '; comparison: ', comparison );
												return value.includes(`--${comparison}--`);
											}
										}
									}
								}
							}
						},
						{ 
							extend: 'colvis',
							exportOptions: { columns: ':visible' }
						},
						{
							extend: 'csv',
							exportOptions: { columns: ':visible' }
						},
						{ 
							extend: 'excel',
							exportOptions: { columns: ':visible' }
						},
						{ 
							extend: 'pdf',
							exportOptions: { columns: ':visible' }
						}
					]
				});

				$('#grants').removeClass('dataTableParentHidden');
				
				$('#grantsTable').DataTable().on( 'buttons-action', function ( e, buttonApi, dataTable, node, config ) {
					const text = buttonApi.text();
					if (text.search(/Panes|Builder/)) {
						$('.dt-button-collection').draggable();
					}
				});
			});
		</script>
    </body>
</html>

