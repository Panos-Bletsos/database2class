<?php
namespace Database2class\Database2class\Api;

use Database2class\Database2class\Interrelationship\Interrelationship;
use Database2class\Database2class\Table;
use Database2class\Database2class\ForeignKey\ForeignKey;
use Database2class\Database2class\ForeignKey\ForeignKeyRepository;
use Database2class\Database2class\Interrelationship\InterrelationshipRepository;
use PDO;

ini_set('display_errors', true);
	ini_set('html_errors', true);
	error_reporting(E_ALL);
	/////////////////////////////////////////


/*
function tablesAttributes($a_table)
{
	$result = array();

	// Finds the tables of the db
	$queryAttributes = "SHOW COLUMNS FROM ".$a_table.";";
	$tablesAttributes = mysql_query($queryAttributes) or die( mysql_error().".<br />\n The query string was: ".$queryPKs);
	
	// Collect table names
	while($tablesAttributesRows = mysql_fetch_object($tablesAttributes)) 
	{
		$result['$tablesAttributesRows->Field']
		if($FKs->existsSource($oRow[0], $tablesAttributesRows->Field) === false)
			$nonFKAttributes[$oRow[0]][$tablesAttributesRows->Field] = $tablesAttributesRows->Field;
	}	
}
*/

function return_result_db($query, $result_type = 'MYSQL_ASSOC', $type='mysql', $path_to_db_file = "")
{
	if (strcmp($type, 'mysql') === 0)
	{
		global $db_uri;
		global $db_name;
		global $db_user_username;
		global $db_user_password;
	
		$db = new PDO('mysql:host='.$db_uri.';charset=utf8;dbname='.$db_name, $db_user_username, $db_user_password);
	}
	else
	{
		if (strcmp($path_to_db_file, "") === 0)
			$db = new PDO('sqlite:XXXX/XXXX.sqlite');	//Change here for default value
		else
			$db = new PDO('sqlite:'.$path_to_db_file);	
	}
		
	//$temp = $db->prepare($query);
	//$execution_result = $temp->execute();
	if (strcmp($result_type, 'MYSQL_ASSOC') === 0)
		$result = $db->query($query, PDO::FETCH_ASSOC);
	elseif (strcmp($result_type, 'MYSQL_NUM') === 0)
		$result = $db->query($query, PDO::FETCH_NUM);
	elseif (strcmp($result_type, 'MYSQL_BOTH') === 0)
		$result = $db->query($query, PDO::FETCH_BOTH);
	elseif (strcmp($result_type, 'MYSQL_OBJ') === 0)
		$result = $db->query($query, PDO::FETCH_OBJ);
	else
		exit("Not recognised result_type");
		
	if ($result === false)
	{
		$the_error = $db->errorInfo();
		exit("There was an error querying the db!<br />The query was: ".$query."<br />Error info: ".$the_error[2]);
	}
	else
	{
		$return_val = array();
		
		foreach($result as $row)
		{
			array_push($return_val, $row);
		}
		
//		if (count($return_val) === 1)
//			$return_val = $return_val[0];
		
		return $return_val;	
	}		
}

