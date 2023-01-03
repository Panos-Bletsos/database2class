<?php
/*
*   class.class.php
*
*   This class provides methods to construct and write a class file.
*/

namespace Database2class\Database2class;

require_once(dirname(__FILE__) . '/CodeObject.php');

class Table
{
    public $classname;                            // Name of our class.
    public $serveraddress;                        // IP Address for MySQL connection.
    public $serverusername;                        // Username for MySQL connection.
    public $serverpassword;                        // Password for MySQL connection.
    public $databasename;                        // Name of database.
    public $tablename;                            // Name of table within database.
    public $variables = array();
    public $primarykey = array();                // Field/publiciable set to primary key(s).
    public $uniquekey = array();                    // Field/publiciable set to unique key(s).
    public $auto_increment_attributes = array();    // Field/publiciable set to auto incremented field.
    public $filesrequired;                            // Path to file we're going to write.
    private $filename;                            // Directory+filename to save file to.
    private $filepath;                            // Today's date.
    private $filedate;                                // Text to write to file.
    private $output;                            // Any files required. (default: class.database.php)
    private $interrelationships;
    private $alldbPKs = array();
    private $alldbFKs;
    private $nonFKAttributes = array();

    public function __construct($sName = "newclass", $sDatabase = "", $sTable = "", $sPrimaryKey = "", $sServerAddress = "localhost", $sServerUsername = "root", $sServerPassword = "", $sUniqueKey = "", $sAuto_Increment_Attributes, $sInterrelationships, $sNonFKAttributes, $sForeignKey)
    {
        // Construction of class
        $this->classname = $sName;
        $this->filedate = date("l, M j, Y - G:i:s T");
        $this->filesrequired = array("class.database.php");                     // Add any other required files here.
        $this->filename = "class.$this->classname.php";
        $this->filepath = realpath(dirname(__FILE__) . "/../output/") . "/Table.php";
        $this->databasename = $sDatabase;
        $this->serveraddress = $sServerAddress;
        $this->serverusername = $sServerUsername;
        $this->serverpassword = $sServerPassword;
        $this->tablename = $sTable;
        $this->primarykey = $sPrimaryKey[$sTable];
        $this->uniquekey = $sUniqueKey;
        $this->interrelationships = $sInterrelationships;
        $this->alldbPKs = $sPrimaryKey;
        $this->nonFKAttributes = $sNonFKAttributes;
        $this->alldbFKs = $sForeignKey;

        if (isset($sAuto_Increment_Attributes[0]))
            $this->auto_increment_attributes = $sAuto_Increment_Attributes[0];
        else
            $this->auto_increment_attributes = NULL;

        /*
                $oLink = @mysql_connect($sServerAddress, $sServerUsername, $sServerPassword) or die("Error: Could not connect to server.");
                mysql_select_db($sDatabase, $oLink);
                $query = "SHOW COLUMNS FROM ".$sTable.";";
                $oResult = mysql_query($query) or die( mysql_error().".<br />\n The query string was: ".$query);

                while($oRow = mysql_fetch_object($oResult)) {
                    array_push($this->variables, $oRow->Field);
                }
        */
        $query = "SHOW COLUMNS FROM " . $sTable . ";";

        $db_uri = $sServerAddress;
        $db_name = $sDatabase;
        $db_user_username = $sServerUsername;
        $db_user_password = $sServerPassword;

        $a_temp = return_result_db($query);

        //print_r($a_temp);exit();

        foreach ($a_temp as $a_temp_instance)
            array_push($this->variables, $a_temp_instance['Field']);
    }

    public function setFile($sPath = "", $sFilename = "")
    {
        // Sets the path and/or the filename to use for the class.
        if ($sPath != "") {
            $this->filepath = $sPath;
        }

        if ($sFilename != "") {
            $this->filename = $sFilename;
        }
    }

    public function setRequired($aFiles)
    {
        // Sets the required files to passed array.
        $this->filesrequired = $aFiles;
    }

