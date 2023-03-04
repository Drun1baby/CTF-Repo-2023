<?php
error_reporting(0);
highlight_file(__FILE__);
$g = $_GET['g'];
$t = $_GET['t'];
echo new $g($t);