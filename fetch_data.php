<?php 
$connection = new mysqli("localhost","root","","evolution");
if ($connection->connect_error) {
	die("Database connection failed: " . $connection->connect_error);
}

// $db_select = mysql_select_db($connection,"evolution");
// if (!$db_select) {
// 	die("Database selection failed: " . mysql_error());
// }

$scl_id=$_POST["scl_id"];
$date=$_POST["date"];

$sql_campus = "SELECT * FROM `campus` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";

$sql_dishwash = "SELECT * FROM `dishwash` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";

$sql_location = "SELECT * FROM `location` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";

$sql_others = "SELECT * FROM `others` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";

$sql_sanitation_boys = "SELECT * FROM `sanitation_boys` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";

$sql_sanitation_girls = "SELECT * FROM `sanitation_girls` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";

$sql_sweeper = "SELECT * FROM `sweeper` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";

$sql_water_area = "SELECT * FROM `water_area` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";

$sql_water_tank = "SELECT * FROM `water_tank` WHERE `scl_id`='".$scl_id."' and `date`='".$date."'";
$rows= array();

$result = $connection->query($sql_campus);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$result = $connection->query($sql_dishwash);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
$result = $connection->query($sql_location);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$result = $connection->query($sql_others);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$result = $connection->query($sql_sanitation_boys);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$result = $connection->query($sql_sanitation_girls);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$result = $connection->query($sql_sweeper);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$result = $connection->query($sql_water_area);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$result = $connection->query($sql_water_tank);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
print json_encode($rows);
?>
