<?php

/** Authors: Jon Scherdin, Scott Pearson, Andrew Poppe */

require_once("base.php");

$timestamp = date('Y-m-d');
$role = "";
$user_id = "";

# query table to authenticate user
$result = authenticate($userid, $timestamp);

# get user_id and role
if (db_num_rows($result) > 0) {
	$user_id = db_result($result, 0, 0);
	$role = db_result($result, 0, 1);
}

# log visit
$module->log("Visited Index Page", array("user"=>$userid, "role"=>$role));

# if they have agreed to the terms, create the cookie and redirect them to the grants page
if (isset($_POST['submit'])) {
	setcookie('grant_repo', $role);
    header("Location: ".$module->getUrl("src/grants.php"));
}
 
$startTs = strtotime("2021-01-01");
if (($user_id != "") && ($startTs <= time())) {
	$saveData = [
			"user_id" => $user_id,
			"accessed" => '1',
			];
	$json = json_encode([$saveData]);
	\REDCap::saveData($userProjectId, "json", $json, "overwrite");
}

echo '<html>
    <head>
        <link rel="stylesheet" type="text/css" href="'.$module->getUrl("css/basic.css").'">
    </head>
    <body style="background-color: #f9f9f9;">
        <br/>    
        <div style="padding-left:8%;  padding-right:10%; margin-left:auto; margin-right:auto; ">
            <div style="padding: 10px; background-color: '.\REDCap::escapeHtml($accentColor).';"></div>  
            <img src="'.\REDCap::escapeHtml($logoImage).'" style="vertical-align:middle"/>
            <hr>
            <h3>'.\REDCap::escapeHtml($databaseTitle).'</h3>
            <br/>';
if ($role != "") {
    echo '<p><strong>NOTICE: You must agree to the following terms before using the '.\REDCap::escapeHtml($databaseTitle).'</strong></p>
                <ul> 
                    <li>I agree to keep the contents of the example grants confidential.</li>
                    <li>I will not share any part(s) of the grants in the database.</li>
                    <li>I agree not to utilize any text of the grant in my own grant.</li>
                    <li>I understand that the individuals who provided grants will be able to view a list of names of those who accessed their grants.</li>
                    <li>I agree to provide a copy of my grant after submission to be kept on file and reviewed for compliance to this agreement.</li>
                </ul>
                <form  method="post">
                    <input type="submit" value="I agree to all terms above" name="submit" style="cursor: pointer;">
                </form>';
} else {
    echo 'Please contact '.\REDCap::escapeHtml($contactName).' at <a href="mailto:'.\REDCap::escapeHtml($contactName).'">'.\REDCap::escapeHtml($contactName).'</a> to gain access to the '.\REDCap::escapeHtml($databaseTitle).'.';
}
echo '</div></html>';
