<?php
/*
*   class.database.php
*   loosely based on a class by: MARCO VOEGELI (www.voegeli.li)
*
*   This class provides one central database-connection for
*   all your php applications. Define only once your connection
*   settings and use it in all your applications.
*/

class FK {
	private $source_table;
	private $source_attribute;
	private $dest_table;
	private $dest_attribute;
	
	public function __construct($source_table = "", $source_attribute = "", $dest_table = "", $dest_attribute = "") {
		// Method : begin
		//Konstruktor
		// ********** ADJUST THESE VALUES HERE **********
		$this->source_table = $source_table;
		$this->dest_table = $dest_table;
		$this->source_attribute = $source_attribute;
		$this->dest_attribute = $dest_attribute;
		// **********************************************
	}

    public function __destruct() {
    }

	// GET Functions
	public function getsource_table() {
		return($this->source_table);
	}
	
	public function getdest_table() {
		return($this->dest_table);
	}
		
	public function getsource_attribute() {
		return($this->source_attribute);
	}
	
	public function getdest_attribute() {
		return($this->dest_attribute);
	}

	
	// SET Functions
	public function setsource_table($mValue) {
		$this->source_table = $mValue;
	}
	
	public function setdest_table($mValue) {
		$this->dest_table = $mValue;
	}
	
	public function setsource_attribute($mValue) {
		$this->source_attribute= $mValue;
	}
	
	public function setdest_attribute($mValue) {
		$this->dest_attribute= $mValue;
	}
	
	public function nice_print() {
		echo "<br />";
		echo "source_table: '".$this->source_table."'<br />";
		echo "source_attribute: '".$this->source_attribute."'<br />";
		echo "dest_table: '".$this->dest_table."'<br />";
		echo "dest_attribute: '".$this->dest_attribute."'<br />";
		echo "<br />";
	}
	
	public function mirror_direction() {
		$temp_source_table = $this->source_table;
		$temp_source_attribute = $this->source_attribute;
		$temp_dest_table = $this->dest_table;
		$temp_dest_attribute = $this->dest_attribute;
		
		$this->source_table = $temp_dest_table;
		$this->source_attribute = $temp_dest_attribute;
		$this->dest_table = $temp_source_table;
		$this->dest_attribute = $temp_source_attribute;
			
		//return $this;
	}
	
	public function returnSourceTableAttribute()
	{
		return 	$this->source_table.".".$this->source_attribute;
	}
	
	public function returnDestTableAttribute()
	{
		return 	$this->dest_table.".".$this->dest_attribute;
	}
}
?>