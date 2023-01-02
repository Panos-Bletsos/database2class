<?php
/*
*   Database.php
*   loosely based on a class by: MARCO VOEGELI (www.voegeli.li)
*
*   This class provides one central database-connection for
*   all your php applications. Define only once your connection
*   settings and use it in all your applications.
*/

namespace Interrelationship;

class Interrelationship
{
    private $source_table;
    private $source_attribute;
    private $dest_table;
    private $dest_attribute;
    private $cardinality;
    private $via_table;
    private $via_table_eq_args;
    private $relationship_type;
    private $tables_involved;
    private $extra_attributes;

    public function __construct($source_table = "", $source_attribute = "", $dest_table = "", $dest_attribute = "", $cardinality = "", $via_table = NULL, $relationship_type = "", $extra_attributes = "")
    {
        // Method : begin
        //

        // ********** ADJUST THESE VALUES HERE **********
        $this->source_table = $source_table;
        $this->dest_table = $dest_table;
        $this->source_attribute = $source_attribute;
        $this->dest_attribute = $dest_attribute;
        $this->cardinality = $cardinality;
        $this->via_table = $via_table;
        $this->relationship_type = $relationship_type;
        $this->extra_attributes = $extra_attributes;
        // **********************************************
    }

    public function __destruct()
    {
    }

    // GET Functions
    public function getsource_table()
    {
        return ($this->source_table);
    }

    public function getdest_table()
    {
        return ($this->dest_table);
    }

    public function getcardinality()
    {
        return ($this->cardinality);
    }

    public function getvia_table()
    {
        return ($this->via_table);
    }

    public function getvia_table_eq_args()
    {
        return ($this->via_table_eq_args);
    }

    public function getsource_attribute()
    {
        return ($this->source_attribute);
    }

    public function getdest_attribute()
    {
        return ($this->dest_attribute);
    }

    public function getrelationship_type()
    {
        return ($this->relationship_type);
    }

    public function gettables_involved()
    {
        return ($this->tables_involved);
    }

    public function getextra_attributes()
    {
        return ($this->extra_attributes);
    }

    // SET Functions
    public function setsource_table($mValue)
    {
        $this->source_table = $mValue;
    }

    public function setdest_table($mValue)
    {
        $this->dest_table = $mValue;
    }

    public function setcardinality($mValue)
    {
        $this->cardinality = $mValue;
    }

    public function setvia_table($mValue)
    {
        $this->via_table = $mValue;
    }

    public function setvia_table_eq_args($mValue)
    {
        $this->via_table_eq_args = $mValue;
    }

    public function setsource_attribute($mValue)
    {
        $this->source_attribute = $mValue;
    }

    public function setdest_attribute($mValue)
    {
        $this->dest_attribute = $mValue;
    }

    public function setrelationship_type($mValue)
    {
        $this->relationship_type = $mValue;
    }

    public function settables_involved($mValue)
    {
        $this->tables_involved = $mValue;
    }

    public function setextra_attributes($mValue)
    {
        $this->extra_attributes = $mValue;
    }

    public function nice_print()
    {
        echo "<br />";
        echo "source_table: '" . $this->source_table . "'<br />";
        echo "source_attribute: '" . $this->source_attribute . "'<br />";
        echo "dest_table: '" . $this->dest_table . "'<br />";
        echo "dest_attribute: '" . $this->dest_attribute . "'<br />";
        echo "cardinality: '" . $this->cardinality . "'<br />";
        echo "via_table: '" . $this->via_table . "'<br />";
        echo "via_table_eq_args: '" . $this->via_table_eq_args . "'<br />";
        echo "relationship_type: '" . $this->relationship_type . "'<br />";
        echo "tables_involved: '" . $this->tables_involved . "'<br />";
        echo "extra_attributes: '" . print_r($this->extra_attributes, true) . "'<br />";
        echo "<br />";
    }

    public function mirror_direction()
    {
        $temp_source_table = $this->source_table;
        $temp_source_attribute = $this->source_attribute;
        $temp_dest_table = $this->dest_table;
        $temp_dest_attribute = $this->dest_attribute;

        $this->source_table = $temp_dest_table;
        $this->source_attribute = $temp_dest_attribute;
        $this->dest_table = $temp_source_table;
        $this->dest_attribute = $temp_source_attribute;

        if (strcmp($this->relationship_type, "1:N, TfR") === 0 || strcmp($this->relationship_type, "1:N, no TfR") === 0) {
            if (strcmp($this->cardinality, "1") === 0)
                $this->cardinality = "N";
            else
                $this->cardinality = "1";
        }

        //return $this;
    }

    public function returnStringForSQL()
    {
        return $this->source_table . "." . $this->source_attribute . " = " . $this->dest_table . "." . $this->dest_attribute;
    }
}

?>