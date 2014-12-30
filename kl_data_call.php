<?php
/*
Plugin Name: 数据调用
Version: 3.0
Plugin URL: https://github.com/aweika/kl_data_call
Description: 实现emlog相关的数据调用功能。
ForEmlog: 5.x版本
Author: 阿维卡
Author Email: kller@foxmail.com
Author URL: http://www.aweika.com
*/
!defined('EMLOG_ROOT') && exit('access deined!');

/**
 * Class 数据调用插件主程序类
 */
class KlDataCall
{
    const ID = 'kl_data_call';
    const NAME = '数据调用';
    const VERSION = '3.0';

    //实例
    private static $_instance;

    //是否初始化
    private $_inited = false;

    //数据库连接实例
    private $_db;

    //缓存、配置、模板目录
    private $_fileDir = array('cache', 'config', 'views');

    //相关资源目录
    private $_urlDir = array('assets', 'res');

    //提示信息（目录不可写、插件版本同数据表中存储的不一致等）
    private $_msg;

    /**
     * 静态方法，返回数据调用插件实例
     *
     * @return KlDataCall
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    /**
     * 获取本地数据表中存储的插件版本号
     * @return string
     */
    private function _getlocalVersion()
    {
        $kl_data_call_info = Option::get('kl_data_call_info');
        if (is_null($kl_data_call_info)) return '';
        $kl_data_call_info = unserialize($kl_data_call_info);
        return $kl_data_call_info['version'];
    }

    /**
     * 插件激活时要执行的代码（一些数据库修改等初始化或升级操作）
     */
    public function callbackInit()
    {
        Cache::getInstance()->updateCache('options');
        $kl_data_call_info = Option::get('kl_data_call_info');
        //未用过数据调用插件或使用2.0版本前不存在$kl_data_call_info信息
        if (is_null($kl_data_call_info)) {
            $info = serialize(array('version' => self::VERSION));
            $this->_db->query("INSERT INTO " . DB_PREFIX . "options(option_name, option_value) VALUES('kl_data_call_info', '{$info}')");
            if ($this->_db->affected_rows() > 0) {
                $cod = @opendir($this->_getDirPath('config'));
                $did_arr = array();
                while (($filename = readdir($cod)) !== false) {
                    $filename = str_replace('.php', '', $filename);
                    if (is_numeric($filename)) $did_arr[] = $filename;
                }
                @closedir($cod);
                //为空的话相当于没使用过插件
                if (empty($did_arr)) return;
                foreach ($did_arr as $did) {
                    $kl_data_call_id = Option::get('kl_data_call_' . $did);
                    if (is_null($kl_data_call_id)) {
                        $data = str_replace('<?php exit;//', '', @file_get_contents($this->_getDirPath('config') . '/' . $did . '.php'));
                        $this->_db->query("INSERT INTO " . DB_PREFIX . "options(option_name, option_value) VALUES('kl_data_call_{$did}', '{$data}')");
                    }
                }
            }
            Cache::getInstance()->updateCache('options');
        } else {
            $kl_data_call_info = unserialize($kl_data_call_info);
            //由于3.0版开始更新了缓存中的数据结构，所以启用时先更新全部数据缓存
            if ($kl_data_call_info['version'] < '3.0') {
                $options_cache = Cache::getInstance()->readCache('options');
                foreach ($options_cache as $ock => $ocv) {
                    if (preg_match('/^kl_data_call_(\d+)$/', $ock)) $this->_mainFun(unserialize($ocv));
                }
            }

            //todo 如果升级有数据表相关修改，则像上面那样在这里写升级相关的代码

            //每次插件升级更新数据库中存储的插件版本
            if ($kl_data_call_info['version'] < self::VERSION) {
                $kl_data_call_info['version'] = self::VERSION;
                $kl_data_call_info = serialize($kl_data_call_info);
                $this->_db->query("UPDATE " . DB_PREFIX . "options SET option_value='{$kl_data_call_info}' WHERE option_name='kl_data_call_info'");
                Cache::getInstance()->updateCache('options');
            }
        }
    }

    /**
     * 侧边栏钩子执行方法
     */
    public function hookAdmSidebarExt()
    {
        echo '<div class="sidebarsubmenu" id="' . self::ID . '"><a href="./plugin.php?plugin=' . self::ID . '">' . self::NAME . '</a></div>';
    }

    public function init()
    {
        if ($this->_inited === true) {
            return;
        }
        $this->_inited = true;
        $this->_getDb();
        $this->_msg = is_writable($this->_getDirPath('config')) && is_writable($this->_getDirPath('cache')) ? '' : '<span class="error">config或cache目录可能不可写，如果已经是可写状态，请忽略此信息。</span>';
        if (empty($this->_msg) && $this->_getlocalVersion() !== self::VERSION) {
            $this->_msg = $this->_getlocalVersion() < self::VERSION ? '<span class="error">系统检测到有新版本的插件已安装，请先到<a href="./plugin.php">插件列表</a>页面先关闭此插件，再开启此插件。</span>' : '<span class="error">系统检测到您可能安装了较低版本的插件，请<a target="_blank" href="#">下载</a>最新版本插件。</span>';
        }
        addAction('adm_sidebar_ext', array($this, 'hookAdmSideBarExt'));
    }

    /**
     * 获取数据库连接实例
     *
     * @return MySql|MySqlii|object
     */
    private function _getDb()
    {
        if (!is_null($this->_db)) return $this->_db;
        if (class_exists('Database', false)) {
            $this->_db = Database::getInstance();
        } else {
            $this->_db = MySql::getInstance();
        }
        return $this->_db;
    }

