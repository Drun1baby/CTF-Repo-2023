<?php
# MetInfo Enterprise Content Management System ceshi
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved. 
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ERROR | E_PARSE);
@set_time_limit(0);
ini_set("magic_quotes_runtime", 0);
if(PHP_VERSION < '4.1.0') {
	$_GET         = &$HTTP_GET_VARS;
	$_POST        = &$HTTP_POST_VARS;
	$_COOKIE      = &$HTTP_COOKIE_VARS;
	$_SERVER      = &$HTTP_SERVER_VARS;
	$_ENV         = &$HTTP_ENV_VARS;
	$_FILES       = &$HTTP_POST_FILES;
}
function randStr($i){
  $str = "abcdefghijklmnopqrstuvwxyz";
  $finalStr = "";
  for($j=0;$j<$i;$j++)
  {
    $finalStr .= substr($str,mt_rand(0,25),1);
  }
  return $finalStr;
}
function deldir_in($fileDir,$type = 0){
	@clearstatcache();
	$fileDir = substr($fileDir, -1) == '/' ? $fileDir : $fileDir . '/';
	if(!is_dir($fileDir)){
		return false;
	}
	$resource = opendir($fileDir);
	@clearstatcache();
	while(($file = readdir($resource))!== false){
		if($file == '.' || $file == '..'){
			continue;
		}
		if(!is_dir($fileDir.$file)){
			delfile_in($fileDir.$file);
		}else{
			deldir_in($fileDir.$file);
		}
	}
	closedir($resource);
	@clearstatcache();
	if($type==0)rmdir($fileDir);
	return true;
}

function delfile_in($fileUrl){
	@clearstatcache();
	if(file_exists($fileUrl)){
		unlink($fileUrl);
		return true;
	}else{
		return false;
	}
	@clearstatcache();
}

define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
isset($_REQUEST['GLOBALS']) && exit('Access Error');
foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		$_key{0} != '_' && $$_key = daddslashes($_value);
	}
}
$m_now_time     = time();
$m_now_date     = date('Y-m-d H:i:s',$m_now_time);
$nowyear    = date('Y',$m_now_time);
$localurl="http://";
$localurl.=$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];
$install_url=$localurl;

