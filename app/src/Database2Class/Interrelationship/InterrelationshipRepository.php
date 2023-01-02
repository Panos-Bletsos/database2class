<?php

namespace Interrelationship;

class InterrelationshipRepository
{
    private $all_interrelationships = array();

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    // GET Functions
    public function getall_interrelationships()
    {
        return ($this->all_interrelationships);
    }

    public function getan_interrelationship($mValue)
    {
        $values = array_values($this->all_interrelationships);
        return ($values[$mValue]);
    }

    public function getnoOfinterrelationships()
    {
        return (count($this->all_interrelationships));
    }

    // SET Functions
    public function setall_interrelationships($mValue)
    {
        $this->all_interrelationships = $mValue;
    }


    public function add_an_interrelationship($mValue)
    {
        array_push($this->all_interrelationships, $mValue);
    }

    public function return_interrelationships_having_source_table($a_source_table)
    {
        $result = clone $this;
        foreach ($result->all_interrelationships as $key => $an_interrelationship) {
            if (strcmp($an_interrelationship->getsource_table(), $a_source_table) !== 0)
                unset($result->all_interrelationships[$key]);
        }

        return $result;
    }

    public function return_interrelationships_having_dest_table($a_dest_table)
    {
        $result = clone $this;

        foreach ($result->all_interrelationships as $key => $an_interrelationship) {
            if (strcmp($an_interrelationship->getdest_table(), $a_dest_table) !== 0)
                unset($result->all_interrelationships[$key]);
        }

        return $result;
    }

    public function return_object_vars()
    {
        $results = get_object_vars($this);
        return $results;
    }

    public function nice_print()
    {
        echo "<br />";
        echo "all_interrelationships<br />";

        $a_dump = str_replace("\n", "<br />", print_r($this->return_object_vars(), true));
        $parts = explode("<br />", $a_dump);

        $a_dump = "";

        foreach ($parts as $a_part) {
            $no_of_tabs = substr_count($a_part, "    ");

            for ($i = 0; $i < $no_of_tabs; $i++) {
                $a_part = "<blockquote>" . $a_part . "</blockquote>";
            }
            $a_dump .= $a_part . "";
        }

        echo $a_dump;
    }
}

?>