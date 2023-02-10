<?php

namespace App\Api;

use PhalApi\Api;
use App\Model\User as UserModel;
use App\Model\Token as TokenModel;
use App\Model\CollectGoods as CollectGoodsModel;
use App\Model\UserAddress as UserAddressModel;
use App\Model\Address as AddressModel;
use App\Lib\User as UserLib;
use App\Model\Payment;
use PhalApi\Exception\BadRequestException;

use App\Model\Order as OrderModel;

/**
 * 用户模块接口服务
 */
class User extends Api
{
    protected $domain;

    public function __construct()
    {
        $this->model = new UserModel();
        $this->token_model = new TokenModel();
        $this->domain = new UserLib();
        $this->order = new OrderModel();
    }


    public function getRules()
    {
        return array(
            'goodsAddCollectApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户名'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'collect_status' => array('name' => 'collect_status', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'collect_status'),
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'goods_id'),
            ),
            'collectList' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户名'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            'memberInfoApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            'getMemberApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            'feedbackSubmitActionApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'content' => array('name' => 'content', 'require' => true, 'min' => 1, 'max' => 500, 'desc' => '反馈内容'),
                'title' => array('name' => 'title', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '反馈标题'),
                'verCode' => array('name' => 'verCode', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '图形验证码'),
                'phone' => array('name' => 'phone', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '反馈人联系方式'),
            ),
            'memberinfoSaveApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'birthdaytime' => array('name' => 'birthdaytime', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员生日'),
                'sex' => array('name' => 'sex', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员性别'),

            ),
            'addressGetListApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            'addressDetailApi' => array(
                'address_id' => array('name' => 'address_id', 'require' => true, 'min' => 1, 'max' => 100, 'desc' => '收货地址id'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 100, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            'GetRealyAddressApi' => array(
                'params' => array('name' => 'params', 'require' => true, 'min' => 1, 'max' => 100, 'desc' => '经纬度'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 100, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            'addaddressDetailActionApi' => array(
                'consignee' => array('name' => 'consignee', 'require' => true, 'min' => 1, 'max' => 100, 'desc' => '收货人姓名'),
                'email' => array('name' => 'email', 'require' => false, 'min' => 0, 'max' => 100, 'desc' => 'email'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 20, 'desc' => '会员ID'),
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 1, 'max' => 11, 'desc' => '手机号码'),
                'address' => array('name' => 'address', 'require' => true, 'min' => 1, 'max' => 100, 'desc' => '详细地址'),
                'country' => array('name' => 'country', 'require' => false, 'min' => 1, 'max' => 11, 'desc' => '国家'),
                'province' => array('name' => 'province', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '省'),
                'city' => array('name' => 'city', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '市'),
                'district' => array('name' => 'district', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '区'),
                'address_id' => array('name' => 'address_id', 'require' => false, 'min' => 0, 'max' => 11, 'desc' => '区号id'),
                'mobile_addr_id_list' => array('name' => 'mobile_addr_id_list', 'require' => false, 'min' => 0, 'max' => 20, 'desc' => 'mobile区号list'),
                'checked' => array('name' => 'checked', 'require' => false, 'min' => 1, 'max' => 11, 'desc' => '默认'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            'depositApi' => array(
                'user_id' => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            "collectlistActionApi" => array(
                "page" => array("name" => "page", "require" => true, "min" => 1, "desc" => "当前页数"),
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户的ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            "advancelistActionApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户的ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "page" => array("name" => "page", "require" => true, "min" => 1, "desc" => "当前页数")
            ),
            "pointListApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户的ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "page" => array("name" => "page", "require" => true, "min" => 1, "desc" => "页数")
            ),
            "SendBonusApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户的ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "bonus_id" => array("name" => "bonus_id", "require" => true, "min" => 1, "desc" => "红包ID")
            ),
            "GetPromoteApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户的ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            "couponListApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户的ID"),
                "page" => array("name" => "page", "require" => true, "min" => 1, "desc" => "页数"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "status" => array("name" => "status", "require" => true, "min" => 1, "desc" => "红包状态")
            ),
            "securityphoneUpdateBeforeApi" => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 11, 'desc' => "手机号"),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户名'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'verCode' => array('name' => 'verCode', 'require' => true, 'min' => 4, 'desc' => '验证码')
            ),
            "securityphoneUpdateAfterApi" => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 11, 'desc' => "手机号"),
                'verCode' => array('name' => 'verCode', 'require' => true, 'min' => 4, 'desc' => '验证码'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "max" => 30, "desc" => "用户ID")
            ),
            "securitypwdUpdateApi" => array(
                'mobile' => array('name' => 'mobile', 'require' => true, 'min' => 11, 'desc' => "手机号"),
                'verCode' => array('name' => 'verCode', 'require' => true, 'min' => 4, 'desc' => '验证码'),
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "max" => 30, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "passData" => array("name" => "passData", "require" => true, "min" => 1, "max" => 30, "desc" => "新密码")
            ),
            "addressDeleteApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "id" => array("name" => "id", "require" => true, "min" => 1, "desc" => "地址ID")
            ),
            "orderDefaultAddressApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token')
            ),
            "deleteCouponApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'coupon_id' => array('name' => 'coupon_id', "require" => true, 'min' => 1, 'max' => 64, 'desc' => '优惠券ID')

            ),
            "activeBonusApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'chart_code' => array('name' => 'chart_code', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '图形验证码'),
                "bonus_id" => array('name' => 'bonus_id', 'require' => true, 'min' => 1, 'max' => 20, 'desc' => '红包id')
            ),
            "chartCodeApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            "registerActiveApi" => array(

            ),
            "getPromoteNumApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                "page" => array("name" => "page", "require" => true, "min" => 1, "desc" => "分页"),
                "token" => array('name' => 'token', "require" => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            "lowerCommissionApi" => array(
                "id" => array("name" => "id", "require" => true, "min" => 1, "desc" => "用户ID"),
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                "token" => array('name' => 'token', "require" => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "page" => array('name' => 'page', 'require' => false, 'min' => 1, 'max' => 10, 'desc' => 'page'),
                "num" => array('name' => 'num', 'require' => false, 'minx' => 1, 'max' => 10, 'desc' => 'num'),
            ),
            "getUserAccountApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                "token" => array('name' => 'token', "require" => true, 'min' => 1, 'max' => 32, 'desc' => 'token')
            ),
            "withdrawalApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                "token" => array('name' => 'token', "require" => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "card" => array('name' => "card", "require" => true, 'min' => 16, 'max' => 20, 'desc' => 'card'),
                "withdrawal" => array('name' => "withdrawal", "require" => true, 'min' => 1, "max" => "10", "desc" => "withdrawal"),
                "bankNo" => array('name' => "bankNo", "require" => false, 'min' => 1, 'max' => 20, "desc" => "bankNo"),
                "bank_addr" => array('name' => "bank_addr", "require" => false, 'min' => 1, 'max' => 40, "desc" => "开户行地址"),
                "bank_account" => array('name' => "bank_account", "require" => false, 'min' => 1, 'max' => 20, "desc" => "开户行"),
                //                "withdrawalFee" => array('name' => 'withdrawalFee', "require" => true, 'min'=> 1, "max" => "10", "desc" => "withdrawalFee"),
                "platform" => array('name' => 'platform', "require" => true, 'min' => 1, "max" => "10", "desc" => "platform")
            )
        );
    }


    /**
     * 收货地址详情
     * @desc 收货地址详情
     * @return array

     */
    public function addressDetailApi()
    {

        $this->address = new AddressModel();
        $address_id = intval($this->address_id);
        $user_id = intval($this->user_id);
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $data = $this->address->get_address_by_id($user_id, $address_id);
        if (!$data) {
            return array('is_detail' => false, 'msg' => '');
        }
        return array('is_detail' => true, 'data' => $data, 'msg' => '');
    }

    /**
     * 增加地址
     * @desc 普通注册
     * @return array

     */
    function  GetRealyAddressApi()
    {
        $params =  $this->params;
        $data =  $this->model->GetRealyAddr($params);

        return $data;
    }

    /**
     * 增加地址
     * @desc 普通注册
     * @return array

     */
    public function addaddressDetailActionApi()
    {
        $this->useraddr_model = new UserAddressModel();
        $this->address_model = new AddressModel();
        $consignee = $this->consignee;
        $email = $this->email;
        $user_id = $this->user_id;
        $mobile = $this->mobile;
        $address = $this->address;
        $country = $this->country;
        $province = $this->province;
        $city = $this->city;
        $district = $this->district;
        $address_id = $this->address_id;
        $checked = $this->checked;
        $mobile_addr_id_list = $this->mobile_addr_id_list;
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        if ($address_id != '') {
            $data = $this->address_model->get_address_by_id($user_id, $address_id);
            if (!$data) {
                return array('is_add' => false, 'msg' => '无此地址不允许修改');
            }
        } else {
            $data = $this->useraddr_model->checkAddress($user_id);
            if (!$data) {
                return array('is_add' => false, 'msg' => '地址不允许超过10个');
            }
        }

        if ($address_id) {

            $res = $this->useraddr_model->update_address_detail(
                $address_id,
                $user_id,
                $consignee,
                $email,
                $mobile,
                $address,
                $country,
                $province,
                $city,
                $district,
                $mobile_addr_id_list,
                $checked
            );
        } else {
            $res = $this->useraddr_model->add_address_detail(
                $consignee,
                $email,
                $user_id,
                $mobile,
                $address,
                $country,
                $province,
                $city,
                $district,
                $mobile_addr_id_list,
                $checked
            );
        }
        //        var_dump("123");
        //        die;
        if ($res) {
            return array('is_add' => true, 'address_id' => $res);
        } else {
            return array('is_add' => false, 'msg' => '');
        }
    }




    /**
     * 收藏列表
     * @desc 收藏列表
     * @return array
     */
    public function collectList()
    {
        $user_id = $this->user_id;   // 密码参数
        $token = $this->token;   // 密码参数
        $this->collect_goods = new CollectGoodsModel();
        $data = $this->collect_goods->get_collect_list($user_id);

        if (!$data) {
            return array('res' => false, 'info' => $data);
        }
        return array('res' => true, 'info' => $data);
    }

    /**
     * 用户地址列表
     * @desc 地址列表
     * @return array
     */
    public function addressGetListApi()
    {
        $this->useraddr_model = new UserAddressModel();

        $user_id = $this->user_id;
        $token = $this->token;
        $data = $this->useraddr_model->getAddressList($user_id);
        return array('res' => true, 'info' => $data, 'length' => count(data));
    }


    /**
     * 用户地址列表
     * @desc 地址列表
     * @return array
     */
    public function getAddressDetail()
    {
        $this->useraddr_model = new UserAddressModel();

        $user_id = $this->user_id;
        $address_id = $this->address_id;
        $token = $this->token;
        $data = $this->useraddr_model->get_address_by_id($user_id, $address_id);
        return array('res' => true, 'info' => $data, 'length' => count(data));
    }

    /**
     * 删除地址
     * @desc 删除地址
     * @return array
     */
    public function deleteAddress()
    {
        $user_id = 1;
        $address_id = 3;
        $data =  $this->domain->deleteAddress($user_id, $address_id);
        return $data;
    }

    /**
     * 默认地址
     * @desc 默认地址
     * @return array
     */
    public function orderDefaultAddressApi()
    {
        $user_id = $_POST['user_id'];
        $user_id = 1;
        $address = $this->domain->defaultAddress($user_id);
        return $address;
    }

    /**
     * 订单详情
     * @desc 订单详情
     * @return array
     */
    public function orderDetail()
    {
        return ['订单详情'];
    }

    /**
     * 订单列表
     * @desc 订单列表
     * @return array
     */
    public function orderList()
    {
        return ['订单列表'];
    }

    /**
     * 用户加入收藏
     * @desc 收藏
     * @return array
     */
    public function goodsAddCollectApi()
    {
        $goods_id = $this->goods_id;   // 账号参数
        $user_id = $this->user_id;   // 密码参数
        $token = $this->token;   // 密码参数
        $collect_status = $this->collect_status;   // 密码参数

        $this->collect_goods = new CollectGoodsModel();

        if ($collect_status == 'true') {
            $data = $this->collect_goods->del($user_id, $goods_id);
            return array('res' => false, 'action' => 'delete', 'msg' => '取消收藏');
        } else {
            $data = $this->collect_goods->add($user_id, $goods_id);
            return array('res' => true, 'action' => 'add', 'msg' => '收藏成功');
        }
    }
    /**
     * 意见反馈
     * @desc 意见反馈
     */
    public function feedbackSubmitActionApi()
    {
        $user_id = $this->user_id;
        $content = $this->content;
        $title = $this->title;
        $phone = $this->phone;
        $token = $this->token;
        $verCode = $this->verCode;
        if ($this->model->Judge_figure($user_id, $verCode)){
            return false;
        }
        $data = $this->model->get_UserFeedback($user_id, $content, $title, $phone);
        return $data;
    }
    /**
     * 个人信息
     * @desc 个人信息接口
     */
    public function getMemberApi()
    {
        $user_id = $this->user_id;   // 密码参数
        $token = $this->token;   // 密码参数
        $this->checkLogin();
        $data = $this->model->get_user($user_id);
        if (!$data) {
            return array('res' => false, 'info' => '用户不存在');
        }
        return array('res' => true, 'info' => $data);
    }
    /**
     * 会员信息保存
     * @desc 会员信息保存接口
     */
    public function memberinfoSaveApi()
    {
        $user_id = $this->user_id;   // 密码参数
        $token = $this->token;   // 密码参数
        $birthdaytime = $this->birthdaytime;
        $sex = $this->sex;
        $this->checkLogin();
        $this->model->memberinfoSave($user_id, $birthdaytime, $sex);

        return array('res' => true,);
    }

    /**
     * 用户基础信息
     * @desc 收藏
     * @return array
     */
    public function memberInfoApi()
    {
        $user_id = $this->user_id;   // 密码参数
        $token = $this->token;   // 密码参数


        $this->checkLogin();

        $data = $this->model->get_user_info($user_id);

        // 全部订单数量
        $order_num = $this->order->getAllOrderNum($user_id);

        // 获取当前的会员等级
        $rank_name = $this->model->getUserRank($user_id);

        // 支付宝H5支付地址
        //$data['alipay_url'] = alipay_h5_url();
        $payment_mod = new Payment();
        $ali_h5_url = $payment_mod->getPayConfig("zhifu");

        $data["alipay_url"] = $ali_h5_url["zid"];
        //图片地址
        $logo_oter = $this->model->logo_other();

        $kefu_tel = $this->model->kefu_tel();
        $kefu_qq = $this->model->getQq();

        $my_subordinates = $this->model->my_subordinates();//我的下级开关

        if (!$data) {
            return array('res' => false, 'info' => '用户不存在');
        }
        return array('res' => true, 'info' => $data, "allOrderNum" => $order_num, "rank_name" => $rank_name, "logo_other" => $logo_oter, 'kefu_tel' => $kefu_tel, 'kefu_qq' => $kefu_qq,'my_subordinates' =>$my_subordinates);
    }

    /**
     * 登录页请求，查找logo
     */
    public function logo()
    {
        $logo_oter = $this->model->logo_other();
        $apple_login = $this->model->apple_login();
        return array("logo_other"=>$logo_oter,"apple_login"=>$apple_login);
    }


    /**
     * 钱包余额
     * @desc 钱包余额
     */
    public function depositApi()
    {
        $user_id = $this->user_id;
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $deposit = $this->model->getDeposit($user_id);
        return $deposit;
    }

    /**
     * 我的收藏
     * @desc 我的收藏
     */

    public function collectlistActionApi()
    {
        $page = $this->page;
        $user_id = $this->user_id;
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $return = array();
        $data = $this->model->getCollectListAction($page, $user_id);
        //        $return['collectGoodsList'] = $data;
        return $data;
    }

    /**
     * 充值记录
     * @desc 充值记录
     */
    public function advancelistActionApi()
    {
        $user_id = intval($this->user_id);
        $page = intval($this->page);
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $data = $this->model->getAdvanceListAction($user_id, $page);

        return $data;
    }

    /**
     * 获取可使用红包记录
     * @desc 红包记录
     */
    public function couponListApi()
    {
        $user_id = intval($this->user_id);
        $page = intval($this->page);
        $token = $this->token;   // 密码参数
        $this->checkLogin();


        $status = $this->status;
        $data = $this->model->getAviBonusList($user_id, $page, $status);

        return $data;
    }
    /**
     * 积分记录
     * @desc 积分记录
     *
     */
    public function pointListApi()
    {
        $user_id = intval($this->user_id);
        $page = intval($this->page);
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $data = $this->model->getPointList($user_id, $page);
        return $data;
    }
    /**
     * 用户领取红包
     * @desc 用户领取红包
     *
     */
    public function SendBonusApi()
    {
        $user_id = $this->user_id;
        $bonus_id = $this->bonus_id;
        $token = $this->token;   // 密码参数

        $data = $this->model->SendBonus($user_id, $bonus_id);
        return $data;
    }
    /**
     * 分享页面，获取二维码
     * @desc 分享页面，获取二维码
     */
    public function GetPromoteApi()
    {
        $user_id = $this->user_id;
        $data = $this->model->GetPromoteApi($user_id);
        return $data;
    }

    /**
     * 修改手机号- 第一步
     * @desc 更换手机号
     */

    public function securityphoneUpdateBeforeApi()
    {
        $mobile = $this->mobile; // 手机号
        $vcode = $this->verCode; // 验证码
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $res = $this->model->securityphoneUpdateBefore($mobile, $vcode);
        if ($res === false) {
            return array("res" => false, "msg" => "验证码错误");
        }
        return array("res" => true, "msg" => "验证通过");
    }

    /**
     * 修改手机号- 第二步
     * @desc 更换手机号
     */

    public function securityphoneUpdateAfterApi()
    {
        $mobile = $this->mobile; // 手机号
        $vcode = $this->verCode; // 验证码
        $user_id = $this->user_id; // 用户id
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $res = $this->model->securityphoneUpdateAfter($mobile, $vcode, $user_id);
        if ($res === false) {
            return array("res" => false, "msg" => "验证码错误");
        }
        return array("res" => true, "msg" => "更改成功");
    }

    /**
     * 修改密码
     * @desc 修改密码
     */
    public function securitypwdUpdateApi()
    {
        $mobile = $this->mobile; // 手机号
        $vcode = $this->verCode; // 验证码
        $user_id = $this->user_id; // 用户id
        $password = $this->passData; // 新密码
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $res = $this->model->securitypwdUpdate($mobile, $vcode, $user_id, $password);
        if ($res === false) {
            return array("res" => false, "msg" => "验证码错误");
        }
        return array("res" => true, "msg" => "修改成功");
    }

    /**
     * 删除收货地址
     * @desc 删除收货地址
     */
    public function addressDeleteApi()
    {
        $user_id = $this->user_id;
        $id = $this->id;
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $this->address = new AddressModel();

        $this->address->addressDelete($user_id, $id);
    }

    /*
     * 删除优惠券
     * */
    public function deleteCouponApi()
    {
        $user_id = $this->user_id;
        $coupon_id = $this->coupon_id;
        $token = $this->token;
        $this->checkLogin();
        $res = $this->model->deleteCoupon($user_id, $coupon_id);
        if ($res) {
            return array('res' => true, 'msg' => 'suc');
        } else {
            return array('res' => false, 'msg' => 'def');
        }
    }

    /*
     * 激活红包
     * */
    public function activeBonusApi()
    {
        $user_id = $this->user_id;
        $token = $this->token;
        $bonus_id = $this->bonus_id;
        $chart_code = $this->chart_code;
        $this->checkLogin();
        //判断图形验证码是否正确
        if ($this->model->Judge_figure($user_id, $chart_code)){
            $this->model->getActiveBonus($user_id);
            return array('res' => false, 'msg' => 'yzm');
        }
        $res = $this->model->activeBonus($user_id, $bonus_id);
        $this->model->getActiveBonus($user_id);
        if ($res) {
            return array('res' => true, 'msg' => 'success');
        } else {
            return array('res' => false, 'msg' => 'def');
        }
    }


    /*
 * 获取图形验证码
 * */
    public function chartCodeApi()
    {
        $user_id = $this->user_id;
        $token = $this->token;
        $this->checkLogin();
        $res = $this->model->getActiveBonus($user_id);
        if ($res) {
            return array('res' => $res, 'msg' => 'success');
        } else {
            return array('res' => false, 'msg' => 'def');
        }
    }

    /*
    * 获取用户推广信息
    * */
    public function getPromoteNumApi()
    {
        $user_id = $this->user_id;
        $page = $this->page;
        $data = $this->model->getPromoteNum($user_id, $page);
        return $data;
    }

    public function lowerCommissionApi()
    {
        $user_id = $this->id;
        $parent_id = $this->user_id;
        $page = $this->page;
        $num = $this->num;
        //查看下级消费订单
        $data = $this->model->LowerOrderList($user_id, $page, $parent_id);

        return $data;
    }

    /*
     * 获取用户账户信息
     * */
    public function getUserAccountApi()
    {
        $user_id = $this->user_id;
        $data = $this->model->getUserAccount($user_id);
        return $data;
    }

    /*
         * 用户提现
         * */
    public function withdrawalApi()
    {
        $user_id = $this->user_id;
        $card = $this->card;
        $withdrawal = $this->withdrawal;
        $bank_account = $this->bank_account;
        $bank_addr = $this->bank_addr;
        //        $bankNo = $this->bankNo;
        //        $withdrawalFee = $this->withdrawalFee;
        $platform = $this->platform;
        $data = $this->model->applyWithdrawal($user_id, $card, $withdrawal, $platform, $bank_account, $bank_addr);
        return $data;
    }




}
