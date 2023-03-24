<?php
error_reporting(0);

if (!isset($_REQUEST['url'])){
    header("Location: /?url=_");
    exit;
}

$url=$_REQUEST['url'];
$x=parse_url($url);
if($x['scheme']==='gopher'||$x['scheme']==='file'){
    if(!preg_match('/localhost|127\.0\.|\。/i', $url)){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_exec($ch);
        curl_close($ch);
    }
    else{
        die('hacker');
    }
}
else{
    die('are you serious？');
}
?>