if(file_exists('../config/install.lock')){
	exit('对不起，该程序已经安装过了。<br/>
	      如你要重新安装，请手动删除config/install.lock文件。');
}
deldir_in('../cache', 1);
switch ($action)
{
	case 'apitest':
	{
		$post=array('t'=>'t');
		echo curl_post($post,15);
		die();
	}
	case 'inspect':
	{
		$mysql_support = (function_exists( 'mysqli_connect')) ? ON : OFF;
		if(function_exists( 'mysqli_connect')){
			$mysql_support  = 'ON';
			$mysql_ver_class ='OK';
		}else {
			$mysql_support  = 'OFF';
			$mysql_ver_class ='WARN';
		}
		if(PHP_VERSION<'5.3.0'){
			$ver_class = 'WARN';
			$errormsg['version']='php 版本过低';
		}else {
			$ver_class = 'OK';
			$check=1;
		}
		$function='OK';
		if(!function_exists('file_put_contents')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持file_put_contents函数，系统无法写文件。</li>";
		}
		if(!function_exists('fsockopen')&&!function_exists('pfsockopen')&&!function_exists('stream_socket_client')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持fsockopen，pfsockopen,stream_socket_client函数，系统邮件功能不能使用。请至少开启其中一个。</li>";
		}
		if(!function_exists('copy')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持copy函数，无法上传文件。</li>";
		}
		if(!function_exists('fsockopen')&&!function_exists('pfsockopen')&&(!get_extension_funcs('curl')||!function_exists('curl_init')||!function_exists('curl_setopt')||!function_exists('curl_exec')||!function_exists('curl_close'))){
				$function='WARN';
				$fstr.="<li class='WARN'>空间不支持fsockopen，pfsockopen函数，curl模块(需同时开启curl_init,curl_setopt,curl_exec,curl_close)，系统在线更新，短信发送功能无法使用。请至少开启其中一个。</li>";
		}
		if(!get_extension_funcs('gd')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持gd模块，图片打水印和缩略生成功能无法使用。</li>";
		}
		if(!function_exists('gzinflate')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持gzinflate函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('fopen')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持fopen函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('opendir')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持opendir函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('crc32')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持crc32函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('gzopen')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持gzopen函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('unpack')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持unpack函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('bin2hex')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持bin2hex函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('pack')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持pack函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('php_uname')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持php_uname函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('ini_set')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持ini_set函数，系统无法正常包含文件，导致后台会出现空白现象。</li>";
		}

        
        
        if(!function_exists('mb_strlen')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持mb_strlen函数，系统无法正常包含文件，会导致前台显示不全。</li>";
		}


		session_start();
		if($_SESSION['install']!='metinfo'){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持session，无法登陆后台。</li>";
		}
		$w_check=array(
		'../about/',
		'../download/',
		'../product/',
		'../news/',
		'../img/',
		'../job/',
		'../search/',
		'../sitemap/',
		'../member/',
		'../upload/',
		'../config/',
		'../config/config_db.php',
		'../config/config_safe.php',
		'../cache/',
		'../upload/file/',
		'../upload/image/',
		'../message/',
		'../feedback/',
		'../admin/databack/',
		'../admin/update/'
		);
		$class_chcek=array();
		$check_msg = array();
		$count=count($w_check);
		for($i=0; $i<$count; $i++){
			if(!file_exists($w_check[$i])){
				$check_msg[$i].= '文件或文件夹不存在请上传';$check=0;
				$class_chcek[$i] = 'WARN';
			} elseif(is_writable_met($w_check[$i])){
				$check_msg[$i].= '通 过';
				$class_chcek[$i] = 'OK';
				$check=1;
			} else{
				$check_msg[$i].='777属性检测不通过'; $check=0;
				$class_chcek[$i] = 'WARN';
			}
			if($check!=1 and $disabled!='disabled'){$disabled = 'disabled';}
		}
		include template('inspect');
		break;
	}
	case 'db_setup':
	{
		if($setup==1){
			$db_prefix      = trim($db_prefix);
			$db_host        = trim($db_host);
			$db_username    = trim($db_username);
			$db_pass        = trim($db_pass);
			$db_name        = trim($db_name);
			$config="<?php
                   /*
                   con_db_host = \"$db_host\"
                   con_db_id   = \"$db_username\"
                   con_db_pass	= \"$db_pass\"
                   con_db_name = \"$db_name\"
                   tablepre    =  \"$db_prefix\"
                   db_charset  =  \"utf8\";
                  */
                  ?>";

			$fp=fopen("../config/config_db.php",'w+');
			fputs($fp,$config);
			fclose($fp);
			$db = mysqli_connect($db_host,$db_username,$db_pass) or die('连接数据库失败: ' . mysqli_connect_error());
			if(!@mysqli_select_db($db , $db_name)){
				mysqli_query($db, "CREATE DATABASE $db_name ") or die('创建数据库失败'.mysqli_error($db));
			}
			mysqli_select_db($db , $db_name);
			if(mysqli_get_server_info($db)>4.1){
			 mysqli_query($db , "set names utf8");
			}
			if(mysqli_get_server_info($db)>'5.0.1'){
                mysqli_query($db , "SET sql_mode=''",$link);
			}
			if(mysqli_get_server_info($db)>='4.1'){
                mysqli_query($db , "set names utf8");
				$content=readover("sql.sql");
                #$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
                $content = preg_replace_callback("/{#(.+?)}/is", function($r)use($lang){ return $lang[$r[1]]; }, $content);
                $installinfo=creat_table($content, $db);
			}else {
				echo "<SCRIPT language=JavaScript>alert('你的mysql版本过低，请确保你的数据库编码为utf-8,官方建议你升级到mysql4.1.0以上');</SCRIPT>";
				die();
				$content=readover("sql.sql");
				$content=str_replace('ENGINE=MyISAM DEFAULT CHARSET=utf8','TYPE=MyISAM',$content);
			}
			if($cndata=="yes"){
				$content=readover("cn_config.sql");
				#$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
                $content = preg_replace_callback("/{#(.+?)}/is", function($r)use($lang){ return $lang[$r[1]]; }, $content);
				$installinfo.=creat_table($content, $db);
            }			
		    if($endata=="yes"){
				$content=readover("en_config.sql");
				#$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
				$content = preg_replace_callback("/{#(.+?)}/is", function($r)use($lang){ return $lang[$r[1]]; }, $content);
				$installinfo.=creat_table($content, $db);
				
            }	
			
			if($showdata=='yes'){
				if($cndata=="yes"){
					$content=readover("cn.sql");
					#$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
                    $content = preg_replace_callback("/{#(.+?)}/is", function($r)use($lang){ return $lang[$r[1]]; }, $content);
					$installinfo.=creat_table($content, $db);
				}
				if($endata=="yes"){
					$content=readover("en.sql"); 
					#$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
                    $content = preg_replace_callback("/{#(.+?)}/is", function($r)use($lang){ return $lang[$r[1]]; }, $content);
					$installinfo.=creat_table($content, $db);
				}
				
			}
			$content=readover("lang.sql"); 
			#$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
            $content = preg_replace_callback("/{#(.+?)}/is", function($r)use($lang){ return $lang[$r[1]]; }, $content);
			$installinfo.=creat_table($content, $db);
			file_put_contents('../config/config_safe.php','<?php/*'.met_rand_i(32).'*/?>');
			header("location:index.php?action=adminsetup&cndata={$cndata}&endata={$endata}&showdata={$showdata}");exit;
		}else {
			include template('databasesetup');
		}
		break;
	}
	case 'adminsetup':
	{
		if($setup==1){
			if($regname=='' || $regpwd=='' || $email==''){
				echo("<script type='text/javascript'> alert('请填写管理员信息！'); history.go(-1); </script>");
			}
			
			if($email_scribe==1){
				$post=array(
					'id'=>'67d6b20a0ee8352affc40bd275b55299df2e04aded66c4e4',
					't'=>'qf_booked_feedback',
					'to'=>"$email"
				);
				$yj = curl_post1($post,45);
			}
            $regname = trim($regname);
            $regpwd  = md5(trim($regpwd));
            $email   = trim($email);
            $m_now_time = time();
            $config = parse_ini_file('../config/config_db.php','ture');
            @extract($config);
            $link = mysqli_connect($con_db_host,$con_db_id,$con_db_pass) or die('连接数据库失败: ' . mysqli_error($link));
            mysqli_select_db($link , $con_db_name);
            if(mysqli_get_server_info($link)>4.1){
                mysqli_query($link , "set names utf8");
            }
            if(mysqli_get_server_info($link)>'5.0.1'){
                mysqli_query($link , "SET sql_mode=''");
            }
            $met_admin_table = "{$tablepre}admin_table";
            $met_config      = "{$tablepre}config";
            $met_column      = "{$tablepre}column";
            $met_lang      = "{$tablepre}lang";
            $met_templates      = "{$tablepre}templates";
            $query = " INSERT INTO $met_admin_table set
                      admin_id           = '$regname',
                      admin_pass         = '$regpwd',
					  admin_introduction = '创始人',
					  admin_group        = '10000',
				      admin_type         = 'metinfo',
					  admin_email        = '$email',
					  admin_mobile       = '$tel',
					  admin_register_date= '$m_now_date',
					  admin_shortcut='[{\"name\":\"lang_skinbaseset\",\"url\":\"system/basic.php?anyid=9&lang=cn\",\"bigclass\":\"1\",\"field\":\"s1001\",\"type\":\"2\",\"list_order\":\"10\",\"protect\":\"1\",\"hidden\":\"0\",\"lang\":\"lang_skinbaseset\"},{\"name\":\"lang_indexcolumn\",\"url\":\"column/index.php?anyid=25&lang=cn\",\"bigclass\":\"1\",\"field\":\"s1201\",\"type\":\"2\",\"list_order\":\"0\",\"protect\":\"1\",\"hidden\":\"0\",\"lang\":\"lang_indexcolumn\"},{\"name\":\"lang_unitytxt_75\",\"url\":\"interface/skin_editor.php?anyid=18&lang=cn\",\"bigclass\":\"1\",\"field\":\"s1101\",\"type\":\"2\",\"list_order\":\"0\",\"protect\":\"1\",\"hidden\":\"0\",\"lang\":\"lang_unitytxt_75\"},{\"name\":\"lang_tmptips\",\"url\":\"interface/info.php?anyid=24&lang=cn\",\"bigclass\":\"1\",\"field\":\"\",\"type\":\"2\",\"list_order\":\"0\",\"protect\":\"1\",\"hidden\":\"0\",\"lang\":\"lang_tmptips\"},{\"name\":\"lang_mod2add\",\"url\":\"content/article/content.php?action=add&lang=cn&anyid=29\",\"bigclass\":\"1\",\"field\":\"\",\"type\":\"2\",\"list_order\":\"0\",\"protect\":\"0\",\"hidden\":\"0\",\"lang\":\"lang_mod2add\"},{\"name\":\"lang_mod3add\",\"url\":\"content/product/content.php?action=add&lang=cn&anyid=29\",\"bigclass\":\"1\",\"field\":\"\",\"type\":2,\"list_order\":\"0\",\"protect\":0}]',
					  usertype        	 = '3',
					  content_type   = '1',
					  admin_ok           = '1'";

            mysqli_query($link , $query) or die('写入数据库失败XXX: ' . mysqli_error($link));
            $query = " UPDATE $met_config set value='$webname_cn' where name='met_webname' and lang='cn'";
            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));
            $met_skin_table="{$tablepre}skin_table";
            $query = " UPDATE $met_config set value='$webkeywords_cn' where name='met_keywords' and lang='cn'";
            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));
			$query = " UPDATE $met_config set value='$webname_en' where name='met_webname' and lang='en'";
            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));
			$query = " UPDATE $met_config set value='$webkeywords_en' where name='met_keywords' and lang='en'";
            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));

			$force =randStr(7);
			$query = " UPDATE $met_config set value='$force' where name='met_member_force'";
            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));
			$install_url=str_replace("install/index.php","",$install_url);
			$query = " UPDATE $met_config set value='$install_url' where name='met_weburl'";
            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));
			$query = " UPDATE $met_lang set met_weburl='$install_url' where lang!='metinfo'";
            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));
			$adminurl=$install_url.'admin/';
			$query = " UPDATE $met_column set out_url='$adminurl' where module='0'";
            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));
		
			$SQL="SELECT * FROM $met_skin_table";
			$query=mysqli_query($link , $SQL);
			#while($row=mysql_fetch_array($query)){
			while($row = mysqli_fetch_array($query)){
				$destination="../templates/".$row[skin_file]."/lang/language_tc.ini";
				if(!file_exists($destination)){
					$fp=fopen("$destination", "w+");
					fclose($fp);
					$source="../templates/".$row[skin_file]."/lang/language_cn.ini";
					copy($source,$destination);
				}
			}

			if($cndata=="yes"&&$endata=="yes"){
				$query = "UPDATE $met_config set value='$lang_index_type' where name='met_index_type' and lang='metinfo'";
			}
			else{
				if($cndata=="yes" or ($cndata<>"yes" and $endata<>"yes")){
					$query = "UPDATE $met_config set value='cn' where name='met_index_type' and lang='metinfo'";
				}
				else{
					if($endata=="yes"){
						$query = "UPDATE $met_config set value='en' where name='met_index_type' and lang='metinfo'";
					}
				}
			}

            mysqli_query($link , $query) or die('写入数据库失败: ' . mysqli_error($link));
            @chmod('../config/config_db.php',0554);
            /*require_once '../include/mysql_class.php';
            $db = new dbmysql()*/;

            define('IN_MET', true);
            require_once '../app/system/include/class/mysql.class.php';
            $db = new DB();

        	$db->dbconn($con_db_host,$con_db_id,$con_db_pass,$con_db_name);
			
			if($showdata != 'yes'){
				 if($cndata == 'yes'){
					install_tag_templates($db,$met_templates,'metv6','cn');
				}

				if($endata == 'yes'){
					install_tag_templates($db,$met_templates,'metv6','en');
				}
			}

			$conlist = $db->get_one("SELECT * FROM $met_config WHERE name='met_weburl'");
			$met_weburl=$conlist[value];
			$indexcont = $db->get_one("SELECT * FROM $met_config WHERE name='met_index_content' and lang='cn'");
			if($indexcont){
				$index_content=str_replace("#metinfo#",$met_weburl,$indexcont[value]);
				$query = "update $met_config SET value = '$index_content' where name='met_index_content' and lang='cn'";
				$db->query($query);
			}
			$showlist = $db->get_all("SELECT * FROM $met_column WHERE module='1'");
			if($showlist){
				foreach($showlist as $key=>$val){
					$contentx=str_replace("#metinfo#",$met_weburl,$val[content]);
					$query = "update $met_column SET content = '$contentx' where id='$val[id]'";
					$db->query($query);
				}
			}

			$agents='';
			if(file_exists('./agents.php')){
				include './agents.php';
				unlink('./agents.php');
			}
			unlink('../cache/langadmin_cn.php');
			unlink('../cache/langadmin_en.php');
			unlink('../cache/lang_cn.php');
			unlink('../cache/lang_en.php');
			$query="select * from $met_config where name='metcms_v'";
			$ver=$db->get_one($query);
			$webname=$webname_cn?$webname_cn:($webname_en?$webname_en:'');
			$webkeywords=$webkeywords_cn?$webkeywords_cn:($webkeywords_en?$webkeywords_en:'');
			$spt = '<script type="text/javascript" src="http://api.metinfo.cn/record_install.php?';
			$spt .= "url=" .$install_url;
			$spt .= "&email=".$email."&installtime=".$m_now_date."&softtype=1";
			$spt .= "&webname=".$webname."&webkeywords=".$webkeywords."&tel=".$tel;
			$spt .= "&version=".$ver[value]."&php_ver=" .PHP_VERSION. "&mysql_ver=" .mysqli_get_server_info($link)."&browser=".$_SERVER['HTTP_USER_AGENT'].'|'.$se360;
			$spt .= "&agents=".$agents;
			$spt .= '"></script>';
			echo $spt;
			$fp  = @fopen('../config/install.lock', 'w');
			@fwrite($fp," ");
			@fclose($fp);
			$metHOST=$_SERVER['HTTP_HOST'];
			$m_now_year=date('Y');
			$metcms_v=$ver[value];
