<?php


namespace app\controllers;


use yii\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class RedbookController  extends Controller
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $url="http://v6-dy.ixigua.com/5dd5a088eeceea4a41270bde1136247c/5d6e3558/video/m/220bbcf37ade188491a8eb533359a44147f1163554a000000fa6bc7580dc/?a=1128&br=1026&cr=0&cs=0&dr=0&ds=1&er=&l=2019090316413601015505721925262F&lr=&rc=M2xvZzppZm8zbzMzOmkzM0ApNzY1PDk8NjszN2lpOTQ3aWcwZW9eMGQubmdfLS1hLS9zczEyYDAtLWAwXl4wYTA0YTA6Yw%3D%3D";
        $path='/home/wwwroot/default/we7/attachment/videos/';
        $this->downFile($url,$path);
    }
    function downFile($url,$path){
        $arr=parse_url($url);
        $fileName=basename($arr['path']);
        $file=file_get_contents($url);
        file_put_contents($path.$fileName,$file);
    }
    public function actionTest(){
        $link =$_GET['link'];
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
        return $data;
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
}

