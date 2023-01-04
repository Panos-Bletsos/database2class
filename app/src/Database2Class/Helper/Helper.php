<?php

namespace Database2class\Database2class\Helper;

use PDO;

class Helper
{
    public static function return_result_db($query, $result_type = 'MYSQL_ASSOC', $type = 'mysql', $path_to_db_file = "")
    {
        if (strcmp($type, 'mysql') === 0) {
            global $db_uri;
            global $db_name;
            global $db_user_username;
            global $db_user_password;

            $db = new PDO('mysql:host=' . $db_uri . ';charset=utf8;dbname=' . $db_name, $db_user_username, $db_user_password);
        } else {
            if (strcmp($path_to_db_file, "") === 0)
                $db = new PDO('sqlite:XXXX/XXXX.sqlite');    //Change here for default value
            else
                $db = new PDO('sqlite:' . $path_to_db_file);
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

        if ($result === false) {
            $the_error = $db->errorInfo();
            exit("There was an error querying the db!<br />The query was: " . $query . "<br />Error info: " . $the_error[2]);
        } else {
            $return_val = array();

            foreach ($result as $row) {
                array_push($return_val, $row);
            }

//		if (count($return_val) === 1)
//			$return_val = $return_val[0];

            return $return_val;
        }
    }
}