    /**
     * 获取目录方法
     *
     * @param $dir
     * @return string
     */
    private function _getDirPath($dir)
    {
        if (in_array($dir, $this->_fileDir)) {
            return dirname(__FILE__) . '/' . $dir;
        } elseif (in_array($dir, $this->_urlDir)) {
            return BLOG_URL . 'content/plugins/' . self::ID . '/' . $dir;
        } else {
            return '';
        }
    }

    /**
     * 获取模板文件全路径
     *
     * @param $view 文件
     * @param string $ext 扩展名
     * @return string
     */
    private function _view($view, $ext = '.php')
    {
        return $this->_getDirPath('views') . '/' . $view . $ext;
    }

    /**
     * 获取调用模板配置
     *
     * @return array
     */
    private function _moduleConfig()
    {
        $options_cache = Cache::getInstance()->readCache('options');
        $data_call_module_config = array();
        foreach ($options_cache as $ock => $ocv) {
            if (preg_match('/^kl_data_call_(\d+)$/', $ock, $didInfo)) $data_call_module_config[$didInfo[1]] = unserialize($ocv);
        }
        ksort($data_call_module_config);
        return $data_call_module_config;
    }

    /**
     * 微语调用主方法
     *
     * @param $act 编辑还是添加
     * @param $did 调用模板ID
     * @param $module
     */
    private function _callT($act, $did, $module)
    {
        $user_cache = Cache::getInstance()->readCache('user');
        $author_option_str = '<option value="0">全部</option>';
        foreach ($user_cache as $uk => $user) {
            $selected = (isset($module['author']) && $uk == $module['author']) ? 'selected' : '';
            $author_option_str .= "<option value=\"{$uk}\" {$selected}>{$user['name']}</option>";
        }

        $is_include_img_arr = array('全部文章', '仅显示有图片的微语', '仅显示没有图片的微语');
        $is_include_img_option_str = '';
        foreach ($is_include_img_arr as $value => $is_include_img) {
            $selected = (isset($module['is_include_img']) && $value == $module['is_include_img']) ? 'selected' : '';
            $is_include_img_option_str .= "<option value=\"{$value}\" {$selected}>{$is_include_img}</option>";
        }

        $order_style_arr = array('发布时间倒序', '回复数倒序', '随机');
        $order_style_option_str = '';
        foreach ($order_style_arr as $value => $order_style) {
            $selected = (isset($module['order_style']) && $value == $module['order_style']) ? 'selected' : '';
            $order_style_option_str .= "<option value=\"{$value}\" {$selected}>{$order_style}</option>";
        }

        $date_style_arr = array('2010-1-1', '2010-01-01', '2010年1月1日', '2010年01月01日', '2010-1-1 0:00', '2010-01-01 00:00', '2010-1-1 0:00:00', '2010-01-01 00:00:00', '2010-1-1 0:00 Friday', '2010-01-01 00:00 Friday');
        $date_style_option_str = '';
        foreach ($date_style_arr as $value => $date_style) {
            $selected = (isset($module['date_style']) && $value == $module['date_style']) ? 'selected' : '';
            $date_style_option_str .= "<option value=\"{$value}\" {$selected}>{$date_style}</option>";
        }

        include $this->_view('t');
    }

    /**
     * EM相册调用主方法
     *
     * @param $act 编辑还是添加
     * @param $did 调用模板ID
     * @param $module
     */
    private function _callEmAlbum($act, $did, $module)
    {
        $active_plugins = Option::get('active_plugins');
        $the_notice = '';
        if (!in_array('kl_album/kl_album.php', $active_plugins)) {
            $the_notice = '<div style="padding:5px;border:1px dashed #CCC"><span style="color:red;">提示信息：您没有安装或没有开启<a href="http://kller.cn/?post=33" target="_blank">EM相册插件</a>。</span></div>';
        } else {
            $kl_album_info = Option::get('kl_album_info');
            if (is_null($kl_album_info)) {
                $the_notice = '<div style="padding:5px;border:1px dashed #CCC"><span style="color:red;">提示信息：您的EM相册相关数据可能不完整，请尝试禁用再开启<a href="http://kller.cn/?post=33" target="_blank">EM相册插件</a>。</span></div>';
            } else {
                $kl_album_info = unserialize($kl_album_info);
                if (empty($kl_album_info)) {
                    $the_notice = '<div style="padding:5px;border:1px dashed #CCC"><span style="color:red;">提示信息：您的EM相册貌似还没有相册哦，请先创建相册再进行调用。</span></div>';
                } else {
                    $kl_album_option_str = '<option value="0">全部相册</option>';
                    foreach ($kl_album_info as $kl_album) {
                        $selected = (isset($module['kl_album']) && $kl_album['addtime'] == $module['kl_album']) ? 'selected' : '';
                        $kl_album_option_str .= "<option value=\"{$kl_album['addtime']}\" {$selected}>{$kl_album['name']}</option>";
                    }

                    $order_style_arr = array('与相册一致', '发布时间倒序', '随机');
                    $order_style_option_str = '';
                    foreach ($order_style_arr as $value => $order_style) {
                        $selected = (isset($module['order_style']) && $value == $module['order_style']) ? 'selected' : '';
                        $order_style_option_str .= "<option value=\"{$value}\" {$selected}>{$order_style}</option>";
                    }

                    $date_style_arr = array('2010-1-1', '2010-01-01', '2010年1月1日', '2010年01月01日', '2010-1-1 0:00', '2010-01-01 00:00', '2010-1-1 0:00:00', '2010-01-01 00:00:00', '2010-1-1 0:00 Friday', '2010-01-01 00:00 Friday');
                    $date_style_option_str = '';
                    foreach ($date_style_arr as $value => $date_style) {
                        $selected = (isset($module['date_style']) && $value == $module['date_style']) ? 'selected' : '';
                        $date_style_option_str .= "<option value=\"{$value}\" {$selected}>{$date_style}</option>";
                    }
                }
            }
        }
        include $this->_view('em_album');
    }

