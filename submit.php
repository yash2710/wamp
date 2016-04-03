<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lakshya";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// $sql = "INSERT INTO participants
// VALUES (, 'Doe', 'john@example.com')";

// if ($conn->query($sql) === TRUE) {
//     echo "New record created successfully";
// } else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
// }



	if(!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['college']) || !isset($_POST['number'])){
		echo ('We are sorry, but there appears to be a problem with the form you submitted.');
	}
	
	$usrName = $_POST['name'];
	$usrEmail = $_POST['email'];
	$college = $_POST['college'];
	$usrMobile = $_POST['number'];
	$reference = $_POST['ref'];

	$regId = "online-".$college."-" . date("d-m") . "-";

	$sql = "SELECT `reg_id` FROM `participants` WHERE (`reg_id` LIKE '".$regId."%')";

	$regnum = 0;
	echo $sql;
	$result = $conn->query($sql);
	if(mysqli_num_rows($result) > 0){

		$a = max($row = $result->fetch_assoc());
		
		$num = (int) substr($a,-3);
		$num +=1;
		$regId .= $num;
	} else{
		$regId .= "1";
	}

	$ins = "'".$regId."'";
	// if ($conn->query($sql) === TRUE) {
    	
	// } else {
 //    	echo "Error: " . $sql . "<br>" . $conn->error;
	// }

	$ins .= ",'".$usrName."','".$college."','".$usrMobile."','".$usrEmail."','".$reference."'";

	// Events
	$jugaadFromKabaad = isset($_POST['mech1'])?"1":"0";
	$autoQuiz = isset($_POST['mech2'])?"1":"0";
	$expertLecture = isset($_POST['mech3'])?"1":"0";
	$circuitDeElectronique = isset($_POST['ec1'])?"1":"0";
	$cradleOfBytes = isset($_POST['ce1'])?"1":"0";
	$designOWeb = isset($_POST['ce2'])?"1":"0";
	$codeMaze = isset($_POST['ce3'])?"1":"0";
	$tacheOHunt = isset($_POST['civil1'])?"1":"0";
	$aashiyaan = isset($_POST['civil2'])?"1":"0";
	$totalStation = isset($_POST['civil3'])?"1":"0";
	$designADoll = isset($_POST['textile1'])?"1":"0";
	$vsm = isset($_POST['nt1'])?"1":"0";
	$gdpi = isset($_POST['nt2'])?"1":"0";
	$logotrix = isset($_POST['nt3'])?"1":"0";
	$genQuiz = isset($_POST['nt4'])?"1":"0";
	$sitcomQuiz = isset($_POST['nt5'])?"1":"0";
	$debate = isset($_POST['nt6'])?"1":"0";
	$jam = isset($_POST['nt7'])?"1":"0";
	$sherlock = isset($_POST['f1'])?"1":"0";
	$treasureHunt = isset($_POST['f2'])?"1":"0";
	$gullyCricket = isset($_POST['f3'])?"1":"0";
	$slowCycling = isset($_POST['f4'])?"1":"0";

	$event = "";
	if($jugaadFromKabaad == 1){
		$event .= "\tJugaad From Kabaad\n";
	}
	if($autoQuiz == 1){
		$event .= "\tMech Auto Quiz\n";
	}
	if($expertLecture == 1){
		$event .= "\tExpert Lecture on Refrigeration and Air Conditioning\n";
	}
	if($circuitDeElectronique == 1){
		$event .= "\tCircuit De Electronique\n";
	}
	if($cradleOfBytes == 1){
		$event .= "\tCradle of Bytes\n";
	}
	if($designOWeb == 1){
		$event .= "\tDesign o Web\n";
	}
	if($codeMaze == 1){
		$event .= "\tCode Maze\n";
	}
	if($tacheOHunt == 1){
		$event .= "\tTache-o-Hunt\n";
	}
	if($aashiyaan == 1){
		$event .= "\tAashiyaan\n";
	}
	if($totalStation == 1){
		$event .= "\tTotal Station Workshop\n";
	}
	if($designADoll == 1){
		$event .= "\tDesign A Doll\n";
	}
	if($vsm == 1){
		$event .= "\tVirtual Stock Market\n";
	}
	if($gdpi == 1){
		$event .= "\tMock GD/PI\n";
	}
	if($logotrix == 1){
		$event .= "\tLogoTrix\n";
	}
	if($genQuiz == 1){
		$event .= "\tGen Quiz\n";
	}
	if($sitcomQuiz == 1){
		$event .= "\tSitCom Quiz\n";
	}
	if($debate == 1){
		$event .= "\tUN Debate\n";
	}
	if($jam == 1){
		$event .= "\tJAM\n";
	}
	if($sherlock == 1){
		$event .= "\tSherlock\n";
	}
	if($treasureHunt == 1){
		$event .= "\tTreasure Hunt\n";
	}
	if($gullyCricket == 1){
		$event .= "\tGully Cricket\n";
	}
	if($slowCycling == 1){
		$event .= "\tSlow Cycling\n";
	}
	

	$ins .= ",'".$jugaadFromKabaad."','".$autoQuiz."','".$expertLecture."','".$circuitDeElectronique."','".$cradleOfBytes."','".$designOWeb."','".$codeMaze."','".$tacheOHunt."',
				'".$aashiyaan."','".$totalStation."','".$designADoll."','".$vsm."','".$gdpi."','".$logotrix."','".$genQuiz."','".$sitcomQuiz."',
				'".$debate."','".$jam."','".$sherlock."','".$treasureHunt."','".$gullyCricket."','".$slowCycling."'";
	

	// Workshops

	$ethicalHacking = isset($_POST['w1'])?"1":"0";
	$pcbDesign = isset($_POST['w2'])?"1":"0";
	$exorde = isset($_POST['w3'])?"1":"0";
	$robotics = isset($_POST['w4'])?"1":"0";
	$photography = isset($_POST['w5'])?"1":"0";
	$entreprenuer = isset($_POST['w6'])?"1":"0";
	$googleAnalytics = isset($_POST['w7'])?"1":"0";

	if($ethicalHacking == 1){
		$event .= "\tEthical Hacking\n";
	}
	if($pcbDesign == 1){
		$event .= "\tPCB Design\n";
	}
	if($exorde == 1){
		$event .= "\tExorde\'51\n";
	}
	if($robotics == 1){
		$event .= "\tRobotics\n";
	}
	if($photography == 1){
		$event .= "\tPhotography\n";
	}
	if($entreprenuer == 1){
		$event .= "\ti-Entrepreneur\n";
	}
	if($googleAnalytics == 1){
		$event .= "\tGoogle Analytics Workshop\n";
	}

	$ins .= ",'".$ethicalHacking."','".$pcbDesign."','".$exorde."','".$robotics."','".$photography."','".$entreprenuer."','".$googleAnalytics."'";
	
	// Amount
	$totalAmt = $ethicalHacking*1100 + $pcbDesign*200 + $exorde*100 + $robotics*800 + $photography*150 + $entreprenuer*30 + $googleAnalytics*100 + $vsm*50 + $gdpi*100 + $logotrix*50 + $genQuiz*25 + $sitcomQuiz*25 + $debate*25 + $jam*30 + $jugaadFromKabaad*100 + $autoQuiz*25 + $expertLecture*20 + $circuitDeElectronique*50 + $cradleOfBytes*50 + $designOWeb*50 + $codeMaze*50 + $tacheOHunt*30 + $aashiyaan*(100/3) + $totalStation*50 + $designADoll*50 + $sherlock*30 + $treasureHunt*(50/3) + $gullyCricket*10 + $slowCycling*10;
	//$amtReceived = $_POST['TextBox12'];
	
	$ins .= ",'".$totalAmt."'";


	
