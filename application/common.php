<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 异常抛出方法封装
 * @param $err_code
 * @param $http_code
 * @param string $msg
 */
function throwError($err_code,$msg='error',$http_code=200){
    response_header($http_code);
    exit(json_encode(['code'=>$err_code , 'message' => $msg]));
}

function response_header($num){
    $http = array (
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
    );
    header($http[$num]);

}

/**
 * 字符串加密
 *
 * @param string $pass
 * @return string
 */
function encryptPass($pass="888888"){
    return md5(sha1($pass));
}

/**
 * 用户的登录或登出
 * 取消session
 *
 * @param $id
 * @param bool $isOver
 * @return bool
 */
function setAccountSess($id = 0 , $isOver=false){

    $userInfo = [
        'id' => $id,
        'token' => uniqid() . rand("10000000", "99999999")
    ];

    session('userInfo', $isOver ? "" : $userInfo );

    return true;
}
function setAdminAccountSess($id = 0 , $isOver=false){

    $adminInfo = [
        'id' => $id,
        'token' => uniqid() . rand("10000000", "99999999")
    ];

    session('adminInfo', $isOver ? "" : $adminInfo );

    return true;
}

/**
 * 时间格式转换
 *
 *
 * @param null $dateTime  （array , string）
 * @return false|null|string
 */
function timeConversion($dateTime = null){
    $dateT = null;
    if(is_string($dateTime)){
        $dateT = date("Y-m-d H:i:s" , strtotime($dateTime));
    }else if(is_array($dateTime)){
        foreach ($dateTime as $key => $value){
            $dateT[$key] = date("Y-m-d H:i:s" , strtotime($dateTime[$key]));
        }
    }
    return $dateT;
}

/**
 * 解压缩
 * @method unzip_file
 * @param  string     $zipName 压缩包名称
 * @param  string     $dest    解压到指定目录
 * @return boolean              true|false
 */
function unzip_file($zipName , $dest){

    //检测要解压压缩包是否存在
    if(!is_file($zipName)){
        return false;
    }

    //检测目标路径是否存在
    if(!is_dir($dest)){
        mkdir($dest,0777,true);
    }

    $zip=new ZipArchive();
    if($zip->open($zipName)){
        $zip->extractTo($dest);
        $zip->close();
        return true;
    }else{
        return false;
    }

}

/**
 * 随机生成订单号
 * head = 01    支付单号
 * head = 08    退款单号
 * @param string $head
 * @return string
 */
function createOrderNumber($head='01'){
    $chars = "0123456789";
    $str ="";
    for ( $i = 0; $i < (32-strlen($head)); $i++ )  {
        $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
    }
    return $head.$str;
}

/**
 * 搜索参数附近的一个参数
 * @param array $arr
 * @param null $str
 * @return array
 */
function getArrayTopNext($arr = [] , $str=null){
    if(empty($arr)){
        return ['',''];
    }
    // 重置键名
    $arr = array_values($arr);

    foreach ($arr as $key => $value){
        if($value == $str){
            $newKey = $key;
            break;
        }
    }

    if(isset($newKey)){
        return [
            isset($arr[$newKey-1]) ? $arr[$newKey-1] : '',
            isset($arr[$newKey+1]) ? $arr[$newKey+1] : ''
        ];
    }

    return ['',''];
}

/**
 * 图片增加域名
 * @param string $imageUrl
 * @return string
 */
function imageSetHead($imageUrl=""){
    if(!(strpos($imageUrl,'http://') !== false || strpos($imageUrl,'https://') !== false)){
        $imageUrl = "https://".config('app.app_host').$imageUrl;
    }
    return $imageUrl;
}

//******************************************************************************************************************//
/**************************************** 发起网络请求 start *********************************************************/
//******************************************************************************************************************//

/**
 * get 请求
 * @param $url
 * @return bool|mixed
 */
function CURL_GET($url){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}

/**
 * post 请求
 * @param $url
 * @param $param
 * @return bool|mixed
 */
function CURL_POST($url,$param){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (is_string($param)) {
        $strPOST = $param;
    } else{
        $aPOST = array();
        foreach($param as $key=>$val){
            $aPOST[] = $key."=".urlencode($val);
        }
        $strPOST =  join("&", $aPOST);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    dump(json_decode($sContent));
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}


//******************************************************************************************************************//
/**************************************** 发起网络请求 end   *********************************************************/
//******************************************************************************************************************//