    /**
     * 日志调用主方法
     *
     * @param $act 编辑还是添加
     * @param $did 调用模板ID
     * @param $module
     */
    private function _callLog($act, $did, $module)
    {
        $user_cache = Cache::getInstance()->readCache('user');
        $author_option_str = '<option value="0">全部</option>';
        foreach ($user_cache as $uk => $user) {
            $selected = (isset($module['author']) && $uk == $module['author']) ? 'selected' : '';
            $author_option_str .= "<option value=\"{$uk}\" {$selected}>{$user['name']}</option>";
        }

        $sort_cache = Cache::getInstance()->readCache('sort');
        $sort_cache[0] = array('sortname' => '未分类', 'sid' => 0);
        sort($sort_cache);
        $sort_option_str = '';
        foreach ($sort_cache as $sort) {
            $selected = (isset($module['sort']) && $sort['sid'] == $module['sort']) ? 'selected' : '';
            $sort_option_str .= "<option value=\"{$sort['sid']}\" {$selected}>{$sort['sortname']}</option>";
        }

        $filter_arr = array('全部文章', '仅置顶文章', '非置顶文章');
        $filter_option_str = '';
        foreach ($filter_arr as $value => $filter) {
            $selected = (isset($module['filter']) && $value == $module['filter']) ? 'selected' : '';
            $filter_option_str .= "<option value=\"{$value}\" {$selected}>{$filter}</option>";
        }

        $is_include_img_arr = array('全部文章', '仅显示有图片的文章', '仅显示没有图片的文章');
        $is_include_img_option_str = '';
        foreach ($is_include_img_arr as $value => $is_include_img) {
            $selected = (isset($module['is_include_img']) && $value == $module['is_include_img']) ? 'selected' : '';
            $is_include_img_option_str .= "<option value=\"{$value}\" {$selected}>{$is_include_img}</option>";
        }

        $link_style_arr = array('<没有设置>', '新窗口(_blank)', '本窗口(_self)');
        $link_style_option_str = '';
        foreach ($link_style_arr as $value => $link_style) {
            $selected = (isset($module['link_style']) && $value == $module['link_style']) ? 'selected' : '';
            $link_style_option_str .= "<option value=\"{$value}\" {$selected}>{$link_style}</option>";
        }

        $order_style_arr = array('发布时间倒序', '评论数倒序', '浏览次数倒序排列', '随机');
        $order_style_option_str = '';
        foreach ($order_style_arr as $value => $order_style) {
            $selected = (isset($module['order_style']) && $value == $module['order_style']) ? 'selected' : '';
            $order_style_option_str .= "<option value=\"{$value}\" {$selected}>{$order_style}</option>";
        }

        $date_style_arr = array('2010-1-1', '2010-01-01', '2010年1月1日', '2010年01月01日', '2010-1-1 0:00', '2010-01-01 00:00', '2010-1-1 0:00:00', '2010-01-01 00:00:00', '2010-1-1 0:00 Friday', '2010-01-01 00:00 Friday');
        $date_style_option_str = '';
        foreach ($date_style_arr as $value => $date_style) {
            $selected = (isset($module['date_style']) && $value == $module['date_style']) ? 'selected' : '';
            $date_style_option_str .= "<option value=\"{$value}\" {$selected}>{$date_style}</option>";
        }
        include $this->_view('log');
    }

    /**
     * 设置页面主程序
     */
    public function settingView()
    {
        $this->_getHeader();
        if (isset($_GET['act'])) {
            echo sprintf('<script src="%s/list.js" type="text/javascript"></script>', $this->_getDirPath('assets'));
        } else {
            echo sprintf('<link rel="stylesheet" href="%s">', $this->_getDirPath('assets') . '/notlist.css?ver=' . urlencode(self::VERSION));
        }
        $kl_t_array = array('<font color="red">文章调用</font>', '<font color="green">微语调用</font>', '<font color="blue">EM相册调用</font>');
        $data_call_module_config = $this->_moduleConfig();
        if (!isset($_GET['act'])) {
            include $this->_view('list');
        } else {
            $act = $_GET['act'];
            if ($act == 'about') {
                include $this->_view('about');
            } else {
                if (isset($_GET['id'])) $id = intval($_GET['id']);
                if (isset($id)) {
                    $module = $data_call_module_config[$id];
                    $kl_t = isset($module['kl_t']) && in_array($module['kl_t'], array_keys($kl_t_array)) ? $module['kl_t'] : 0;
                    $did = $module['did'];
                } else {
                    $kl_t = isset($_GET['kl_t']) ? intval($_GET['kl_t']) : 0;
                    $did = count($data_call_module_config) == 0 ? 1 : max(array_keys($data_call_module_config)) + 1;
                }
                if ($kl_t == 1) {//微语调用
                    $this->_callT($act, $did, $module);
                } elseif ($kl_t == 2) {//EM相册调用
                    $this->_callEmAlbum($act, $did, $module);
                } else {//文章调用
                    $this->_callLog($act, $did, $module);
                }
            }

        }
    }