    public function getreturnPKs()
    {
        $sRet = "public function returnPKs() {\n";

        $sRet .= "\$result = array(";
        foreach ($this->primarykey as $a_primarykey) {
            $sRet .= "\"" . $a_primarykey . "\", ";
        }
        $sRet = substr($sRet, 0, -2) . ");\n\n";

        $sRet .= "return \$results;\n";

        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function createClass($bEcho = 0, $bWrite = 1)
    {
        // Creates class file.

        // Generate the file text.
        $sFile = $this->getHeader() . $this->getVariables() .
            $this->getConstructorDestructor() . $this->getGetters() .
            $this->getSetters() . $this->getSelect() .
            $this->return_object_vars() . $this->getSelectSome() .
            $this->getSelectAll() . $this->nice_print() .
            $this->getInsert() . $this->getUpdate() .
            $this->getDelete();

        //$this->getreturnPKs();


        //Decide which interrelation will be made
        $interrelationships_for_this_table = $this->interrelationships->return_interrelationships_having_source_table($this->tablename);
        //$interrelationships_for_this_table->nice_print();

        for ($i = 0; $i < $interrelationships_for_this_table->getnoOfinterrelationships(); $i++) {
            //getters
            if (strcmp($interrelationships_for_this_table->getan_interrelationship($i)->getrelationship_type(), "1:N, no TfR") === 0)
                $sFile .= $this->getSimpleInterrelation($interrelationships_for_this_table->getan_interrelationship($i));
            else//if (strcmp($interrelationships_for_this_table->getan_interrelationship($i)->getrelationship_type(), "1:N, TfR") === 0)
                $sFile .= $this->getTfRInterrelation($interrelationships_for_this_table->getan_interrelationship($i));

            //setters
            if (strcmp($interrelationships_for_this_table->getan_interrelationship($i)->getrelationship_type(), "1:N, no TfR") === 0 &&
                strcmp($interrelationships_for_this_table->getan_interrelationship($i)->getcardinality(), "N") === 0)
                $sFile .= $this->setSimpleInterrelation($interrelationships_for_this_table->getan_interrelationship($i));
            elseif (strcmp($interrelationships_for_this_table->getan_interrelationship($i)->getrelationship_type(), "M:N, TfR") === 0 || strcmp($interrelationships_for_this_table->getan_interrelationship($i)->getrelationship_type(), "1:N, TfR") === 0)
                $sFile .= $this->setTfRInterrelation($interrelationships_for_this_table->getan_interrelationship($i));
        }

        //Add footer
        $sFile .= $this->getFooter();

        // Format the code.
        $sFile = $this->formatCode($sFile);

        // If we are to display the file contents to the browser, we do so here.
        if ($bEcho) {
            echo "";
            highlight_string($sFile);
            echo "<br><br><br>Output save path: $this->filepath";
        }

        // If we are to write the file (default=TRUE) then we do so here.
        if ($bWrite) {
            // Check to see if file already exists, and if so, delete it.
            if (file_exists($this->filename)) {
                unlink($this->filename);
            }

            // Open file (insert mode), set the file date, and write the contents.
            $oFile = fopen($this->filepath, "w+");
            fwrite($oFile, $sFile);
        }
//exit();
        // Exit the function
        return ($this->filepath);
    }

    public function getHeader()
    {
        // Returns text for a header for our class file.
        $sRet = "<?php\n";
        $sRet .= "/*******************************************************************************
* Class Name:       $this->classname
* File Name:        $this->filename
* Generated:        $this->filedate
*  - for Table:     $this->tablename
*   - in Database:  $this->databasename
* Created by: db2class (http://playground.cwa.gr/db2class)
********************************************************************************/\n\n";
        $sRet .= $this->getRequired();
        $sRet .= "// Begin Class \"$this->classname\"\n";
        $sRet .= "class $this->classname {\n";

        return ($sRet);
    }

    public function getRequired()
    {
        // Returns text to require all files in filesrequired array.
        $sRet = "// Files required by class:\n";
        if (!empty($this->filesrequired)) {
            foreach ($this->filesrequired as $file) {
                $sRet .= "require_once(\"$file\");\n";
            }
        } else {
            $sRet .= "// No files required.\n";
        }

        $sRet .= "\n";

        return ($sRet);
    }

    public function getVariables()
    {
        // public function to return text to declare all the variables in the class.
        $sRet = "// Variable declaration\n";
        //$sRet .=    "private \$$this->primarykey; // Primary Key\n";
        foreach ($this->variables as $variable) {
            // Loop through variables and declare them.
            if (array_search($variable, $this->primarykey) === false)
                // Variable is not primary key, so we'll add it.
                $sRet .= "private \$$variable;\n";
            else
                $sRet .= "private \$$variable; // Primary Key\n";
        }
        // Add variable for connection to database.
        $sRet .= "private \$database;\n\n";

        return ($sRet);
    }

    public function getConstructorDestructor()
    {

        global $path_to_database;
        global $path_to_settings;
        include_once("../Settings.php");


        // public function to create the class constructor and destructor.
        $sRet = "// Class Constructor\npublic function __construct() {\n";
        $sRet .= "require(\"" . $path_to_settings . "Settings.php\");\n";
        $sRet .= "\$this->database = new Database();\n";
        $sRet .= "\$this->database->SetSettings(\$db_uri, \$db_user_username, \$db_user_password, \$db_name);\n}\n\n";
        $sRet .= "// Class Destructor\npublic function __destruct() {\n";
        $sRet .= "unset(\$this->database);\n}\n\n";

        return ($sRet);
    }

    public function getGetters()
    {
        // public function to create all the GET methods for the class.
        $sRet = "// GET Functions\n";

        // Loop through variables to create the functions.
        foreach ($this->variables as $variable) {
            // Loop through variables and declare them.
            $sRet .= "public function get$variable() {\n";
            $sRet .= "return(\$this->$variable);\n}\n\n";
        }

        return ($sRet);
    }

    public function getSetters()
    {
        // public function to create all the SET methods for the class.
        $sRet = "// SET Functions\n";

        // Loop through variables to create the functions.
        foreach ($this->variables as $variable) {
            // Loop through variables and declare them.
            $sRet .= "public function set$variable(\$mValue) {\n";
            $sRet .= "\$this->$variable = \$mValue;\n}\n\n";
        }

        return ($sRet);
    }

    public function getSelect()
    {
        $sRet = "";
        if (count($this->primarykey) === 1) {
            $sRet .= "public function select(\$mID) { // SELECT Function\n// Execute SQL Query to get record.\n";
            $sRet .= "\$sSQL =  \"SELECT * FROM $this->tablename WHERE " . (array_values($this->primarykey)[0]) . " = \$mID;\";\n";
        } else {
            $sRet .= "public function select(";
            foreach ($this->primarykey as $a_primarykey) {
                $sRet .= "\$m" . $a_primarykey . ", ";
            }

            $sRet = substr($sRet, 0, -2) . ") { // SELECT Function\n// Execute SQL Query to get record.\n";

            $sRet .= "\$sSQL =  \"SELECT * FROM $this->tablename WHERE ";

            foreach ($this->primarykey as $a_primarykey) {
                $sRet .= $a_primarykey . " = \$m" . $a_primarykey . " AND ";
            }
            $sRet = substr($sRet, 0, -5) . ";\";\n";
        }
        $sRet .= "\$oResult =  \$this->database->query(\$sSQL);\n\$oResult = \$this->database->result;\n\$oRow = \$oResult->fetch(PDO::FETCH_OBJ);\n\n";


        $sRet .= "if (\$oRow === false){\n";
        $sRet .= "return false;\n}\n\n";

        $sRet .= "// Assign results to class.\n";
        //$sRet .= "\$this->$this->primarykey = \$oRow->$this->primarykey; // Primary Key\n";

        // Loop through variables.
        foreach ($this->variables as $variable) {
            $sRet .= "\$this->$variable = \$oRow->$variable;\n";
        }
        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function return_object_vars()
    {
        $sRet = "public function return_object_vars() {\n";
        $sRet .= "\$results = get_object_vars(\$this);\n";
        $sRet .= "unset(\$results[\"database\"]);\n";
        $sRet .= "return \$results;\n";
        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function getSelectSome()
    {
        if (count($this->primarykey) > 1 || count($this->primarykey) + count($this->uniquekey) > 1) {
            $sRet = "public function selectSome(";
            foreach ($this->primarykey as $a_primarykey) {
                $sRet .= "\$m" . $a_primarykey . ", ";
            }

            foreach ($this->uniquekey as $a_uniquekey) {
                $sRet .= "\$m" . $a_uniquekey . ", ";
            }

            $sRet = substr($sRet, 0, -2) . ", \$return_objects = true, \$addtowhereclause = NULL) {\n";
            $sRet .= "\$result = array();\n";
            $sRet .= "\$sSQL =  \"SELECT * FROM $this->tablename WHERE \";\n\n";

            foreach ($this->primarykey as $a_primarykey) {
                $sRet .= "if (\$m$a_primarykey !== NULL) {\n";
                $sRet .= "\$sSQL .=  \"$a_primarykey = \$m$a_primarykey AND \";\n";
                $sRet .= "}\n\n";
            }

            foreach ($this->uniquekey as $a_uniquekey) {
                $sRet .= "if (\$m$a_uniquekey !== NULL) {\n";
                $sRet .= "\$sSQL .=  \"$a_uniquekey = \$m$a_uniquekey AND \";\n";
                $sRet .= "}\n\n";
            }

            $sRet .= "\$sSQL = substr(\$sSQL, 0, -4);\n\n";

            $sRet .= "if(\$addtowhereclause !== NULL) {\n";
            $sRet .= "\$sSQL .= \"AND \".\$addtowhereclause;\n";
            $sRet .= "}\n\n";
            $sRet .= "\$sSQL .= \";\";\n\n";

            $sRet .= "\$oResult =  \$this->database->query(\$sSQL);\n";

            $sRet .= "\$oResult = \$this->database->result;\n";
            $sRet .= "while(\$oRow = \$oResult->fetch(PDO::FETCH_OBJ)){\n";
            $sRet .= "\$temp = new $this->classname;\n";

            // Loop through variables.
            foreach ($this->variables as $variable) {
                $sRet .= "\$temp->$variable = \$oRow->$variable;\n";
            }

            $sRet .= "\nif (\$return_objects === false) {\n";
            $sRet .= "\$temp = \$temp->return_object_vars();\n";
            $sRet .= "}\n\n";

            $sRet .= "array_push(\$result, \$temp);\n";
            $sRet .= "}\n\n";
            $sRet .= "return \$result;\n";
            $sRet .= "}\n\n";

            return ($sRet);
        }
    }

    public function getSelectAll()
    {
        $sRet = "public function selectAll(\$return_objects = true, \$addwhereclause = NULL) {\n";
        $sRet .= "\$result = array();\n";
        $sRet .= "\$sSQL =  \"SELECT * FROM $this->tablename\";\n\n";

        $sRet .= "if(\$addwhereclause !== NULL) {\n";
        $sRet .= "\$sSQL .= \" WHERE \".\$addwhereclause;\n";
        $sRet .= "}\n\n";
        $sRet .= "\$sSQL .= \";\";\n\n";

        $sRet .= "\$oResult =  \$this->database->query(\$sSQL);\n";
        $sRet .= "\$oResult = \$this->database->result;\n";
        $sRet .= "while(\$oRow = \$oResult->fetch(PDO::FETCH_OBJ)){\n";
        $sRet .= "\$temp = new $this->classname;\n";

        // Loop through variables.
        foreach ($this->variables as $variable) {
            $sRet .= "\$temp->$variable = \$oRow->$variable;\n";
        }

        $sRet .= "\nif (\$return_objects === false) {\n";
        $sRet .= "\$temp = \$temp->return_object_vars();\n";
        $sRet .= "}\n\n";

        $sRet .= "array_push(\$result, \$temp);\n";
        $sRet .= "}\n\n";
        $sRet .= "return \$result;\n";
        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function nice_print()
    {
        $sRet = "public function nice_print() {\n";
        $sRet .= "echo \"<br />\";\n";

        // Loop through variables.
        foreach ($this->variables as $variable) {
            $sRet .= "echo \"$variable: '\".\$this->$variable.\"'<br />\";\n";
        }

        $sRet .= "echo \"<br />\";\n";
        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function getInsert()
    {
        $sRet = "public function insert(\$onlyReturnQuery = false) {\n";
        //$sRet .= "\$this->$this->primarykey = NULL; // Remove primary key value for insert\n";
        $sRet .= "\$sSQL = \"INSERT INTO $this->tablename (";
        $i = "";
        foreach ($this->variables as $variable) {
            $sRet .= "$i$variable";
            $i = ", ";
        }
        $i = "";
        $sRet .= ") VALUES (\";\n\n";
        foreach ($this->variables as $variable) {
            $sRet .= "if (\$this->$variable === NULL) {\n";
            $sRet .= "\$sSQL .= \"" . $i . "DEFAULT\";\n";
            $sRet .= "}else{\n";
            $sRet .= "\$sSQL .= \"$i'\$this->$variable'\";\n";
            $sRet .= "}\n\n";

            $i = ", ";
        }
        $sRet .= "\$sSQL .= \");\";\n\n";

        $sRet .= "if(\$onlyReturnQuery === true){\n";
        $sRet .= "return \$sSQL;\n";
        $sRet .= "}else{\n";
        $sRet .= "\$oResult = \$this->database->query(\$sSQL);\n";
        $sRet .= "}\n\n";

        if ($this->auto_increment_attributes !== NULL)
            $sRet .= "\$this->$this->auto_increment_attributes = \$this->database->lastinsertid;\n";

        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function getUpdate()
    {
        $sRet = "/* \$updateOnlyNonPKs is either true or an array with as many entries as the PKs of the table \n";
        $sRet .= "with key the title of the PK and each value the corresponding original value of the PK\n";
        $sRet .= "e.g. \$updateOnlyNonPKs = array(\"table_1_id\"=>3, \"table_2_id\"=>2);*/\n";
        $sRet .= "public function update(\$updateOnlyNonPKs = true, \$onlyReturnQuery = false) {\n";
        $sRet .= "\$sSQL = \"\";\n\n";
        $sRet .= "if(\$updateOnlyNonPKs !== true){\n";
        $sRet .= "\$sSQL = \"UPDATE $this->tablename SET ";
        // Loop through variables.
        foreach ($this->variables as $variable) {
            $sRet .= "$variable = '\$this->$variable', ";
        }
        $sRet = substr($sRet, 0, -2) . " WHERE ";

        foreach ($this->primarykey as $a_primarykey)
            $sRet .= $a_primarykey . " = '\".\$updateOnlyNonPKs['" . $a_primarykey . "'].\"' AND ";

        $sRet = substr($sRet, 0, -5) . ";\";\n}\nelse{\n";
        $sRet .= "\$sSQL = \"UPDATE $this->tablename SET ";
        // Loop through variables.
        foreach ($this->variables as $variable) {
            if (array_search($variable, $this->primarykey) === false)
                $sRet .= "$variable = '\$this->$variable', ";
        }
        $sRet = substr($sRet, 0, -2) . " WHERE ";

        foreach ($this->primarykey as $a_primarykey)
            $sRet .= $a_primarykey . " = \$this->" . $a_primarykey . " AND ";

        $sRet = substr($sRet, 0, -5) . ";\";\n}\n\n";

        $sRet .= "if(\$onlyReturnQuery === true){\n";
        $sRet .= "return \$sSQL;\n";
        $sRet .= "}else{\n";
        $sRet .= "\$this->database->Query(\$sSQL);\n}\n";
        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function getDelete()
    {
        // Creates the delete function.
        $sRet = "public function delete() {\n\$sSQL = \"DELETE FROM $this->tablename WHERE ";

        foreach ($this->primarykey as $a_primarykey)
            $sRet .= $a_primarykey . " = '\$this->" . $a_primarykey . "' AND ";

        $sRet = substr($sRet, 0, -5);

        $sRet .= ";\";\n\$this->database->Query(\$sSQL);\n}\n\n";

        return ($sRet);
    }

    public function getSimpleInterrelation($an_interrelationship)
    {
        // Creates the delete function.
        $sRet = "public function get_rlt_" . $an_interrelationship->getdest_table() . "() {\n";
        $sRet .= "\$sSQL = \"SELECT " . $an_interrelationship->getdest_table() . ".* FROM " . $an_interrelationship->getdest_table() . ", " . $an_interrelationship->getsource_table() . " WHERE " . $an_interrelationship->returnStringForSQL() . " AND ";

        foreach ($this->primarykey as $a_primarykey) {
            $sRet .= $an_interrelationship->getsource_table() . "includes" . $a_primarykey . " = \$this->" . $a_primarykey . " AND ";
        }
        $sRet = substr($sRet, 0, -5) . ";\";\n";

        $sRet .= "\$oResult =  \$this->database->query(\$sSQL);\n\$oResult = \$this->database->result;\n\n";

        $sRet .= "if (\$oResult->rowCount() === 0){\n";
        $sRet .= "return false;\n}\n\n";

        if (strcmp($an_interrelationship->getcardinality(), "1") === 0) {
            $sRet .= "\$oRow = \$oResult->fetch(PDO::FETCH_OBJ);\n\n";
            $sRet .= "// Assign results to class.\n";
            $sRet .= "\$result = new " . $an_interrelationship->getdest_table() . "();\n";

            $sRet .= "\$result->select(";

            foreach ($this->alldbPKs[$an_interrelationship->getdest_table()] as $asPK)
                $sRet .= "\$oRow->" . $asPK . ", ";

            $sRet = substr($sRet, 0, -2) . ");\n\n";
        } else {
            $sRet .= "// Assign results to an array of class.\n";
            $sRet .= "\$result = array();\n";
            $sRet .= "while (\$oRow = \$oResult->fetch(PDO::FETCH_OBJ)){\n";
            $sRet .= "\$temp = new " . $an_interrelationship->getdest_table() . "();\n";
            $sRet .= "\$temp->select(";

            foreach ($this->alldbPKs[$an_interrelationship->getdest_table()] as $asPK)
                $sRet .= "\$oRow->" . $asPK . ", ";

            $sRet = substr($sRet, 0, -2) . ");\n\n";
            $sRet .= "array_push(\$result, \$temp);\n}\n\n";
        }

        $sRet .= "return \$result;\n";
        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function getTfRInterrelation($an_interrelationship)
    {
        $sRet = "public function get_rlt_" . $an_interrelationship->getdest_table() . "() {\n";

        if (count($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === 0)    //No extra attributes in the TfR
            $sRet .= "\$sSQL = \"SELECT DISTINCT " . $an_interrelationship->getdest_table() . ".* FROM " . $an_interrelationship->gettables_involved() . " WHERE " . $an_interrelationship->getvia_table_eq_args() . " AND ";
        else
            $sRet .= "\$sSQL = \"SELECT DISTINCT " . $an_interrelationship->getdest_table() . ".*, " . $an_interrelationship->getvia_table() . ".* FROM " . $an_interrelationship->gettables_involved() . " WHERE " . $an_interrelationship->getvia_table_eq_args() . " AND ";

        foreach ($this->primarykey as $a_primarykey) {
            $anFK = $this->alldbFKs->getFK_from_source_by_dest_table_and_attribute_from($an_interrelationship->getvia_table(), $this->tablename, $a_primarykey);
            $sRet .= $anFK[0]->getsource_table() . "includes" . $anFK[0]->getsource_attribute() . " = \$this->" . $a_primarykey . " AND ";
        }
        $sRet = substr($sRet, 0, -5) . ";\";\n";

        $sRet .= "\$oResult =  \$this->database->query(\$sSQL);\n\$oResult = \$this->database->result;\n\n";

        $sRet .= "if (\$oResult->rowCount() === 0){\n";
        $sRet .= "return false;\n}\n\n";

        if (strcmp($an_interrelationship->getcardinality(), "1") === 0)    //cardinality is 1 - returns one result
        {
            $sRet .= "\$oRow = \$oResult->fetch(PDO::FETCH_OBJ);\n\n";

            if (count($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === 0)        //No extra attributes in the TfR
            {
                $sRet .= "// Assign results to class.\n";
                $sRet .= "\$result = new " . $an_interrelationship->getdest_table() . "();\n";

                $sRet .= "\$result->select(";

                foreach ($this->alldbPKs[$an_interrelationship->getdest_table()] as $asPK)
                    //foreach ($this->alldbFKs[$an_interrelationship->getdest_table()] as $asPK)
                    //foreach ($this->alldbFKs->return_FKs_having_source_table($an_interrelationship->getvia_table())->getall_FKs() as $an_FK)
                {
                    //if (strcmp($an_FK->getdest_table(), $an_interrelationship->getdest_table()) === 0)
                    //$sRet .= "\$oRow->".$an_FK->getsource_attribute().", ";
                    $sRet .= "\$oRow->" . $asPK . ", ";
                }
                $sRet = substr($sRet, 0, -2) . ");\n\n";
            } else        //With extra attributes in the TfR - returns any array with the object + the extra attributes
            {
                $sRet .= "// Assign results to class.\n";
                $sRet .= "\$result = array();\n";

                $sRet .= "\$temp = new " . $an_interrelationship->getdest_table() . "();\n";
                $sRet .= "\$temp->select(";

                foreach ($this->alldbPKs[$an_interrelationship->getvia_table()] as $asPK)
                    $sRet .= "\$oRow->" . $asPK . ", ";

                $sRet = substr($sRet, 0, -2) . ");\n\n";

                $sRet .= "\$result['" . $an_interrelationship->getdest_table() . "'] = \$temp;\n";
                foreach ($this->nonFKAttributes[$an_interrelationship->getvia_table()] as $a_nonFKAttributes)
                    $sRet .= "\$result['" . $a_nonFKAttributes . "'] = \$oRow->" . $a_nonFKAttributes . ";\n";
            }
        } else    //cardinality is N - returns many results/array
        {
            if (count($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === 0)        //No extra attributes in the TfR - returns just an object
            {
                $sRet .= "// Assign results to an array of class.\n";
                $sRet .= "\$result = array();\n";
                $sRet .= "while (\$oRow = \$oResult->fetch(PDO::FETCH_OBJ)){\n";

                $sRet .= "\$temp = new " . $an_interrelationship->getdest_table() . "();\n";

                $sRet .= "\$temp->select(";

                $anFK = $this->alldbFKs->getFK_from_source_by_dest_table_and_attribute_from($an_interrelationship->getvia_table(), $this->tablename, $a_primarykey);
                foreach ($this->alldbPKs[$an_interrelationship->getdest_table()] as $asPK)
                    $sRet .= "\$oRow->" . $asPK . ", ";

                $sRet = substr($sRet, 0, -2) . ");\n\n";

                $sRet .= "array_push(\$result, \$temp);\n}\n\n";
            } else        //With extra attributes in the TfR - returns any array with the object + the extra attributes
            {
                $sRet .= "// Assign results to an array of class.\n";
                $sRet .= "\$result = array();\n";
                $sRet .= "while (\$oRow = \$oResult->fetch(PDO::FETCH_OBJ)){\n";
                $sRet .= "\$partial_result = array();\n\n";
                $sRet .= "\$temp = new " . $an_interrelationship->getdest_table() . "();\n";

                $sRet .= "\$temp->select(";
                foreach ($this->alldbPKs[$an_interrelationship->getdest_table()] as $asPK)
                    $sRet .= "\$oRow->" . $asPK . ", ";
                $sRet = substr($sRet, 0, -2) . ");\n\n";

                $sRet .= "\$partial_result['" . $an_interrelationship->getdest_table() . "'] = \$temp;\n";

                foreach ($this->nonFKAttributes[$an_interrelationship->getvia_table()] as $a_nonFKAttributes)
                    $sRet .= "\$partial_result['" . $a_nonFKAttributes . "'] = \$oRow->" . $a_nonFKAttributes . ";\n";

                $sRet .= "array_push(\$result, \$partial_result);\n}\n\n";
            }
        }

        $sRet .= "return \$result;\n";
        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function setSimpleInterrelation($an_interrelationship)
    {
        $sRet = "public function set_rlt_" . $an_interrelationship->getdest_table() . "(\$a_" . $an_interrelationship->getdest_table() . ") {\n";
        $sRet .= "\$sSQL = \"UPDATE " . $an_interrelationship->getdest_table() . " SET " . $an_interrelationship->getdest_attribute() . " = '\$this->" . $an_interrelationship->getsource_attribute() . "' WHERE ";

        foreach ($this->alldbPKs[$an_interrelationship->getdest_table()] as $a_PK)
            $sRet .= $a_PK . " = '\".\$a_" . $an_interrelationship->getdest_table() . "->get" . $a_PK . "().\"' AND ";

        $sRet = substr($sRet, 0, -5);
        $sRet .= ";\";\n";

        $sRet .= "\$this->database->query(\$sSQL);\n";
        $sRet .= "}\n\n";

        return ($sRet);
    }

    public function setTfRInterrelation($an_interrelationship)
    {
        $sRet = "/*\nDepending on whether the Table for Relation has (or has not) attributes more than the Foreign Keys of the\n";
        $sRet .= "tables to be related the input variable \$a_" . $an_interrelationship->getdest_table() . " is either the object to be interrelated with\n";
        $sRet .= "or an array with the object and the remaining extra attributes of the table: \n";
        $sRet .= "e.g. \$a_" . $an_interrelationship->getdest_table() . " = new " . $an_interrelationship->getdest_table() . "() - for TfR with no extra attributes\n";
        $sRet .= "or \$a_" . $an_interrelationship->getdest_table() . " = array('object'=>new " . $an_interrelationship->getdest_table() . "(), 'extra_attribute_1_title'=>4,\n";
        $sRet .= "'extra_attribute_2_title'=>'peace'); - for TfR with no extra attributes\n*/\n";

        if (isset($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === false || count($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === 0)        //No extra attributes in the TfR
            $sRet .= "public function set_rlt_" . $an_interrelationship->getdest_table() . "(\$a_" . $an_interrelationship->getdest_table() . ") {\n";
        else
            $sRet .= "public function set_rlt_" . $an_interrelationship->getdest_table() . "(\$a_mixed_array) {\n";

        $sRet .= "//Test if the relation exists\n";
        $sRet .= "\$relation_exists = \$this->get_rlt_" . $an_interrelationship->getdest_table() . "();\n\n";

        $sRet .= "if (\$relation_exists === false){\n";

        if (isset($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === false || count($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === 0)        //No extra attributes in the TfR
        {
            //prepare attribute title-variable pairs
            $attribute_title_variable_pairs = array('title' => "", 'variable' => "");

            $temp_all_FKs_of_via_table = $this->alldbFKs->return_FKs_having_source_table($an_interrelationship->getvia_table())->getall_FKs();
            foreach ($temp_all_FKs_of_via_table as $an_FK) {
                $attribute_title_variable_pairs['title'] .= $an_FK->getsource_attribute() . ", ";

                if (strcmp($an_FK->getdest_table(), $this->tablename) === 0)
                    $attribute_title_variable_pairs['variable'] .= "'\".\$this->" . $an_FK->getdest_attribute() . ".\"', ";
                else
                    $attribute_title_variable_pairs['variable'] .= "'\".\$a_" . $an_interrelationship->getdest_table() . "->get" . $an_FK->getdest_attribute() . "().\"', ";
            }

            $attribute_title_variable_pairs['title'] = substr($attribute_title_variable_pairs['title'], 0, -2);
            $attribute_title_variable_pairs['variable'] = substr($attribute_title_variable_pairs['variable'], 0, -2);

            $sRet .= "\$sSQL = \"INSERT INTO " . $an_interrelationship->getvia_table() . " (" . $attribute_title_variable_pairs['title'] . ") VALUES (" . $attribute_title_variable_pairs['variable'] . ");\";\n";
            $sRet .= "\$this->database->query(\$sSQL);\n";
        } else {
            //prepare attribute title-variable pairs
            $attribute_title_variable_pairs = array('title' => "", 'variable' => "");

            $temp_all_FKs_of_via_table = $this->alldbFKs->return_FKs_having_source_table($an_interrelationship->getvia_table())->getall_FKs();
            foreach ($temp_all_FKs_of_via_table as $an_FK) {
                $attribute_title_variable_pairs['title'] .= $an_FK->getsource_attribute() . ", ";

                if (strcmp($an_FK->getdest_table(), $this->tablename) === 0)
                    $attribute_title_variable_pairs['variable'] .= "'\".\$this->" . $an_FK->getdest_attribute() . ".\"', ";
                else
                    $attribute_title_variable_pairs['variable'] .= "'\".\$a_mixed_array[\"object\"]->get" . $an_FK->getdest_attribute() . "().\"', ";
            }

            //add the extra attributes
            foreach ($this->nonFKAttributes[$an_interrelationship->getvia_table()] as $an_extra_attribute) {
                $attribute_title_variable_pairs['title'] .= $an_extra_attribute . ", ";
                $attribute_title_variable_pairs['variable'] .= "'\".\$a_mixed_array[\"" . $an_extra_attribute . "\"].\"', ";
            }

            $attribute_title_variable_pairs['title'] = substr($attribute_title_variable_pairs['title'], 0, -2);
            $attribute_title_variable_pairs['variable'] = substr($attribute_title_variable_pairs['variable'], 0, -2);

            $sRet .= "\$sSQL = \"INSERT INTO " . $an_interrelationship->getvia_table() . " (" . $attribute_title_variable_pairs['title'] . ") VALUES (" . $attribute_title_variable_pairs['variable'] . ");\";\n";
            $sRet .= "\$this->database->query(\$sSQL);\n";
        }

        $sRet .= "}else{\n";

        if (isset($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === false || count($this->nonFKAttributes[$an_interrelationship->getvia_table()]) === 0)        //No extra attributes in the TfR
        {
            //prepare attribute title-variable pairs
            $attribute_title_variable_pairs = array("to_alter" => "", "where_clause" => "");
            /**/
            $temp_all_FKs_of_via_table = $this->alldbFKs->return_FKs_having_source_table($an_interrelationship->getvia_table())->getall_FKs();
            foreach ($temp_all_FKs_of_via_table as $an_FK) {
                if (strcmp($an_FK->getdest_table(), $this->tablename) !== 0)
                    $attribute_title_variable_pairs["to_alter"] .= $an_FK->getsource_attribute() . " = '\".\$a_" . $an_interrelationship->getdest_table() . "->get" . $an_FK->getdest_attribute() . "().\"', ";

                if (strcmp($an_FK->getdest_table(), $this->tablename) === 0)
                    $attribute_title_variable_pairs["where_clause"] .= $an_FK->getsource_attribute() . " = '\".\$this->" . $an_FK->getdest_attribute() . ".\"' AND ";
                else
                    $attribute_title_variable_pairs["where_clause"] .= $an_FK->getsource_attribute() . " = '\".\$a_" . $an_interrelationship->getdest_table() . "->get" . $an_FK->getdest_attribute() . "().\"' AND ";
            }

            $attribute_title_variable_pairs["to_alter"] = substr($attribute_title_variable_pairs["to_alter"], 0, -2);
            $attribute_title_variable_pairs["where_clause"] = substr($attribute_title_variable_pairs["where_clause"], 0, -5);

            $sRet .= "\$sSQL = \"UPDATE " . $an_interrelationship->getvia_table() . " set " . $attribute_title_variable_pairs["to_alter"] . " WHERE " . $attribute_title_variable_pairs["where_clause"] . ";\";\n";
            $sRet .= "\$this->database->query(\$sSQL);\n";
            $sRet .= "}\n";
        } else {
            //prepare attribute title-variable pairs
            $attribute_title_variable_pairs = array("to_alter" => "", "where_clause" => "");

            $temp_all_FKs_of_via_table = $this->alldbFKs->return_FKs_having_source_table($an_interrelationship->getvia_table())->getall_FKs();

            foreach ($temp_all_FKs_of_via_table as $an_FK) {
                if (strcmp($an_FK->getdest_table(), $this->tablename) !== 0)
                    $attribute_title_variable_pairs["to_alter"] .= $an_FK->getsource_attribute() . " = '\".\$a_mixed_array[\"object\"]->get" . $an_FK->getdest_attribute() . "->get" . $an_FK->getdest_attribute() . "().\"', ";

                if (strcmp($an_FK->getdest_table(), $this->tablename) === 0)
                    $attribute_title_variable_pairs["where_clause"] .= $an_FK->getsource_attribute() . " = '\".\$this->" . $an_FK->getdest_attribute() . ".\"' AND ";
                else
                    $attribute_title_variable_pairs["where_clause"] .= $an_FK->getsource_attribute() . " = '\".\$a_mixed_array[\"object\"]->get" . $an_FK->getdest_attribute() . "().\"' AND ";
            }

            //add the extra attributes
            foreach ($this->nonFKAttributes[$an_interrelationship->getvia_table()] as $an_extra_attribute) {
                $attribute_title_variable_pairs["to_alter"] .= $an_extra_attribute . " = '\".\$a_mixed_array[\"" . $an_extra_attribute . "\"].\"', ";
            }

            $attribute_title_variable_pairs["to_alter"] = substr($attribute_title_variable_pairs["to_alter"], 0, -2);
            $attribute_title_variable_pairs["where_clause"] = substr($attribute_title_variable_pairs["where_clause"], 0, -5);

            $sRet .= "\$sSQL = \"UPDATE " . $an_interrelationship->getvia_table() . " set " . $attribute_title_variable_pairs["to_alter"] . " WHERE " . $attribute_title_variable_pairs["where_clause"] . ";\";\n";
            $sRet .= "\$this->database->query(\$sSQL);\n";
            $sRet .= "}\n";
        }

        $sRet .= "}\n\n";
        return ($sRet);
    }

    public function getFooter()
    {
        // Returns text for a footer for our class file.
        $sRet = "}\n";
        $sRet .= "// End Class \"$this->classname\"\n?>";

        return ($sRet);
    }

    private function formatCode($sCode)
    {
        // Returns formatted code string.
        $oCode = new CodeObject($sCode, FALSE);
        $oCode->process();
        return ($oCode->code);
    }
}

?>
