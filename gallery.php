<?php
$conn = mysqli_connect("localhost","root","","app_db") or die("connection_aborted");
$result = mysqli_fetch_assoc(mysqli_query($conn,"SELECT title,image,year FROM `app_db`.`gallery`"))	;
if($result){
	echo json_encode($result);
}