    /**
     * 设置页面后台保存主程序
     */
    public function setting()
    {
        $act = isset($_GET['act']) ? addslashes(trim($_GET['act'])) : '';
        if ($act == 'add' || $act == 'edit') {
            $module = array();
            $module['kl_t'] = intval($_POST['kl_t']);
            if ($module['kl_t'] == 1) {
                $intval_argu_arr1 = array('did' => '', 'start_num' => 0, 'dis_rows' => 10, 'cache_limit' => 300, 'author' => 0, 'is_include_img' => 0);
                $intval_argu_arr2 = array('order_style', 'date_style');
                foreach ($intval_argu_arr1 as $iaak => $iaav) $module[$iaak] = trim($_POST[$iaak]) != '' ? intval($_POST[$iaak]) : $iaav;
                foreach ($intval_argu_arr2 as $iaav) $module[$iaav] = isset($_POST[$iaav]) ? intval($_POST[$iaav]) : 0;
                $module['description'] = trim($_POST['description']) != '' ? addslashes($_POST['description']) : '';
                $module['code'] = isset($_POST['code']) ? base64_encode($_POST['code']) : '';
                $module['custom_tailor'] = trim($_POST['custom_tailor']) != '' ? addslashes(trim($_POST['custom_tailor'])) : '';
            } elseif ($module['kl_t'] == 2) {
                $intval_argu_arr1 = array('did' => '', 'start_num' => 0, 'dis_rows' => 10, 'cache_limit' => 300, 'em_album' => 0);
                $intval_argu_arr2 = array('order_style', 'date_style');
                foreach ($intval_argu_arr1 as $iaak => $iaav) $module[$iaak] = trim($_POST[$iaak]) != '' ? intval($_POST[$iaak]) : $iaav;
                foreach ($intval_argu_arr2 as $iaav) $module[$iaav] = isset($_POST[$iaav]) ? intval($_POST[$iaav]) : 0;
                $module['description'] = trim($_POST['description']) != '' ? addslashes($_POST['description']) : '';
                $module['code'] = isset($_POST['code']) ? base64_encode($_POST['code']) : '';
            } else {
                $intval_argu_arr1 = array('did' => '', 'start_num' => 0, 'dis_rows' => 10, 'cache_limit' => 300, 'filter' => 0, 'is_include_img' => 0);
                $intval_argu_arr2 = array('sort', 'nopwd', 'link_style', 'order_style', 'date_style');
                foreach ($intval_argu_arr1 as $iaak => $iaav) $module[$iaak] = trim($_POST[$iaak]) != '' ? intval($_POST[$iaak]) : $iaav;
                foreach ($intval_argu_arr2 as $iaav) $module[$iaav] = isset($_POST[$iaav]) ? intval($_POST[$iaav]) : 0;
                $module['description'] = trim($_POST['description']) != '' ? addslashes($_POST['description']) : '';
                $module['code'] = isset($_POST['code']) ? base64_encode($_POST['code']) : '';
                $module['custom_tailor'] = trim($_POST['custom_tailor']) != '' ? addslashes(trim($_POST['custom_tailor'])) : '';
            }
            $data = serialize($module);
            Cache::getInstance()->updateCache('options');
            $kl_data_call_info = Option::get('kl_data_call_' . $module['did']);
            if (is_null($kl_data_call_info)) {
                $this->_db->query("INSERT INTO " . DB_PREFIX . "options(option_name, option_value) VALUES('kl_data_call_{$module['did']}', '{$data}')");
            } else {
                $this->_db->query("UPDATE " . DB_PREFIX . "options SET option_value='{$data}' WHERE option_name='kl_data_call_{$module['did']}'");
            }
            $this->_mainFun($module);
            //把更新缓存移到kl_data_call__mainFun()后面，是因为如果在前面会造成后面读取缓存报错，导致新建后第一次不能正常生成。
            Cache::getInstance()->updateCache('options');
            emDirect("./plugin.php?plugin=kl_data_call&act=edit&id={$module['did']}&active_save=1");
        } elseif ($act == 'del') {
            $id = intval($_GET['id']);
            $this->_db->query("DELETE FROM " . DB_PREFIX . "options WHERE option_name='kl_data_call_{$id}'");

            Cache::getInstance()->updateCache('options');
            @unlink($this->_getDirPath('cache') . '/' . $id . '.php');
            emDirect("./plugin.php?plugin=kl_data_call&active_del=1");
        }

        if (isset($_GET['update']) && $_GET['update'] == 'true') {
            $options_cache = Cache::getInstance()->readCache('options');
            foreach ($options_cache as $ock => $ocv) {
                if (preg_match('/^kl_data_call_(\d+)$/', $ock)) $this->_mainFun(unserialize($ocv));
            }
        }
        emDirect("./plugin.php?plugin=kl_data_call&active_update=1");
    }

    /**
     * 将调用数据写入缓存，返回拼接的字符串
     *
     * @param $module 参数配置
     * @param int $cols 总列数
     * @param int $col 第几列
     * @return string
     */
    private function _mainFun($module, $cols = 1, $col = 1)
    {
        $code = stripslashes(base64_decode($module['code']));
        $output = $this->_mainFunForPreview($module, $code);
        $file_name = $this->_getDirPath('cache') . '/' . $module['did'] . '.php';
        $fp = @fopen($file_name, 'w');
        @fwrite($fp, "<?php return " . var_export($output, true) . ';');
        @fclose($fp);
        $output = $this->_theOutputData($output, $cols, $col);
        return implode('', $output);
    }

