<?php 
$connection = new mysqli("localhost","root","","evolution");
if ($connection->connect_error) {
	die("Database connection failed: " . $connection->connect_error);
}

/*$db_select = mysqli_select_db($connection,"evolution");
if (!$db_select) {
	die("Database selection failed: " . mysqli_error($connection));
}*/



$sql = "SELECT * FROM `admin` WHERE (`username`='".$_POST["user"]."' and `password`='".$_POST["pass"]."')"; // change these VALUES as your POST request will have many variables

$result = $connection->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "User Found, " . $row["name"];
    }
} else {
    echo "Received: ".$_POST["user"].$_POST["pass"];
}
$connection->close();
?>