function processFKs($PKs, $UKs, $FKs)
{
	$all_interrelationships = new InterrelationshipRepository();
		
	//Find cardinality cases
	$allSourceTables = $FKs->getAllSourceTables();
	
	foreach($allSourceTables as $a_table)
	{
		$a_tables_FKs = $FKs->return_FKs_having_source_table($a_table);
				
		//Check no of FKs that are PKs in a table
		$noOfPKs = 0;	
		$noOfFKs = $FKs->count_FKs_orginating_from_table($a_table);
		
		//foreach($a_tables_FKs as $source_FK => $dest_FK)
		foreach($a_tables_FKs->getall_FKs() as $a_tables_FK)
		{
			if (isset($PKs[$a_table][$a_tables_FK->getsource_table() . "includes" .$a_tables_FK->getsource_attribute()]) === true)
				$noOfPKs++;
		}
		
		if($noOfPKs === $noOfFKs)	//All FKs are PKs => TfR with M:N cardinality
		{
			$an_interrelationship = new Interrelationship();
			
			$tables_used = array();
			$attributes_used = array();
			
			//foreach($a_tables_FKs as $source_FK => $dest_FK)
			$temp_via_table_eq_args = "";
			foreach($a_tables_FKs->getall_FKs() as $a_tables_FK)
			{				
				if (strcmp($an_interrelationship->getvia_table(), "") === 0)
					$temp_via_table_eq_args .= $a_tables_FK->getsource_table() . "includes" .$a_tables_FK->getsource_attribute()." = ".$a_tables_FK->getdest_table().".".$a_tables_FK->getdest_attribute(). " AND ";
				else
					$temp_via_table_eq_args .= $an_interrelationship->getvia_table()." AND ".$a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()." = ".$a_tables_FK->getdest_table().".".$a_tables_FK->getdest_attribute(). " AND ";
					
				$tables_used[$a_tables_FK->getdest_table()] = $a_tables_FK->getdest_table();
				
				$an_interrelationship->settables_involved($an_interrelationship->gettables_involved() . $a_tables_FK->getdest_table() .", ");
			}
			
			$temp_via_table_eq_args = substr($temp_via_table_eq_args,0, -5);
			$an_interrelationship->setvia_table_eq_args($temp_via_table_eq_args);
			
			if (count($tables_used) > 2)
				die ("N-ary relationships with N>2 are not supported. Sorry :-(");
			else
			{
				$values=array_values($tables_used);
				$an_interrelationship->setsource_table($values[0]);				
				$an_interrelationship->setdest_table($values[1]);
			}
			
			$an_interrelationship->settables_involved($an_interrelationship->gettables_involved() . $a_table);
			$an_interrelationship->setrelationship_type("M:N, TfR");
			$an_interrelationship->setextra_attributes(false);
			$an_interrelationship->setcardinality("N");
			
			//
			$an_interrelationship->setvia_table($a_table);
			//

			$all_interrelationships->add_an_interrelationship($an_interrelationship);
			
			$a_mirrored_interrelationship = clone $an_interrelationship;
			$a_mirrored_interrelationship->mirror_direction();
			
			$all_interrelationships->add_an_interrelationship($a_mirrored_interrelationship);
		}
		elseif($noOfPKs > 0 && $noOfPKs < $noOfFKs)	//At least one FK is a PK => TfR with 1:N cardinality
		{
			$temp_extra_attributes = "";
			$an_interrelationship = new Interrelationship();
			
			$tables_used = array();
			
			//foreach($a_tables_FKs as $source_FK => $dest_FK)
			$temp_via_table_eq_args = "";
			foreach($a_tables_FKs->getall_FKs() as $a_tables_FK)
			{
				if (strcmp($an_interrelationship->getvia_table(), "") === 0)
					$temp_via_table_eq_args .= $a_tables_FK->getsource_table() . "api" .$a_tables_FK->getsource_attribute()." = ".$a_tables_FK->getdest_table().".".$a_tables_FK->getdest_attribute()." AND ";
				else
					$temp_via_table_eq_args .= $an_interrelationship->getvia_table()." AND ".$a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()." = ".$a_tables_FK->getdest_table().".".$a_tables_FK->getdest_attribute()." AND ";
					
				$tables_used[$a_tables_FK->getdest_table()] = $a_tables_FK->getdest_table();
				$an_interrelationship->settables_involved($an_interrelationship->gettables_involved() . $a_tables_FK->getdest_table() .", ");
				
				if (isset($PKs[$a_table][$a_tables_FK->getsource_table() . "api" .$a_tables_FK->getsource_attribute()]) === true)
					$an_interrelationship->setcardinality("N");
				else
					$an_interrelationship->setcardinality("1");
			}
			
			$temp_via_table_eq_args = substr($temp_via_table_eq_args,0, -5);
			$an_interrelationship->setvia_table_eq_args($temp_via_table_eq_args);
			
			if (count($tables_used) > 2)
				die ("N-ary relationships with N>2 are not supported. Sorry :-(");
			else
			{
				$values=array_values($tables_used);
				$an_interrelationship->setsource_table($values[0]);
				$an_interrelationship->setdest_table($values[1]);
			}
			
			$an_interrelationship->settables_involved($an_interrelationship->gettables_involved() . $a_table);
			$an_interrelationship->setrelationship_type("1:N, TfR");	
			$an_interrelationship->setextra_attributes("Aaaa");	
			
			//
			$an_interrelationship->setvia_table($a_table);
			//
			
			$all_interrelationships->add_an_interrelationship($an_interrelationship);
			
			$a_mirrored_interrelationship = clone $an_interrelationship;
			$a_mirrored_interrelationship->mirror_direction();
			
			$all_interrelationships->add_an_interrelationship($a_mirrored_interrelationship);
		}
		else	//No FKs that are also PKs exist => No use of TfR with 1:N cardinality
		{
			foreach($a_tables_FKs->getall_FKs() as $a_tables_FK)
			{	
				$an_interrelationship = new Interrelationship();
				$an_interrelationship->setsource_table($a_table);
				$an_interrelationship->setsource_attribute($a_tables_FK->getsource_attribute());
				$an_interrelationship->setdest_table($a_tables_FK->getdest_table());
				$an_interrelationship->setdest_attribute($a_tables_FK->getdest_attribute());

				if (isset($PKs[$a_table][$a_tables_FK->getsource_table() . "api" .$a_tables_FK->getsource_attribute()]) === true)
					$an_interrelationship->setcardinality("N");
				else
					$an_interrelationship->setcardinality("1");

				$an_interrelationship->setrelationship_type("1:N, no TfR");
				$an_interrelationship->setextra_attributes(false);
				
				$all_interrelationships->add_an_interrelationship($an_interrelationship);
				
				$a_mirrored_interrelationship = clone $an_interrelationship;
				$a_mirrored_interrelationship->mirror_direction();
				
				$all_interrelationships->add_an_interrelationship($a_mirrored_interrelationship);
			}
		}
	}
	
	return $all_interrelationships;
}

