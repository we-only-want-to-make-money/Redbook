<?php


namespace app\commands;

use phpspider\core\db;
use yii\console\Controller;
use yii\console\ExitCode;
use phpspider\core\phpspider;

class RedbookController extends Controller
{
    public function actionIndex($docId)
    {

        $url = 'http://t.cn/AiTomEAA';
        $headers = get_headers($url, TRUE);
        //输出跳转到的网址
        $url= $headers['Location'];
        $url=substr($url,0,stripos($url, '?'));
        $productId=substr($url,strrpos($url, '/')+1);
        {
            $db_config = array(
            'host'  => '127.0.0.1',
            'port'  => 3306,
            'user'  => 'root',
            'pass'  => 'hzdz20190424',
            'name'  => 'crawler',
        );
            db::set_connect('default', $db_config);
            db::_init();
            $doc_data['productId'] = $productId;
            $doc_data['openid'] = "1";
            db::insert("t_redbook_doc", $doc_data);
            $doc_data=[];
        }
        $configs = array(
            'name' => '小红书',
            'domains' => array(
                'xiaohongshu.com',
                'www.xiaohongshu.com'

            ),
            'log_show' => true,
            'log_type' => 'error,debug',

            'scan_urls' => array(
                //'https://www.xiaohongshu.com/discovery/item/5d494d7a000000002801e154'
                //'https://www.xiaohongshu.com/discovery/item/5d399207000000002803955d'
                $url
            ),
            'content_url_regexes' => array(
                //"https://www.xiaohongshu.com/discovery/item/5d494d7a000000002801e154"
                //'https://www.xiaohongshu.com/discovery/item/5d399207000000002803955d'
                $url
            ),
            'db_config' => array(
                'host'  => '127.0.0.1',
                'port'  => 3306,
                'user'  => 'root',
                'pass'  => 'hzdz20190424',
                'name'  => 'crawler',
            ),
            'fields' => array(
                array(
                    // 抽取内容页的文章内容
                    'name' => "content",
                    'selector' => "//div[@class='content']/p",
                    'required' => false
                ),
                array(
                    // 抽取内容页的文章作者
                    'name' => "title",
                    'selector' => "//h1[contains(@class,'title')]",//div[@class='note-image-container']/img",
                    'required' => false
                ),
                // 图片
                array(
                    'name' => "images",
                    'selector' => "//ul[@class='slide']//li//span/@style",
                    'required' => false,
                    'repeated' => true,
                ),
                array(
                    'name' => "video",
                    'selector' => "//div[@class='videoframe']/video[@class='videocontent']/@src",
                    'required' => false,
                ),
            ),
        );
        $spider = new phpspider($configs);
        $spider->on_start = function($phpspider)
        {
            $db_config = $phpspider->get_config("db_config");
            //print_r($db_config);
            //exit;
            // 数据库连接
            db::set_connect('default', $db_config);
            db::_init();
        };

        $spider->on_extract_page = function ($page, $data)use($productId,$docId) {
            /*echo "<" . $data['title'] . ">";
            echo "<".$data['content'].">";*/
            echo "<" . $data['video'] . ">";
            $images=[];
            if($data['images']!=null) {
                foreach ($data['images'] as $item) {
                    $item = str_replace("background-image:url(//", "", $item);
                    $item = str_replace(");", "", $item);
                    //echo json_encode($item).PHP_EOL;
                    $images[] = $item;
                }
            }
            $db_data['content'] = $data['content'];
            $db_data['images'] = json_encode($images);
            $db_data['title'] =$data['title'];
            $db_data['video'] =$data['video'];
            $db_data['productId'] =$productId;
            $sql = "Select Count(*) As `count` From `t_redbook` Where `productId`='$productId'";
            $row = db::get_one($sql);
            if (!$row['count'])
            {
                echo '开始插入数据库'.PHP_EOL;
                db::insert("t_redbook", $db_data);
            }
            $sqldoc= "Select * From `t_redbook_doc` Where `id=`'$docId'";
            $row = db::get_one($sqldoc);
            if($row&&$row['status']==0){
                db::update('t_redbook_doc',['status'=>1],['id'=>$docId]);
            }
            return $data;
        };
        $spider->start();


    }

    /**
     *   * unicode 转 utf-8
     *   *
     *   * @param string $name
     *   * @return string
     *   */
    function myunicode_decode($name)
    {
        $name = strtolower($name);
        // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches)) {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++) {
                $str = $matches[0][$j];
                if (strpos($str, '\\u') === 0) {
                    $code = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c = chr($code) . chr($code2);
                    $c = iconv('UCS-2BE', 'UTF-8', $c);
                    $name .= $c;
                } else {
                    $name .= $str;
                }
            }
        }
        return $name;
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
        $spider->on_extract_field = function ($fieldname, $data, $page) {
            echo "!!!!!!!!!!!!" . json_encode($data) . "!!!!!!!!!!";


            return $data;
        };
        $spider->on_extract_page = function ($page, $data) {

        };
        $spider->start();


    }
}
