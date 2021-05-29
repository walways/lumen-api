<?php
namespace App\Librarys;

use Illuminate\Support\Facades\Log;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Redis;
use App\Librarys\HttpRequest;
use App\Constants\CacheKey\QywxCacheKey;

class QywxLib
{
    private $api_host = 'https://qyapi.weixin.qq.com/cgi-bin';
    private $QYWX_TOKEN;
    private $QYWX_ENCODINGAESKEY;
    private $QYWX_CORPID;
    private $QYWX_PROVIDERSECRET;
    private $QYWX_SUITE_ID;
    private $QYWX_SUITE_SECRET;
    private $QYWX_REDIRECT_URL;
    
    public function __construct()
    {
        $qywx = config('ad.qywx');
        $this->QYWX_TOKEN          = $qywx['QYWX_TOKEN'];
        $this->QYWX_ENCODINGAESKEY = $qywx['QYWX_ENCODINGAESKEY'];
        $this->QYWX_CORPID         = $qywx['QYWX_CORPID'];
        $this->QYWX_PROVIDERSECRET = $qywx['QYWX_PROVIDERSECRET'];

        $this->QYWX_SUITE_ID = $qywx['QYWX_SUITE_ID'];
        $this->QYWX_SUITE_SECRET = $qywx['QYWX_SUITE_SECRET'];
        $this->QYWX_REDIRECT_URL = $qywx['QYWX_REDIRECT_URL'];
        $this->http = (new HttpRequest());
        $this->http->setHost($this->api_host);
    }

    /**
     * 获取服务商token
     * api /get_provider_token
     */
    public function getProviderToken(){
        $cache_key = QywxCacheKey::getProviderTokenCacheKey($this->QYWX_CORPID);
        $token     = Redis::get($cache_key);
        if(!empty($token)){
            return $token;
        }

        $row = ['corpid' => $this->QYWX_CORPID, 'provider_secret' => $this->QYWX_PROVIDERSECRET];
        $res = $this->request('/service/get_provider_token', 'post', $row);
        Redis::set($cache_key, $res['provider_access_token']);
        Redis::expire($cache_key, $res['expires_in'] - 200);
        return $res['provider_access_token'];
    }

    /**
     * 获取第三方应用凭证 suite_access_token
     * api /get_suite_token
     */
    public function getSuiteAccessToken(){

        $suite_ticket_key = QywxCacheKey::getSuiteTicketCacheKey($this->QYWX_SUITE_ID);
        $suite_ticket = Redis::get($suite_ticket_key);
        if(empty($suite_ticket)){
            throw new CustomException('suite_ticket is empty', 400001);
        }

        $cache_key = QywxCacheKey::getSuiteAccessToken($this->QYWX_CORPID);

        $token     = Redis::get($cache_key);
        if(!empty($token)){
            return $token;
        }

        $row = [
            "suite_id" => $this->QYWX_SUITE_ID,
            "suite_secret" => $this->QYWX_SUITE_SECRET, 
            "suite_ticket" => $suite_ticket
        ];
        $api = '/service/get_suite_token';
        $res = $this->request($api, 'post', $row);
        Redis::set($cache_key, $res['suite_access_token']);
        Redis::expire($cache_key, $res['expires_in'] - 200);

        return $res['suite_access_token'];
    }

    /**
     * 获取预授权码
     * api /get_pre_auth_code
     */
    public function getPreAuthCode(){
        $suite_access_token = $this->getSuiteAccessToken();

        $cache_key = QywxCacheKey::getPreAuthCodeCacheKey($this->QYWX_CORPID);

        $token     = Redis::get($cache_key);
        if(!empty($token)){
            return $token;
        }

        $res = $this->request('/service/get_pre_auth_code?suite_access_token=' . $suite_access_token);

        Redis::set($cache_key, $res['pre_auth_code']);
        Redis::expire($cache_key, $res['expires_in'] - 200);
        return $res['pre_auth_code'];
    }


