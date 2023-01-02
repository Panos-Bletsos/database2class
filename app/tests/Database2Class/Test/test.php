<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Untitled Document</title>
</head>

<body>
<?php

use ForeignKey\ForeignKey;
use ForeignKey\ForeignKeyRepository;

function getFKs($oLink)
	{
		$query = "SHOW TABLES FROM test_db_2_php;";
		$oResult = mysql_query($query) or die( mysql_error().".<br />\n The query string was: ".$query);
	
		// Preprocessing: For each table find foreign keys
		$FKs = new ForeignKeyRepository();
		
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
	
	function my_scan_dir($a_path)
	{
		$output = array();

		$barescandir = scandir($a_path);
		
		foreach ($barescandir as $a_barescandir_dir)
			if (strcmp($a_barescandir_dir, ".") !== 0 && strcmp($a_barescandir_dir, "..") !== 0)
				array_push($output, $a_barescandir_dir);
				
		return $output;
	}
	
	$files_to_include = my_scan_dir("output/");

	include_once("Database.php");
	include_once("Settings.php");
	
	$instances_array = array();
	foreach ($files_to_include as $a_file_to_include)
	{
		$class_name = substr($a_file_to_include, 6, -4);
		
		if (strcmp($class_name, "database") === 0)
			continue;
		
		include_once ("output/".$a_file_to_include);
	}

/*
require_once("Interrelationship.php");
require_once("Interrelationships.php");
require_once("ForeignKey.php");
require_once("class.FKs.php");

echo "<strong>initial test</strong><br/>";	
$oLink = @mysql_connect("localhost", "jonjon", "kerkyra") or die("Error: Could not connect to server.");
mysql_select_db("test_db_2_php", $oLink);
$FKs = getFKs($oLink);
$part_of_FKs = $FKs->getFK_by_source_table_and_dest_table("student_residence", "student");
print_r($part_of_FKs);
echo "<br/>";
exit();
*/

echo "<strong>car</strong><br/>";	
$a_car = new car();
$a_car->select(1);
if(strcmp($a_car->getplates(), "plates_1") === 0) echo "OK<br/>"; else echo "error car<br/>";

echo "<br/><strong>student</strong><br/>";	
$a_student = new student();
$a_student->select(3);
if(strcmp($a_student->getname(), "student_3") === 0) echo "OK<br/>"; else echo "error student<br/>";

echo "<br/><strong>teacher</strong><br/>";	
$a_teacher = new teacher();
$a_teacher->select(8);
if(strcmp($a_teacher->getname(), "teacher_8") === 0) echo "OK<br/>"; else echo "error teacher<br/>";

echo "<br/><strong>theclass</strong><br/>";	
$a_theclass = new theclass();
$a_theclass->select(4);
if(strcmp($a_theclass->gettitle(), "class_4") === 0) echo "OK<br/>"; else echo "error theclass<br/>";

echo "<br/><strong>residence</strong><br/>";	
$a_residence = new residence();
$a_residence->select(4);
if(strcmp($a_residence->getaddress(), "residence_4") === 0) echo "OK<br/>"; else echo "error residence<br/>";

echo "<br/><strong>car->student</strong><br/>";	
$results = $a_car->get_rlt_student();
$result = $results[2];
if(strcmp($result->getname(), "student_10") === 0) echo "OK<br/>"; else echo "error car->student<br/>";

$a_car->set_rlt_student($a_student);

$results = $a_car->get_rlt_student();
$result = $results[1];
if(strcmp($result->getowns_car(), "1") === 0) echo "OK<br/>"; else echo "error car->student<br/>";


echo "<br/><strong>student->residence</strong><br/>";
$result = $a_student->get_rlt_residence();
if(strcmp($result->getaddress(), "residence_2") === 0) echo "OK<br/>"; else echo "error student->residence<br/>";

echo "<br/><strong>residence->student</strong><br/>";
$a_residence = new residence();
$a_residence->select(2);

$results = $a_residence->get_rlt_student();
$all_ok = 0;
foreach ($results as $a_result)
{
	if(strcmp($a_result->getname(), "student_3") === 0) 
		$all_ok += 3;
		
	if(strcmp($a_result->getname(), "student_2") === 0)
		$all_ok += 2;
}

if ($all_ok === 5)
	echo "OK<br/>"; else echo "error residence->student<br/>";

echo "<br/><strong>theclass->teacher</strong><br/>";
$a_theclass = new theclass();
$a_theclass->select(2);

$results = $a_theclass->get_rlt_teacher();
$all_ok = true;

if (count($results) !== 6)
	$all_ok = false;

//echo prettyFormatDump(print_r($results, true));exit();

foreach ($results as $a_result)
{
	if(strcmp($a_result['teacher']->getname(), "teacher_9") === 0 && strcmp($a_result['from'], "0000-00-00 00:00:00") === 0 && strcmp($a_result['to'], "0000-00-00 00:00:00") === 0)
		$all_ok = $all_ok && true;
	elseif(strcmp($a_result['teacher']->getname(), "teacher_7") === 0 && strcmp($a_result['from'], "0000-00-00 00:00:00") === 0 && strcmp($a_result['to'], "0000-00-00 00:00:00") === 0) 
		$all_ok = $all_ok && true;
	elseif(strcmp($a_result['teacher']->getname(), "teacher_6") === 0 && strcmp($a_result['from'], "0000-00-00 00:00:00") === 0 && strcmp($a_result['to'], "0000-00-00 00:00:00") === 0)
		$all_ok = $all_ok && true;
	elseif(strcmp($a_result['teacher']->getname(), "teacher_5") === 0 && strcmp($a_result['from'], "0000-00-00 00:00:00") === 0 && strcmp($a_result['to'], "0000-00-00 00:00:00") === 0)
		$all_ok = $all_ok && true;
	elseif(strcmp($a_result['teacher']->getname(), "teacher_2") === 0 && strcmp($a_result['from'], "") === 0 && strcmp($a_result['to'], "") === 0)
		$all_ok = $all_ok && true;
	elseif(strcmp($a_result['teacher']->getname(), "teacher_1") === 0 && strcmp($a_result['from'], "2014-07-01 00:00:00") === 0 && strcmp($a_result['to'], "2014-08-01 00:00:00") === 0)
		$all_ok = $all_ok && true;
	else
		$all_ok = $all_ok && false;
}

if ($all_ok === true)
	echo "OK<br/>"; else echo "error theclass->teacher<br/>";


echo "<br/><strong>teacher->theclass</strong><br/>";
$results = $a_teacher->get_rlt_theclass();
$all_ok = true;

if (count($results) !== 3)
	$all_ok = false;

//echo prettyFormatDump(print_r($results, true));

foreach ($results as $a_result)
{
	if(strcmp($a_result['theclass']->gettitle(), "class_1") === 0 && strcmp($a_result['from'], "0000-00-00 00:00:00") === 0 && strcmp($a_result['to'], "0000-00-00 00:00:00") === 0)
		$all_ok = $all_ok && true;
	elseif(strcmp($a_result['theclass']->gettitle(), "class_3") === 0 && strcmp($a_result['from'], "0000-00-00 00:00:00") === 0 && strcmp($a_result['to'], "0000-00-00 00:00:00") === 0) 
		$all_ok = $all_ok && true;
	elseif(strcmp($a_result['theclass']->gettitle(), "class_4") === 0 && strcmp($a_result['from'], "0000-00-00 00:00:00") === 0 && strcmp($a_result['to'], "0000-00-00 00:00:00") === 0)
		$all_ok = $all_ok && true;
	else
		$all_ok = $all_ok && false;
}

if ($all_ok === true)
	echo "OK<br/>"; else echo "error teacher->theclass<br/>";

//echo prettyFormatDump(print_r($result, true));


echo "<br/><strong>teacher->student</strong><br/>";
$a_teacher->select(2);
$results = $a_teacher->get_rlt_student();
$all_ok = true;

if (count($results) !== 2)
	$all_ok = false;

//echo prettyFormatDump(print_r($results, true)); exit();

foreach ($results as $a_result)
{
	if(strcmp($a_result->getid(), "2") === 0)
		$all_ok = $all_ok && true;
	elseif(strcmp($a_result->getid(), "8") === 0)
		$all_ok = $all_ok && true;
	else
		$all_ok = $all_ok && false;
}

if ($all_ok === true)
	echo "OK<br/>"; else echo "error teacher->student<br/>";

//echo prettyFormatDump(print_r($result, true));


echo "<br/><strong>student->teacher</strong><br/>";
$result = $a_student->get_rlt_teacher();
if(strcmp($result->getid(), "1") === 0) echo "OK<br/>"; else echo "error student->teacher<br/>";


echo "<br/><strong>student->car</strong><br/>";
$a_student->select(10);
$result = $a_student->get_rlt_car();
//echo prettyFormatDump(print_r($result, true));exit();
if(strcmp($result->getid(), "1") === 0) echo "OK<br/>"; else echo "error student->teacher<br/>";




?>
</body>
</html>