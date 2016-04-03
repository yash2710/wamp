<?php
mysql_connect("sql112.byethost9.com","your_username","your_password") or  die(mysql_error());
mysql_select_db("your_database_name");
$sql=mysql_query("select * from demo");
while($row=mysql_fetch_assoc($sql))
$output[]=$row;
print(json_encode($output));
mysql_close();
?>