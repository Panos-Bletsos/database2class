<?php
class FKs {
	private $all_FKs = array();
	
	public function __construct() {
	}

    public function __destruct() {
    }

	public function return_object_vars() {
		$results = get_object_vars($this);
		return $results;
	}
	
	public function nice_print() {
		echo "<br />";
		echo "all_FKs<br />";
		
		$a_dump = str_replace("\n", "<br />", print_r($this->return_object_vars(), true));
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
	
		echo $a_dump;
	}

	// GET Functions
	public function getall_FKs() {
		return($this->all_FKs);
	}
	
	public function getan_FK($mValue) {
		$values=array_values($this->all_FKs);
		return($values[$mValue]);
	}
	
	public function getnoFKs() {
		return(count($this->all_FKs));
	}
	
	public function getFK_by_source_table_and_attribute($a_source_table, $a_source_attribute) 
	{
		$result = array();
		foreach ($result->all_FKs as $key => $an_FK)
		{
			if (strcmp($an_FK->getsource_table(), $a_source_table) === 0 && strcmp($an_FK->getsource_attribute(), $a_source_attribute) === 0)
				array_push($result, $an_FK);
		}
		
		return $result;
	}
	
	public function getFK_by_dest_table_and_attribute($a_dest_table, $a_dest_attribute) 
	{
		$result = array();	
		foreach ($this->all_FKs as $key => $an_FK)
		{
			if (strcmp($an_FK->getdest_table(), $a_dest_table) === 0 && strcmp($an_FK->getdest_attribute(), $a_dest_attribute) === 0)
				array_push($result, $an_FK);
		}
		
		return $result;
	}
	
	public function getFK_from_source_by_dest_table_and_attribute_from($a_source_table, $a_dest_table, $a_dest_attribute) 
	{
		$result = array();	
		foreach ($this->all_FKs as $key => $an_FK)
		{
			if (strcmp($an_FK->getdest_table(), $a_dest_table) === 0 && strcmp($an_FK->getdest_attribute(), $a_dest_attribute) === 0 && strcmp($an_FK->getsource_table(), $a_source_table) === 0)
				array_push($result, $an_FK);
		}
		
		return $result;
	}
	
	public function getFK_by_source_table_and_dest_table($a_source_table, $a_dest_table) 
	{
		$result = array();	
		foreach ($this->all_FKs as $key => $an_FK)
		{
			if (strcmp($an_FK->getsource_table(), $a_source_table) === 0 && strcmp($an_FK->getdest_table(), $a_dest_table) === 0)
				array_push($result, $an_FK);
		}
		
		return $result;
	}
		
	// SET Functions
	public function setall_FKs($mValue) {
		$this->all_FKs = $mValue;
	}
	
	public function add_an_FK($mValue)
	{
		array_push($this->all_FKs, $mValue);	
	}
	
	public function return_FKs_having_source_table($a_source_table)
	{
		$result = clone $this;	
		foreach ($result->all_FKs as $key => $an_FK)
		{
			if (strcmp($an_FK->getsource_table(), $a_source_table) !== 0)
				unset($result->all_FKs[$key]);
		}
		
		return $result;
	}
	
	public function return_FKs_having_dest_table($a_dest_table)
	{
		$result = clone $this;
		
		foreach ($result->all_FKs as $key => $an_FK)
		{
			if (strcmp($an_FK->getdest_table(), $a_dest_table) !== 0)
				unset($result->all_FKs[$key]);
		}
		
		return $result;
	}

/*	
	public function return_source_FKs($a_dest_FK)
	{
		$result = clone $this;
		
		foreach ($result->all_FKs as $key => $an_FK)
		{
			if (strcmp($an_FK->getdest_table(), $a_dest_FK->getdest_table) !== 0 && strcmp($an_FK->getdest_attribute(), $a_dest_FK->getdest_attribute()) !== 0)
				unset($result->all_FKs[$key]);
		}
		
		return $result;
	}
*/	
	public function count_FKs_orginating_from_table($a_source_table)
	{
		$result = 0;
		
		foreach ($this->all_FKs as $key => $an_FK)
		{
			if (strcmp($an_FK->getsource_table(), $a_source_table) === 0)
				$result++;
		}
		
		return $result;	
	}	
	
	public function getAllSourceTables()
	{
		$result = array();
		
		foreach ($this->all_FKs as $an_FK)
			$result[$an_FK->getsource_table()] = $an_FK->getsource_table();
				
		return $result;	
	}	
	
	public function existsSource($FK_source_table, $FK_source_attribute)
	{
		$result = false;
		
		foreach ($this->all_FKs as $an_FK)
		{
			if (strcmp($an_FK->getsource_table(),$FK_source_table) === 0 && strcmp($an_FK->getsource_attribute(), $FK_source_attribute) === 0)
				$result = true;
		}
		
		return $result;	
	}	

}
?>