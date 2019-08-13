<?php


namespace app\commands;

use phpspider\core\db;
use yii\console\Controller;
use yii\console\ExitCode;
use phpspider\core\phpspider;

class RedbookController extends Controller
{
    public function actionIndex($action,$docId,$url,$type)
    {
        //$url = 'http://t.cn/AiTomEAA';
        $headers = get_headers($url, TRUE);
        //输出跳转到的网址
        $url= $headers['Location'];
        $url=substr($url,0,stripos($url, '?'));
        $productId=substr($url,strrpos($url, '/')+1);
        $files=[];
        if($type=0){
            $files[]=array(
                'name' => "images",
                'selector' => "//ul[@class='slide']//li//span/@style",
                'required' => false,
                'repeated' => true,
            );
        }else if($type=1){
            $files[]=   array(
                // 抽取内容页的文章内容
                'name' => "content",
                'selector' => "//div[@class='content']/p",
                'required' => false
            );
        }else if($type=2){
            $files[]=array(
                'name' => "video",
                'selector' => "//div[@class='videoframe']/video[@class='videocontent']/@src",
                'required' => false,
            );
            $files[]=array(
                'name' => "poster",
                'selector' => "//div[@class='videoframe']/video[@class='videocontent']/@poster",
                'required' => false,
            );
        }
        echo  json_encode($files)
        {
            $db_config = array(
            'host'  => '127.0.0.1',
            'port'  => 3306,
            'user'  => 'root',
            'pass'  => 'hzdz20190424',
            'name'  => 'crawler',
        );
            /*db::set_connect('default', $db_config);
            db::_init();
            $doc_data['id']=microtime()*1000;
            $doc_data['productId'] = $productId;
            $doc_data['openid'] = "1";
            $docId=db::insert("t_redbook_doc", $doc_data);
            echo 'docId:  '.$docId.PHP_EOL;
            $doc_data=[];*/
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
                $url
            ),
            'content_url_regexes' => array(
                $url
            ),
            'db_config' => array(
                'host'  => '127.0.0.1',
                'port'  => 3306,
                'user'  => 'root',
                'pass'  => 'hzdz20190424',
                'name'  => 'crawler',
            ),
            'fields' => $files,
            /*array(
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
            ),*/
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
            if(isset($data['title'])){
                echo "<" . $data['title'] . ">";
            }
            if(isset($data['content'])){
                echo "<".$data['content'].">";
            }
            if(isset($data['video'])){
                echo "<" . 'https://'.$data['video'] . ">";
            }
            if(isset($data['poster'])){
                echo "<" . $data['poster'] . ">";

            }
           /* echo "<" . $data['title'] . ">";
            echo "<".$data['content'].">";
            echo "<" . $data['video'] . ">";*/
            $images=[];
            if(isset($data['images'])) {
                foreach ($data['images'] as $item) {
                    $item = str_replace("background-image:url(//", "", $item);
                    $item = str_replace(");", "", $item);
                    $item = 'https://'.$item;
                    //echo json_encode($item).PHP_EOL;
                    $images[] = $item;
                }
                if($data['images']){
                    echo json_encode($images);
                }
            }
            //echo json_encode($images);

           /* $db_data['content'] = $data['content'];
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
            $sqldoc= "Select * From `t_redbook_doc` Where `id=`.$docId";
            $row = db::get_one($sqldoc);
            if($row&&$row['status']==0){
                db::update('t_redbook_doc',['status'=>1],['id'=>$docId]);
            }*/
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



}
