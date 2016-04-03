<?php 
$connection =new mysqli("localhost","root","","evolution");
if ($connection->connect_error) {
	die("Database connection failed: " . $connection->connect_error);
}




$sanitation_girls = "INSERT INTO `sanitation_girls` 
					( 
						`uname`,`date`,`scl_id`,`basin`,`urinal`,`washroom`,`flow_basin`,`flow_urinal`,`window`,`mirror`,`taps`,`tumbler`,`bucket`,`door_latch`,`stinking`,`roof`,`clog`,`status`,`comments`
					) 
					VALUES 
					(
						'".$_POST["uname"]."','".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["basin_girls"]."','".$_POST["urinal_girls"]."','"
						.$_POST["washroom_girls"]."','".$_POST["flow_basin_girls"]."','".$_POST["flow_urinal_girls"]."','".$_POST["window_girls"]."','"
						.$_POST["mirror_girls"]."','".$_POST["taps_girls"]."','".$_POST["tumb_girls"]."','".$_POST["buck_girls"]."','".$_POST["latch_girls"]."','".$_POST["stinking_girls"]."','".$_POST["roof_girls"]."','".$_POST["clog_girls"]."','".$_POST["status_girls"]."','"
						.$_POST["comments_girls"]."'
					)"; // change these VALUES as your POST request will have many variables
						
			
$sanitation_boys = "INSERT INTO `sanitation_boys` 
					( 
						`uname`,`date`,`scl_id`,`basin`,`urinal`,`washroom`,`flow_basin`,`flow_urinal`,`window`,`mirror`,`taps`,`tumbler`,`bucket`,`door_latch`,`stinking`,`roof`,`clog`,`status`,`comments`
					) 
					VALUES 
					(
						'".$_POST["uname"]."','".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["basin_boys"]."','".$_POST["urinal_boys"]."','"
						.$_POST["washroom_boys"]."','".$_POST["flow_basin_boys"]."','".$_POST["flow_urinal_boys"]."','".$_POST["window_boys"]."','"
						.$_POST["mirror_boys"]."','".$_POST["taps_boys"]."','".$_POST["tumb_boys"]."','".$_POST["buck_boys"]."','".$_POST["latch_boys"]."','".$_POST["stinking_boys"]."','".$_POST["roof_boys"]."','".$_POST["clog_boys"]."','".$_POST["status_boys"]."','"
						.$_POST["comments_boys"]."'
					)";

$sweeper = "INSERT INTO `sweeper`
				(
					`uname`,`date`,`scl_id`,`boys`,`girls`,`corridor`,`campus`,`water`,`dishwash`,`class`,`storage`,`dustbin`
				)
				VALUES 
				(
					'".$_POST["uname"]."','".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["boys"]."','".$_POST["girls"]."','".$_POST["corridor"]."','".$_POST["campus"]
					."','".$_POST["water"]."','".$_POST["dishwash"]."','".$_POST["class"]."','".$_POST["storage"]."','".$_POST["dustbin"]."'
				)";

$water_tank = "INSERT INTO `water_tank`
				(
					`uname`,`date`,`scl_id`,`rglr_clean`,`purifier_proper`,`f_filling`,`capacity`,`f_clean_month`,`status`
				)
				VALUES
				(
					'".$_POST["uname"]."','".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["reg_clean"]."','".$_POST["purifier_proper"]."','".$_POST["freq_fil"]."','".$_POST["capacity_tank"]."','".$_POST["f_clean_month"]."','".$_POST["staus_water"]."'
				)";

$water_area = "INSERT INTO `water_area`
				(
					`uname`,`date`,`scl_id`,`reg_flow`,`tap_leakage`,`drain_clog`,`stink`,`broken_taps`,`dustbin`,`clean`,`status`,`comments`
				)
				VALUES
				(
					'".$_POST["uname"]."','".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["reg_flow_water"]."','".$_POST["tap_leakage_water"]."','".$_POST["drain_clog_water"]."','".$_POST["stinking_water_area"]."','".$_POST["broken_taps_water"]."','".$_POST["dustbins_water"]."','".$_POST["clean_water"]."','".$_POST["staus_water_area"]."','".$_POST["comments_water"]."'
				)";

$dishwash = "INSERT INTO `dishwash`
				(
					`uname`,`date`,`scl_id`,`reg_flow`,`tap_leakage`,`drain_clog`,`stink`,`dustbin`,`clean`,`status`,`comments`
				)
				VALUES
				(
					'".$_POST["uname"]."','".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["reg_flow_dish"]."','".$_POST["tap_leakage_dish"]."','".$_POST["drain_clog_dish"]."','".$_POST["stinking_dish"]."','".$_POST["dustbins_dish"]."','".$_POST["clean_dish"]."','".$_POST["staus_dishwash"]."','".$_POST["comments_dish"]."'
				)";

$others = "INSERT INTO `others`
				(
					`uname`,`date`,`scl_id`,`clean_midday`,`clean_campus`,`soundsys`,`kitchen`,`sports`,`stationery`,`cultural`,`comments`
				)
				VALUES
				(
					'".$_POST["uname"]."','".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["clean_midday"]."','".$_POST["clean_campus"]."','".$_POST["sound_system"]."','".$_POST["kitchen_midday_meals"]."','".$_POST["sports_kits"]."','".$_POST["books_stationery"]."','".$_POST["amenities_cultural"]."','".$_POST["comments"]."'
				)";

$campus = "INSERT INTO `campus`
				(
					`uname`,`date`,`scl_id`,`need_repair`,`clean`,`board`,`dustbin`,`cond_board`,`color`,`comments`,`shade`
				)
				VALUES
				(
					'".$_POST["uname"]."','".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["need_repair"]."','".$_POST["clean"]."','".$_POST["board"]."','".$_POST["dustbin"]."','".$_POST["cond_board"]."','".$_POST["color"]."','".$_POST["renovation_comments"]."','".$_POST["status_shade"]."'
				)";

$location = "INSERT INTO `location`
				(
					`date`,`scl_id`,`uname`,`lat`,`lon`
				)
				VALUES
				(
					'".$_POST["date"]."','".$_POST["scl_id"]."','".$_POST["uname"]."','".$_POST["lat"]."','".$_POST["lon"]."'
				)";

if ($connection->query($sanitation_girls) === TRUE 
	&& $connection->query($sanitation_boys) === TRUE 
	&& $connection->query($sweeper) === TRUE 
	&& $connection->query($water_tank) === TRUE
	&& $connection->query($water_area) === TRUE
	&& $connection->query($dishwash) === TRUE 
	&& $connection->query($others) === TRUE 
	&& $connection->query($campus) === TRUE 
	&& $connection->query($location) === TRUE) {

	echo "successfully created";
}else{
	echo mysqli_error($connection);
}
?>
