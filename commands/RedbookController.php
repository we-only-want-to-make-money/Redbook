<?php


namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use phpspider\core\phpspider;

class RedbookController extends Controller
{
    public function actionIndex()
    {

        $configs = array(
            'name' => '小红书',
            'domains' => array(
                'xiaohongshu.com',
                'www.xiaohongshu.com'

            ),
            'log_show' => true,
            'log_type' => 'error,debug',

            'scan_urls' => array(
                'https://www.xiaohongshu.com/discovery/item/5d494d7a000000002801e154'

            ),
            'content_url_regexes' => array(
                "https://www.xiaohongshu.com/discovery/item/5d494d7a000000002801e154"
            ),
            /*'list_url_regexes' => array(
                "http://www.qiushibaike.com/8hr/page/\d+\?s=\d+"
            ),*/
            'fields' => array(
                /*array(
                    // 抽取内容页的文章内容
                    'name' => "article_content",
                    'selector' => "//*[@id='single-next-link']",
                    'required' => true
                ),*/
                array(
                    // 抽取内容页的文章作者
                    'name' => "article_author",
                    'selector' => "//title",
                    'required' => true
                ),
            ),
        );
        $spider = new phpspider($configs);
        $spider->on_download_attached_page = function ($content, $phpspider) {
            echo "on_download_attached_page";
            $content = trim($content);
            $content = ltrim($content, "[");
            $content = rtrim($content, "]");
            $content = json_decode($content, true);
            return $content;
        };
        $spider->on_extract_field = function ($fieldname, $data, $page) {
            echo "!!!!!!!!!!!!" . json_encode($data) . "!!!!!!!!!!";
            if ($fieldname == 'gender') {
                // data中包含"icon-profile-male"，说明当前知乎用户是男性
                if (strpos($data, "icon-profile-male") !== false) {
                    return "男";
                } // data中包含"icon-profile-female"，说明当前知乎用户是女性
                elseif (strpos($data, "icon-profile-female") !== false) {
                    return "女";
                } else {
                    return "未知";
                }
            }

            return $data;
        };
        $spider->on_extract_page = function ($page, $data) {

        };
        $spider->start();


    }
    function decodeUnicode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ),
            $str);
    }
    public function actionTest()
    {

        $configs = array(
            'name' => '糗事百科',
            'domains' => array(
                /*'xiaohongshu.com',
                'www.xiaohongshu.com'*/
                'shcydy.com',
                'www.shcydy.com'
            ),
            'log_type' => 'error,debug',

            'scan_urls' => array(
                // 'https://www.xiaohongshu.com/discovery/item/5d494d7a000000002801e154?xhsshare=CopyLink&appuid=5c0a053e000000000500b9f2&apptime=1565158031'
                'http://www.shcydy.com/'
            ),
            /*'content_url_regexes' => array(
                "https://www.xiaohongshu.com/discovery/item/5d494d7a000000002801e154?xhsshare=CopyLink&appuid=5c0a053e000000000500b9f2&apptime=1565158031"
            ),*/
            /*'list_url_regexes' => array(
                "http://www.qiushibaike.com/8hr/page/\d+\?s=\d+"
            ),*/
            'fields' => array(
                /*array(
                    // 抽取内容页的文章内容
                    'name' => "article_content",
                    'selector' => "//*[@id='single-next-link']",
                    'required' => true
                ),*/
                array(
                    // 抽取内容页的文章作者
                    'name' => "article_author",
                    'selector' => "//title",
                    'required' => true
                ),
            ),
        );
        $spider = new phpspider($configs);
        $spider->on_download_attached_page = function ($content, $phpspider) {
            echo "on_download_attached_page";
            $content = trim($content);
            $content = ltrim($content, "[");
            $content = rtrim($content, "]");
            $content = json_decode($content, true);
            return $content;
        };
        $spider->on_extract_field = function ($fieldname, $data, $page) {
            echo "!!!!!!!!!!!!" . json_encode($data) . "!!!!!!!!!!";
            if ($fieldname == 'gender') {
                // data中包含"icon-profile-male"，说明当前知乎用户是男性
                if (strpos($data, "icon-profile-male") !== false) {
                    return "男";
                } // data中包含"icon-profile-female"，说明当前知乎用户是女性
                elseif (strpos($data, "icon-profile-female") !== false) {
                    return "女";
                } else {
                    return "未知";
                }
            }

            return $data;
        };
        $spider->on_extract_page = function ($page, $data) {

        };
        $spider->start();


    }
}