function getNonFKAttributes($FKs)
{
	$query = "SHOW TABLES FROM ".$_POST["database"].";";
	$oResult = return_result_db($query, 'MYSQL_NUM');
	
	$nonFKAttributes = array();
		
	// For each table
	foreach ($oResult as $oRow)
	{
		$nonFKAttributes[$oRow[0]] = array();
		
		// Finds the tables of the db
		$queryAttributes = "SHOW COLUMNS FROM ".$oRow[0].";";
		$tablesAttributes = return_result_db($queryAttributes, 'MYSQL_OBJ');
		
		// Collect table names
		foreach ($tablesAttributes as $tablesAttributesRows ) 
		{
			//if(isset($FKs[$oRow[0]][$oRow[0].".".$tablesAttributesRows->Field]) !== true)
			if($FKs->existsSource($oRow[0], $tablesAttributesRows->Field) === false)
				$nonFKAttributes[$oRow[0]][$tablesAttributesRows->Field] = $tablesAttributesRows->Field;
		}	
	}
	
	return $nonFKAttributes;
}

function prettyFormatDump($a_dump)
{
	$a_dump = str_replace("\n", "<br />", $a_dump);
	$parts = explode("<br />", $a_dump);

	$a_dump = "";
		
	foreach ($parts as $a_part)
	{
		$no_of_tabs = substr_count($a_part, "    ");
		
		for($i=0; $i<$no_of_tabs; $i++)
		{
			$a_part = "<blockquote>".$a_part."</blockquote>";
		}	
		$a_dump .= $a_part."";
	}

	return $a_dump;
}

function getFKs()
{
	$query = "SHOW TABLES FROM ".$_POST["database"].";";
	$oResult = return_result_db($query, 'MYSQL_NUM');

	// Preprocessing: For each table find foreign keys
	$FKs = new ForeignKeyRepository();
	
	//while($oRow = mysql_fetch_row($oResult))
	foreach ($oResult as $oRow)
	{
		$queryFKs = "SELECT DISTINCT
		concat(table_name, '.', column_name) as 'foreign_key',  
		concat(referenced_table_name, '.', referenced_column_name) as 'references'
	FROM
		information_schema.key_column_usage
	WHERE
		referenced_table_name is not null
		AND table_name = '".$oRow[0]."';";
		
		//$tablesFKs = mysql_query($queryFKs) or die( mysql_error().".<br />\n The query string was: ".$queryFKs);
		
		$oResult2 = return_result_db($queryFKs, 'MYSQL_OBJ');
		
		// Collect table's FKs
		//while($tablesFKsRows = mysql_fetch_object($tablesFKs)) 
		foreach ($oResult2 as $tablesFKsRows)
		{
			//Old array solution that was wrong!
			//$FKs[stristr($tablesFKsRows->foreign_key, ".", true)][$tablesFKsRows->foreign_key] = $tablesFKsRows->references;
			
			//place all info in an object
			$FK_object = new ForeignKey();
			$source_parts = explode(".", $tablesFKsRows->foreign_key);
			$dest_parts = explode(".", $tablesFKsRows->references);
			
			$FK_object->setsource_table($source_parts[0]);
			$FK_object->setsource_attribute($source_parts[1]);
			$FK_object->setdest_table($dest_parts[0]);
			$FK_object->setdest_attribute($dest_parts[1]);
			
			$FKs->add_an_FK($FK_object);
		}	
	}
	
	return $FKs;
}

