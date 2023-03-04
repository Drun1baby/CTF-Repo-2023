<?php
highlight_file(__FILE__);
error_reporting(0);

$num = $_GET['num'];

if (preg_match("/\'|\"|\`| |<|>|?|\^|%|\$/", $num)) {
    die("nononno");
}

if (eval("return ${num} != 2;") && $num == 0 && is_numeric($num) != true) {
    system('cat flag.php');
} else {
    echo '2';
}