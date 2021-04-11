<?php

namespace YaleREDCap\FundedGrantDatabase;

# verify user access
if (!isset($_COOKIE['grant_repo'])) {
	header("Location: ".$module->getUrl("src/index.php"));
}

require_once("base.php");

$role = updateRole($userid);
if ($role == 1 | $role == "") {
	header("Location: ".$module->getUrl("src/index.php"));
}

# get metadata
$metadataJSON = \REDCap::getDataDictionary($grantsProjectId, "json");
$choices = getChoices(json_decode($metadataJSON, true));

$filterLogic = $role == 2 ? '[pi_netid] = "'.$userid.'"' : NULL;

# if role=2, then we only want to show stats for their specific grants
if ($role == 2) {
	$filterLogSql = " AND e.pk IN
		(SELECT record
		FROM redcap_data
		WHERE project_id = $grantsProjectId
		AND field_name = 'pi_netid'
		AND value = '$userid')";
}

$grants_result = json_decode(\REDCap::getData(array(
	'project_id'=>$grantsProjectId, 
	'filterLogic'=>$filterLogic,
	"return_format"=>"json"
)), true);

$grant_types = $choices['grants_type'];

# create array to hold downloads
$downloads = array();
foreach ($grants_result as $row) {
	$downloads[$row['record_id']]['title'] = $row['grants_title'];
	$downloads[$row['record_id']]['number'] = $row['grants_number'];
	$downloads[$row['record_id']]['pi'] = $row['grants_pi'];
	$downloads[$row['record_id']]['type'] = $grant_types[$row['grants_type']];
}


$user_result = json_decode(\REDCap::getData(array('project_id'=>$userProjectId, "return_format"=>"json")), true);

$netIds = array();
foreach ($user_result as $row) {
	$netIds[$row['user_id']] = array($row['first_name'], $row['last_name']);
}
$logEventTable = \REDCap::getLogEventTable($grantsProjectId);
$sql = "SELECT e.ts, e.user, e.pk
		FROM $logEventTable e
        WHERE e.project_id = $grantsProjectId
            AND e.description = 'Download uploaded document'
			$filterLogSql
		ORDER BY e.ts DESC";
$result = db_query($sql);

while ($row = db_fetch_array($result)) {
	if ($netIds[$row['user']] && $netIds[$row['user']][0]) {
		$name = $netIds[$row['user']][0] . " " . $netIds[$row['user']][1];
		$username = $row['user'];
	}
	else if ($netIds[$row['user']]) {
		$name = '';
		$username = $row['user'];
	}

	$downloads[$row['pk']]['hits'][] = array('ts' => $row['ts'], 'user' => $name, 'username' => $username);
}
?>

<html>
	<head>
		<title>The Yale University Funded Grant Database - Document Download Information</title>
		<link rel="shortcut icon" type="image" href="<?php echo $module->getUrl("img/favicon.ico") ?>"/> 
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.24/af-2.3.5/b-1.7.0/b-colvis-1.7.0/b-html5-1.7.0/b-print-1.7.0/rg-1.1.2/sb-1.0.1/sp-1.2.2/sl-1.3.3/datatables.min.css"/>
		<link rel="stylesheet" type="text/css" href="<?php echo $module->getUrl("css/basic.css") ?>">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.24/af-2.3.5/b-1.7.0/b-colvis-1.7.0/b-html5-1.7.0/b-print-1.7.0/rg-1.1.2/sb-1.0.1/sp-1.2.2/sl-1.3.3/datatables.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>		
	</head>
	<body>
		<br/>
		<div style="padding-left:8%;  padding-right:10%; margin-left:auto; margin-right:auto;   ">
			<div id="header">
				<?php
					createHeaderAndTaskBar($role);
				?>
				<h3>Yale University Funded Grant Database - Usage Statistics</h3>
				<i>This page shows who has downloaded grant documents and when they did so.</i>
				<hr><br/>
			</div>
			<div id="stats" class="dataTableParentHidden">
				<br/>
				<table id="statsTable" class="dataTable" style="display:hidden;">
				<thead>
					<tr>
						<th>PI</th>
						<th>Grant Title</th>
						<th>Grant #</th>
						<th>Grant Type</th>
						<th>User</th>
						<th>Username</th>
						<th>Access Datetime</th>
					</tr>
				</thead>
				<tbody>
				<?php
				# loop through each grant
				foreach ($downloads as $id => $value) {
					# loop through array of files download and display
					foreach ($value['hits'] as $log) {
						$timestamp = strtotime($log['ts']);
						echo "<tr>";
							echo "<td style='white-space:nowrap;'>".$value['pi']."</td>";
							echo "<td>".$value['title']."</td>";
							echo "<td style='text-align: center;'>".$value['number']."</td>";
							echo "<td style='text-align: center;'>".$value['type']."</td>";
							echo "<td style='text-align: center;'>".$log['user']."</td>";
							echo "<td style='text-align: center;'>".$log['username']."</td>";
							echo "<td style='text-align: center;'>".date('Y-m-d H:i:s', $timestamp)."</td>";
						echo "</tr>";
					}
				}
				?>
				</tbody>
		</div>
		<script>
		$(document).ready( function () {
			$('#statsTable').DataTable({
				/*columns: [
					{"data": "pi"},
					{"data": "title"},
					{"data": "number"},
					{"data": "type"},
					{"data": "user"},
					{"data": "username"},
					{"data": "timestamp"}
				],*/
				order: [[0, 'asc'], [1, 'asc']],
				rowGroup: {
					dataSrc: [
						0, 
						function (row) {
							return `${row[1]} (${row[2]})`;
						}
					]
				},
				columnDefs: [
					{
						targets: [0,1,2,3],
						visible: false,
						searchable: true
					},
					{
						targets: [2],
						searchPanes:{
							options:[
								{
									label: 'GRANT NUMBER CONTAINS 5',
									value: function(rowData, rowIdx) {
										return rowData[2].includes("5");
									}
								}
							]
						}
					}
				],
				
				//pageLength: 1000,
				dom: 'lBfrtip',
				buttons: [
					{
						extend: 'searchPanes',
						config: {
							cascadePanes: true
						}
						
					},
					{
						extend: 'searchBuilder'
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

			$('#stats').removeClass('dataTableParentHidden');
			
			$('#statsTable').DataTable().on( 'buttons-action', function ( e, buttonApi, dataTable, node, config ) {
				const text = buttonApi.text();
				if (text.search(/Panes|Builder/)) {
					$('.dt-button-collection').draggable();
				}
			});
	});
		</script>
	</body>
</html>
