# 浙江传媒学院铁三比赛 WP

- 信息
- 队伍名称：浙江传媒学院
- 答题数：4
- 总分：1500
- 排名：25



## 一生壹世

题目说解压密码可能和谐音有关，猜测密码为 1314

解压出来后是四个 txt，一开始想了很久没明白是什么，后来想着把这些内容 16 进制读出来，可能是一个新的东西。

编写 EXP

```python
with open('一.txt','rb') as f:
    a = ("{:02X}".format(int(temp)) for temp in f.read())
    first_list = list(a)

with open('生.txt','rb') as f:
    b = ("{:02X}".format(int(temp)) for temp in f.read())
    second_list = list(b)

with open('壹.txt','rb') as f:
    c = ("{:02X}".format(int(temp)) for temp in f.read())
    third_list = list(c)

with open('世.txt','rb') as f:
    d = ("{:02X}".format(int(temp)) for temp in f.read())
    fourth_list = list(d)

for i in range(len(first_list)):
    print(first_list[i],end="")
    print(second_list[i],end="")
    print(third_list[i],end="")
    print(fourth_list[i],end="")
```

最终得到的结果是一串 16 进制，保存到 010 的 hex file

运行结果其实就是一整个 png，将其保存

![](/images/ysysFlag.png)

Flag{JPG}



## CBC

题如其名，CBC 字节反转攻击

已知 iv，key 直接构造 payload

```python
import base64
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad

iv=b"1234567891234567"
key=b"abcdef0123456789"

encrypted_data = pad(b'username:admin;password:admin', 32)

pad = lambda s: s + (16 - len(s)%16) * chr(0)
success_data = pad('username:admin;password:admin')
print(success_data)
base64_cipher = AES.new(key, AES.MODE_CBC, iv)
print(base64.b64encode(base64_cipher.encrypt(success_data.encode('utf8'))))
```



![](/images/cbcKey.png)

传参访问

```none
?username=admin&password=admin&base64_cipher=idVegWpPAM8A414waIjDKaG7eLLrT4vtnR1WIO9x6do=
```

得到 flag

## hashattack

通过 git 泄露获取到源码，如下

```php
<?php 
error_reporting(0);
include "flag.php";
$user=$_POST['user'];
function encrypt($text){
	global $key;
	return md5($key.$text);
}
if (encrypt($user)===$_COOKIE['verify']) {
	if(is_numeric(strpos($user,'root'))){
		die($flag);
	}
	else{
		die('not root！！！');
	}
}
else{
	setcookie("verify",encrypt("guest"),time()+60*60*24);
	setcookie("len",strlen($key),time()+60*60*24);
}
//show_source(__FILE__);
```



是个原题，原理是 字节拓展攻击，使用 hashpump 构造

![](/images/hashPump.png)

接着将 `\x` 转换为 `%`，再将多余的 `(` 进行 url 编码，最后得到 user 参数应该是这样的

```none
user=guest%80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%28%01%00%00%00%00%00%00root
```

攻击，getflag

![](/images/web2Flag.png)