    /**
     * 设置授权配置
     * api /set_session_info
     */
    public function setSessionInfo($auth_type = 1){
        $suite_access_token = $this->getSuiteAccessToken();
        $pre_auth_code = $this->getPreAuthCode();
        
        $row = [
            'pre_auth_code' => $pre_auth_code,
            'session_info' => [
                'auth_type' => $auth_type // 授权类型：0 正式授权， 1 测试授权
            ]
        ];

        $res = $this->request('/service/set_session_info?suite_access_token=' . $suite_access_token, 'post', $row);
        return $res;
    }

    /**
     * 授权跳转地址
     */
    public function getAuthUrl($state = '', $redirect_url = ''){
        if(empty($redirect_url)){
            $redirect_url = $this->QYWX_REDIRECT_URL;
        }
        $redirect_url = urlencode($redirect_url);
        $pre_auth_code = $this->getPreAuthCode();
        $url = "https://open.work.weixin.qq.com/3rdapp/install?suite_id={$this->QYWX_SUITE_ID}&pre_auth_code={$pre_auth_code}&redirect_uri={$redirect_url}&state={$state}";
        return $url;
    }

    /**
     * 获取企业永久授权码
     * api /get_permanent_code
     */
    public function getPermanentCode($auth_code = ''){
        $suite_access_token = $this->getSuiteAccessToken();
        $row = [
            'auth_code' => $auth_code
        ];
        $res = $this->request('/service/get_permanent_code?suite_access_token=' . $suite_access_token, 'post', $row);
        Log::info('qywx_permanent_code: ' . json_encode($res, 320));
        return $res;
    }

    /**
     * 获取access token  40014 invalid access_token
     */
    public function getAccessToken($auth_corpid, $permanent_code, $cache = true){
        $suite_access_token = $this->getSuiteAccessToken();

        $cache_key = QywxCacheKey::getAccessTokenCacheKey($auth_corpid);

        $token     = Redis::get($cache_key);
        if(!empty($token)){
            if($cache){
                return $token;
            }
        }
  
        $row = [
            'auth_corpid' => $auth_corpid,
            'permanent_code' => $permanent_code,
        ];

        $res = $this->request('/service/get_corp_token?suite_access_token=' . $suite_access_token, 'post', $row);

        Redis::set($cache_key, $res['access_token']);
        Redis::expire($cache_key, $res['expires_in'] - 200);
        return $res['access_token'];
    }

     /**
     * 获取通讯录 access token
     * api /gettoken
     */
    public function getBookAccessToken($auth_corpid, $corpsecret = '', $cache = true){

        $cache_key = QywxCacheKey::getBookAccessTokenCacheKey($auth_corpid);
    
        $token     = Redis::get($cache_key);
        if(!empty($token)){
            if($cache){
                return $token;
            }
        }
        $res = $this->request('/gettoken?corpid=' . $auth_corpid . '&corpsecret=' . $corpsecret);
        Redis::set($cache_key, $res['access_token']);
        Redis::expire($cache_key, $res['expires_in'] - 200);
        return $res['access_token'];
    }

    /**
     * 获取部门列表
     * api /department/list
     */

     public function departmentList($access_token, $id = 0){
        if(empty($access_token)){
            return [];
        }

        $api = '/department/list?access_token=' . $access_token;
        if(!empty($id)){
            $api .= '&id=' . $id;
        }
        $res = $this->request($api);
        $res = $this->dataFormat($res);
        return $res;
    }

    /**
     * 获取部门成员
     * api /user/simplelist
     */

    public function userSimpleList($access_token, $department_id = 0, $fetch_child = 0){
        if(empty($access_token)){
            return [];
        }

        $api = '/user/simplelist?access_token=' . $access_token . '&department_id=' . $department_id . '&fetch_child=' . $fetch_child;
        $res = $this->request($api);
        $res = $this->dataFormat($res);
        return $res;
    }


