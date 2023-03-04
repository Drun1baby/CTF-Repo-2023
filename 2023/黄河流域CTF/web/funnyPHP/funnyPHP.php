HTTP/1.1 200 OK
Host: 47.104.14.160:3345
Date: Sat, 04 Mar 2023 06:56:34 GMT
Connection: close
Content-Length: 805

<?php
error_reporting(0);

class A{
    public $sdpc = ["welcome" => "yeah, something hidden."];

    function __call($name, $arguments)
    {
        $this->$name[$name]();
    }

}


class B{
    public $a;

    function __construct()
    {
        $this->a = new A();
    }

    function __toString()
    {
        echo $this->a->sdpc["welcome"]; //对大家表示欢迎
    }

}

class C{
    public $b;
    protected $c;

    function __construct(){
        $this->c = new B();
    }

    function __destruct(){
        $this->b ? $this->c->sdpc('welcom') : 'welcome!'.$this->c; //变着法欢迎大家
    }
}

class Evil{
    function getflag() {
        echo file_get_contents('/fl4g');
    }
}


if(isset($_POST['sdpc'])) {
    unserialize($_POST['sdpc']);
} else {
    serialize(new C());
}


?>