<?php

namespace Database2class\Database2class\Api;

require_once '../../../../vendor/autoload.php';

use Database2class\Database2class\Database;


$a_db = new Database($_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]);

$a_db->Query('SHOW DATABASES');

// Output first option
echo "<option value=\"\"></option>";

foreach ($a_db->result as $a_result) {
    echo "<option value=\"" . $a_result['Database'] . "\">" . $a_result['Database'] . "</option>\n";
}

/*
// Attempt to list databases with supplied credentials.
$oLink = @mysql_connect($_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]) or die("Error: Could not connect to server.");
$oResult = mysql_list_dbs($oLink);

// Check for valid results
if(mysql_affected_rows($oLink) == 0) {
    echo "Error: No databases returned from server \"" . $_POST["serveraddress"] . "\" (" . $_POST["serverusername"] . "@" . $_POST["serverpassword"] . ")";
    exit;
}

// Output first option
echo "<option value=\"\"></option>";

// Output Database Names
while($oRow = mysql_fetch_object($oResult)) {
    echo "<option value=\"" . $oRow->Database . "\">" . $oRow->Database . "</option>\n";
}
*/
?>