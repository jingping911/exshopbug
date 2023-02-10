<?php

namespace App\Api;

use App\Lib\User as UserLib;
use App\Model\Token as TokenModel;
use App\Model\User as UserModel;
use App\Model\Vcode as VcodeModel;
use App\Model\Payment as PaymentModel;
use PhalApi\Api;
use App\Model\Payment;
use PhalApi\Exception\BadRequestException;

/**
 * 用户模块接口服务
 */
class Passport extends Api
{
    protected $domain;
    protected $code;
    public function __construct()
    {
        $this->model = new UserModel();
        $this->token_model = new TokenModel();
        $this->vcode_model = new VcodeModel();
        $this->Userlib = new UserLib();
        $this->pass_model = new PaymentModel();
    }

    public function getRules()
    {
        return array(
            'logincommonAccountApi' => array(
                'username' => array('name' => 'username', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户名'),
                'password' => array('name' => 'password', 'require' => true, 'min' => 6, 'max' => 20, 'desc' => '密码'),
            ),
            'registerPhoneApi' => array(
                'user_name' => array('name' => 'user_name', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户名'),
                'password' => array('name' => 'password', 'require' => true, 'min' => 6, 'max' => 20, 'desc' => '密码'),
                'vcode' => array('name' => 'vcode', 'require' => true, 'min' => 1, 'max' => 6, 'desc' => '验证码'),
                'platform' => array('name' => 'platform', 'require' => true, 'min' => 1, 'max' => 10, 'desc' => '来源'),
                'recode' => array('name' => 'recode', 'require' => false, 'min' => 0, 'max' => 6, 'desc' => '邀请码'),
                'parent_id' => array('name' => 'parent_id', 'require' => false, 'min' => 0, max => 9, 'desc' => '绑定的id')
            ),
            'checkLoginApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'token'),
            ),
            'forgetPhoneApi' => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '手机号'),
                'password' => array('name' => 'passData', 'require' => true, 'min' => 6, 'max' => 20, 'desc' => '新密码'),
                'vcode' => array('name' => 'verCode', 'require' => true, 'min' => 1, 'max' => 6, 'desc' => '验证码'),
            ),
            'logincommonPhoneApi' => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '手机号'),
                'vcode' => array('name' => 'verCode', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '验证码'),
            ),
            "checkWechatRegApi" => array(
                "openid" => array("name" => "openid", "require" => true, "min" => 1, "desc" => "openid"),
                "access_token" => array("name" => "access_token", "require" => true, "min" => 1, "desc" => "access_token"),
                "platform" => array("name" => "platform", "require" => false, "min" => 1, "desc" => "平台类型")
            ),
            "bindingWechatApi" => array(
                "mobile" => array("name" => "mobile", "require" => true, "min" => 1, "desc" => "手机号"),
                "verCode" => array("name" => "verCode", "require" => true, "min" => 1, "desc" => "验证码"),
                "openId" => array("name" => "openId", "require" => true, "min" => 1, "desc" => "openid"),
                "platform" => array("name" => "platform", "require" => false, "min" => 1, "desc" => "平台类型"),
                "unionid" => array("name" => "unionid", "require" => false, "min" => 0, "desc" => "unionid")
            ),
            "nextBingdingMobile" => array(
                "openid" => array("name" => "openid", "require" => true, "min" => 1, "desc" => "openid"),
                "platform" => array("name" => "platform", "require" => false, "min" => 1, "desc" => "平台类型"),
                "unionid" => array("name" => "unionid", "require" => false, "min" => 0, "desc" => 'unionid')
            ),
            "bangdingMobile" => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户ID'),
                "mobile" => array("name" => "mobile", "require" => true, "min" => 1, "desc" => "手机号"),
                "verCode" => array("name" => "verCode", "require" => true, "min" => 1, "desc" => "验证码"),
                "platform" => array("name" => "platform", "require" => false, "min" => 1, "desc" => "平台类型")
            ),
            "getSetting" => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户ID'),
            ),
            "loginWechatApi" => array(
                "code" => array("name" => "code", "require" => true, "min" => 1, "desc" => "code"),
                "platform" => array("name" => "platform", "require" => false, "min" => 1, "desc" => "平台类型")
            ),
            "getWxAuthApi" => array(),
            "getWXUserInfoApi" => array(
                "code" => array("name" => "code", "require" => true, "min" => 1, "desc" => "code")
            ),
            "wechatAppLogin" => array(
                "access_token" => array("name" => "access_token", "require" => true),
                "openid" => array("name" => "openid", "require" => true),
                "unionid" => array("name" => "unionid", "require" => true),
                "platform" => array("name" => "platform", "require" => true),
            ),
            "appleAccountLoginApi" => array(
                "openid" => array("name" => "openid", "require" => false),
                "code"  => array("name" => "code", "require" => false),
                "token" => array("name" => "token", "require" => false)
            )
        );
    }

    /**
     * 普通注册接口
     * @desc 普通注册
     * @return array

     */
    public function registerPhoneApi()
    {
        $user_name = $this->user_name; // 账号参数
        $password = $this->password; // 密码参数
        $vcode = $this->vcode; // 密码参数
        $platform = $this->platform;
        $parent_id = $this->parent_id;
        $recode = $this->recode;

        if (!$this->vcode_model->check_vcode($user_name, $vcode)) {
            return array('is_register' => false, 'msg' => '验证码错误');
        }


        $user_check = $this->Userlib->check_mobile_register($user_name);
        if (!$user_check) {
            return array('is_register' => false, 'msg' => $this->Userlib->msg);
            throw new BadRequestException(\PhalApi\T($this->Userlib->msg));
            //return false;
        }


        $user_data = $this->model->mobile_register($user_name, $password, $platform, $parent_id, $recode);

        if ($user_data['is_register'] === false) {
            return  $user_data;
        }

        $sess_id = $this->token_model->set_sess($user_data);

        return array('is_register' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id);
    }

    /**
     * 手机注册接口
     * @desc 手机注册接口
     * @return array
     */
    public function sms_register()
    {
        $user_name = $this->user_name; // 账号参数

        $user_check = $this->vcode_model->check_vcode($user_name, $vcode);
        if (!$user_check) {
            return array('is_register' => false, 'msg' => $model->msg);
            //return false;
        }

        $user_data = $model->register($user_name);

        $sess_id = $this->token_model->set_sess($user_data);

        return array('is_login' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id);
    }

    /**
     * 忘记密码
     * @desc 根据手机和验证码进行重置密码操作
     * @return boolean is_login 是否登录成功
     * @return int user_id 用户ID, int sess_id
     */
    public function forgetPhoneApi()
    {
        $user_name = $this->mobile; // 账号参数
        $password = $this->password; // 密码参数
        $vcode = $this->vcode; // 密码参数
        $user_check = $this->Userlib->check_mobile_register($user_name);
        /**
        判断账号是否存在
         **/
        if ($user_check) {
            return array('is_lost' => false, 'msg' => 'mobile_error');
        }

        if (!$this->vcode_model->check_vcode($user_name, $vcode)) {
            return array('is_lost' => false, 'msg' => $this->vcode_model->msg);
        }
        /**
        更新密码
         **/
        $this->model->update_password($user_name, $password);
        /**
        重新登录
         **/
        $user_data = $this->model->sms_login($user_name, $password);
        $sess_id = $this->token_model->set_sess($user_data);

        return array('is_lost' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id);
    }

    /**
     * 登录接口
     * @desc 根据手机和密码进行登录操作
     * @return boolean is_login 是否登录成功
     * @return int user_id 用户ID, int sess_id
     */
    public function logincommonAccountApi()
    {
        //        var_dump(123);exit;
        $username = $this->username; // 账号参数
        $password = $this->password; // 密码参数

        $model = new UserModel();
        $user_data = $model->sms_login($username, $password);
        //        var_dump($user_data);exit;
        if (!$user_data) { //手机号登录不成功 尝试用户名登录
            $user_data = $model->username_login($username, $password);
        }
        if (!$user_data) {
            return array('is_login' => false, 'msg' => 'login error', 'user_id' => '');
        }
        // 更多其他操作……

        $sess_id = $this->token_model->set_sess($user_data);

        return array('is_login' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id);
    }

    /**
     * 邮箱登录接口
     * @desc 根据邮箱和密码进行登录操作
     * @return boolean is_login 是否登录成功
     * @return int user_id 用户ID, int sess_id
     */
    public function email_login()
    {
        $username = $this->username; // 账号参数
        $password = $this->password; // 密码参数

        $model = new UserModel();
        $user_data = $model->email_login($username, $password);
        if (!$user_data) {
            return array('is_login' => false, 'user_id' => '');
        }
        // 更多其他操作……

        $sess_id = $this->token_model->set_sess($user_data);

        return array('is_login' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id);
    }

    /**
     * 用户名登录接口
     * @desc 根据用户名和密码进行登录操作
     * @return boolean is_login 是否登录成功
     * @return int user_id 用户ID, int sess_id
     */
    public function username_login()
    {
        $username = $this->username; // 账号参数
        $password = $this->password; // 密码参数

        $model = new UserModel();
        $user_data = $model->username_login($username, $password);
        if (!$user_data) {
            return array('is_login' => false, 'user_id' => '');
        }
        // 更多其他操作……

        $sess_id = $this->token_model->set_sess($user_data);

        return array('is_login' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id);
    }

    /**
     * 判断登录状态
     * @desc 判断登录状态
     * @return boolean is_login 是否登录成功
     * @return string user_id, string token
     */
    public function checkLoginApi()
    {
        $user_id = $this->user_id; // 账号参数
        $token = $this->token; // 密码参数

        $user_data = $this->token_model->get_sess($token, $user_id);
        if (!$user_data) {
            return array('is_login' => true, 'msg' => '');
        }
        // 更多其他操作……
        return array('is_login' => false, 'msg' => '');
    }

    /**
     * 忘记密码
     * @desc 忘记密码
     * @return array
     */
    public function forgetPassword()
    {
        return ['忘记密码'];
    }

    /**
     * 短信验证码登录
     * @desc 验证码登录
     */
    public function logincommonPhoneApi()
    {
        $mobile = $this->mobile; // 手机号
        $vcode = $this->vcode; // 验证码

        if (!$this->vcode_model->check_vcode($mobile, $vcode)) {
            return array('is_login' => false, 'msg' => $this->vcode_model->msg);
        }

        $model = new UserModel();
        $user_data = $model->vcodeLogin($mobile);

        if (!$user_data) {
            $user_data = $this->model->mobile_register($mobile, $mobile, 'H5', '', '');
//            return array('is_login' => false, 'user_id' => '');
        }

        $sess_id = $this->token_model->set_sess($user_data);
        return array('is_login' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id);
    }

    /**
     * 检测微信是否已注册
     * @desc 检测该微信是否已注册
     */
    public function checkWechatRegApi()
    {
        $openid = $this->openid;
        $access_token = $this->access_token;
        $platform = $_POST['platform'];

        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . ""; // 获取相应用户的头像及昵称
        $wechat_msg = json_decode($this->getWechatMsg($url));

        $res = $this->model->checkWechatReg('', $platform, $openid);   // 需要判断一下平台

        if ($res['res'] === true) { // 已注册 直接登录
            $user_data = $this->model->wechatLogin('', $platform, $openid);
            $sess_id = $this->token_model->set_sess($user_data);

            return array('res' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id, "nickname" => $wechat_msg->nickname, 'avatar_url' => $wechat_msg->headimgurl);
        }
        $res['nickname'] = $wechat_msg->nickname;
        $res['avatar_url'] = $wechat_msg->headimgurl;
        return $res; // 没有注册
    }

    /**
     * 获取微信信息
     * @desc 请求获取微信信息
     */
    public function getWechatMsg($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:26.0) Gecko/20100101 Firefox/26.0");
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
    /**
     * 绑定微信
     * @desc 绑定微信
     */
    public function bindingWechatApi()
    {
        $mobile = $this->mobile;
        $vcode = $this->verCode;
        $openid = $this->openId;
        $platform = $this->platform;
        $unionid = $this->unionid;

        // 首先检测该手机号是否注册
        $user_check = $this->Userlib->check_mobile_register($mobile);
        if ($user_check) {
            // 尚未注册  则进行注册绑定操作

            $res = $this->model->bindWechat($mobile, $vcode, $openid, "reg", $platform, $unionid);   // 进行注册绑定操作
            if (!$res) {
                return array('res' => false, "msg" => "验证码不正确");
            }
            $sess_id = $this->token_model->set_sess($res);
            return array('res' => true, 'user_id' => $res['user_id'], 'sess_id' => $sess_id, 'openId' => $res['openid_mp']);
        } else {
            // 已经注册过了  直接进行绑定操作

            $res = $this->model->bindWechat($mobile, $vcode, $openid, "bind", $platform, $unionid);
            // 绑定完成之后就进行登录操作
            if (!$res) {
                return array('res' => false, "msg" => "验证码不正确");
            }
            $user_data = $this->model->wechatLogin($unionid, $platform, $openid);

            $sess_id = $this->token_model->set_sess($user_data);

            return array('res' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id, 'openId' => $user_data['openid_mp']);
        }

        return $res;

        // 没有注册则进行注册操作
    }

    /**
     * @return array|string
     * 跳过手机号绑定
     */
    public function nextBingdingMobile()
    {
        $openid = $this->openid;
        $platform = $this->platform;   // 平台类型
        $unionid = $this->unionid;
        $data['user_id'] = $this->model->nextBingdingMobileModel($openid, $platform, $unionid);
        $res = $this->model->getData($data);
        $data['sess_id'] = $this->token_model->set_sess($res);
        return $data;
    }


    /**
     * @return bool[]
     * 绑定手机号
     */
    public function bangdingMobile()
    {
        $user_id = $this->user_id;
        $mobile = $this->mobile;
        $verCode = $this->verCode;
        $platform = $this->platform;
        $user_data = $this->model->bangdingMobileModel($user_id, $mobile, $verCode, $platform);
        return array('res' => true);
    }

    public function getSetting()
    {
        $user_id = $this->user_id;
        $user_data = $this->model->getSettingModel($user_id);
        return $user_data;
    }

    /**
     * 微信登录 小程序
     * @desc 微信登录
     */
    public function loginWechatApi()
    {
        $code = $this->code;  // 微信code
        $platform = $this->platform;   // 平台类型
        $pay_config = new Payment();
        $wx = $pay_config->getPayConfig("small");   // 获取微信的配置信息
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $wx['id'] . "&secret=" . $wx['secret'] . "&js_code=" . $code . "&grant_type=authorization_codee";
        $wx_config = json_decode($this->getWechatMsg($url));   // 获取到微信的openid和unionid

        if ($platform == 'MP-WEIXIN-MOBILE') //-MOBILE
        {
            // 单纯的获取openid
            return array("res" => true, "openid" => $wx_config->openid);
        }

        //        if (isset($wx_config->unionid) && !empty($wx_config->unionid)){
        //            $res = $this->model->checkWechatReg($wx_config->unionid,$platform,$wx_config->openid);
        //        }else {
        $res = $this->model->checkWechatReg($wx_config->unionid, $platform, $wx_config->openid);
        //        }

        if ($res['res'] === true) { // 已注册 直接登录()
            //            if (isset($wx_config->unionid) && !empty($wx_config->unionid)){
            //                $user_data = $this->model->wechatLogin($wx_config->unionid,$platform,$wx_config->openid);
            //            }else {
            $user_data = $this->model->wechatLogin($wx_config->unionid, $platform, $wx_config->openid);
            //            }
            $sess_id = $this->token_model->set_sess($user_data);

            return array('res' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id, "openid" => $wx_config->openid, 'pt' => $platform);
        }

        return $wx_config;
    }

     //H5 微信登录
     public function getWxAuthApi(){
         $hresult = $this->model->wxLogin();

         $appid = $hresult['hfiveid'];
         $state = 'wechat';
         $scope = 'snsapi_userinfo';
         $url = \PhalApi\DI()->config->get('app');

         $cfg_baseurl =  $url['host_url'];
         $datawx = $this ->pass_model->getweixinConfig();
         $cfg_vaseurl = $datawx[hfivepage];
         $back_url = $cfg_vaseurl.'h5/apiPam/logincommon/main';
         $redirect_uri = urlencode($back_url);
         $oauth_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
         $data['url'] = $oauth_url;
         $data['status'] ='succ';
         $data['response'] = '';
         $data['message'] ='成功';
         return $data;
     }

    //H5 微信登录获取的信息
    public function getWXUserInfoApi()
    {
        $code = $this->code; //回调地址的code
        $hresult = $this->model->wxLogin();
        $appid = $hresult['hfiveid'];
        $appsecret = $hresult['hfivesecret'];

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $appsecret . "&code=" . $code . "&grant_type=authorization_code";
        //        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$appsecret."&js_code=".$code."&grant_type=authorization_codee";
        $access_token = $this->getWechatMsg($url);
        $access_token = json_decode($access_token, true);
        $unionid = $access_token['unionid'];
        $openid = $access_token['openid'];
        $access_token = $access_token['access_token'];

        $url1 = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $uesr_data = $this->getWechatMsg($url1);
        $uesr_data = json_decode($uesr_data, true);

        $nickname = $uesr_data['nickname'];
        $headimgurl = $uesr_data['headimgurl'];

        //        if (isset($unionid) && !empty($unionid)){
        //            if(empty($openid) && empty($unionid)){
        //                $data['status'] ='error';
        //
        //                return $data;
        //            }
        //        }else {
        if (empty($openid)) {
            $data['status'] = 'error';

            return $data;
        }
        //        }



        //        if (isset($unionid) && !empty($unionid)){
        //            $res = $this->model->checkWechatReg($unionid,'H5',$openid);   // 判断这个用户是否存在
        //        }else {
        $res = $this->model->checkWechatReg($unionid, 'H5', $openid);   // 判断这个用户是否存在
        //        }

        if ($res['res'] === true) { // 已注册 直接登录
            //            if (isset($unionid) && !empty($unionid)){
            //                $user_data = $this->model->wechatLogin($unionid,"H5",$openid);
            //            }else {
            $user_data = $this->model->wechatLogin($unionid, "H5", $openid);
            //            }
            $sess_id = $this->token_model->set_sess($user_data);

            return array('res' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id, "nickname" => $nickname, 'avatar_url' => $headimgurl, 'openid' => $openid);
        }

        if ($res['res'] === false) { //没有注册 需要将数据写到表中
            $randpwd = time();

            $status = $this->model->bindWXH5($nickname, $openid, $randpwd, $unionid);
            if ($status) {
                //                $user_data = $this->model->wechatLogin($openid,'H5');
                $user_data = $this->model->wechatLogin($unionid, 'H5', $openid);
                $sess_id = $this->token_model->set_sess($user_data);
                return array('res' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id, "nickname" => $nickname, 'avatar_url' => $headimgurl, 'openid' => $openid, 'unionid' => $unionid);
            }
        }
    }

    public function wechatAppLogin()
    {
        $access_token = $this->access_token;
        $openid = $this->openid;
        $unionid = $this->unionid;
        $platform = $this->platform;

        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $uesr_data = $this->getWechatMsg($url);
        $uesr_data = json_decode($uesr_data, true);

        $nickname = $uesr_data['nickname'];
        $headimgurl = $uesr_data['headimgurl'];

        // 查看当前是否注册了
        $res = $this->model->checkWechatReg($unionid, $platform, $openid);

        if ($res['res']) {
            $user_data = $this->model->wechatLogin($unionid, "APP-PLUS", $openid);

            $sess_id = $this->token_model->set_sess($user_data);
            return array('res' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id, "nickname" => $nickname, 'avatar_url' => $headimgurl, 'openid' => $openid);
        }

        if ($res['res'] === false) { //没有注册 需要将数据写到表中
            $randpwd = time();

            $status = $this->model->bindWxApp($nickname, $openid, $randpwd, $unionid);
            if ($status) {
                $user_data = $this->model->wechatLogin($unionid, 'APP-PLUS', $openid);
                $sess_id = $this->token_model->set_sess($user_data);
                return array('res' => true, 'user_id' => $user_data['user_id'], 'sess_id' => $sess_id, "nickname" => $nickname, 'avatar_url' => $headimgurl, 'openid' => $openid, 'unionid' => $unionid);
            }
        }
    }

    /**
     * 苹果登录
     * @desc Apple ID 登录
     * @return array
     * @throws \PhalApi\Exception
     */
    public function appleAccountLoginApi()
    {
        $openid = $this->openid ?: '';
        $code = $this->code ?: '';
        $token = $this->token ?: '';

        return $this->Userlib->appleAccountLogin($openid, $code, $token);
    }
}