    /**
     * 生成预览数据
     *
     * @param $module 参数配置
     * @param $code 调用模板
     * @return array
     */
    private function _mainFunForPreview($module, $code)
    {
        $kl_t_array = array('文章调用', '微语调用', 'EM相册调用');
        $kl_t = isset($module['kl_t']) && in_array($module['kl_t'], array_keys($kl_t_array)) ? $module['kl_t'] : 0;
        if ($kl_t == 1) {
            return $this->_mainFunForPreviewT($module, $code);
        } elseif ($kl_t == 2) {
            return $this->_mainFunForPreviewKlAlbum($module, $code);
        } else {
            return $this->_mainFunForPreviewLog($module, $code);
        }
    }

    /**
     * 日志调用模板解析方法
     *
     * @param $module 参数配置
     * @param $code 调用模板
     * @return array
     */
    private function _mainFunForPreviewLog($module, $code)
    {
        preg_match_all('%{(.*?)}%s', $code, $anArr, PREG_PATTERN_ORDER);
        $vArr = $anArr[1];

        $condition = '';
        if ($module['custom_tailor'] != '') {
            $custom_tailor_arr = explode(',', $module['custom_tailor']);
            foreach ($custom_tailor_arr as $k => $custom_tailor) {
                if (intval($custom_tailor) == 0) {
                    unset($custom_tailor_arr[$k]);
                } else {
                    $custom_tailor_arr[$k] = intval($custom_tailor);
                }
            }
            $custom_tailor_str = implode(',', $custom_tailor_arr);
            $condition .= "and a.gid in({$custom_tailor_str})";
        } else {
            if ($module['filter'] == 1) $condition .= 'and a.top="y" ';
            if ($module['filter'] == 2) $condition .= 'and a.top="n" ';
            if ($module['is_include_img'] == 1) $condition .= 'and content not regexp "<img[^>]*src=[\'\"][^>]*/admin/[^>]*[\'\"][^>]*>" and content regexp "<img[^>]*src=[\'\"][^>]*[\'\"][^>]*>" ';
            if ($module['is_include_img'] == 2) $condition .= 'and content not regexp "<img[^>]*src=[\'\"][^>]*[\'\"][^>]*>" ';
            if ($module['nopwd'] == 1) $condition .= 'and a.password="" ';
            if ($module['sort'] != -1) $condition .= $module['sort'] == 0 ? 'and a.sortid=-1 ' : 'and a.sortid=' . $module['sort'] . ' ';
            if (isset($module['author']) && !empty($module['author'])) $condition .= "and c.uid={$module['author']} ";
        }
        $condition .= 'group by a.gid ';
        if ($module['order_style'] == 0) $condition .= 'order by a.date desc ';
        if ($module['order_style'] == 1) $condition .= 'order by cnum desc, a.gid desc ';
        if ($module['order_style'] == 2) $condition .= 'order by a.views desc, a.gid desc ';
        if ($module['order_style'] == 3) $condition .= 'order by rand() ';
        if ($module['custom_tailor'] == '') $condition .= 'limit ' . $module['start_num'] . ',' . $module['dis_rows'];
        $sql = 'select a.gid as id, a.title, if(a.password!="", "", a.excerpt) as excerpt, if(a.password!="", "", a.content) as content, a.date, c.username as author, a.type, a.views, b.sortname as sort, count(d.cid) as cnum from ' . DB_PREFIX . 'blog a left join ' . DB_PREFIX . 'sort b on a.sortid=b.sid left join ' . DB_PREFIX . 'user c on c.uid=a.author left join ' . DB_PREFIX . 'comment d on d.gid=a.gid and d.hide="n" where a.hide="n" and type!="page" ' . $condition;
        $result = $this->_db->query($sql);
        $data_arr = array();
        $auto_id = 1;
        while ($row = $this->_db->fetch_array($result, MYSQL_ASSOC)) {
            $row['auto_id'] = $auto_id;
            array_push($data_arr, $row);
            $auto_id++;
        }

        if (count($data_arr) != 0) {
            $data_arr_key = array_keys($data_arr[0]);
            $evArr = array_intersect($vArr, $data_arr_key);
            $extra_arr = array('log_url', 'image', 'image_include_link', 'imageurl', 'title_without_link', 'excerpt_include_readmore', 'comment_count');
            foreach ($extra_arr as $ev) {
                if (in_array($ev, $vArr)) array_push($evArr, $ev);
            }
            foreach ($data_arr as $dk => $data) {
                if ($module['link_style'] == 0) $target = '';
                if ($module['link_style'] == 1) $target = 'target="_blank"';
                if ($module['link_style'] == 2) $target = 'target="_self"';
                $title = $data['title'];
                if (in_array('title_without_link', $vArr)) $data['title_without_link'] = $title;
                if (in_array('comment_count', $vArr)) $data['comment_count'] = $data['cnum'];
                $date_style_encode_arr = array('Y-n-j', 'Y-m-d', 'Y年n月j日', 'Y年m月d日', 'Y-n-j g:i', 'Y-m-d H:i', 'Y-n-j g:i:s', 'Y-m-d H:i:s', 'Y-n-j g:i:s l', 'Y-m-d H:i:s l');
                if (in_array('date', $vArr)) $data['date'] = date($date_style_encode_arr[$module['date_style']], $data['date']);
                $log_url = Url::log($data['id']);
                $blog_link_header = '<a href="' . $log_url . '" ' . $target . ' title="' . $title . '">';
                if (in_array('log_url', $vArr)) $data['log_url'] = $log_url;
                if (in_array('title', $vArr)) $data['title'] = $blog_link_header . $title . '</a>';
                $excerpt = $data['excerpt'];
                if (in_array('excerpt', $vArr)) $data['excerpt'] = empty($excerpt) ? $this->_breakLog($data['content'], $data['id']) : $excerpt;
                if (in_array('excerpt_include_readmore', $vArr)) $data['excerpt_include_readmore'] = empty($excerpt) ? $this->_breakLog($data['content'], $data['id'], true) : $excerpt .= '<p><a href="' . Url::log($data['id']) . '">阅读全文&gt;&gt;</a></p>';
                $tmpArr = array_intersect(array('image', 'image_include_link', 'imageurl'), $vArr);
                if (!empty($tmpArr)) {
                    $search_pattern = '%<img[^>]*?src=[\'\"]((?:(?!\/admin\/|>).)+?)[\'\"][^>]*?>%s';
                    preg_match($search_pattern, $data['content'], $kl_arr);
                }
                if (in_array('image', $vArr)) $data['image'] = isset($kl_arr[1]) ? $kl_arr[0] : '';
                if (in_array('image_include_link', $vArr)) $data['image_include_link'] = isset($kl_arr[1]) ? $blog_link_header . $kl_arr[0] . '</a>' : '';
                if (in_array('imageurl', $vArr)) $data['imageurl'] = isset($kl_arr[1]) ? $kl_arr[1] : '';

                $codebak = $code;
                foreach ($evArr as $ev) {
                    if ($ev == 'content') continue;
                    $codebak = str_replace('{' . $ev . '}', $data[$ev], $codebak);
                }
                $data_arr[$dk] = $codebak;
            }
        }
        return $data_arr;
    }

