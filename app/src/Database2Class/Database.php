<?php
/*
*   Database.php
*   loosely based on a class by: MARCO VOEGELI (www.voegeli.li)
*
*   This class provides one central database-connection for
*   all your php applications. Define only once your connection
*   settings and use it in all your applications.
*/

namespace Database2Class;

class Database {
	public $host;           // Hostname / Server
	public $password;       // MySQL Password
	public $user;           // MySQL Username
    public $database;       // MySQL Database Name
	public $link;
	public $query;
	public $result;
	public $rows;
    public $debug;          // Whether to print debug (testing) info (default 0)
    private $logfile;       // Where to log errors (optional)
    public $persistentconn; // Whether to use persistent connections.
    public $lastinsertid;   // ID of last record inserted, if we ever did.

	public function __construct($strHost = "localhost", $strUser = "root", $strPassword = "", $strDatabase = "") {
		// Method : begin
		//Konstruktor
		// ********** ADJUST THESE VALUES HERE **********
		$this->host = $strHost;
		$this->password = $strPassword;
		$this->user = $strUser;
		$this->database = $strDatabase;
		$this->rows = 0;
        $this->link = NULL;
        $this->debug = TRUE;
        //$this->persistentconn = FALSE;
        $this->lastinsertid = -1;
		// **********************************************
	}

    public function __destruct() {
        // Destroy the MySQL connection on unset, even if we are using mysql_pconnect().
        $this->CloseDB();
    }

    private function failureHandler($iError, $sQuery = "") {
        $sRet = "SQL Error: $iError";
        if($sQuery != "") {
            $sRet .= "\n... Executing Query: $sQuery\n";
        }

        // Log to file, if set.
        if($this->logfile) {
            error_log($sRet, 3, $this->logfile);
        }

        // Return full debug info if in debug mode.
        if($this->debug) {
            return("<hr>" . $sRet);
        }
        return("<hr>Requested page has encountered an error, please try again later.");
    }

    public function SetSettings($strHost, $strUser, $strPass, $strDatabase) {
        // Sets the connection settings.
        $this->host = $strHost;
        $this->user = $strUser;
        $this->password = $strPass;
        $this->database = $strDatabase;
    }

	public function OpenLink_SelectDB() {
		// Close the previous connection if we have one open.
        // We do this because if the server/user/pass change, the class will open a new link and never close the old one.
        if(!(($this->link === NULL) || ($this->link === FALSE))) {
            $this->link = NULL;
        }

        // Open the connection, persistent or not.
		$this->link = new PDO('mysql:host='.$this->host.';charset=utf8;dbname='.$this->database, $this->user, $this->password, array(PDO::ATTR_PERSISTENT => true));

        // Return link value.
        return($this->link);
	}

	public function CloseDB() {
        // Closes our connection and resets the link variable if successful.

        // First check to see if we have a connection open.
        if(isset($this->link)) 
			$this->link = NULL;
  
        return(TRUE);
	}

	public function Query($query, $result_type = 'MYSQL_ASSOC') {
        // Reset our rows/result variables.
        $this->rows = 0;
        $this->result = NULL;

        // Establish connection to the database.
        $this->OpenLink_SelectDB();

        // Clean SQL to prevent attacks
        //$query = stripslashes(mysql_real_escape_string($query));
		//$query = addslashes($query);
		
/*
//Make it with prepared statements
//http://stackoverflow.com/questions/14012642/what-is-the-pdo-equivalent-of-mysql-real-escape-string

try {
       $db = new PDO("mysql:host=localhost;dbname=xx;charset=utf8", "xx", "xx"); 
    } catch(PDOException $e){
       echo "ERROR: ". $e->getMessage();
    }

  $connection->setAttribute(
       PDO::ATTR_EMULATE_PREPARES => false, 
       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ); 


if($_POST["username"]){
    $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute(array($_POST['name']));
 }

*/
		

        // Execute query.
		$this->query = $query;
		
		if (strcmp($result_type, 'MYSQL_ASSOC') === 0)
			$this->result = $this->link->query($this->query, PDO::FETCH_ASSOC);
		elseif (strcmp($result_type, 'MYSQL_NUM') === 0)
			$this->result = $this->link->query($this->query, PDO::FETCH_NUM);
		elseif (strcmp($result_type, 'MYSQL_BOTH') === 0)
			$this->result = $this->link->query($this->query, PDO::FETCH_BOTH);
		else
			exit("Not recognised result_type");
		
		//var_dump($this->result);
			
		if ($this->result === false)
		{
			$the_error = $this->link->errorInfo();
			die(print "class.database: Error while executing Query." . $this->failureHandler($the_error[2], $this->query));
		}
/*
        // Count the number of rows returned, if a SELECT query was made.
		if(stristr($query, "SELECT") != FALSE) {
			$this->rows = mysql_num_rows($this->result);
		}

        // Count the number of rows affected by an INSERT, UPDATE, REPLACE or DELETE query.
        if(((stristr($query, "INSERT") + stristr($query, "UPDATE") + stristr($query, "REPLACE") + stristr($query, "DELETE"))) != FALSE) {
            $this->rows = mysql_affected_rows();
		}
*/
        if(stristr($query, "INSERT") !== FALSE) {
            $this->lastinsertid = $this->link->lastInsertId();
        }

        // Close the connection after we're done executing query.
		$this->CloseDB();
	}
}

/*
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
*/
?>
