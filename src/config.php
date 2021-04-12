<?php

namespace YaleREDCap\FundedGrantDatabase;

// PIDs of REDCap Projects
$grantsProjectId        = $module->getSystemSetting("grants-project");
$userProjectId          = $module->getSystemSetting("users-project");

// Aesthetics
$accentColor            = $module->getSystemSetting("accent-color");
$accentTextColor        = $module->getSystemSetting("text-color");
$secondaryAccentColor   = $module->getSystemSetting("secondary-accent-color");
$secondaryTextColor     = $module->getSystemSetting("secondary-text-color");
$logoFile               = $module->getSystemSetting("logo");
$favicon                = $module->getSystemSetting("favicon");
$databaseTitle          = $module->getSystemSetting("database-title");


// Check out provided PIDs
checkPID($grantsProjectId, 'Grants Project');
checkPID($userProjectId, 'Users Project');

// Set default if any aesthetics are missing
$accentColor            = is_null($accentColor) ? "#00356b" : $accentColor;
$accentTextColor        = is_null($accentTextColor) ? "#f9f9f9" : $accentTextColor;
$secondaryAccentColor   = is_null($secondaryAccentColor) ? adjustBrightness($accentColor, 0.50) : $secondaryAccentColor;
$secondaryTextColor     = is_null($secondaryTextColor) ? adjustBrightness($accentTextColor, getBrightness($secondaryAccentColor) >= 0.70 ? -1 : 1) : $secondaryTextColor;
$logoImage              = is_null($logoFile) ? $module->getUrl("img/yu.png") : getFile($logoFile);
$faviconImage           = is_null($favicon) ? $module->getUrl("img/favicon.ico") : getFile($favicon);
$databaseTitle          = is_null($databaseTitle) ? "Yale University Funded Grant Database" : $databaseTitle;



function checkPID($pid, $label) {
    global $module;
    if (is_null($pid)) die ("A PID must be listed for the ".$label." in the system settings.");

    $sql = 'select * from redcap_projects where project_id = ?';
    $result = $module->query($sql, $pid);
    $row = $result->fetch_assoc();
    $error = false;
    if (is_null($row)) {
        $error = true;
        $message = "The project does not exist";
    } else if (!empty($row["date_deleted"])) {
        $error = true;
        $message = "The project must not be deleted.";
    } else if (!empty($row["completed_time"])) {
        $error = true;
        $message = "The project must not be in Completed status";
    } else if ($row["status"] == 2) {
        $error = true;
        $message = "The project must not be in Analysis/Cleanup status";
    }
    if ($error) {
        die ("<strong>There is a problem with PID".$pid." (".$label."):</strong><br>".$message);
    }
}

function getFile($edocId) {
    global $module;
    $result = $module->query('SELECT stored_name FROM redcap_edocs_metadata WHERE doc_id = ?', $edocId);
    $filename = $result->fetch_assoc()["stored_name"];
    return APP_PATH_WEBROOT_FULL."edocs/".$filename;
}
