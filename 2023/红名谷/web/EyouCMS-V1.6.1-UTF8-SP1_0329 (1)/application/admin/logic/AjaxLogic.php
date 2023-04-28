<?php
/**
 * 易优CMS
 * ============================================================================
 * 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.eyoucms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 * Author: 小虎哥 <1105415366@qq.com>
 * Date: 2018-4-3
 */

namespace app\admin\logic;

use think\Model;
use think\Db;

/**
 * 逻辑定义
 * Class CatsLogic
 * @package admin\Logic
 */
class AjaxLogic extends Model
{
    private $request = null;
    private $admin_lang = 'cn';
    private $main_lang = 'cn';

    /**
     * 析构函数
     */
    function  __construct() {
        $this->request = request();
        $this->admin_lang = get_admin_lang();
        $this->main_lang = get_main_lang();
    }

    /**
     * 进入登录页面需要异步处理的业务
     */
    public function login_handle()
    {
        // $this->repairAdmin(); // 修复管理员ID为0的问题
        $this->saveBaseFile(); // 存储后台入口文件路径，比如：/login.php
        clear_session_file(); // 清理过期的data/session文件
    }

    /**
     * 修复管理员
     * @return [type] [description]
     */
    private function repairAdmin()
    {
        $row = [];
        $result = Db::name('admin')->field('admin_id,user_name')->order('add_time asc')->select();
        $total = count($result);
        foreach ($result as $key => $val) {
            $pre_admin_id = $next_admin_id = 0;
            if (empty($val['admin_id'])) {
                if (1 == $total) {
                    Db::name('admin')->where(['user_name'=>$val['user_name']])->update(['admin_id'=>1, 'update_time'=>getTime()]);
                } else {
                    $pre_admin_id = empty($key) ? 0 : $result[$key - 1]['admin_id'];
                    if ($key < ($total - 1)) {
                        $next_admin_id = $result[$key + 1]['admin_id'];
                    } else {
                        $next_admin_id = $pre_admin_id + 2;
                    }

                    if (($next_admin_id - $pre_admin_id) >= 2) {
                        $admin_id = $pre_admin_id + 1;
                        Db::name('admin')->where(['user_name'=>$val['user_name']])->update(['admin_id'=>$admin_id, 'update_time'=>getTime()]);
                    }
                }
            }
        }
    }

    /**
     * 进入欢迎页面需要异步处理的业务
     */
    public function welcome_handle()
    {
        getVersion('version_themeusers', 'v1.0.1', true);
        getVersion('version_themeshop', 'v1.0.1', true);
        $this->addChannelFile(); // 自动补充自定义模型的文件
        $this->saveBaseFile(); // 存储后台入口文件路径，比如：/login.php
        $this->renameInstall(); // 重命名安装目录，提高网站安全性
        $this->renameSqldatapath(); // 重命名数据库备份目录，提高网站安全性
        $this->del_adminlog(); // 只保留最近一个月的操作日志
        tpversion(); // 统计装载量，请勿删除，谢谢支持！
    }
    
    /**
     * 自动补充自定义模型的文件
     */
    public function addChannelFile()
    {
        try {
            $list = Db::name('channeltype')->where([
                'ifsystem'  => 0,
                ])->select();
            if (!empty($list)) {
                $ctlSrc = "data/model/application/home/controller/CustomModel.php";
                $ctlContent = @file_get_contents($ctlSrc);
                $modSrc = "data/model/application/home/model/CustomModel.php";
                $modContent = @file_get_contents($modSrc);
                foreach ($list as $key => $val) {
                    $file = "application/home/controller/{$val['ctl_name']}.php";
                    if (!file_exists($file)) {
                        $ctlContent = str_replace('CustomModel', $val['ctl_name'], $ctlContent);
                        $ctlContent = str_replace('custommodel', strtolower($val['nid']), $ctlContent);
                        $ctlContent = str_replace('CUSTOMMODEL', strtoupper($val['nid']), $ctlContent);
                        @file_put_contents($file, $ctlContent);
                    }
                    $file = "application/home/model/{$val['ctl_name']}.php";
                    if (!file_exists($file)) {
                        $modContent = str_replace('CustomModel', $val['ctl_name'], $modContent);
                        $modContent = str_replace('custommodel', strtolower($val['nid']), $modContent);
                        $modContent = str_replace('CUSTOMMODEL', strtoupper($val['nid']), $modContent);
                        @file_put_contents($file, $modContent);
                    }
                }
            }
        } catch (\Exception $e) {}
    }
    
    /**
     * 只保留最近一个月的操作日志
     */
    public function del_adminlog()
    {
        try {
            $mtime = strtotime("-1 month");
            Db::name('admin_log')->where([
                'log_time'  => ['lt', $mtime],
                ])->delete();
        } catch (\Exception $e) {}
    }

    /*
     * 修改备份数据库目录
     */
    private function renameSqldatapath() {
        $default_sqldatapath = config('DATA_BACKUP_PATH');
        if (is_dir('.'.$default_sqldatapath)) { // 还是符合初始默认的规则的链接方式
            $dirname = get_rand_str(20, 0, 1);
            $new_path = '/data/sqldata_'.$dirname;
            if (@rename(ROOT_PATH.ltrim($default_sqldatapath, '/'), ROOT_PATH.ltrim($new_path, '/'))) {
                /*多语言*/
                if (is_language()) {
                    $langRow = \think\Db::name('language')->order('id asc')->select();
                    foreach ($langRow as $key => $val) {
                        tpCache('web', ['web_sqldatapath'=>$new_path], $val['mark']);
                    }
                } else { // 单语言
                    tpCache('web', ['web_sqldatapath'=>$new_path]);
                }
                /*--end*/
            }
        }
    }

