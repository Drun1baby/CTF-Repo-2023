<?php
//flag is in /flag
$con = new PDO($dsn,$user,$pass);
$sql = "select * from ctf.admin where username=? and password=?";
$sth = $con->prepare($sql);
$res = $sth->execute([$_POST['username'],$_POST['password']]);
if($sth->rowCount()!==0){
    readfile('/flag');
}