/*	echo "Hello, $usrName $usrSurName!";
	echo "<br>";
	echo "Branch: $usrBranch<br>";
	echo "Sem: $usrSemester<br>";
	echo "Gender: $usrGender<br>";
	echo "Accomodation: $accomodationRequired<br>";
	echo "Accomodation13: $accomodation13<br>";
	echo "Accomodation14: $accomodation14<br>";
	echo "Accomodation15: $accomodation15<br>";
	echo "Accomodation16: $accomodation16<br>";
	echo "Total Amount: $totalAmt<br>";
	echo "Received Amount: $amtReceived<br>";*/

	$sql = "INSERT INTO `participants` VALUES (".$ins.")";

	$regnum = 0;
	
	if($conn->query($sql) === TRUE){	
	
	// EDIT THE 2 LINES BELOW AS REQUIRED

	$email_to = "trivediyash0@gmail.com,".$usrEmail;
	$email_subject = "[Auto-Generated]Lakshya 2015 Registration Successful";
	$email_from = "13bce123@nirmauni.ac.in";

	$email_message = "Form details below.\n\n";

	function clean_string($string) {
		$bad = array("content-type","bcc:","to:","cc:","href");

		return str_replace($bad,"",$string);
	}

	$email_message .= "Name: ".clean_string($usrName)."\n";

	$email_message .= "Email: ".clean_string($usrEmail)."\n";

	$email_message .= "Contact: ".clean_string($usrMobile)."\n";

	$email_message .= "Events:".$event."\n";

	$email_message .= "Total :".$totalAmt."\n";

	// create email headers

	$headers = 'From: '.$email_from."\r\n".

	'Reply-To: '.$email_from."\r\n" .

	'X-Mailer: PHP/' . phpversion();

	@mail($email_to, $email_subject, $email_message, $headers);

	echo "Thank you for contacting us. We will be in touch with you very soon.";

	} else{
		echo "We are facing some error. Please try again later.". $conn->error;
	}
?>