    /**
     * 微语调用模板解析方法
     *
     * @param $module 参数配置
     * @param $code 调用模板
     * @return array
     */
    private function _mainFunForPreviewT($module, $code)
    {
        preg_match_all('%{(.*?)}%s', $code, $anArr, PREG_PATTERN_ORDER);
        $vArr = $anArr[1];

        $condition = '';
        if ($module['custom_tailor'] != '') {
            $custom_tailor_arr = explode(',', $module['custom_tailor']);
            foreach ($custom_tailor_arr as $k => $custom_tailor) {
                if (intval($custom_tailor) == 0) {
                    unset($custom_tailor_arr[$k]);
                } else {
                    $custom_tailor_arr[$k] = intval($custom_tailor);
                }
            }
            $custom_tailor_str = implode(',', $custom_tailor_arr);
            $condition .= "and a.id in({$custom_tailor_str})";
        } else {
            if ($module['is_include_img'] == 1) $condition .= 'and a.img!="" ';
            if ($module['is_include_img'] == 2) $condition .= 'and a.img="" ';
            if (isset($module['author']) && !empty($module['author'])) $condition .= "and b.uid={$module['author']} ";
        }
        if ($module['order_style'] == 0) $condition .= 'order by a.date desc ';
        if ($module['order_style'] == 1) $condition .= 'order by a.replynum desc, a.id desc ';
        if ($module['order_style'] == 2) $condition .= 'order by rand() ';
        if ($module['custom_tailor'] == '') $condition .= 'limit ' . $module['start_num'] . ',' . $module['dis_rows'];
        $sql = 'select a.id, a.content, a.img as imageurl, b.username as author, a.date, a.replynum from ' . DB_PREFIX . 'twitter a left join ' . DB_PREFIX . 'user b on b.uid=a.author where 1 ' . $condition;
        $result = $this->_db->query($sql);
        $data_arr = array();
        $auto_id = 1;
        while ($row = $this->_db->fetch_array($result, MYSQL_ASSOC)) {
            $row['auto_id'] = $auto_id;
            array_push($data_arr, $row);
            $auto_id++;
        }

        if (count($data_arr) != 0) {
            $data_arr_key = array_keys($data_arr[0]);
            $evArr = array_intersect($vArr, $data_arr_key);
            $extra_arr = array('thum_imageurl');
            foreach ($extra_arr as $ev) {
                if (in_array($ev, $vArr)) array_push($evArr, $ev);
            }
            foreach ($data_arr as $dk => $data) {
                if (in_array('date', $vArr)) {
                    $date_style_encode_arr = array('Y-n-j', 'Y-m-d', 'Y年n月j日', 'Y年m月d日', 'Y-n-j g:i', 'Y-m-d H:i', 'Y-n-j g:i:s', 'Y-m-d H:i:s', 'Y-n-j g:i:s l', 'Y-m-d H:i:s l');
                    $data['date'] = date($date_style_encode_arr[$module['date_style']], $data['date']);
                }
                if (in_array('imageurl', $vArr)) $data['imageurl'] = BLOG_URL . str_replace('thum-', '', $data['imageurl']);
                if (in_array('thum_imageurl', $vArr)) $data['thum_imageurl'] = BLOG_URL . $data['imageurl'];
                $codebak = $code;
                foreach ($evArr as $ev) {
                    $codebak = str_replace('{' . $ev . '}', $data[$ev], $codebak);
                }
                $data_arr[$dk] = $codebak;
            }
        }
        return $data_arr;
    }

