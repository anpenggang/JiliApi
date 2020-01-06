<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/5/23
 * Time: 17:20
 */

class Encode {

    protected static $code = "abcdefghigklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    // 字符串加密
    public static function encrypt($string)
    {
        $key = self::$code;
        $key=md5($key);
        $key_length=strlen($key);
        $string=substr(md5($string.$key),0,20).$string;
        $string_length=strlen($string);
        $rndkey=$box=array();
        $result='';
        for($i=0;$i<=255;$i++){
            $rndkey[$i]=ord($key[$i%$key_length]);
            $box[$i]=$i;
        }
        for($j=$i=0;$i<256;$i++){
            $j=($j+$box[$i]+$rndkey[$i])%256;
            $tmp=$box[$i];
            $box[$i]=$box[$j];
            $box[$j]=$tmp;
        }
        for($a=$j=$i=0;$i<$string_length;$i++){
            $a=($a+1)%256;
            $j=($j+$box[$a])%256;
            $tmp=$box[$a];
            $box[$a]=$box[$j];
            $box[$j]=$tmp;
            $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
        }

        return str_replace('=','',base64_encode($result));
    }

    // 字符串解密
    public static function decrypt($string)
    {
        // 替换字符串中的空格为+号
        $string = str_replace(" " , "+" , $string);

        $key = self::$code;
        $key=md5($key);
        $key_length=strlen($key);
        $string=base64_decode($string);
        $string_length=strlen($string);
        $rndkey=$box=array();
        $result='';
        for($i=0;$i<=255;$i++){
            $rndkey[$i]=ord($key[$i%$key_length]);
            $box[$i]=$i;
        }
        for($j=$i=0;$i<256;$i++){
            $j=($j+$box[$i]+$rndkey[$i])%256;
            $tmp=$box[$i];
            $box[$i]=$box[$j];
            $box[$j]=$tmp;
        }
        for($a=$j=$i=0;$i<$string_length;$i++){
            $a=($a+1)%256;
            $j=($j+$box[$a])%256;
            $tmp=$box[$a];
            $box[$a]=$box[$j];
            $box[$j]=$tmp;
            $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
        }

        if(substr($result,0,20)==substr(md5(substr($result,20).$key),0,20)){
            return substr($result,20);
        }else{
            return '';
        }
    }

}