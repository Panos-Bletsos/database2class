<?php
	/////////////////////////////////////////
//	ini_set('display_errors', true);
//	ini_set('html_errors', true);
//	error_reporting(E_ALL);
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

function processFKs($PKs, $UKs, $FKs)
{
	$all_interrelationships = new Interrelationships();
		
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
			if (isset($PKs[$a_table][$a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()]) === true)
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
					$temp_via_table_eq_args .= $a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()." = ".$a_tables_FK->getdest_table().".".$a_tables_FK->getdest_attribute(). " AND ";
				else
					$temp_via_table_eq_args .= $an_interrelationship->getvia_table()." AND ".$a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()." = ".$a_tables_FK->getdest_table().".".$a_tables_FK->getdest_attribute(). " AND ";
					
				$tables_used[$a_tables_FK->getdest_table()] = $a_tables_FK->getdest_table();
				
				$an_interrelationship->settables_involved($an_interrelationship->gettables_involved().$a_tables_FK->getdest_table().", ");
			}
			
			$temp_via_table_eq_args = substr($temp_via_table_eq_args,0, -5);
			$an_interrelationship->setvia_table_eq_args($temp_via_table_eq_args);
			
			if (count($tables_used) > 2)
				die ("[1] N-ary relationships with N>2 are not supported. Sorry :-( ");
			else
			{
				$values=array_values($tables_used);
				$an_interrelationship->setsource_table($values[0]);				
				$an_interrelationship->setdest_table($values[1]);
			}
			
			$an_interrelationship->settables_involved($an_interrelationship->gettables_involved().$a_table);
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
					$temp_via_table_eq_args .= $a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()." = ".$a_tables_FK->getdest_table().".".$a_tables_FK->getdest_attribute()." AND ";
				else
					$temp_via_table_eq_args .= $an_interrelationship->getvia_table()." AND ".$a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()." = ".$a_tables_FK->getdest_table().".".$a_tables_FK->getdest_attribute()." AND ";
					
				$tables_used[$a_tables_FK->getdest_table()] = $a_tables_FK->getdest_table();
				$an_interrelationship->settables_involved($an_interrelationship->gettables_involved().$a_tables_FK->getdest_table().", ");
				
				if (isset($PKs[$a_table][$a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()]) === true)
					$an_interrelationship->setcardinality("N");
				else
					$an_interrelationship->setcardinality("1");
			}
			
			$temp_via_table_eq_args = substr($temp_via_table_eq_args,0, -5);
			$an_interrelationship->setvia_table_eq_args($temp_via_table_eq_args);
			
			if (count($tables_used) > 2)
				die ("[2] N-ary relationships with N>2 are not supported. Sorry :-(");
			else if(count($tables_used) == 2)
			{
				$values=array_values($tables_used);
				$an_interrelationship->setsource_table($values[0]);
				$an_interrelationship->setdest_table($values[1]);
			}
			else 
			{
				die ("Unexpected relationship! Aborting...");
			}
			
			$an_interrelationship->settables_involved($an_interrelationship->gettables_involved().$a_table);
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

				if (isset($PKs[$a_table][$a_tables_FK->getsource_table().".".$a_tables_FK->getsource_attribute()]) === true)
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
	$oResult = mysql_query($query) or die( mysql_error().".<br />\n The query string was: ".$query);
	
	$nonFKAttributes = array();
		
	// For each table
	while($oRow = mysql_fetch_row($oResult))
	{
		$nonFKAttributes[$oRow[0]] = array();
		
		// Finds the tables of the db
		$queryAttributes = "SHOW COLUMNS FROM ".$oRow[0].";";
		$tablesAttributes = mysql_query($queryAttributes) or die( mysql_error().".<br />\n The query string was: ".$queryPKs);
		
		// Collect table names
		while($tablesAttributesRows = mysql_fetch_object($tablesAttributes)) 
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

function getFKs($oLink)
{
	$query = "SHOW TABLES FROM ".$_POST["database"].";";
	$oResult = mysql_query($query) or die( mysql_error().".<br />\n The query string was: ".$query);

	// Preprocessing: For each table find foreign keys
	$FKs = new FKs();
	
	while($oRow = mysql_fetch_row($oResult))
	{
		$queryFKs = "SELECT DISTINCT
		concat(table_name, '.', column_name) as 'foreign_key',  
		concat(referenced_table_name, '.', referenced_column_name) as 'references'
	FROM
		information_schema.key_column_usage
	WHERE
		referenced_table_name is not null
		AND table_name = '".$oRow[0]."'";
		
		$tablesFKs = mysql_query($queryFKs) or die( mysql_error().".<br />\n The query string was: ".$queryFKs);
		
		// Collect table's FKs
		while($tablesFKsRows = mysql_fetch_object($tablesFKs)) 
		{
			//Old array solution that was wrong!
			//$FKs[stristr($tablesFKsRows->foreign_key, ".", true)][$tablesFKsRows->foreign_key] = $tablesFKsRows->references;
			
			//place all info in an object
			$FK_object = new FK();			
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

function getPKsUKsAIs($oLink, &$primary_keys, &$unique_keys, &$auto_increment_attributes)
{
	$query = "SHOW TABLES FROM ".$_POST["database"].";";
	$oResult = mysql_query($query) or die( mysql_error().".<br />\n The query string was: ".$query);
	
	//Get tables' primary keys
	$primary_keys = array();
	
	//Get tables' unique keys
	$unique_keys = array();
	
	//Get tables' auto_increment attributes
	$auto_increment_attributes = array();
	
	// For each table
	while($oRow = mysql_fetch_row($oResult))
	{
		$primary_keys[$oRow[0]] = array();
		$unique_keys[$oRow[0]] = array();
		$auto_increment_attributes[$oRow[0]] = array();
		
		// Finds the tables of the db
		$queryPKs = "SHOW COLUMNS FROM ".$oRow[0].";";
		$tablesPKs = mysql_query($queryPKs) or die( mysql_error().".<br />\n The query string was: ".$queryPKs);
		
		// Collect table names
		while($tablesPKsRows = mysql_fetch_object($tablesPKs)) 
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

require_once(dirname(__FILE__) . '/class.tableclass.php');
require_once("../class.interrelationship.php");
require_once("../class.interrelationships.php");
require_once("../class.FK.php");
require_once("../class.FKs.php");

echo "eee";exit();

//Setup variable
$primary_keys;
$unique_keys;
$auto_increment_attributes;


// Finds the tables of the db
$oLink = @mysql_connect($_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]) or die("Error: Could not connect to server.");
mysql_select_db($_POST["database"], $oLink);

//Get table's primary keys
//Get table's unique keys
//Get table's auto_increment attributes
getPKsUKsAIs($oLink, $primary_keys, $unique_keys, $auto_increment_attributes);
//echo prettyFormatDump(print_r($primary_keys, true));
//echo prettyFormatDump(print_r($unique_keys, true));
//echo prettyFormatDump(print_r($auto_increment_attributes, true));

//Get all FKs
$FKs = getFKs($oLink);
//echo prettyFormatDump(print_r($FKs, true));

//Process FKs
$all_interrelationships = processFKs($primary_keys, $unique_keys, $FKs);
//echo prettyFormatDump(print_r($all_interrelationships, true));

//Identify non-FK attributes in tables
$nonFKAttributes = getNonFKAttributes($FKs);
//echo prettyFormatDump(print_r($nonFKAttributes, true));

//Start processing each table-class
$query = "SHOW TABLES FROM ".$_POST["database"].";";
$oResult = mysql_query($query) or die( mysql_error().".<br />\n The query string was: ".$query);

// For each table
while($oRow = mysql_fetch_row($oResult))
{	
	// Create object for class file.
//	$oClass = new tableClass($oRow[0], $_POST["database"], $oRow[0], $primary_keys[$oRow[0]], $_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"], $unique_keys[$oRow[0]], $auto_increment_attributes[$oRow[0]], $all_interrelationships);
	$oClass = new tableClass($oRow[0], $_POST["database"], $oRow[0], $primary_keys, $_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"], $unique_keys[$oRow[0]], $auto_increment_attributes[$oRow[0]], $all_interrelationships, $nonFKAttributes, $FKs);

	
	// Save the class to a file.
	$strPath = realpath($oClass->createClass());
	
	echo "Generating class file for class ".$oRow[0]."<br />";
}

	echo "<a href='download_output.php'>Get all classes of the db in a zip archive</a>";

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