$met404="
<!DOCTYPE HTML>
<html>
<head>
<meta charset=\"utf-8\" />
<title>Page Not Found!</title>
<style type=\"text/css\">
<!--
body, td, th {  font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000; margin: 0; padding: 0;}
a:link,
a:visited {color: #545454;}
.list-none{list-style:none; padding:0px; margin:0px;}
.clear{clear:both;}
.headLogo{width:720px; margin:0 auto; *margin:15px auto -6px; _margin:15px auto -6px;}
.headLogo img{border:none;}
.navspan{font-weight:bold; font-size:14px;}
.headNav{margin:0 auto; width:707px; padding-left:13px; _width:710px; _padding-left:10px; height:43px; border:1px solid #9EAA99; border-radius: 3px; box-shadow: 0 0 4px rgba(0,0,0,.25);}
.headNav ul .line{color:#9EAA99; width:2px;}
.headNav ul .line2{color:#000; font-weight:bold; width:3px; overflow:hidden;}
.headNav ul li{float:left; height:43px; line-height:43px; text-align:center;}
.headNav ul li a{font-size:14px; text-decoration:none;}
.headNav ul li a:hover{color:#000;}
.mod_lost_child, .mod_lost_child_little{margin:20px auto 40px !important; *padding-bottom:40px; _padding-bottom:40px;}
-->
</style>
</head>
<body>

<div class=\"headLogo\">
			<h2 class=\"title\">
				<a href=\"{$met_weburl}\" title=\"{$webname}\">
					<img src=\"{$met_weburl}upload/201801/1515549638.png\" alt=\"{$webname}\" title=\"{$webname}\" />
				</a>
			</h2>
</div>

<div class=\"headNav\"><ul class=\"list-none\"><li id=\"nav_10001\" style='width:99px;' class='navdown'><a href='{$met_weburl}' title='网站首页' class='nav'><span>网站首页</span></a></li><li class=\"line\">|</li><li id='nav_1' style='width:99px;' ><a href='about/'  title='关于我们' class='hover-none nav'><span>关于我们</span></a></li><li class=\"line\">|</li><li id='nav_2' style='width:99px;' ><a href='news/'  title='新闻资讯' class='hover-none nav'><span>新闻资讯</span></a></li><li class=\"line\">|</li><li id='nav_3' style='width:99px;' ><a href='product/'  title='产品展示' class='hover-none nav'><span>产品展示</span></a></li><li class=\"line\">|</li><li id='nav_32' style='width:99px;' ><a href='download/'  title='下载中心' class='hover-none nav'><span>下载中心</span></a></li><li class=\"line\">|</li><li id='nav_33' style='width:99px;' ><a href='case/'  title='客户案例' class='hover-none nav'><span>客户案例</span></a></li><li class=\"line\">|</li><li id='nav_36' style='width:98px;' ><a href='job/'  title='招贤纳士' class='hover-none nav'><span>招贤纳士</span></a></li></ul><div class=\"clear\"></div></div>
<div style=\"width:720px; margin:20px auto;\">
<iframe scrolling='no' frameborder='0' src='http://yibo.iyiyun.com/Home/Distribute/ad404/key/16748' width='654' height='470' style='display:block;'></iframe>
</div>
</body>
</html>

";


			$fp = @fopen("../404.html",w);
			@fputs($fp, $met404);
			@fclose($fp);
			@chmod('../config/install.lock',0554);				
			include template('finished');
		}else {
			$langnum=($cndata=="yes"||$endata=="yes")?2:1;
			$lang=$langnum==2?'中文':($endata=="yes"&&$cndata<>"yes"?'英文':'中文');
			include template('adminsetup');
		}
		break;
	}
	case 'license':
		include template('license');
	break;
	default:
	{	
		session_start();
		$_SESSION['install']='metinfo';
		include template('index');
	}
}

function creat_table($content , $link) {
	global $installinfo,$db_prefix,$db_setup,$install_url;
	$install_url2=str_replace("install/index.php","",$install_url);
	$sql=explode("\n",$content);
	$query='';
	$j=0;
    foreach($sql as $key => $value){
        $value=trim($value);
        if(!$value || $value[0]=='#') continue;
        if(preg_match("/\;$/",$value)){
			$query.=$value;
			if(preg_match("/^CREATE/",$query)){
				$name=substr($query,13,strpos($query,'(')-13);
				$c_name=str_replace('met_',$db_prefix,$name);
				$i++;
			}
			$query = str_replace('met_',$db_prefix,$query);
			$query = str_replace('metconfig_','met_',$query);
			$query = str_replace('web_metinfo_url',$install_url2,$query);
			if(!mysqli_query($link , $query) && !mysqli_error($link)){
                $db_setup=0;
                if($j!='0'){
                    echo '<li class="WARN">出错：'.mysqli_error($link).'<br/>sql:'.$query.'</li>';
                }
            }else {
                #var_dump($query);
                if(preg_match("/^CREATE/",$query)){
					$installinfo=$installinfo.'<li class="OK"><font color="#0000EE">建立数据表'.$i.'</font>'.$c_name.' ... <font color="#0000EE">完成</font></li>';
				}
				$db_setup=1;
			}
			$query='';
		} else{
			$query.=$value;
		}
		$j++;
	}
	return $installinfo;
}

function readover($filename,$method="rb"){
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}

function daddslashes($string, $force = 0) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
	}
	return $string;
}