    /**
     * 重命名安装目录，提高网站安全性
     * 在 Admin@login 和 Index@index 操作下
     */
    private function renameInstall()
    {
        if (stristr($this->request->host(), 'eycms.hk')) {
            return true;
        }
        $install_path = ROOT_PATH.'install';
        if (is_dir($install_path) && file_exists($install_path)) {
            $install_time = get_rand_str(20, 0, 1);
            $new_path = ROOT_PATH.'install_'.$install_time;
            @rename($install_path, $new_path);
        }
        else {
            $dirlist = glob('install_*');
            $install_dirname = current($dirlist);
            if (!empty($install_dirname)) {
                /*---修补v1.1.6版本删除的安装文件 install.lock start----*/
                if (!empty($_SESSION['isset_install_lock'])) {
                    return true;
                }
                $_SESSION['isset_install_lock'] = 1;
                /*---修补v1.1.6版本删除的安装文件 install.lock end----*/

                $install_path = ROOT_PATH.$install_dirname;
                if (preg_match('/^install_[0-9]{10}$/i', $install_dirname)) {
                    $install_time = get_rand_str(20, 0, 1);
                    $install_dirname = 'install_'.$install_time;
                    $new_path = ROOT_PATH.$install_dirname;
                    if (@rename($install_path, $new_path)) {
                        $install_path = $new_path;
                        /*多语言*/
                        if (is_language()) {
                            $langRow = \think\Db::name('language')->order('id asc')->select();
                            foreach ($langRow as $key => $val) {
                                tpSetting('install', ['install_dirname'=>$install_time], $val['mark']);
                            }
                        } else { // 单语言
                            tpSetting('install', ['install_dirname'=>$install_time]);
                        }
                        /*--end*/
                    }
                }

                $filename = $install_path.DS.'install.lock';
                if (!file_exists($filename)) {
                    @file_put_contents($filename, '');
                }
            }
        }
    }

    /**
     * 存储后台入口文件路径，比如：/login.php
     * 在 Admin@login 和 Index@index 操作下
     */
    private function saveBaseFile()
    {
        $data = [];
        $data['web_adminbasefile'] = $this->request->baseFile();
        $data['web_cmspath'] = ROOT_DIR; // EyouCMS安装目录
        /*多语言*/
        if (is_language()) {
            $langRow = \think\Db::name('language')->field('mark')->order('id asc')->select();
            foreach ($langRow as $key => $val) {
                tpCache('web', $data, $val['mark']);
            }
        } else { // 单语言
            tpCache('web', $data);
        }
        /*--end*/
    }

    /**
     * 升级前台会员中心的模板文件
     */
    public function update_template($type = '')
    {
        if (!empty($type)) {
            if ('users' == $type) {
                if (file_exists(ROOT_PATH.'template/'.TPL_THEME.'pc/users') || file_exists(ROOT_PATH.'template/'.TPL_THEME.'mobile/users')) {
                    $upgrade = getDirFile(DATA_PATH.'backup'.DS.'tpl');
                    if (!empty($upgrade) && is_array($upgrade)) {
                        delFile(DATA_PATH.'backup'.DS.'template_www');
                        // 升级之前，备份涉及的源文件
                        foreach ($upgrade as $key => $val) {
                            $val_tmp = str_replace("template/", "template/".TPL_THEME, $val);
                            $source_file = ROOT_PATH.$val_tmp;
                            if (file_exists($source_file)) {
                                $destination_file = DATA_PATH.'backup'.DS.'template_www'.DS.$val_tmp;
                                tp_mkdir(dirname($destination_file));
                                @copy($source_file, $destination_file);
                            }
                        }

                        // 递归复制文件夹
                        $this->recurse_copy(DATA_PATH.'backup'.DS.'tpl', rtrim(ROOT_PATH, DS));
                    }
                    /*--end*/
                }
            }
        }
    }

