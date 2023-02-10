<?php

namespace App\Api;

use App\Lib\User as UserLib;
use App\Model\Region as RegionModel;
use App\Model\ShopConfig;
use App\Model\Token as TokenModel;
use App\Model\User as UserModel;
use App\Model\Vcode as VcodeModel;
use PhalApi\Api;

/**
 * 用户模块接口服务
 */
class Tools extends Api
{
    protected $domain;

    public function __construct()
    {
        $this->model = new UserModel();
        $this->token_model = new TokenModel();
        $this->vcode_model = new VcodeModel();
        $this->Userlib = new UserLib();
        $this->shopConfigModel = new ShopConfig();
    }

    public function getRules()
    {
        return array(
            'registerSendSmsApi' => array(
                'user_name' => array('name' => 'user_name', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '手机号码'),
            ),
            'forgetSendSmsApi' => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '手机号码'),
            ),
            'getAddrId' => array(
                'addr' => array('name' => 'addr', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '省市区'),
            ),
            'logincommonSendSmsApi' => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '手机号'),
            ),
            'securityphoneSendBeforeApi' => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 1, 'max' => 11, 'desc' => '手机号'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 30, 'desc' => '会员ID'),
                'status' => array('name' => 'status', 'require' => true, 'min' => 1, 'max' => 30, 'desc' => "提交的状态"),
            ),
            "securitypwdSendSmsApi" => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '手机号码'),
            ),
            "bindingSendSmsApi" => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '手机号码'),
            ),
            "getLiveListApi" => array(
                'page'          =>  array('name' => 'page', 'require' => true, 'min' => 1, 'max' => 20, 'desc' => '当前页数', 'default' => 1),
                'total_page'    =>  array('name' => 'total_page', 'require' => true, 'min' => 1, 'max' => 20, 'desc' => '总页数', 'default' => 1)
            )

        );
    }

    /**
     * 获取省市区ID
     * @desc 获取省市区id
     */

    public function getAddrId()
    {
        $this->region_model = new RegionModel();
        $addr = $this->addr;
        $res = $this->region_model->get_addr_id_list($addr);
        if (!$res) {
            $return = array('is_id' => false, 'msg' => '无相应地址信息');
            return $return;
        }
        $return = array(
            'is_id' => true,
            'country' => $res['country'],
            'province' => $res['province'],
            'city' => $res['city'],
            'district' => $res['district'],
        );
        return $return;
    }

    /**
     * 普通注册接口
     * @desc 普通注册
     * @return array
     */
    public function registerSendSmsApi()
    {
        $user_name = $this->user_name; // 账号参数
        $user_info = $this->model->check_username($user_name);
//        if ($user_info) {
//            return array('is_send' => false, 'msg' => '手机号码已经存在');
//        }

//                $user_check = $this->vcode_model->set_vcode($user_name);
        // 查询手机号前半小时短信发送次数
        if($this->vcode_model->get_sms_number($user_name)){
            return array('is_send' => false, 'msg' => '发送次数过于频繁,请稍后在试');
        }
        $user_check = $this->vcode_model->signup_sms($user_name);

        if ($user_check) {
            return array('is_send' => true, 'user_name' => $user_name);
        } else {
            return array('is_send' => false, 'user_name' => $user_name);
        }
    }

    /**
     * 忘记密码
     * @desc 普通注册
     * @return array
     */
    public function forgetSendSmsApi()
    {

        // var_dump($this);die;
        $user_name = $this->mobile; // 账号参数

        $user_check = $this->Userlib->check_mobile_register($user_name);
        /**
        判断账号是否存在
         **/
        if ($user_check) {
            return array('is_lost' => false, 'msg' => 'mobile_error');
        }
        // 查询手机号前半小时短信发送次数
        if($this->vcode_model->get_sms_number($user_name)){
            return array('is_send' => false, 'msg' => '发送次数过于频繁,请稍后在试');
        }
        $data = $this->vcode_model->signup_sms($user_name);
        return array('is_lost' => true, 'username' => $user_name);
    }

    /**
     * 验证码登录
     * @desc 验证码登录
     */
    public function logincommonSendSmsApi()
    {
        $mobile = $this->mobile; // 手机号

        $user_check = $this->Userlib->check_mobile_register($mobile);

        /**
         * 判断手机号是否存在
         */
//        if ($user_check) {
//            return array('is_login' => false, 'msg' => 'mobile_error');
//        }
        // 查询手机号前半小时短信发送次数
        if($this->vcode_model->get_sms_number($mobile)){
            return array('is_send' => false, 'msg' => '发送次数过于频繁,请稍后在试');
        }
        $data = $this->vcode_model->signup_sms($mobile);
        return array('is_login' => true, 'mobile' => $mobile);
    }
    /**
     * 修改手机号
     * @desc 修改手机号
     */

    public function securityphoneSendBeforeApi()
    {
        $mobile = $this->mobile; // 手机号
        $user_id = intval($this->user_id); // 会员id
        $status = $this->status; // 提交的状态 => before  after

        $res = $this->model->checkMobile($user_id, $mobile);

        if ($status == 'before' && $res === false) {

            return array("res" => false, 'msg' => "手机号与用户ID不匹配");
        }
        if ($status == 'after') {
            $user_check = $this->Userlib->check_mobile_register($mobile); // 检测手机号是否已注册
            if ($user_check === false) {
                return array("res" => false, "msg" => "该手机号已存在");
            }
        }
        if($this->vcode_model->get_sms_number($mobile)){
            return array('is_send' => false, 'msg' => '发送次数过于频繁,请稍后在试');
        }
        $data = $this->vcode_model->signup_sms($mobile);
        return array('res' => true, 'mobile' => $mobile);
    }

    /**
     * 修改密码
     * @desc 修改密码
     * @return array
     */
    public function securitypwdSendSmsApi()
    {
        $mobile = $this->mobile; // 账号参数

        $user_check = $this->Userlib->check_mobile_register($mobile);
        /**
        判断账号是否存在
         **/
        if ($user_check) {
            return array('res' => false, 'msg' => '手机号不一致');
        }
        if($this->vcode_model->get_sms_number($mobile)){
            return array('is_send' => false, 'msg' => '发送次数过于频繁,请稍后在试');
        }
        $data = $this->vcode_model->signup_sms($mobile);
        return array('res' => true, 'mobile' => $mobile);
    }

    /**
     * 绑定微信获取验证码
     * @desc 绑定微信获取验证码
     */
    public function bindingSendSmsApi()
    {
        $mobile = $this->mobile; // 手机号码

        $data = $this->vcode_model->signup_sms($mobile);

        return array("res" => true, "mobile" => $mobile); // 直接发送验证码

    }

    /**
     * 获取APP更新信息
     * @desc 获取APP更新信息
     */
    public function versionApi()
    {

        $data = $this->vcode_model->appUpdate();
        $return['name'] = $data['name']; //app名称
        $return['nowId'] = $data['nowId']; //app当前版本
        $return['updateId'] = $data['updateId']; //app更新版本
        $return['iosLink'] = $data['iosLink']; //iosapp更新地址
        $return['androidLink'] = $data['androidLink']; //安卓app更新地址
        return $return;
    }

    /**
     * 直播列表获取
     * @desc 直播列表获取
     */
    public function getLiveListApi()
    {
        $page       = intval($this->page);
        $page_total = $this->total_page;

        $liveList = $this->shopConfigModel->getLiveList($page, $page_total);
        return $liveList;
    }
}
