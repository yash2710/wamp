<?php 
$connection = new mysqli("localhost","root","","evolution");
if ($connection->connect_error) {
	die("Database connection failed: " . $connection->connect_error);
}

/*$db_select = mysqli_select_db($connection,"evolution");
if (!$db_select) {
	die("Database selection failed: " . mysqli_error($connection));
}*/



$sql = "insert into `volunteer` values('".$_POST["user"]."', '".$_POST["pass"]."')"; // change these VALUES as your POST request will have many variables

//$sql = "SELECT * FROM `volunteer` WHERE (`username`='".$_POST["user"]."' and `password`='".$_POST["pass"]."')"; // change these VALUES as your POST request will have many variables

$result = $connection->query($sql);

if ($result) {
    echo "Entered data successfully\n"
}
$connection->close();
?>