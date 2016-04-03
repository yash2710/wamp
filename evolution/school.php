<?php 
$connection = new mysqli("localhost","root","","evolution");
if ($connection->connect_error) {
	die("Database connection failed: " . $connection->connect_error);
}

// $db_select = mysql_select_db($connection,"evolution");
// if (!$db_select) {
// 	die("Database selection failed: " . mysql_error());
// }



$sql = "SELECT `id`, `name` FROM `school`"; // change these VALUES as your POST request will have many variables
		
$rows = array();

$result = $connection->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
print json_encode($rows);
?>