    /**
     * EM相册调用模板解析方法
     *
     * @param $module 参数配置
     * @param $code 调用模板
     * @return array
     */
    private function _mainFunForPreviewKlAlbum($module, $code)
    {
        preg_match_all('%{(.*?)}%s', $code, $anArr, PREG_PATTERN_ORDER);
        $vArr = $anArr[1];

        $condition = '';
        $kl_album_info = Option::get('kl_album_info');
        if (is_null($kl_album_info)) return array();
        $kl_album_info = unserialize($kl_album_info);
        $kl_album = Option::get('kl_album_' . $module['em_album']);
        if ($module['em_album'] != 0) {
            if (is_null($kl_album)) {
                $condition = " and album={$module['em_album']} ";
                if ($module['order_style'] == 0) $condition .= "order by id desc ";
            } else {
                $idStr = empty($kl_album) ? 0 : $kl_album;
                $condition = " and id in({$idStr}) ";
                if ($module['order_style'] == 0) $condition .= "order by substring_index('{$idStr}', id, 1) ";
            }
        } else {
            if ($module['order_style'] == 0) $condition .= "order by id desc ";
        }

        if ($module['order_style'] == 1) $condition .= 'order by id desc ';
        if ($module['order_style'] == 2) $condition .= 'order by rand() ';
        $condition .= 'limit ' . $module['start_num'] . ',' . $module['dis_rows'];
        $sql = 'select filename as thum_photo_url, description as photo_description, album as photo_album, addtime as photo_datetime from ' . DB_PREFIX . 'kl_album where 1 ' . $condition;
        $result = $this->_db->query($sql);
        $data_arr = array();
        $auto_id = 1;
        while ($row = $this->_db->fetch_array($result, MYSQL_ASSOC)) {
            $row['auto_id'] = $auto_id;
            array_push($data_arr, $row);
            $auto_id++;
        }

        if (count($data_arr) != 0) {
            $data_arr_key = array_keys($data_arr[0]);
            $evArr = array_intersect($vArr, $data_arr_key);
            $extra_arr = array('album_name', 'album_description', 'album_datetime', 'album_url', 'album_cover', 'photo_url');
            foreach ($extra_arr as $ev) {
                if (in_array($ev, $vArr)) array_push($evArr, $ev);
            }
            foreach ($data_arr as $dk => $data) {
                if (in_array('album_datetime', $vArr)) {
                    $date_style_encode_arr = array('Y-n-j', 'Y-m-d', 'Y年n月j日', 'Y年m月d日', 'Y-n-j g:i', 'Y-m-d H:i', 'Y-n-j g:i:s', 'Y-m-d H:i:s', 'Y-n-j g:i:s l', 'Y-m-d H:i:s l');
                    $data['album_datetime'] = date($date_style_encode_arr[$module['date_style']], $data['photo_album']);
                }
                if (in_array('photo_datetime', $vArr)) $data['photo_datetime'] = date($date_style_encode_arr[$module['date_style']], $data['photo_datetime']);
                $thumb_photo_url = $data['thum_photo_url'];
                if (in_array('thum_photo_url', $vArr)) $data['thum_photo_url'] = BLOG_URL . substr($thumb_photo_url, 3);
                if (in_array('photo_url', $vArr)) $data['photo_url'] = BLOG_URL . str_replace('thum-', '', substr($thumb_photo_url, 3));
                foreach ($kl_album_info as $kl_album) {
                    if ($kl_album['addtime'] == $data['photo_album']) {
                        if (in_array('album_name', $vArr)) $data['album_name'] = $kl_album['name'];
                        if (in_array('album_description', $vArr)) $data['album_description'] = $kl_album['description'];
                        if (in_array('album_url', $vArr)) $data['album_url'] = BLOG_URL . '?plugin=kl_album&album=' . $kl_album['addtime'];
                        if (in_array('album_cover', $vArr)) {
                            $data['album_cover'] = '';
                            if (isset($kl_album['head'])) {
                                $iquery = $this->_db->query("SELECT * FROM " . DB_PREFIX . "kl_album WHERE id={$kl_album['head']}");
                                if ($this->_db->num_rows($iquery) > 0) {
                                    $irow = $this->_db->fetch_array($iquery);
                                    $data['album_cover'] = BLOG_URL . substr($irow['filename'], 3);
                                }
                            }
                            if (empty($data['album_cover'])) {
                                $iquery = $this->_db->query("SELECT * FROM " . DB_PREFIX . "kl_album WHERE 1 " . $condition);
                                if ($this->_db->num_rows($iquery) > 0) {
                                    $irow = $this->_db->fetch_array($iquery);
                                    $data['album_cover'] = BLOG_URL . substr($irow['filename'], 3);
                                }
                            }
                        }
                    }
                }
                $codebak = $code;
                foreach ($evArr as $ev) {
                    $codebak = str_replace('{' . $ev . '}', $data[$ev], $codebak);
                }
                $data_arr[$dk] = $codebak;
            }
        }
        return $data_arr;
    }

    /**
     * 调用的数据具体的分列处理方法
     *
     * @param $data_arr 总数据
     * @param $cols 分几列
     * @param $col 第几列
     * @return array
     */
    private function _theOutputData($data_arr, $cols, $col)
    {
        if (is_array($data_arr) && !empty($data_arr)) {
            $i = 1;
            $new_data_arr = array();
            foreach ($data_arr as $dk => $dv) {
                if ($cols > 0 && $col > 0 && $cols >= $col) {
                    $new_col = $cols == $col ? 0 : $col;
                    if ($i % $cols == $new_col) {
                        array_push($new_data_arr, $dv);
                    }
                }
                $i++;
            }
        }
        if (!empty($new_data_arr)) $data_arr = $new_data_arr;
        return $data_arr;
    }

