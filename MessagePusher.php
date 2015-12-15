<?php
/**
 * Created by PhpStorm.
 * User: yinchao
 * Date: 2015/12/14
 * Time: 16:43
 */
namespace TyMessage;

class MessagePusher{
    protected $app_id = '';
    protected $app_secret = '';
    protected $access_token = '';
    protected $token = '';
    protected $cache_key ='';

    public function __construct($config){
        $this->app_id = $config['app_id'];
        $this->app_secret = $config['app_secret'];
        $this->cache_key = $config['cache_key'];
    }

    /** 获取信任码
     *  调用该接口可以获取到一个临时信任码token（该信任码在调用“验证短信下发”接口时使用），
     *  该信任码在三分钟内或被使用过一次即失效。
     *  http://api.189.cn/v2/dm/randcode/token
     *
     */
    private function refreshToken()
    {
        $url = 'http://api.189.cn/v2/dm/randcode/token?';
        $timestamp = date('Y-m-d H:i:s', time());
        $param['app_id'] = "app_id=" . $this->app_id;
        $param['access_token'] = "access_token=" . $this->access_token;
        $param['timestamp'] = "timestamp=" . $timestamp;

        $url .= $this->getSignature($param);
        $result = $this->curl_get($url);   //获取信任码
        $resultArray = json_decode($result, true);
        $token = $resultArray['token'];
        $this->token = $token;
    }

    /** 生产签名 返回请求的
     * @param array $param
     * @return string
     */
    private function getSignature(Array $param)
    {
        ksort($param, -1);
        $plaintext = implode("&", $param);
        $param['sign'] = "sign=" . rawurlencode(base64_encode(hash_hmac("sha1", $plaintext, $this->app_secret, True)));
        ksort($param, -1);
        return implode("&", $param);

    }

    /** 请求AT
     * @return mixed
     */
    private function doAccessToken()
    {
        $url = 'https://oauth.api.189.cn/emp/oauth2/v2/access_token';  //获取access_token的路径
        $param = 'app_id=' . $this->app_id . '&app_secret=' . $this->app_secret
            . '&grant_type=client_credentials'; //参数
        $result = $this->curl_post($url, $param);  //获取access_token
        $result = json_decode($result, true);
        return $result['access_token'];
    }

    /** 获取Access_token
     * @return mixed
     */
    private function getAccessToken()
    {
        $access_token = \Cache::get($this->cache_key, null); // 从缓存获取ACCESS_TOKEN
        if ($access_token === null) {
            $access_token['token'] = $this->doAccessToken();
            \Cache::put($this->cache_key, $access_token, 150);
        }
        $this->access_token = $access_token['token'];
        return $access_token['token'];
    }

    /** 自定义短信验证码下发
     * @param $phone
     * @param $code
     * @return mixed
     */
    public function sendSms($phone, $code)
    {
        $url = 'http://api.189.cn/v2/dm/randcode/sendSms';
        $this->getAccessToken();
        $this->refreshToken();

        $param['timestamp'] = "timestamp=" . date('Y-m-d H:i:s', time());     //时间戳
        $param['app_id'] = "app_id=" . $this->app_id;
        $param['access_token'] = "access_token=" . $this->access_token;
        $param['token'] = "token=" . $this->token;
        $param['phone'] = "phone=" . $phone;
        $param['randcode'] = "randcode=" . $code;        //自定义的验证码
        $response = $this->curl_post($url, $this->getSignature($param));   //发送验证码
        $resultArray = json_decode($response, true);
        return $resultArray;
    }

    /**
     * 发送模板短信
     * @param $phone
     * @param $template_id
     * @param $template_param
     * @return mixed
     */
    public function sendModelMessage($phone,$template_id, $template_param)
    {
        $url = "http://api.189.cn/v2/emp/templateSms/sendSms";
        $this->getAccessToken();
        $this->refreshToken();

        $param['timestamp'] = "timestamp="  . date('Y-m-d H:i:s', time());    //时间戳
        $param['app_id'] = "app_id=" . $this->app_id;                      //创建应用时的app_id
        $param['access_token'] = "access_token=" . $this->access_token;  //access_token
        $param['acceptor_tel'] = "acceptor_tel=" . $phone;             //要发送模板短信的手机号码
        $param['template_param'] = "template_param=" . json_encode($template_param);
        $param['template_id'] = "template_id=" . $template_id;          //短信模板id
        ksort($param);
        $plaintext = implode("&", $param);
        $param['sign'] = "sign=" . rawurlencode(base64_encode(hash_hmac("sha1", $plaintext, $this->app_secret, $raw_output = True))); //参数签名
        ksort($param);
        $str = implode("&", $param);
        $response = $this->curl_post($url, $str);       //发送模板短信
        $resultArray = json_decode($response, true);
        return $resultArray;
    }

    private function curl_get($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    private function curl_post($url = '', $postdata = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}