<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/8/6
 * Time: 17:51
 */

namespace WechatApp;

class WeApp
{

    const WEB = "https://api.weixin.qq.com";        // 域名
    const SESSION_URL = "/sns/jscode2session";      // 获取sessionKey
    const TOKEN_URL = "/cgi-bin/token";             // 获取accessToken

    protected $appId = '';
    protected $appSecret = '';

    public function __construct()
    {

        $this->appId = config('WechatApp.appId');
        $this->appSecret = config('WechatApp.appSecret');

    }

    /**
     * 根据JS_code 获取用户信息
     * @param $code
     * @return array
     */
    public function getSessionKey($code){
        $param = [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];

        $url = self::WEB.self::SESSION_URL."?".http_build_query($param);
        $INFO = CURL_GET($url);

        if($INFO_ARRAY = json_decode($INFO , true)){

            if(isset($INFO_ARRAY['openid']) && isset($INFO_ARRAY['session_key'])){
                return [true , ['data'=>$INFO_ARRAY]];
            }

            return [false , ['error'=>'解析失败 error 1' , 'data'=>$INFO_ARRAY]];
        }

        return [ false , ['error'=>'解析失败 error 2' , 'data'=>$INFO]];
    }

    /**
     * 获取接口调用凭证
     * @return array
     */
    public function getAccessToken(){
        $param = [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'client_credential'
        ];

        $url = self::WEB.self::SESSION_URL."?".http_build_query($param);
        $INFO = CURL_GET($url);

        if($INFO_ARRAY = json_decode($INFO , true)){

            if(isset($INFO_ARRAY['errcode']) && $INFO_ARRAY['errcode']=="0"){
                return [true , ['data'=>$INFO_ARRAY]];
            }

            return [false , ['error'=>'解析失败 error 1' , 'data'=>$INFO_ARRAY]];
        }

        return [ false , ['error'=>'解析失败 error 2' , 'data'=>$INFO]];
    }

    /**
     * 解密用户信息
     * @param $code
     * @param $iv
     * @param $encryptedData
     * @return array
     */
    public function DeInfo($code , $iv , $encryptedData){

        // 获取sessionKey
        list($sessionStatus , $data) = $this->getSessionKey($code);
        if(!$sessionStatus){
            return [false , $data];
        }
        $sessionKey = $data['data']['session_key'];
        $openId = $data['data']['openid'];

        // 解密用户信息
        $wxBizData = new WXBizDataCrypt($this->appId , $sessionKey);
        $data = null;
        $status = $wxBizData->decryptData($encryptedData , $iv , $data);
        if($status != "0"){
            return [false , ['error'=>"错误：{$status}" , 'data'=>$data]];
        }

        $data = json_decode($data , true);
        if(!isset($data['openId'])){
            $data['openId'] = $openId;
        }

        return ['true' , ['data'=>$data]];
    }


}

/**
 * 用户数据解密
 * Class WXBizDataCrypt
 * @package WechatApp
 */
class WXBizDataCrypt
{
    private $appid;
    private $sessionKey;

    /**
     * 构造函数
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    public function __construct( $appid, $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $encryptedData, $iv, &$data )
    {
        if (strlen($this->sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey=base64_decode($this->sessionKey);


        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return ErrorCode::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != $this->appid )
        {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result;
        return ErrorCode::$OK;
    }

}



/**
 * error code 说明.
 * <ul>

 *    <li>-41001: encodingAesKey 非法</li>
 *    <li>-41003: aes 解密失败</li>
 *    <li>-41004: 解密后得到的buffer非法</li>
 *    <li>-41005: base64加密失败</li>
 *    <li>-41016: base64解密失败</li>
 * </ul>
 */
class ErrorCode
{
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;
}