function template($template,$EXT="htm"){
	global $met_skin_user,$skin;
	unset($GLOBALS[con_db_id],$GLOBALS[con_db_pass],$GLOBALS[con_db_name]);
	$path = "templates/$template.$EXT";
	return  $path;
}

function is_writable_met($dir){
	$str='';
	$is_dir=0;
	if(is_dir($dir)){
		$dir=$dir.'metinfo.txt';
		$is_dir=1;
		$info='metinfo';
	}
	else{
		$fp = @fopen($dir,'r+');
		$i=0;
		while($i<10){
			$info.=@fgets($fp);
			$i++;
		}
		@fclose($fp);
		if($info=='')return false;
	}
	$fp = @fopen($dir,'w+');
	@fputs($fp, $info);
	@fclose($fp);
	if(!file_exists($dir))return false;
	$fp = @fopen($dir,'r+');
	$i=0;
	while($i<10){
		$str.=@fgets($fp);
		$i++;
	}
	@fclose($fp);
	if($str!=$info)return false;
	if($is_dir==1){
		@unlink($dir);
	}
	return true;
}

function curl_post1($post,$timeout){
global $met_weburl;
	$host='list.qq.com/cgi-bin/qf_compose_send';
	if(get_extension_funcs('curl')&&function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')&&function_exists('curl_close')){
		$curlHandle=curl_init(); 
		curl_setopt($curlHandle,CURLOPT_URL,'http://'.$host); 
		curl_setopt($curlHandle,CURLOPT_REFERER,$met_weburl);
		curl_setopt($curlHandle,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($curlHandle,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($curlHandle,CURLOPT_TIMEOUT,$timeout);
		curl_setopt($curlHandle,CURLOPT_POST, 1);	
		curl_setopt($curlHandle,CURLOPT_POSTFIELDS, $post);
		$result=curl_exec($curlHandle); 
		curl_close($curlHandle); 
	}
	else{
		if(function_exists('fsockopen')||function_exists('pfsockopen')){
			$post_data=$post;
			$post='';
			@ini_set("default_socket_timeout",$timeout);
			while (list($k,$v) = each($post_data)) {
				$post .= rawurlencode($k)."=".rawurlencode($v)."&";
			}
			$post = substr( $post , 0 , -1 );
			$len = strlen($post);
			if(function_exists(fsockopen)){
				$fp = @fsockopen($host,80,$errno,$errstr,$timeout);
			}
			else{
				$fp = @pfsockopen($host,80,$errno,$errstr,$timeout);
			}
			if (!$fp) {
				$result='';
			}
			else {
				$result = '';
				$out = "POST $file HTTP/1.0\r\n";
				$out .= "Host: $host\r\n";
				$out .= "Referer: $met_weburl\r\n";
				$out .= "Content-type: application/x-www-form-urlencoded\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Content-Length: $len\r\n";
				$out .="\r\n";
				$out .= $post."\r\n";
				fwrite($fp, $out);
				$inheader = 1; 	
				while(!feof($fp)){
					$line = fgets($fp,1024); 
						if ($inheader == 0) {    
							$result.=$line;
						}  
						if ($inheader && ($line == "\n" || $line == "\r\n")) {  
							$inheader = 0;  
					}    

				}
			
				while(!feof($fp)){
					$result.=fgets($fp,1024);
				}
				fclose($fp);
				str_replace($out,'',$result);
			}
		}
		else{
			$result='';
		}
	}
	return '订阅邮件已发送到你的邮箱！';
}

function curl_post($post,$timeout){
global $met_weburl,$met_host,$met_file;
	$host='api.metinfo.cn';
	$file='/test/apilinktest.php';
	if(get_extension_funcs('curl')&&function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')&&function_exists('curl_close')){
		$curlHandle=curl_init(); 
		curl_setopt($curlHandle,CURLOPT_URL,'http://'.$host.$file); 
		curl_setopt($curlHandle,CURLOPT_REFERER,$met_weburl);
		curl_setopt($curlHandle,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($curlHandle,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($curlHandle,CURLOPT_TIMEOUT,$timeout);
		curl_setopt($curlHandle,CURLOPT_POST, 1);	
		curl_setopt($curlHandle,CURLOPT_POSTFIELDS, $post);
		$result=curl_exec($curlHandle); 
		curl_close($curlHandle); 
	}
	else{
		if(function_exists('fsockopen')||function_exists('pfsockopen')){
			$post_data=$post;
			$post='';
			@ini_set("default_socket_timeout",$timeout);
			while (list($k,$v) = each($post_data)) {
				$post .= rawurlencode($k)."=".rawurlencode($v)."&";
			}
			$post = substr( $post , 0 , -1 );
			$len = strlen($post);
			if(function_exists(fsockopen)){
				$fp = @fsockopen($host,80,$errno,$errstr,$timeout);
			}
			else{
				$fp = @pfsockopen($host,80,$errno,$errstr,$timeout);
			}
			if (!$fp) {
				$result='';
			}
			else {
				$result = '';
				$out = "POST $file HTTP/1.0\r\n";
				$out .= "Host: $host\r\n";
				$out .= "Referer: $met_weburl\r\n";
				$out .= "Content-type: application/x-www-form-urlencoded\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Content-Length: $len\r\n";
				$out .="\r\n";
				$out .= $post."\r\n";
				fwrite($fp, $out);
				$inheader = 1; 	
				while(!feof($fp)){
					$line = fgets($fp,1024); 
						if ($inheader == 0) {    
							$result.=$line;
						}  
						if ($inheader && ($line == "\n" || $line == "\r\n")) {  
							$inheader = 0;  
					}    

				}
			
				while(!feof($fp)){
					$result.=fgets($fp,1024);
				}
				fclose($fp);
				str_replace($out,'',$result);
			}
		}
		else{
			$result='';
		}
	}
	$result=trim($result);
	if(substr($result,0,7)=='metinfo'){
		return substr($result,7);
	}
	else{
		return 'nohost';
	}
}

function met_rand_i($length){
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$password = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	return $password;
}

function deldir($dir,$dk=1) {
  $dh=opendir($dir);
  while ($file=readdir($dh)) {
    if($file!="." && $file!="..") {
      $fullpath=$dir."/".$file;
      if(!is_dir($fullpath)) {
          unlink($fullpath);
      } else {
          deldir($fullpath);
      }
    }
  }
  closedir($dh);
  if($dk==0 && $dir!='../../upload')$dk=1;
  if($dk==1){
	  if(rmdir($dir)){
		return true;
	  }else{
		return false;
	  }
  }
}

function get_sql($data) {
$sql = "";
    foreach ($data as $key => $value) {
        $sql .= " {$key} = '{$value}',";
    }
    return trim($sql,',');
}

function install_tag_templates($db,$templates,$skin_name,$lang)
{

	$template_json = "../templates/{$skin_name}/install/template.json";

		if(file_exists($template_json)){
			$configs = json_decode(file_get_contents($template_json),true);
			$query = "DELETE FROM {$templates} WHERE no = '{$skin_name}' AND lang = '{$lang}'";
			
			$db->query($query);
				foreach ($configs as $k => $v) {
					$cid = $v['id'];
					$sub = $v['sub'];
					$v['lang'] = $lang;
					unset($v['id'],$v['sub']);
					$v['no'] = $skin_name;
					$area_sql  = get_sql($v);
					$query = "INSERT INTO {$templates} SET {$area_sql}";
					$db->query($query);
					$area_id = $db->insert_id();
					foreach ($sub as $m => $s) {
						unset($s['id']);
						$s['lang'] = $lang;
						$s['bigclass'] = $area_id;
						$s['no'] = $skin_name;
						$sub_sql = get_sql($s);
						$sub_query = "INSERT INTO {$templates} SET {$sub_sql}";

						$db->query($sub_query);
					}
			}
		}

}
# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) MetInfo Co., Ltd. (http://www.metinfo.cn). All rights reserved.
?>