function getPKsUKsAIs(&$primary_keys, &$unique_keys, &$auto_increment_attributes)
{
	$query = "SHOW TABLES FROM ".$_POST["database"].";";
	$oResult = return_result_db($query, 'MYSQL_NUM');
	
	//Get tables' primary keys
	$primary_keys = array();
	
	//Get tables' unique keys
	$unique_keys = array();
	
	//Get tables' auto_increment attributes
	$auto_increment_attributes = array();
	
	// For each table
	foreach($oResult as $oRow)
	{
		$primary_keys[$oRow[0]] = array();
		$unique_keys[$oRow[0]] = array();
		$auto_increment_attributes[$oRow[0]] = array();
		
		// Finds the tables of the db
		$queryPKs = "SHOW COLUMNS FROM ".$oRow[0].";";
		$oResult2 = return_result_db($queryPKs, 'MYSQL_OBJ');
		//$tablesPKs = mysql_query($queryPKs) or die( mysql_error().".<br />\n The query string was: ".$queryPKs);
		
		
		// Collect table names
		//while($tablesPKsRows = mysql_fetch_object($tablesPKs)) 
		foreach ($oResult2 as $tablesPKsRows)
		{
			if(strcmp($tablesPKsRows->Key, "PRI") === 0)
				$primary_keys[$oRow[0]][$oRow[0].".".$tablesPKsRows->Field] = $tablesPKsRows->Field; 
				
			if(strcmp($tablesPKsRows->Key, "UNI") === 0)
				array_push($unique_keys[$oRow[0]], $tablesPKsRows->Field); 
				
			if(strcmp($tablesPKsRows->Extra, "auto_increment") === 0)
				array_push($auto_increment_attributes[$oRow[0]], $tablesPKsRows->Field); 
		}
		
		if (count($auto_increment_attributes[$oRow[0]]) > 1)
			die("More than auto incremented attributes. Not supported. Exiting!");
	}
}
require_once('../Database.php');
require_once('Table.php');
require_once("../Interrelationship/Interrelationship.php");
require_once("../Interrelationship/InterrelationshipRepository.php");
require_once("../ForeignKey/ForeignKey.php");
require_once("../ForeignKey/ForeignKeyRepository.php");

//Setup variable
$primary_keys;
$unique_keys;
$auto_increment_attributes;


// Finds the tables of the db
$db_uri 			= $_POST["serveraddress"];
$db_name 			= $_POST["database"];
$db_user_username 	= $_POST["serverusername"];
$db_user_password 	= $_POST["serverpassword"];

//$oLink = @mysql_connect($_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]) or die("Error: Could not connect to server.");
//mysql_select_db($_POST["database"], $oLink);

//Get table's primary keys
//Get table's unique keys
//Get table's auto_increment attributes
getPKsUKsAIs($primary_keys, $unique_keys, $auto_increment_attributes);
//echo prettyFormatDump(print_r($primary_keys, true));
//echo prettyFormatDump(print_r($unique_keys, true));
//echo prettyFormatDump(print_r($auto_increment_attributes, true));

//Get all FKs
$FKs = getFKs();
//echo prettyFormatDump(print_r($FKs, true));

//Process FKs
$all_interrelationships = processFKs($primary_keys, $unique_keys, $FKs);
//echo prettyFormatDump(print_r($all_interrelationships, true));

//Identify non-FK attributes in tables
$nonFKAttributes = getNonFKAttributes($FKs);
//echo prettyFormatDump(print_r($nonFKAttributes, true));

//Start processing each table-class
$query = "SHOW TABLES FROM ".$_POST["database"].";";
$oResult = return_result_db($query, 'MYSQL_NUM');

// For each table
foreach ($oResult as $oRow)
{	
	// Create object for class file.
//	$oClass = new tableClass($oRow[0], $_POST["database"], $oRow[0], $primary_keys[$oRow[0]], $_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"], $unique_keys[$oRow[0]], $auto_increment_attributes[$oRow[0]], $all_interrelationships);
	$oClass = new Table($oRow[0], $_POST["database"], $oRow[0], $primary_keys, $_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"], $unique_keys[$oRow[0]], $auto_increment_attributes[$oRow[0]], $all_interrelationships, $nonFKAttributes, $FKs);

	
	// Save the class to a file.
	$strPath = realpath($oClass->createClass());
	
	echo "Generating class file for class ".$oRow[0]."<br />";
}

	echo "<br /><a href='GetClassesZipped.php'>Get all classes of the db in a zip archive</a>";

/*
if(isset($_GET["displayclass"]) && $_GET["displayclass"] > 0) {
    // Display the class, do not save.
    $oClass->createClass(TRUE, FALSE);
} else {
    // Save the class to a file.
    $oClass->createClass();
    echo "Class created successfully.";
}
*/
?>