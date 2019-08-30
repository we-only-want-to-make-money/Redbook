<?php


namespace app\commands;

use phpspider\core\db;
use phpspider\core\requests;
use yii\console\Controller;
use yii\console\ExitCode;
use phpspider\core\phpspider;
class RedbookController extends Controller
{
    public function getVideoSize($url)
    {
        /*header("Content-Type: video/mp4");
        header("Content-Disposition: attachment;filename=qwe.mp4");*/

        $movie = file_get_contents($url);
        $format_num = sprintf("%.2f",strlen($movie)/1024/1024);
        return $format_num; //10.46
    }
    public function actionIndex1($action, $sessionId, $url, $type)
    {
        \Yii::warning("url:".$url);
        //$url = 'http://t.cn/AiTomEAA';
        $headers = get_headers($url, TRUE);
        \Yii::warning("headers:".json_encode($headers));

        //输出跳转到的网址
        $url = $headers['Location'];
        $url = substr($url, 0, stripos($url, '?'));
        $productId = substr($url, strrpos($url, '/') + 1);
        $files = [];
        if ($type == 0) {
            $files[] = array(
                'name' => "images",
                'selector' => "//ul[@class='slide']//li//span/@style",
                'required' => false,
                'repeated' => true,
            );
        } else if ($type == 1) {
            $files[] = array(
                // 抽取内容页的文章内容
                'name' => "content",
                'selector' => "//div[@class='left-card']//div[@class='content']/p",
                'required' => false,
                'repeated' => true,
            );
        } else if ($type == 2) {
            $files[] = array(
                'name' => "video",
                'selector' => "//div[@class='videoframe']/video[@class='videocontent']/@src",
                'required' => false,
            );
            $files[] = array(
                'name' => "lunimg",
                'selector' => "//div[@class='videoframe']/video[@class='videocontent']/@poster",
                'required' => false,
            );
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
            'proxies' => array(
                'http://36.42.117.23:37761'
            ),
            'db_config' => array(
                'host' => '127.0.0.1',
                'port' => 3306,
                'user' => 'root',
                'pass' => 'hzdz20190424',
                'name' => 'crawler',
            ),
            'fields' => $files,
        );
        $spider = new phpspider($configs);
        $spider->on_start = function ($phpspider) {
            $db_config = $phpspider->get_config("db_config");
            //print_r($db_config);
            //exit;
            // 数据库连接
            db::set_connect('default', $db_config);
            db::_init();
        };

        $spider->on_extract_page = function ($page, $data) use ($productId, $sessionId) {
            if (isset($data['title'])) {
                //echo "<" . $data['title'] . ">";
            }
            if (isset($data['content'])) {
                //echo "<".$data['content'].">";
                $dowloadFile = fopen("/home/wwwroot/default/downloads/" . $sessionId . ".txt", "w");
                $txt = json_encode($data['content']);
                fwrite($dowloadFile, $txt);
                fclose($dowloadFile);
            }
            if (isset($data['video'])) {
                //echo "<" . 'https://'.$data['video'] . ">";
                $dowloadFile = fopen("/home/wwwroot/default/downloads/" . $sessionId . ".txt", "w");
                //$txt = $data['video'];
                $data['video'] = str_replace("http", "https", $data['video']);
                $data['size'] =$this->getVideoSize($data['video']);
                fwrite($dowloadFile, json_encode($data));
                fclose($dowloadFile);
            }
            /*if(isset($data['poster'])){
                echo "<" . $data['poster'] . ">";
                $dowloadFile = fopen("/home/wwwroot/default/downloads/".$sessionId.".txt", "w");
                $txt = $data['video'];
                fwrite($dowloadFile, $txt);
                fclose($dowloadFile);
            }*/
            /* echo "<" . $data['title'] . ">";
             echo "<".$data['content'].">";
             echo "<" . $data['video'] . ">";*/
            $images = [];
            if (isset($data['images'])) {
                \Yii::warning(json_encode($data));
                if($data['images']&&count($data['images'])>0) {
                    foreach ($data['images'] as $item) {
                        $item = str_replace("background-image:url(//", "", $item);
                        $item = str_replace(");", "", $item);
                        $item = 'https://' . $item;
                        //echo json_encode($item).PHP_EOL;
                        $images[] = $item;
                    }
                    if ($data['images']) {
                        //echo json_encode($images);
                        $dowloadFile = fopen("/home/wwwroot/default/downloads/" . $sessionId . ".txt", "w");
                        $txt = json_encode($images);
                        fwrite($dowloadFile, $txt);
                        fclose($dowloadFile);
                    }
                }
            }
            return $data;
        };
        $spider->start();


    }
    public function actionIndex($action, $sessionId, $link, $type){
        //配置信息
        $iiiLabVideoDownloadURL = "http://service.iiilab.com/video/download";   //iiiLab通用视频解析接口
        $client = "7fb57db574e461fb";;   //iiiLab分配的客户ID
        $clientSecretKey = "5e0f03b2ee1405d8b0ed8d99ed962dd9";  //iiiLab分配的客户密钥
        //必要的参数
//        $link = "http://v.douyin.com/DdRo2a/";
//        $link = "https://weibo.com/tv/v/EFSNuE1Ky";
        $timestamp = time() * 1000;
        $sign = md5($link . $timestamp . $clientSecretKey);
        $data = $this->file_get_contents_post($iiiLabVideoDownloadURL, array("link" => $link, "timestamp" => $timestamp, "sign" => $sign, "client" => $client));
        $link_data = json_decode($data,true);
        \Yii::warning("iiilab:".$data);
        if ($link_data['retCode'] != 200) {
            echo json_encode(['code'=>100,'message'=>'视频解析失败！','data'=>['video'=>'']]);
        }else{
            $txt="";
            if($type==0){
                $images = [];
                if($link_data['data']['imgs']&&count($link_data['data']['imgs'])>0) {
                    foreach ($link_data['data']['imgs'] as $item) {
                        $item = 'https:' . $item;
                        //echo json_encode($item).PHP_EOL;
                        $images[] = $item;
                    }
                    if (count($images)>0) {
                        $txt = json_encode($images);
                        \Yii::warning("imgs:".$txt);
                    }
                }

            }else if($type==1){
                $txt = json_encode($link_data['data']['text']);
                \Yii::warning("text:".$txt);
            }else if($type==2){
                $data['video'] = str_replace("http", "https", $link_data['data']['video']);
                $data['size'] =$this->getVideoSize($link_data['data']['video']);
                $data['lunimg']=$link_data['data']['cover'];
                $txt = json_encode($data);
                \Yii::warning("video:".$txt);
            }
            if($txt) {
                $dowloadFile = fopen("/home/wwwroot/default/downloads/" . $sessionId . ".txt", "w");
                fwrite($dowloadFile, $txt);
                fclose($dowloadFile);
            }
        }
    }
    function file_get_contents_post($url, $post) {
        $options = array(
            "http"=> array(
                "method"=>"POST",
                "header" => "Content-type: application/x-www-form-urlencoded",
                "content"=> http_build_query($post)
            ),
        );
        $result = file_get_contents($url,false, stream_context_create($options));
        return $result;
    }
    function actionTest(){
        $url = "https://www.xiaohongshu.com/discovery/item/5d3794cc0000000027039533?xhsshare=CopyLink&appuid=5c0a053e000000000500b9f2&apptime=1566358465";
        requests::set_proxy(array('36.42.117.23:37761'));
        $html = requests::get($url);
        echo $html;
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