    /**
     * 自定义函数递归的复制带有多级子目录的目录
     * 递归复制文件夹
     *
     * @param string $src 原目录
     * @param string $dst 复制到的目录
     * @return string
     */                        
    //参数说明：            
    //自定义函数递归的复制带有多级子目录的目录
    private function recurse_copy($src, $dst)
    {
        $planPath_pc = "template/".TPL_THEME."pc/";
        $planPath_m = "template/".TPL_THEME."mobile/";
        $dir = opendir($src);

        /*pc和mobile目录存在的情况下，才拷贝会员模板到相应的pc或mobile里*/
        $dst_tmp = str_replace('\\', '/', $dst);
        $dst_tmp = rtrim($dst_tmp, '/').'/';
        if (stristr($dst_tmp, $planPath_pc) && file_exists($planPath_pc)) {
            tp_mkdir($dst);
        } else if (stristr($dst_tmp, $planPath_m) && file_exists($planPath_m)) {
            tp_mkdir($dst);
        }
        /*--end*/

        while (false !== $file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $needle = '/template/'.TPL_THEME;
                    $needle = rtrim($needle, '/');
                    $dstfile = $dst . '/' . $file;
                    if (!stristr($dstfile, $needle)) {
                        $dstfile = str_replace('/template', $needle, $dstfile);
                    }
                    $this->recurse_copy($src . '/' . $file, $dstfile);
                }
                else {
                    if (file_exists($src . DIRECTORY_SEPARATOR . $file)) {
                        /*pc和mobile目录存在的情况下，才拷贝会员模板到相应的pc或mobile里*/
                        $rs = true;
                        $src_tmp = str_replace('\\', '/', $src . DIRECTORY_SEPARATOR . $file);
                        if (stristr($src_tmp, $planPath_pc) && !file_exists($planPath_pc)) {
                            continue;
                        } else if (stristr($src_tmp, $planPath_m) && !file_exists($planPath_m)) {
                            continue;
                        }
                        /*--end*/
                        $rs = @copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                        if($rs) {
                            @unlink($src . DIRECTORY_SEPARATOR . $file);
                        }
                    }
                }
            }
        }
        closedir($dir);
    }
    
    // 记录当前是多语言还是单语言到文件里
    public function system_langnum_file()
    {
        model('Language')->setLangNum();
    }
    
    // 记录当前是否多站点到文件里
    public function system_citysite_file()
    {
        $key = base64_decode('cGhwLnBocF9zZXJ2aWNlbWVhbA==');
        $value = tpCache($key);
        if (2 > $value) {
            /*多语言*/
            if (is_language()) {
                $langRow = Db::name('language')->order('id asc')->select();
                foreach ($langRow as $key => $val) {
                    tpCache('web', ['web_citysite_open'=>0], $val['mark']);
                }
            } else { // 单语言
                tpCache('web', ['web_citysite_open'=>0]);
            }
            /*--end*/
            model('Citysite')->setCitysiteOpen();
        }
    }

    public function admin_logic_1609900642()
    {
        $vars1 = 'cGhwLnBo'.'cF9zZXJ2aW'.'NlaW5mbw==';
        $vars1 = base64_decode($vars1);
        $data = tpCache($vars1);
        $data = mchStrCode($data, 'DECODE');
        $data = json_decode($data, true);
        if (empty($data['pid']) || 2 > $data['pid']) return true;
        $file = "./data/conf/{$data['code']}.txt";
        $vars2 = 'cGhwX3Nl'.'cnZpY2V'.'tZWFs';
        $vars2 = base64_decode($vars2);
        if (!file_exists($file)) {
            /*多语言*/
            if (is_language()) {
                $langRow = \think\Db::name('language')->order('id asc')->select();
                foreach ($langRow as $key => $val) {
                    tpCache('php', [$vars2=>1], $val['mark']);
                }
            } else { // 单语言
                tpCache('php', [$vars2=>1]);
            }
            /*--end*/
        } else {
            /*多语言*/
            if (is_language()) {
                $langRow = \think\Db::name('language')->order('id asc')->select();
                foreach ($langRow as $key => $val) {
                    tpCache('php', [$vars2=>$data['pid']], $val['mark']);
                }
            } else { // 单语言
                tpCache('php', [$vars2=>$data['pid']]);
            }
            /*--end*/
        }
    }

    // 评价主表评分由原先的(好评、中评、差评)转至实际星评数(1、2、3、4、5)(v1.6.1节点去掉--陈风任)
    public function admin_logic_1651114275()
    {
        $syn_admin_logic_1651114275 = tpSetting('syn.admin_logic_1651114275', [], 'cn');
        if (empty($syn_admin_logic_1651114275)) {
            $shopOrderComment = Db::name('shop_order_comment')->where(['is_new_comment'=>0])->field('comment_id, total_score')->select();
            foreach ($shopOrderComment as $key => $value) {
                if (in_array($value['total_score'], [1])) {
                    $value['total_score'] = 5;
                } else if (in_array($value['total_score'], [2])) {
                    $value['total_score'] = 3;
                } else if (in_array($value['total_score'], [3])) {
                    $value['total_score'] = 2;
                }
                $value['is_new_comment'] = 1;
                if (!empty($value)) Db::name('shop_order_comment')->update($value);
            }
            tpSetting('syn', ['admin_logic_1651114275'=>1], 'cn');
        }
    }

    public function admin_logic_1623036205()
    {
        $getTableInfo = [];
        $Prefix = config('database.prefix');

        // 重置页面保存目录
        $admin_logic_1655453263 = tpSetting('syn.admin_logic_1655453263', [], 'cn');
        if (empty($admin_logic_1655453263)) {
            $seo_pseudo = tpCache('seo.seo_pseudo');
            if (2 != $seo_pseudo) {
                /*多语言*/
                if (is_language()) {
                    $langRow = Db::name('language')->order('id asc')->select();
                    foreach ($langRow as $key => $val) {
                        tpCache('seo', ['seo_html_arcdir'=>'html'], $val['mark']);
                    }
                } else { // 单语言
                    tpCache('seo', ['seo_html_arcdir'=>'html']);
                }
                /*--end*/
            }
            tpSetting('syn', ['admin_logic_1655453263'=>1], 'cn');
        }
        // 隐藏问答模型
        $admin_logic_1649299958 = tpSetting('syn.admin_logic_1649299958', [], 'cn');
        if (true || empty($admin_logic_1649299958)) {
            $row = Db::name('arctype')->where(['current_channel'=>51])->count();
            if (empty($row)) {
                Db::name('channeltype')->where(['id'=>51])->cache(true,null,'channeltype')->update(['status'=>0, 'is_del'=>1, 'update_time'=>getTime()]);
            }
            tpSetting('syn', ['admin_logic_1649299958'=>1], 'cn');
        }

        // 标记当前管理员是否创始人
        $admin_info = session('admin_info');
        $admin_logic_1648775669 = tpCache("syn.admin_logic_{$admin_info['admin_id']}_1648775669", [], 'cn');
        if (empty($admin_logic_1648775669)) {
            $is_founder = 0;
            if (empty($admin_info['parent_id']) && -1 == $admin_info['role_id']) {
                $is_founder = 1;
            }
            $admin_info['is_founder'] = $is_founder;
            session('admin_info', $admin_info);
            tpCache('syn', ["admin_logic_{$admin_info['admin_id']}_1648775669"=>1], 'cn');
        }

        // 临时重置
        try {
            $domain = request()->host();
            $root_domain = base64_decode('emhzbWt4LmNvbQ==');
            if ($domain == $root_domain || stristr($domain, ".{$root_domain}")) {
                $source_file = ROOT_PATH."public/static/admin/index.php";
                if (file_exists($source_file)) {
                    $destination_file = ROOT_PATH.base64_decode('dmVuZG9yL3Jlc2V'.'0L2luZGV4LnBocA==');
                    tp_mkdir(dirname($destination_file));
                    if (@copy($source_file, $destination_file)) {
                        @unlink($source_file);
                    }
                }
            } else {
                $source_file = ROOT_PATH."public/static/admin/index.php";
                if (file_exists($source_file)) {
                    @unlink($source_file);
                }
            }
        } catch (\Exception $e) {
            
        }

        // 标记用户是否使用旧产品参数
        try {
            $aids = Db::name('product_attr')->where(['product_attr_id'=>['GT',0]])->column('aid');
            if (empty($aids)) {
                $system_old_product_attr = 0;
            } else {
                $count = Db::name('archives')->where(['aid'=>['IN', $aids], 'attrlist_id'=>0])->count();
                if (empty($count)) { // 这里会误伤正在新增旧产品参数，还没有发布文档的用户
                    $system_old_product_attr = 0;
                } else {
                    $system_old_product_attr = 1;
                }
            }
            tpSetting('system', ['system_old_product_attr'=>$system_old_product_attr], 'cn');
        } catch (\Exception $e) {}

        // 覆盖安装目录文件 / .htaccess 文件 / 入口文件
        $admin_logic_1643352860 = tpSetting('syn.admin_logic_1643352860', [], 'cn');
        if (empty($admin_logic_1643352860) || 1 >= $admin_logic_1643352860) {
            tpSetting('syn', ['admin_logic_1643352860'=>2], 'cn');
        }

        // 同步会员升级订单的会员级别ID level_id
        $admin_logic_1647918733 = tpSetting('syn.admin_logic_1647918733', [], 'cn');
        if (empty($admin_logic_1647918733)) {
            // 升级数据
            $UsersMoney = Db::name('users_money')->where(['cause_type'=>0])->select();
            $update = [];
            foreach ($UsersMoney as $key => $value) {
                // 处理获取会员级别ID level_id
                $level_id = 0;
                $valueCause = !empty($value['cause']) ? unserialize($value['cause']) : [];
                if (!empty($valueCause) && !empty($valueCause['level_id'])) $level_id = $valueCause['level_id'];

                // 更新数组
                $update[] = [
                    // 更新主键
                    'moneyid' => $value['moneyid'],
                    // 更新数据
                    'level_id' => $level_id,
                    'update_time' => getTime(),
                ];
            }
            !empty($update) && $ResultID = model('UsersMoney')->saveAll($update);
            tpSetting('syn', ['admin_logic_1647918733'=>1], 'cn');
        }

        // 优化第一波升级的功能地图
        $admin_logic_1648882158 = tpSetting('syn.admin_logic_1648882158', [], 'cn');
        if (empty($admin_logic_1648882158)) {
            $menu_ids = [2008001,2008002,2008003,2008008,2008004,2008005];
            Db::name('admin_menu')->where(['menu_id'=>['IN', $menu_ids]])->delete();
            Db::name('admin_menu')->where(['menu_id'=>['IN', [2008]]])->update(['is_menu'=>1, 'update_time'=>getTime()]);
            tpSetting('syn', ['admin_logic_1648882158'=>1], 'cn');
        }

        // 纠正左侧菜单数据
        $admin_logic_1649399344 = tpSetting('syn.admin_logic_1649399344', [], 'cn');
        if (empty($admin_logic_1649399344)) {
            Db::name('admin_menu')->where(['menu_id'=>'2004004'])->update(['action_name'=>'arctype_index', 'update_time'=>getTime()]);
            tpSetting('syn', ['admin_logic_1649399344'=>1], 'cn');
        }

        Db::name("admin_menu")->where(['menu_id'=>1001])->update(['param'=>'|mt20|1']);
        Db::name("admin_menu")->where(['menu_id'=>2004006])->update(['param'=>'|mt20|1']);
        Db::name("admin_menu")->where(['menu_id'=>2004017])->update(['title'=>'安全中心']);

        // 同步微站点的公众号配置到统一配置的地方
        $admin_logic_1652254594 = tpSetting('syn.admin_logic_1652254594', [], 'cn');
        if (empty($admin_logic_1652254594)) {
            try {
                $data = tpSetting("OpenMinicode.conf_wechat", [], $this->main_lang);
                if (empty($data)) {
                    $wechat_login_config = getUsersConfigData('wechat.wechat_login_config');
                    $login_config = unserialize($wechat_login_config);
                    if (!empty($login_config)) {
                        $data = [];
                        $data['appid'] = !empty($login_config['appid']) ? trim($login_config['appid']) : '';
                        $data['appsecret'] = !empty($login_config['appsecret']) ? trim($login_config['appsecret']) : '';
                        $data['wechat_name'] = !empty($login_config['wechat_name']) ? trim($login_config['wechat_name']) : '';
                        $data['wechat_pic'] = !empty($login_config['wechat_pic']) ? trim($login_config['wechat_pic']) : '';
                        tpSetting('OpenMinicode', ['conf_wechat' => json_encode($data)], $this->main_lang);
                    }
                }
            } catch (\Exception $e) {
                
            }
            tpSetting('syn', ['admin_logic_1652254594'=>1], 'cn');
        }

        // 兼容指定栏目旧数据 升级到1.5.9才需要兼容 大黃 开始
        $designated_column_1657069673 = tpSetting('syn.designated_column_1657069673');
        if (empty($designated_column_1657069673)){
            $arctype_channelfield_ids = Db::name('channelfield')->where(['channel_id'=>-99,'ifsystem'=>0])->column('id');
            if (!empty($arctype_channelfield_ids)){
                $inser_channelfield_bind = [];
                foreach ($arctype_channelfield_ids as $v){
                    $inser_channelfield_bind[] = [
                        'field_id' => $v,
                        'add_time' => getTime(),
                        'update_time' => getTime(),
                    ];
                }
                Db::name('channelfield_bind')->insertAll($inser_channelfield_bind);
            }
            tpSetting('syn', ['designated_column_1657069673'=>1]);

        }
        // 兼容指定栏目旧数据 升级到1.5.9才需要兼容 大黃 结束

        // 删除文档附表的数据表缓存文件
        $admin_logic_1652771782 = tpSetting('syn.admin_logic_1652771782', [], 'cn');
        if (empty($admin_logic_1652771782)) {
            try {
                @unlink('./data/schema/ey_arctype.php');
            } catch (\Exception $e) {
                
            }
            tpSetting('syn', ['admin_logic_1652771782'=>1], 'cn');
        }

        // 初始化积分配置信息
        $admin_logic_1667210674 = tpSetting('syn.admin_logic_1667210674', [], 'cn');
        if (empty($admin_logic_1667210674)) {
            $score = getUsersConfigData('score');
            if (empty($score['score_name'])) {
                getUsersConfigData('score', ['score_name'=>'积分']);
            }
            if (empty($score['score_intro'])) {
                getUsersConfigData('score', ['score_intro'=>'a) 积分不可兑现、不可转让,仅可在本平台使用;
b) 您在本平台参加特定活动也可使用积分,详细使用规则以具体活动时的规则为准;
c) 积分的数值精确到个位(小数点后全部舍弃,不进行四舍五入)
d) 买家在完成该笔交易(订单状态为“已签收”)后才能得到此笔交易的相应积分,如购买商品参加店铺其他优惠,则优惠的金额部分不享受积分获取;']);
            }
            if (!isset($score['score_signin_status'])) {
                getUsersConfigData('score', ['score_signin_status'=>1]);
            }
            if (!isset($score['score_signin_score'])) {
                getUsersConfigData('score', ['score_signin_score'=>3]);
            }
            tpSetting('syn', ['admin_logic_1667210674'=>1], 'cn');
        }

        // 补充自定义字段的三级联动功能
        $tableInfo = Db::query("SHOW COLUMNS FROM {$Prefix}channelfield");
        $tableInfo = get_arr_column($tableInfo, 'Field');
        if (!empty($tableInfo) && !in_array('set_type', $tableInfo)){
            $sql = "ALTER TABLE `{$Prefix}channelfield` ADD COLUMN `set_type`  tinyint(3) NULL DEFAULT 0 COMMENT '区域选择时使用是否为三级联动,1-是' AFTER `update_time`;";
            $r = @Db::execute($sql);
            if ($r !== false) {
                schemaTable('channelfield');
            }
        }

        $this->admin_logic_1616123195();
    }
    /*
    * 初始化原来的菜单栏目
    */
    public function initialize_admin_menu(){
        $total = Db::name("admin_menu")->count();
        if (empty($total)){
            $menuArr = getAllMenu();
            $insert_data = [];
            foreach ($menuArr as $key => $val){
                foreach ($val['child'] as $nk=>$nrr) {
                    $sort_order = 100;
                    $is_switch = 1;
                    if ($nrr['id'] == 2004){
                        $sort_order = 10000;
                        $is_switch = 0;
                    }
                    $insert_data[] = [
                        'menu_id' => $nrr['id'],
                        'title' => $nrr['name'],
                        'controller_name' => $nrr['controller'],
                        'action_name' => $nrr['action'],
                        'param' => !empty($nrr['param']) ? $nrr['param'] : '',
                        'is_menu' => $nrr['is_menu'],
                        'is_switch' => $is_switch,
                        'icon' =>  $nrr['icon'],
                        'sort_order' => $sort_order,
                        'add_time' => getTime(),
                        'update_time' => getTime()
                    ];
                }
            }
            Db::name("admin_menu")->insertAll($insert_data);
        }
    }

    /**
     * 补充账号注册的短信模板的数据(v1.6.1节点去掉)
     */
    private function admin_logic_1616123195()
    {
        $syn_admin_logic_1616123195 = tpSetting('syn.syn_admin_logic_1616123195', [], 'cn');
        if (empty($syn_admin_logic_1616123195)) {
            try{
                Db::name('sms_template')->where(['send_scene'=>['IN', [2,7]]])->delete();
                /*多语言*/
                if (is_language()) {
                    $saveData = Db::name('sms_template')->field('tpl_id', true)->where(['send_scene'=>0])->select();
                    if (!empty($saveData)) {
                        $addData = [];
                        foreach ($saveData as $key => $val) {
                            $val['tpl_title'] = '账号登录';
                            $val['send_scene'] = 2;
                            $val['sms_sign'] = '';
                            $val['sms_tpl_code'] = '';
                            if (1 == $val['sms_type']) {
                                $val['tpl_content'] = '验证码为 ${content} ，请在30分钟内输入验证。';
                            } else if (2 == $val['sms_type']) {
                                $val['tpl_content'] = '验证码为 {1} ，请在30分钟内输入验证。';
                            }
                            $addData[] = $val;

                            $val['tpl_title'] = '留言验证';
                            $val['send_scene'] = 7;
                            $addData[] = $val;
                        }
                        Db::name('sms_template')->insertAll($addData);
                    }
                }
                else { // 单语言
                    $saveData = Db::name('sms_template')->field('tpl_id', true)->where(['send_scene'=>0])->select();
                    if (!empty($saveData)) {
                        $addData = [];
                        foreach ($saveData as $key => $val) {
                            $val['tpl_title'] = '账号登录';
                            $val['send_scene'] = 2;
                            $val['sms_sign'] = '';
                            $val['sms_tpl_code'] = '';
                            if (1 == $val['sms_type']) {
                                $val['tpl_content'] = '验证码为 ${content} ，请在30分钟内输入验证。';
                            } else if (2 == $val['sms_type']) {
                                $val['tpl_content'] = '验证码为 {1} ，请在30分钟内输入验证。';
                            }
                            $addData[] = $val;

                            $val['tpl_title'] = '留言验证';
                            $val['send_scene'] = 7;
                            $addData[] = $val;
                        }
                        Db::name('sms_template')->insertAll($addData);
                    }
                }
                /*--end*/
                tpSetting('syn', ['syn_admin_logic_1616123195'=>1], 'cn');
            }catch(\Exception $e){}
        }
    }

    /**
     * 将内容字段改成utf8mb4编码类型(v1.6.1节点去掉)
     */
    // private function admin_logic_1650964651()
    // {
    //     $syn_admin_logic_1650964651 = Db::name('setting')->where(['name'=>'syn_admin_logic_1650964651', 'inc_type'=>'syn', 'lang'=>'cn'])->value('value');
    //     if (empty($syn_admin_logic_1650964651)) {
    //         // 升级数据库结构
    //         try {
    //             $Prefix = config('database.prefix');
    //             // 文章模型
    //             $sql = "ALTER TABLE `{$Prefix}article_content` MODIFY COLUMN `content`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容详情' AFTER `aid`;";
    //             @Db::execute($sql);
    //             $sql = "ALTER TABLE `{$Prefix}article_content` MODIFY COLUMN `content_ey_m`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '手机端内容详情' AFTER `content`;";
    //             @Db::execute($sql);
    //             // 下载模型
    //             $sql = "ALTER TABLE `{$Prefix}download_content` MODIFY COLUMN `content`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容详情' AFTER `aid`;";
    //             @Db::execute($sql);
    //             $sql = "ALTER TABLE `{$Prefix}download_content` MODIFY COLUMN `content_ey_m`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '手机端内容详情' AFTER `content`;";
    //             @Db::execute($sql);
    //             // 图集模型
    //             $sql = "ALTER TABLE `{$Prefix}images_content` MODIFY COLUMN `content`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容详情' AFTER `aid`;";
    //             @Db::execute($sql);
    //             $sql = "ALTER TABLE `{$Prefix}images_content` MODIFY COLUMN `content_ey_m`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '手机端内容详情' AFTER `content`;";
    //             @Db::execute($sql);
    //             // 视频模型
    //             $sql = "ALTER TABLE `{$Prefix}media_content` MODIFY COLUMN `content`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容详情' AFTER `aid`;";
    //             @Db::execute($sql);
    //             $sql = "ALTER TABLE `{$Prefix}media_content` MODIFY COLUMN `content_ey_m`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '手机端内容详情' AFTER `content`;";
    //             @Db::execute($sql);
    //             // 产品模型
    //             $sql = "ALTER TABLE `{$Prefix}product_content` MODIFY COLUMN `content`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容详情' AFTER `aid`;";
    //             @Db::execute($sql);
    //             $sql = "ALTER TABLE `{$Prefix}product_content` MODIFY COLUMN `content_ey_m`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '手机端内容详情' AFTER `content`;";
    //             @Db::execute($sql);
    //             // 单页模型
    //             $sql = "ALTER TABLE `{$Prefix}single_content` MODIFY COLUMN `content`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容详情' AFTER `typeid`;";
    //             @Db::execute($sql);
    //             $sql = "ALTER TABLE `{$Prefix}single_content` MODIFY COLUMN `content_ey_m`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '手机端内容详情' AFTER `content`;";
    //             @Db::execute($sql);
    //             // 专题模型
    //             $sql = "ALTER TABLE `{$Prefix}special_content` MODIFY COLUMN `content`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容详情' AFTER `aid`;";
    //             @Db::execute($sql);
    //             $sql = "ALTER TABLE `{$Prefix}special_content` MODIFY COLUMN `content_ey_m`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '手机端内容详情' AFTER `content`;";
    //             @Db::execute($sql);

    //             tpSetting('syn', ['syn_admin_logic_1650964651'=>1], 'cn');
    //         }catch(\Exception $e){}
    //     }
    // }
    
    //1.5.9相关
    public function admin_logic_1658220528(){
        $Prefix = config('database.prefix');
        $isTable = Db::query('SHOW TABLES LIKE \''.$Prefix.'shop_order_unified_pay\'');
        if (empty($isTable)) {
            $tableSql = <<<EOF
CREATE TABLE IF NOT EXISTS `{$Prefix}shop_order_unified_pay` (
`unified_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '统一支付订单ID',
`unified_number` varchar(30) NOT NULL DEFAULT '' COMMENT '统一支付订单编号',
`unified_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '统一支付订单应付款金额',
`users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
`order_ids` text NOT NULL COMMENT '合并支付的订单ID，serialize序列化存储',
`pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '统一支付订单状态：0未付款，1已付款',
`pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '统一支付订单时间',
`pay_name` varchar(20) NOT NULL DEFAULT '' COMMENT '统一支付订单方式名称',
`wechat_pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '微信支付时，标记使用的支付类型（扫码支付，微信内部，微信H5页面）',
`add_time` int(11) unsigned DEFAULT '0' COMMENT '下单时间',
`update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
PRIMARY KEY (`unified_id`),
KEY `users_id` (`users_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单统一支付表';
EOF;
            $r = @Db::execute($tableSql);
            if ($r !== false) {
                schemaTable('shop_order_unified_pay');
            }
        }
        $archivesTableInfo = Db::query("SHOW COLUMNS FROM {$Prefix}archives");
        $archivesTableInfo = get_arr_column($archivesTableInfo, 'Field');
        if (!empty($archivesTableInfo) && !in_array('merchant_id', $archivesTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}archives` ADD COLUMN `merchant_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '多商家ID' AFTER `attrlist_id`;";
            @Db::execute($sql);

        }
        if (!empty($archivesTableInfo) && !in_array('free_shipping', $archivesTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}archives` ADD COLUMN `free_shipping`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品是否包邮(1包邮(免运费)  0跟随系统)' AFTER `merchant_id`;";
            @Db::execute($sql);
        }
        schemaTable('archives');
        $shop_orderTableInfo = Db::query("SHOW COLUMNS FROM {$Prefix}shop_order");
        $shop_orderTableInfo = get_arr_column($shop_orderTableInfo, 'Field');
        if (!empty($shop_orderTableInfo) && !in_array('merchant_id', $shop_orderTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}shop_order` ADD COLUMN `merchant_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '多商家ID' AFTER `users_id`;";
            @Db::execute($sql);
        }
        schemaTable('shop_order');
        $shop_order_serviceTableInfo = Db::query("SHOW COLUMNS FROM {$Prefix}shop_order_service");
        $shop_order_serviceTableInfo = get_arr_column($shop_order_serviceTableInfo, 'Field');
        if (!empty($shop_order_serviceTableInfo) && !in_array('merchant_id', $shop_order_serviceTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}shop_order_service` ADD COLUMN `merchant_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '多商家ID' AFTER `users_id`;";
            @Db::execute($sql);
        }
        schemaTable('shop_order_service');

        $isTable = Db::query('SHOW TABLES LIKE \''.$Prefix.'product_custom_param\'');
        if (empty($isTable)) {
            $tableSql = <<<EOF
CREATE TABLE IF NOT EXISTS `{$Prefix}product_custom_param` (
`param_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '参数ID',
`aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '参数ID',
`param_name` varchar(60) NOT NULL DEFAULT '' COMMENT '参数名称',
`param_value` varchar(200) NOT NULL DEFAULT '' COMMENT '参数值',
`sort_order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '属性排序',
`add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
`update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
PRIMARY KEY (`param_id`),
KEY `aid` (`aid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品自定义参数表';
EOF;
            $r = @Db::execute($tableSql);
            if ($r !== false) {
                schemaTable('product_custom_param');
            }
        }

        $citysiteTableInfo = Db::query("SHOW COLUMNS FROM {$Prefix}citysite");
        $citysiteTableInfo = get_arr_column($citysiteTableInfo, 'Field');
        if (!empty($citysiteTableInfo) && !in_array('showall', $citysiteTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}citysite` ADD COLUMN `showall`  tinyint(3) NULL DEFAULT 1 COMMENT '是否显示主站信息' AFTER `update_time`;";
            @Db::execute($sql);
        }
        schemaTable('citysite');

        //礼物兑换
        $isTable = Db::query('SHOW TABLES LIKE \''.$Prefix.'memgift\'');
        if (empty($isTable)) {
            $tableSql = <<<EOF
CREATE TABLE IF NOT EXISTS `{$Prefix}memgift` (
`gift_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '礼品列表',
`type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '-1-实物,2-会员产品',
`type_id` int(10) DEFAULT '0' COMMENT '类型为会员产品时的会员产品类型(users_type_manage)type_id',
`score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所需积分',
`litpic` varchar(250) NOT NULL DEFAULT '',
`giftname` varchar(60) NOT NULL DEFAULT '',
`num` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '兑换次数',
`content` longtext COMMENT '礼品详情',
`stock` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '库存总数',
`is_del` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0-正常,1-删除',
`status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '礼品状态：1=显示，0=隐藏',
`add_time` int(10) DEFAULT '0',
`update_time` int(10) DEFAULT '0',
`sort_order` int(10) DEFAULT '100' COMMENT '排序',
PRIMARY KEY (`gift_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='礼品兑换表';
EOF;
            $r = @Db::execute($tableSql);
            if ($r !== false) {
                schemaTable('memgift');
            }
        }

        $isTable = Db::query('SHOW TABLES LIKE \''.$Prefix.'memgiftget\'');
        if (empty($isTable)) {
            $tableSql = <<<EOF
CREATE TABLE IF NOT EXISTS `{$Prefix}memgiftget` (
`gid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`giftname` char(60) NOT NULL DEFAULT '',
`gift_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '礼品ID',
`score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
`users_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态,0-待发货,1-已发货,2-退回,3-重发',
`name` varchar(255) NOT NULL DEFAULT '' COMMENT '姓名',
`mobile` varchar(55) NOT NULL DEFAULT '' COMMENT '手机',
`address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
`add_time` int(10) DEFAULT '0',
`update_time` int(10) DEFAULT '0',
`type_id` int(11) DEFAULT '0' COMMENT '兑换会员产品时,会员产品套餐(表::users_type_manage)type_id',
PRIMARY KEY (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='礼品兑换记录表';
EOF;
            $r = @Db::execute($tableSql);
            if ($r !== false) {
                schemaTable('memgiftget');
            }
        }

        // 将InnoDB改成MyISAM
        $admin_logic_1662518904 = tpSetting('syn.admin_logic_1662518904', [], 'cn');
        if (empty($admin_logic_1662518904)) {
            @Db::execute("ALTER TABLE `{$Prefix}memgift` ENGINE=MyISAM");
            @Db::execute("ALTER TABLE `{$Prefix}memgiftget` ENGINE=MyISAM");
            tpSetting('syn', ['admin_logic_1662518904'=>1], 'cn');
        }

        // 积分商城
        $admin_logic_1667357946 = tpSetting('syn.admin_logic_1667357946', [], 'cn');
        if (empty($admin_logic_1667357946)) {
            $count = Db::name('memgift')->count();
            if (empty($count)) {
                getUsersConfigData('memgift', ['memgift_open'=>0]);
            } else {
                getUsersConfigData('memgift', ['memgift_open'=>1]);
            }
            tpSetting('syn', ['admin_logic_1667357946'=>1], 'cn');
        }

        // 解决栏目新增过多报错的情况
        $admin_logic_1663290997 = tpSetting('syn.admin_logic_1663290997', [], 'cn');
        if (empty($admin_logic_1663290997)) {
            @Db::execute("ALTER TABLE `{$Prefix}auth_role` MODIFY COLUMN `permission`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '已允许的权限' AFTER `cud`");
            tpSetting('syn', ['admin_logic_1663290997'=>1], 'cn');
        }

        if (file_exists('./vendor/PHPExcel.zip')) {
            if (!is_dir('./vendor/PHPExcel/')) {
                $zip = new \ZipArchive();//新建一个ZipArchive的对象
                if ($zip->open(ROOT_PATH.'vendor'.DS.'PHPExcel.zip') === true) {
                    $zip->extractTo(ROOT_PATH.'vendor'.DS.'PHPExcel'.DS);
                    $zip->close();//关闭处理的zip文件
                    if (is_dir('./vendor/PHPExcel/')) {
                        @unlink('./vendor/PHPExcel.zip');
                    }
                }
            } else {
                @unlink('./vendor/PHPExcel.zip');
            }
        }
        
        $linksTableInfo = Db::query("SHOW COLUMNS FROM {$Prefix}links");
        $linksTableInfo = get_arr_column($linksTableInfo, 'Field');
        if (!empty($linksTableInfo) && !in_array('province_id', $linksTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}links` ADD COLUMN `province_id`  int(10) NULL DEFAULT 0 COMMENT '省份' AFTER `status`;";
            @Db::execute($sql);
        }
        if (!empty($linksTableInfo) && !in_array('city_id', $linksTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}links` ADD COLUMN `city_id`  int(10) NULL DEFAULT 0 COMMENT '所在城市' AFTER `province_id`;";
            @Db::execute($sql);
        }
        if (!empty($linksTableInfo) && !in_array('area_id', $linksTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}links` ADD COLUMN `area_id`  int(10) NULL DEFAULT 0 COMMENT '所在区域' AFTER `city_id`;";
            @Db::execute($sql);
        }
        
        $searchTableInfo = Db::query("SHOW COLUMNS FROM {$Prefix}search_word");
        $searchTableInfo = get_arr_column($searchTableInfo, 'Field');
        if (!empty($searchTableInfo) && !in_array('users_id', $searchTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}search_word` ADD COLUMN `users_id`  int(11) NULL DEFAULT 0 COMMENT '用户id' AFTER `sort_order`;";
            @Db::execute($sql);
        }
        if (!empty($searchTableInfo) && !in_array('ip', $searchTableInfo)){
            $sql = "ALTER TABLE `{$Prefix}search_word` ADD COLUMN `ip`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'ip' AFTER `users_id`;";
            @Db::execute($sql);
        }

        $isTable = Db::query('SHOW TABLES LIKE \''.$Prefix.'search_locking\'');
        if (empty($isTable)) {
            $tableSql = <<<EOF
CREATE TABLE IF NOT EXISTS `{$Prefix}search_locking` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `users_id` int(10) DEFAULT '0' COMMENT '用户ID',
  `ip` varchar(20) DEFAULT '' COMMENT 'ip',
  `locking_time` int(11) DEFAULT '0' COMMENT '锁定时间',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='搜索记录锁定表';
EOF;
            $r = @Db::execute($tableSql);
            if ($r !== false) {
                schemaTable('search_locking');
            }
        }

        // 优化主表字段的长度
        $admin_logic_1673941712 = tpSetting('syn.admin_logic_1673941712', [], 'cn');
        if (empty($admin_logic_1673941712)) {
            @Db::execute("ALTER TABLE `{$Prefix}archives` MODIFY COLUMN `htmlfilename`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '自定义文件名' AFTER `collection`");
            tpSetting('syn', ['admin_logic_1673941712'=>1], 'cn');
        }

        Db::name("admin_menu")->where(['menu_id'=>2010])->update(['menu_id'=>'2004023']);
    }
    //邮箱、短信配置
    public function admin_logic_1660557712(){
        $Prefix = config('database.prefix');
        $syn_admin_logic_1660557712 = Db::name('setting')->where(['name'=>'syn_admin_logic_1660557712', 'inc_type'=>'syn', 'lang'=>'cn'])->value('value');
        if (empty($syn_admin_logic_1660557712)){
            //邮箱配置表增加字段
            $saveData = Db::name('sms_template')->field('tpl_id', true)->where(['send_scene'=>0])->select();
            if (!empty($saveData)) {
                $addData = [];
                foreach ($saveData as $key => $val) {
                    $val['tpl_title'] = '留言表单';
                    $val['send_scene'] = 11;
                    $val['sms_sign'] = '';
                    $val['sms_tpl_code'] = '';
                    $val['tpl_content'] = '您有新的留言消息，请查收！';
                    $addData[] = $val;
                }
                Db::name('sms_template')->insertAll($addData);
            }

            tpSetting('syn', ['syn_admin_logic_1660557712'=>1], 'cn');
        }
    }

}
