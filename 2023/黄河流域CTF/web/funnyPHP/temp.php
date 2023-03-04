<?php
@$iipp=$_POST["iipp"];
echo'
                <script language="javascript">  
                function f_check_IP()      
                {  var ip = document.getElementById(\'reg_ip\').value;  
                   var re=/^(\d+)\.(\d+)\.(\d+)\.(\d+)$/;
                   if(re.test(ip))     
                   {     
                       if( RegExp.$1<256 && RegExp.$2<256 && RegExp.$3<256 && RegExp.$4<256)   
                       return true;     
                   }     
                   alert("IP\u683C\u5F0F\u4E0D\u6B63\u786E");     
                   return false;      
                }  
                </script>  
                <form action="" method="post" onsubmit="return f_check_IP()">
                IP: <input type="text" id="reg_ip" name="iipp" />
                <input type="submit" name="submit" value="Ping">  
                </form>
                
        ';
?>
    <div style="height:500px;border:1px solid #000;background-color:#222222;color:#00ff00">

<?php if(!isset($_POST["iipp"]))
{
    echo '';
}

else
{
    if(preg_match("/\;|cat|ls|ll|\/|l|:|flag|IFS| |\*|more|less|head|sort|tail|sed|cut|tac|awk|strings|od|curl|\`|\%|\x26|\>|\<|\\\\|{|}|\&|\?/i", $iipp)){
        die("hacker!!!");}
    $result=shell_exec('ping -c 4 '.$iipp);
    $result=str_replace("\n","<br>",$result);
    echo $result;

}