    /**
     * 获取调用的数据
     *
     * @param $id 模板ID
     * @param int $cols 分几列
     * @param int $col 第几列
     * @return string
     */
    public function forInternalValue($id, $cols = 1, $col = 1)
    {
        $id = intval($id);
        $cols = intval($cols);
        $col = intval($col);
        $output = '';
        $kl_data_call_info = Option::get('kl_data_call_' . $id);
        if (is_null($kl_data_call_info)) return $output;
        $module = unserialize($kl_data_call_info);
        $cache_file = $this->_getDirPath('cache') . "/{$id}.php";
        if (file_exists($cache_file) && time() - filemtime($cache_file) < $module['cache_limit']) {
            $data_arr = include $cache_file;
            $data_arr = $this->_theOutputData($data_arr, $cols, $col);
            $output .= implode('', $data_arr);
        } else {
            $output = $this->_mainFun($module, $cols, $col);
        }
        return $output;
    }

    /**
     * 日志摘要方法
     *
     * @param $content 日志内容
     * @param $lid 日志ID
     * @param bool $isreadmore 是不显示阅读全文
     * @return string
     */
    private function _breakLog($content, $lid, $isreadmore = false)
    {
        $a = explode('[break]', $content, 2);
        if (!empty($a[1]) && $isreadmore === true) $a[0] .= '<p><a href="' . Url::log($lid) . '">阅读全文&gt;&gt;</a></p>';
        return $a[0];
    }

    /**
     * 生成预览数据的方法
     */
    public function preview()
    {
        $module = array();
        $kl_t = isset($_GET['kl_t']) ? intval($_GET['kl_t']) : 0;
        if ($kl_t == 1) {
            $intval_argu_arr = array('kl_t', 'start_num', 'dis_rows', 'author', 'is_include_img', 'order_style', 'date_style');
        } elseif ($kl_t == 2) {
            $intval_argu_arr = array('kl_t', 'start_num', 'dis_rows', 'em_album', 'order_style', 'date_style');
        } else {
            $intval_argu_arr = array('kl_t', 'sort', 'start_num', 'dis_rows', 'author', 'filter', 'is_include_img', 'nopwd', 'link_style', 'order_style', 'date_style');
        }
        foreach ($intval_argu_arr as $iaav) $module[$iaav] = intval($_GET[$iaav]);
        if ($kl_t != 2) $module['custom_tailor'] = addslashes(trim($_GET['custom_tailor']));
        $output = $this->_mainFunForPreview($module, $_POST['code']);
        $output = implode('', $output);
        if ($output == '') $output = '<font color="red"><b>没有符合条件的记录！</b></font>';
        echo $output;
    }

    /**
     * 插件设置各页面头部
     */
    private function _getHeader()
    {
        echo '<script src="../include/lib/js/common_tpl.js" type="text/javascript"></script>';
        echo sprintf('<script src="%s/jquery.zclip.min.js" type="text/javascript"></script>', $this->_getDirPath('res'));
        echo sprintf('<script type="text/javascript">$("#%s").addClass("sidebarsubmenu1");setTimeout(hideActived,2600);</script>', self::ID);
        echo sprintf('<link rel="stylesheet" href="%s">', $this->_getDirPath('assets') . '/main.css?ver=' . urlencode(self::VERSION));
        echo sprintf('<div class=containertitle><b>%s</b><span style="font-size:12px;color:#999999;">（版本：%s）</span>%s</div>', self::NAME, self::VERSION, $this->_msg);
    }

    /**
     * 外部调用数据的处理方法
     */
    public function callDo()
    {
        $cols = !empty($_GET['COLS']) && intval($_GET['COLS']) > 1 ? intval($_GET['COLS']) : 1;
        $col = !empty($_GET['COL']) && intval($_GET['COL']) > 1 ? $_GET['COL'] : 1;
        $output = isset($_GET['ID']) ? $this->forInternalValue(intval($_GET['ID']), $cols, $col) : '';
        if (trim($output) != '') {
            if (isset($_GET['callback']) && trim($_GET['callback']) == 'html') exit($output);
            $lineArr = explode("\n", $output);
            foreach ($lineArr as $line) {
                if (substr($line, strlen($line) - 1, strlen($line)) == "\n") $line = substr($line, 0, strlen($line) - 1);
                echo 'document.write(\'' . addslashes(trim($line)) . '\');' . "\n";
            }
        }
    }

    /**
     * 内部调用方法
     *
     * @param $id 模板ID
     * @param int $cols 分几列
     * @param int $col 第几列
     */
    public function forInternal($id, $cols = 1, $col = 1)
    {
        echo $this->forInternalValue($id, $cols, $col);
    }
}

/**
 * 内部调用方法（为了兼容老版本的内部调用，在类外面单独写个同名的方法）
 * @param $id 模板ID
 * @param int $cols 分几列
 * @param int $col 第几列
 * @return string
 */
function kl_data_call_for_internal($id, $cols = 1, $col = 1)
{
    return KlDataCall::getInstance()->forInternalValue($id, $cols, $col);
}

KlDataCall::getInstance()->init();
