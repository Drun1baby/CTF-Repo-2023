<?php
# MetInfo Enterprise Content Management System
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved.
if(!file_exists('./config/install.lock')){
	if(file_exists('./install/index.php')){
		header("location:./install/index.php");exit;
	}
	else{
		header("Content-type: text/html;charset=utf-8");
		echo "安装文件不存在，请上传安装文件。如已安装过，请新建config/install.lock文件。";
		die();
	}
}
define('M_NAME', 'index');
define('M_MODULE', 'web');
define('M_CLASS', 'index');
define('M_ACTION', 'doindex');
require_once './app/system/entrance.php';

# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>
