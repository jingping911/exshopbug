<?php

namespace App\Api;

use App\Model\Order as OrderModel;
use App\Model\Payment as PaymentModel;
use App\Pay\AliPayNotify;
use App\Pay\WeChatNotify;
use PhalApi\Api;

/**
 * 支付相关接口服务
 * @package App\Api
 */
class Pay extends Api
{
    public function __construct()
    {
        $this->model = new OrderModel();
        $this->payment_model = new PaymentModel();
    }

    public function getRules()
    {
        return array(
            'payDetailApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'order_id' => array('name' => 'order_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'order_id'),
            ),
            'doPaymentBalanceApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'order_id' => array('name' => 'order_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'order_id'),
                'pay_id' => array('name' => 'pay_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'pay_id'),
                'platform' => array('name' => 'platform', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '来源'),
            ),
            'payCardApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'order_id' => array('name' => 'order_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'order_id'),
                'pay_type' => array('name' => 'pay_type', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '支付类型'),
                'p_code' => array('name' => 'p_code', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '云卡通卡号'),
                's_code' => array('name' => 's_code', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '云卡通安全码'),
            ),
            'getPaymentListApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'source' => array('name' => 'source', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => 'source'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            "payDopaymentApi" => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                "order_id" => array("name" => "order_id", "require" => true, "min" => 1, "desc" => "订单号"),
                "money" => array("name" => "money", "require" => true, "min" => 1, "desc" => "订单金额"),
                "pay_type" => array("name" => "pay_type", "require" => true, "min" => 1, "desc" => "支付类型"),
                "passback_params" => array("name" => "passback_params", "require" => false, "min" => 1, "desc" => "原样返回的值"),
                "openid" => array("name" => "openid", "request" => false, "desc" => "openid"),
            ),
            "aliPayWap" => array(
                "order_sn" => array("name" => "order_sn", "require" => true, "min" => 1, "desc" => "订单号"),
                "money" => array("name" => "money", "require" => true, "min" => 1, "desc" => "订单金额"),
                "body" => array("name" => "body", "require" => true, "min" => 1, "desc" => "订单描述"),
                "quit_url" => array("name" => "quit_url", "require" => true, "min" => 1, "desc" => "返回地址"),
                "passback_params" => array("name" => "passback_params", "require" => false, "min" => 1, "desc" => "回调原样返回参数"),
            ),
            "aliPayNotify" => array(
                "trade_status" => array("name" => "trade_status", "require" => true, "min" => 1, "desc" => "交易状态"),
                "trade_no" => array("name" => "trade_no", "require" => true, "min" => 1, "desc" => "交易号"),
                "out_trade_no" => array("name" => "out_trade_no", "require" => true, "min" => 1, "desc" => "订单号"),
                "total_amount" => array("name" => "total_amount", "require" => true, "min" => 1, "desc" => "订单金额"),
                "passback_params" => array("name" => "passback_params", "require" => false, "min" => 1, "desc" => "交易类型"),
            ),
            "wxPayNotify" => array(),
            "BankCcbPayNotify" => array(),
            "createOrderApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                "money" => array("name" => "money", "require" => true, "min" => 1, "desc" => "充值金额"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            "getPaymentIsUseApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            "BankCcbPayApi" => array(
                "user_id" => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "用户ID"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                "order_id" => array("name" => "order_id", "require" => true, "min" => 1, "desc" => "订单号"),
                "money" => array("name" => "money", "require" => true, "min" => 1, "desc" => "订单金额"),
                "pay_type" => array("name" => "pay_type", "require" => true, "min" => 1, "desc" => "支付类型"),
            ),
            "wechatPayApi" => array(
                "order_id" => array("name" => "order_id", "require" => true, "min" => 1, "desc" => "订单号"),
                "money" => array("name" => "money", "require" => false, "min" => 1, 'default'=>2, "desc" => "订单金额"),
                "passback_params" => array("name" => "passback_params", "require" => false, "min" => 1, "desc" => "原样返回的值", "default" => "goods"),
                "openid" => array("name" => "openid", "request" => false, "desc" => "openid"),
                "code" => array("name" => "code", "request" => false, "desc" => "code")
            )
        );
    }

    /**
     * 订单确认页面
     * @desc 获取所有支付方式
     */
    public function getPaymentListApi()
    {
        $source = $this->source;
        $user_id = $this->user_id;
        $token = $this->token; // 密码参数
        $this->checkLogin();

        $data = $this->payment_model->getPaymentList($user_id);

        return array('payment_list' => true, 'info' => $data);
    }

    /**
     * 订单确认页面
     * @desc 订单确认页
     */
    public function payDetailApi()
    {
        $user_id = intval($this->user_id);
        $token = $this->token;
        $order_id = $this->order_id;
        $this->checkLogin();

        $order_data = $this->model->pay_detail($order_id, $user_id);
        $other_data = $this->model->other_data($order_id, $user_id); // 商品名称，支付宝wap支付链接地址
        $sms_order_placed = $this->model->sms_order_placed($order_id, $user_id); //商家接收新订单
        //$sms_order_payed = $this->model->sms_order_payed($order_id);//消费者支付订单时发商家:
        //$sms_order_payed_to_customer = $this->model->sms_order_payed_to_customer($order_id);//消费者支付订单时发消费者
        if (intval($order_data['surplus']) > 0) {
            $order_data['total_fee'] = $order_data['surplus'];
        }
        if ($order_data['order_id'] == '') {
            return array('pay_detail' => false, 'info' => $this->model->msg);
        }
        return array('pay_detail' => true, 'info' => $order_data, 'other' => $other_data);
    }
    /**
     * 发起支付
     * @desc 用户执行支付
     */

    public function doPaymentBalanceApi()
    {
        $user_id = intval($this->user_id);
        $token = $this->token;
        $order_id = $this->order_id;
        $pay_id = $this->pay_id;
        $platform = $this->platform;

        $this->checkLogin();

        $paydata = $this->model->doPaymentBalance($order_id, $user_id, $pay_id, $platform);


        if (!$paydata) {
            return array('dopay' => false, 'info' => $this->model->msg);
        }
        return array('dopay' => true, 'info' => $paydata);
    }

    /**
     * 云卡通支付
     * @desc 云卡通支付接口
     */
    public function payCardApi()
    {
        //        var_dump($order_sn.$pay_type.'++'.$p_code.$s_code);die;
        $order_sn = $this->order_id;
        $pay_type = $this->pay_type;
        $p_code = $this->p_code;
        $s_code = $this->s_code;
        $user_id = $this->user_id;
        $data = $this->model->doPayCard($order_sn, $user_id, $pay_type, $p_code, $s_code);
        //        var_dump($data);die;
        return $data;
    }
    /**
     * 万能支付
     * @desc 万能支付
     */
    public function payDopaymentApi()
    {
        $order_sn = $this->order_id; // 订单账号
        $money = $this->money; // 订单金额
        //$pay_type = 'wxpayJsapi'; // 支付类型
        $pay_type = $this->pay_type; // 支付类型
        $passback_params = $this->passback_params; // 原样返回的值 用于判断是余额充值还是商品订单支付
        $openid = $this->openid;
        $user_id = intval($this->user_id);
        $order_data = $this->model->pay_detail($order_sn, $user_id);
        if ($order_data['total_fee'] > $money){
            return array('res' => false, "msg" => "订单错误,请稍后再试");
        }
        if ($pay_type == 'alipayApp') { // 支付宝app支付
            $res = $this->payment_model->doPaymentAliApp($order_sn, $money, $passback_params);
        } else if ($pay_type == 'wxpayApp') { // 微信app支付
            $res = $this->payment_model->doPaymentWxApp($order_sn, $money, $passback_params);

        } else if ($pay_type == 'wxpayJsapi'){ //微信内部充值
            $res =$this->payment_model->doPaymentMpH5Jsapi($order_sn, $money, $openid, $passback_params);

        } else if ($pay_type == 'wxpayH5') { // 微信H5支付
            $res = $this->payment_model->doPaymentMpH5($order_sn, $money, $passback_params);
            if ($res['res'] === false) {
                return array('res' => false, "msg" => "微信配置错误");
            }
        } else if ($pay_type == 'wxpayMp') {
            // 微信小程序支付
            $res = $this->payment_model->doPaymentWxMp($order_sn, $money, $openid, $passback_params);
        }

        return $res;
    }

    /**
     * 支付宝移动支付
     * @desc 支付宝移动支付
     */
    public function aliPayWap()
    {
        $order_sn = $this->order_sn; // 订单号
        $money = $this->money; // 订单金额
        $body = $this->body; // 订单描述
        $quit_url = $this->quit_url; //返回地址
        $passback_params = $this->passback_params; // 异步回调通知时 该参数原样返回

        $res = $this->payment_model->doPaymentAliWap($order_sn, $money, $body, $quit_url, $passback_params);
        echo $res;
        exit;
    }

    /**
     * 支付宝支付回调
     * @desc 支付宝支付回调
     */
    public function aliPayNotify()
    {
        $data = $_POST; // 所有的数据

        $notify = new AliPayNotify();

        $notify->Notify($data);
        exit;
    }

    /**
     * 微信支付回调
     * @desc 微信支付回调
     */
    public function wxPayNotify()
    {
        $data = $_POST; // 貌似暂时用不到

        $notify = new WeChatNotify();
        $res = $notify->Notify();
        return $res;
    }

    /**
     * 余额充值订单生成
     * @desc 余额充值订单生成
     */
    public function createOrderApi()
    {
        $user_id = intval($this->user_id);
        $money = $this->money;
        $token = $this->token; // 密码参数
        $this->checkLogin();

        $data = $this->model->createOrder($user_id, $money);
        return $data;
    }

    // 支付方式开关
    public function getPaymentIsUseApi()
    {
        $user_id = intval($this->user_id);
        $token = $this->token; // 密码参数
        $data = $this->model->getPaymentIsUse();
        return $data;
    }

    //微信内置浏览器H5支付
    public function wechatPayApi()
    {
        $order_sn = $this->order_id; // 订单账号
        $money = $this->money; // 订单金额
        $passback_params = $this->passback_params; // 原样返回的值 用于判断是余额充值还是商品订单支付
        $openid = $this->openid;
        $code = $this->code;
        if($passback_params == 'goods'){
            $res = $this->payment_model->wechatPay($order_sn, $money, $openid, $passback_params, $code);
        }else {
            $res = $this->payment_model->doPaymentMpH5Jsapi($order_sn, $money, $openid, $passback_params, $code);
        }
        return $res;
    }
    //建设银行龙支付
    public function BankCcbPayApi(){
        $order_sn = $this->order_id; // 订单账号
        $money = $this->money; // 订单金额
        $user_id = $this->user_id; // 用户ID
        $res = $this->payment_model->BankCcbPay($order_sn, $money,$user_id);
        return $res;
    }

    //建设银行龙支付
    public function BankCcbPayNotify(){
        $Data = $_POST;
//        if($Data['REFERER'] =="NULL"){
//            $Data['REFERER'] ='';
//        }
        $data = "POSID=".$Data['POSID']."&BRANCHID=".$Data['BRANCHID']."&ORDERID=".$Data['ORDERID']."&PAYMENT=".$Data['PAYMENT']."&CURCODE=".$Data['CURCODE']."&REMARK1=".$Data['REMARK1']."&REMARK2=".$Data['REMARK2']."&ACC_TYPE=".$Data['ACC_TYPE']."&SUCCESS=".$Data['SUCCESS']."&TYPE=".$Data['TYPE']."&REFERER=".$Data['REFERER']."&CLIENTIP=".$Data['CLIENTIP']."&ACCDATE=".$Data['ACCDATE']."&SIGN=".$Data['SIGN']."\n";


        $address = '127.0.0.1';
//        $address = '47.103.121.22';
//        $address = '139.129.100.217';
        $service_port = '55533';
//        file_put_contents('/data/httpd/ecshop.test2.shopex123.com/ecshop/abcde.log',var_export($data,true),FILE_APPEND);
        $res = $this->payment_model->BankCcbPayNotify($address,$service_port,$data,$Data['ORDERID']);
        return $res;
    }
}
