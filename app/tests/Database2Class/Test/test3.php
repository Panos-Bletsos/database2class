<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Untitled Document</title>
</head>

<body>
<?php 

function return_result_db($query, $type='mysql', $path_to_db_file = "", $result_type = 'MYSQL_ASSOC')
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
			$db = new PDO('sqlite:diagonismos_db/diagonismos.sqlite');
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
		
		//while ($row = $temp->fetch(PDO::FETCH_ASSOC))
		//{
		foreach($result as $row)
		{
			array_push($return_val, $row);
		}
		
//		if (count($return_val) === 1)
//			$return_val = $return_val[0];
		
		return $return_val;	
	}		
}

$q_innerjoin = "SELECT apofasi.* from apofasi 
				INNER JOIN apofasi_foreis on apofasi.id = apofasi_foreis.apofasi_id 
				INNER JOIN foreis on apofasi_foreis.foreis_id = foreis.id
				WHERE foreis.id = 3;";
$q_where = "SELECT apofasi.* from apofasi 
				INNER JOIN apofasi_foreis on apofasi.id = apofasi_foreis.apofasi_id 
				INNER JOIN foreis on apofasi_foreis.foreis_id = foreis.id
				WHERE foreis.id = 3;";
				
				
	

?>
</body>
</html>