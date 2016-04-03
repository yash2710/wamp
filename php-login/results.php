<!DOCTYPE html>
<html>


<head>
<meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<title>Instant Quiz Results</title>
<script src="quizconfig.js">
</script>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.easypiechart.js"></script>


</head>

<body bgcolor="#FFFFFF">

<p align="center"><strong><font face="Arial">


<big>Instant Quiz Results</big></font></strong></p>
<div align="center"><center>



<form name="form" method="POST" action="results.php"><div align="center"><center><p>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%"><form method="POST" name="result"><table border="0" width="100%" cellpadding="0" height="116">
        <tr>
          <td height="25" bgcolor="#D3FFA8"><strong><font face="Arial"># of questions you got right:</font></strong></td>
          <td height="25"><p><input id="marks" name="marks" type="text" name="p" size="24"></td>
        </tr>
        <tr>
          <td height="17" bgcolor="#D3FFA8"><strong><font face="Arial">The questions you got wrong:</font></strong></td>
          <td height="17"><p><textarea name="T2" rows="3" cols="24" wrap="virtual"></textarea></td>
        </tr>
        <tr>
          <td height="25" bgcolor="#D3FFA8"><strong><font face="Arial">Grade in percentage:</font></strong></td>
          <td height="25"><input type="text" name="q" size="8"></td>

        </tr>
      </table>
      
    </form>
    </td>
  </tr>
</table>
</center></div>

<script>
var wrong=0
for (e=0;e<=2;e++)
document.result[e].value=""

var results=document.cookie.split(";")
console.log(results)
for (n=0;n<=results.length-1;n++){
if (results[n].charAt(1)=='q')
parse=n
}

var incorrect=results[0].split("=")
incorrect=incorrect[1].split("/")
if (incorrect[incorrect.length-1]=='b')
incorrect=""
document.result[0].value=totalquestions-incorrect.length
var x;
document.result[2].value=(totalquestions-incorrect.length)/totalquestions*100+"%"
x=(totalquestions-incorrect.length)/totalquestions*100
for (temp=0;temp<incorrect.length;temp++)
document.result[1].value+=incorrect[temp]+", "

$('.girish').html("<div class=\"chart\" data-percent="+x+">"+x+"%</div>")
window.location="results.php"
</script>

<!-- the user name input field uses a HTML5 pattern check -->
<label for="name">Student Name</label>
<input id="name" class="login_input" type="text" pattern="[a-zA-Z]{2,64}" name="name" required />

<!-- the email input field uses a HTML5 email type check -->
<label for="login_input_email">School</label>
<select name="scl" id="scl">
  <option>Ranip School No. 2</option>
  <option>Ranip School No. 8</option>
</select>
<br>
<br>
<input type="submit" value="Take the quiz again" name="button" id="button"> <input type="button" value="View solution" name="B2"
  onClick="showsolution()"></p>
  </center></div>

</form>

</html>

<?php
if(isset($_POST['button'])){
  $scl = $_POST["scl"];
  $name = $_POST["name"];
  $marks = $_POST["marks"];

  echo $scl;
  $connection = new mysqli("localhost","root","","app_db") or die("connection failed");

  $query = "INSERT INTO `".$scl."` VALUES ('".$name."', '".$marks."')";

  echo $query;
  if($connection->query($query) === TRUE){
    //redirect to index.html here
    header("Location: /php-login/index.html");
  }else{
    //jo terko acha lage
  }

  $connection->close();
}
?>