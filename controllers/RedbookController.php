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

        $ch = curl_init();
        $url="https://www.xiaohongshu.com/discovery/item/5d494d7a000000002801e154?xhsshare=CopyLink&appuid=5c0a053e000000000500b9f2&apptime=1565158031";
        curl_setopt ( $ch , CURLOPT_USERAGENT ,"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.113 Safari/537.36" );
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content=curl_exec($ch);
        $string=file_get_contents($url);
        preg_match_all("/<img([^>]*)\s*src=('|\")([^'\"]+)('|\")/",
            $string,$matches);
        $new_arr=array_unique($matches[3]);
        foreach($new_arr as $key){
            echo "<img src=$key>";
        }
       return 'success';
    }
}