    /**
     * 获取标签列表
     * api /tag/get
     */

    public function tagList($access_token){
        if(empty($access_token)){
            return [];
        }

        $api = '/tag/list?access_token=' . $access_token;
        $res = $this->request($api);
        $res = $this->dataFormat($res);
        return $res;
    }

    /**
     * 获取标签成员
     * api /tag/list
     */

    public function tagGet($access_token, $tagid = 0){
        if(empty($access_token)){
            return [];
        }

        $api = '/tag/get?access_token=' . $access_token . '&tagid=' . $tagid;
        if(!empty($id)){
            $api .= '&id=' . $id;
        }
        $res = $this->request($api);
        $res = $this->dataFormat($res);
        return $res;
    }

    /**
     * 获取成员详情
     * api /user/get
     */
    public function userGet($access_token, $userid = ''){
        if(empty($access_token)){
            return [];
        }

        $api = '/user/get?access_token=' . $access_token . '&userid=' . $userid;
        $res = $this->request($api);
        $res = $this->dataFormat($res);
        return $res;
    }

    /**
     * 发送消息
     * api /user/get
     */
    public function messageSend($access_token, $params = []){
        if(empty($access_token)){
            return [];
        }        
        $api = '/message/send?access_token=' . $access_token;
        $params = $this->converData($params);
        $res = $this->request($api, 'post', $params);
        $res = $this->dataFormat($res);
        return $res;
    }

    private function converData($params = []){
        if(isset($params['touser']) && is_array($params['touser'])){
            $params['touser'] = join('|', $params['touser']);
        }

        if(isset($params['toparty']) && is_array($params['toparty'])){
            $params['toparty'] = join('|', $params['toparty']);
        }

        if(isset($params['totag']) && is_array($params['totag'])){
            $params['totag'] = join('|', $params['totag']);
        }
        return $params;
    }
    
    /**
     * 客户联系人群
     * api /externalcontact/groupchat/list
     */
    public function exGroupchatList($access_token, $params = []){
        if(empty($access_token)){
            return [];
        }
        $api = '/externalcontact/groupchat/list?access_token=' . $access_token;
        $res = $this->request($api, 'post', $params);
        $res = $this->dataFormat($res);
        return $res;
    }

     /**
     * 客户群详情
     * api /externalcontact/groupchat/get
     */
    public function exGroupchatGet($access_token, $chat_id = ''){
        if(empty($access_token)){
            return [];
        }
        $params = [
            'chat_id' => $chat_id,
        ];
        $api = '/externalcontact/groupchat/get?access_token=' . $access_token;
        $res = $this->request($api, 'post', $params);
        $res = $this->dataFormat($res);
        return $res;
    }

    /**
     * 客户群发
     */
    public function exGroupSending($access_token, $params = []){
        if(empty($access_token)){
            return [];
        }
        $params = [
            'chat_type' => 'single',
            'sender' => 'em-wxn',
            'text' => [
                'content' => '接口客户群发测试'
            ],
        ];
        $api = '/externalcontact/add_msg_template?access_token=' . $access_token;
        $res = $this->request($api, 'post', $params);
        $res = $this->dataFormat($res);
        return $res;
    }
    

    /**
     * 格式化数据
     */
    private function dataFormat($res = []){
        unset($res['errcode'], $res['errmsg']);
        return $res;
    }

    /**
     * http请求
     */
    private function request($api, $method = 'get', $body = []){
        switch($method) {
            case 'get':
                $res = $this->http->get($api);
                break;
            case 'post':
                $res = $this->http->post($api, $body);
                break;
        }

        if(isset($res['errcode']) && $res['errcode'] !== 0){
            // invalid access_token
            // if($res['errcode'] == 40014){

            // }
            Log::error('qywx_api_error: ', func_get_args());
            throw new CustomException($res['errmsg'], $res['errcode']);
        }

        return $res;
    }

}
