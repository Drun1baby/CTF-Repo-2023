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

namespace think\template\taglib\eyou;

use think\Db;

/**
 * 友情链接
 */
class TagFlink extends Base
{
    //初始化
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取友情链接
     * @author wengxianhu by 2018-4-20
     */
    public function getFlink($type = 'text', $limit = '', $groupid = 1)
    {
        if ($type == 'text' || $type == 'textall') {
            $typeid = 1;
        } elseif ($type == 'image') {
            $typeid = 2;
        }

        $condition = array();
        if (!empty($typeid)) {
            $condition['a.typeid'] = array('eq', $typeid);
        }
        if (!empty($groupid) && $groupid != 'all') {

            /*多语言*/
            $groupid = model('LanguageAttr')->getBindValue($groupid, 'links_group');
            /*--end*/

            $condition['a.groupid'] = array('eq', $groupid);
        }
        // 多城市站点
        if (self::$city_switch_on) {
            if (!empty(self::$site_info)) {
                $site_flink_showall = tpCache('site.site_flink_showall');
                if (self::$site_info['level'] == 1) { // 省份
                    // 包含全国、当前省份
                    if ($siteall != null){  //标签属性控制
                        if(empty($siteall)){  //不显示主站
                            $condition['a.province_id'] = self::$siteid;
                            $condition['a.city_id'] = 0;
                        }else{      //显示主站
                            $province_where = [self::$siteid];
                            if (empty($site_flink_showall)) {
                                $province_where[] = 0;
                            }
                            $condition['a.province_id'] = ['in', $province_where];
                            $condition['a.city_id'] = 0;
                        }
                    }else if(empty(self::$site_info['showall'])){   //分站点配置信息不显示主站信息
                        $condition['a.province_id'] = self::$siteid;
                        $condition['a.city_id'] = 0;
                    }else{
                        $province_where = [self::$siteid];
                        if (empty($site_flink_showall)) {
                            $province_where[] = 0;
                        }
                        $condition['a.province_id'] = ['in', $province_where];
                        $condition['a.city_id'] = 0;
                    }
                } else if (self::$site_info['level'] == 2) { // 城市
                    // 包含全国、当前城市
                    if ($siteall != null){  //标签属性控制
                        if(empty($siteall)){  //不显示主站
                            $condition['a.city_id'] = self::$siteid;
                            $condition['a.area_id'] = 0;
                        }else{      //显示主站
                            $province_where = '';
                            if (empty($site_flink_showall)) {
                                $province_where = ' OR a.province_id = 0 ';
                            }
                            $condition[] = Db::raw(" ((a.city_id = ".self::$siteid." AND a.area_id = 0) {$province_where} ) ");
                        }
                    }else if(empty(self::$site_info['showall'])){   //分站点配置信息,不显示主站信息
                        $condition['a.city_id'] = self::$siteid;
                        $condition['a.area_id'] = 0;
                    }else{  //分站点配置信息,显示主站信息
                        $province_where = '';
                        if (empty($site_flink_showall)) {
                            $province_where = ' OR a.province_id = 0 ';
                        }
                        $condition[] = Db::raw(" ((a.city_id = ".self::$siteid." AND a.area_id = 0) {$province_where} ) ");
                    }

                    // 包含全国、全省、当前城市
                    // $citysiteInfo = Db::name('citysite')->where(['id'=>self::$siteid])->cache(true, EYOUCMS_CACHE_TIME, 'citysite')->find();
                    // $condition[] = Db::raw(" ((a.city_id IN (".self::$siteid.",0) AND a.province_id = ".$citysiteInfo['parent_id'].") OR (a.province_id = 0)) ");
                } else { // 区域
                    // 包含全国、当前区域
                    if ($siteall != null){  //标签属性控制
                        if(empty($siteall)){  //不显示主站
                            $condition['a.area_id'] = self::$siteid;
                        }else{      //显示主站
                            $province_where = '';
                            if (empty($site_flink_showall)) {
                                $province_where = ' OR a.province_id = 0 ';
                            }
                            $condition[] = Db::raw(" (a.area_id = ".self::$siteid." {$province_where} ) ");
                        }
                    }else if(empty(self::$site_info['showall'])){   //分站点配置信息,不显示主站信息
                        $condition['a.area_id'] = self::$siteid;
                    }else{  //分站点配置信息,显示主站信息
                        $province_where = '';
                        if (empty($site_flink_showall)) {
                            $province_where = ' OR a.province_id = 0 ';
                        }
                        $condition[] = Db::raw(" (a.area_id = ".self::$siteid." {$province_where} ) ");
                    }
                    // 包含全国、全省、全城市
                    // $citysiteInfo = Db::name('citysite')->where(['id'=>self::$siteid])->cache(true, EYOUCMS_CACHE_TIME, 'citysite')->find();
                    // $condition[] = Db::raw(" ((a.area_id IN (".self::$siteid.",0) AND a.city_id = ".$citysiteInfo['parent_id'].") OR (a.province_id = ".$citysiteInfo['topid']." AND a.city_id = 0) OR (a.province_id = 0)) ");
                }
            } else {   //以下为主站内容展示
                if ($siteall != null){    //标签属性控制
                    if (empty($siteall)){      //不显示分站文档
                        $condition['a.province_id'] = 0;
                    }else{    //显示分站文档

                    }
                }else if (empty($site_showall)) {    //主页不显示分站文档
                    $condition['a.province_id'] = 0;
                }
            }
        }
        $condition['a.lang'] = self::$home_lang;
        $condition['a.status'] = 1;
        $result = M("links")->alias('a')->where($condition)
            ->order('a.sort_order asc')
            ->limit($limit)
            ->cache(true,EYOUCMS_CACHE_TIME,"links")
            ->select();
        foreach ($result as $key => $val) {
            $val['logo'] = get_default_pic($val['logo']);
            $val['target'] = ($val['target'] == 1) ? ' target="_blank" ' : ' target="_self" ';
            $val['nofollow'] = ($val['nofollow'] == 1) ? ' rel="nofollow" ' : '';
            $result[$key] = $val;
        }

        return $result;
    }
}