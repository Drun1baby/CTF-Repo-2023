<?php
error_reporting(0);

$flag=getenv("DASFLAG");
$key = md5($flag);

if ($_SERVER["REMOTE_ADDR"] != "127.0.0.1") {
    echo "Just View From 127.0.0.1 \n";
    echo "\n";
    echo $key;
    return;
}

if (isset($_POST["key"]) && $_POST["key"] == $key) {
    echo $flag;
    exit;
}
?>