<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Untitled Document</title>
</head>

<body>
<?php

use Interrelationship\Interrelationship;
use Interrelationship\InterrelationshipRepository;

include("Interrelationship.php");
include("Interrelationships.php");

$all_interrelationships = new InterrelationshipRepository();
$an_interrelationship = new Interrelationship();
$an_interrelationship->setsource_table("a");
$an_interrelationship->setsource_attribute("b");
$an_interrelationship->setdest_table("c");
$an_interrelationship->setdest_attribute("d");
$an_interrelationship->setcardinality("1:N");
//$an_interrelationship->nice_print();

$all_interrelationships->add_an_interrelationship($an_interrelationship);

$a_mirrored_interrelationship = clone $an_interrelationship;
$a_mirrored_interrelationship->mirror_direction();

$all_interrelationships->add_an_interrelationship($a_mirrored_interrelationship);
$all_interrelationships->nice_print();

?>
</body>
</html>