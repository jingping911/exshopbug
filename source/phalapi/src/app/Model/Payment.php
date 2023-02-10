<?php

namespace App\Model;

use App\Model\User as UserModel;
use App\Model\wxH5Pay as wxH5Pay;
use App\Pay\AlipayService;
use App\Pay\AlipayTradeAppPayRequest;
use App\Pay\AopClient as AopClient;
use App\Pay\wxMpPay;
use App\Pay\WxpayAppSDK;
use App\Model\Order as OrderModel;
use PhalApi\Model\NotORMModel as NotORM;
use PhalApi\Request\Formatter\StringFormatter;
use App\Model\Payment as PaymentModel;


class Payment extends NotORM
{
    protected function getTableName($id)
    {
        return 'payment';
    }

    protected function getTableKey($table)
    {
        return 'pay_id';
    }

    public function getPaymentList($user_id)
    {
        $data = $this->getORM()->where(array('enabled' => '1'))->fetchAll();
        $this->user_model = new UserModel();

        foreach ($data as $key => $item) {
            /**
             * 获取预存款金额，显示在付款页面
             **/
            if ($item['pay_code'] == 'balance') {
                $tmp = $this->user_model->get_user_info($user_id);
                $data[$key]['amount_txt'] = $tmp['user_money'];
            }
        }

        return $data;
    }

    public function doPaymentMpH5($order_id, $money, $passback_params)
    {
        if ($passback_params == 'deposit') { // 余额充值
            $body = "账户余额充值";
        } else { // 订单支付
            $passback_params = 'goods';
            $sql = "SELECT goods_name FROM ecs_order_goods WHERE order_id = (SELECT order_id FROM ecs_order_info WHERE order_sn = '" . $order_id . "')";
            $goods_name = $this->getORM()->queryRows($sql); // 取出当前订单的商品名称  只用第一个
            $body = $goods_name[0]['goods_name'];
        }

        // 查询微信支付配置信息  config的值
        // $sql = "SELECT pay_config FROM ecs_payment WHERE pay_code = 'wxpay'";
        // $wx_pay = $this->getORM()->queryRow($sql);
        // $wx_config = unserialize($wx_pay['pay_config']);   // 暂时不使用数据库
        // $wx_config = wxpay_config();


        $hselect = "select * from ecs_shop_config where code = 'hfive'";
        $hquery = $this->getORM()->queryRow($hselect);
        $hresult = unserialize($hquery['value']);

        $wechatAppPay = new wxH5Pay($hresult['hfiveid'], $hresult['hfivesw'], $notify_url = $hresult['hfivegethome'], $hresult['hfivekey']); // 回调地址需要更改

        $params['body'] = $body; //商品描述
        $params['out_trade_no'] = $order_id; //自定义的订单号
        $params['total_fee'] = $money * 100; //订单金额 只能为整数 单位为分   传来的单位为元
        $params['trade_type'] = 'MWEB'; //交易类型 JSAPI | NATIVE | APP | WAP
        $params['attach'] = $passback_params; // 原样返回的数据 进行支付类型的判断

        $result = $wechatAppPay->h5unifiedOrder($params);


        $redirect_url = urlencode($hresult['hfivepage']);
        if (!empty($result['mweb_url'])) {
            $url = $result['mweb_url'] . '&redirect_url=' . $redirect_url; //redirect_url 是支付完成后返回的页面   后面的跳转地址需要更改
            return ['res' => true, "url" => $url];
        } else {
            // return false;   // 暂时没有错误
            return ['res' => false];
        }
    }

    public function doPaymentAliApp($order_sn, $money, $passback_params)
    {
        $sql = "SELECT goods_name FROM ecs_order_goods WHERE order_id = (SELECT order_id FROM ecs_order_info WHERE order_sn = '" . $order_sn . "')";
        $goods_name = $this->getORM()->queryRows($sql); // 取出当前订单的商品名称  只用第一个

        $aop = new AopClient();
        $request = new AlipayTradeAppPayRequest();

        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";


        $zselect = "select * from ecs_shop_config where code = 'zhifu'";
        $zquery = $this->getORM()->queryRow($zselect);
        $zresult = unserialize($zquery['value']);
        $aop->rsaPrivateKey = $zresult['zsy']; //请填写开发者私钥去头去尾去回车，一行字符串
        $aop->alipayrsaPublicKey = $zresult['zgy']; //请填写支付宝公钥，一行字符串
        $aop->gatewayUrl = $zresult['zwg'];
        $aop->appId = $zresult['zappid'];


        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        // $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        if ($passback_params != 'deposit') {
            // $passback_params = 'goods';  暂时不需要原样返回参数验证
            $bizcontent = "{\"body\":\"" . $goods_name[0]['goods_name'] . "\","
                . "\"subject\": \"" . $goods_name[0]['goods_name'] . "\","
                . "\"out_trade_no\": \"" . $order_sn . "\","
                . "\"total_amount\": \"" . sprintf('%.2f', $money) . "\","
                . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                . "}";
        } else {
            $bizcontent = "{\"body\":\"账户余额充值\","
                . "\"subject\": \"账户余额充值\","
                . "\"out_trade_no\": \"" . $order_sn . "\","
                . "\"total_amount\": \"" . sprintf('%.2f', $money) . "\","
                . "\"product_code\":\"QUICK_MSECURITY_PAY\","
                . "\"passback_params\":\"" . $passback_params . "\""
                . "}";
        }

        $request->setNotifyUrl($zresult['zgethome']); //商户外网可以访问的异步地址,回调地址
        $request->setBizContent($bizcontent);

        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        //echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。

        $data['payInfo'] = $response;
        return array('status' => 'succ', 'message' => '', 'response' => $data);
    }

    public function doPaymentWxApp($order_sn, $money, $passback_params)
    {
        if ($passback_params == 'deposit') { // 余额支付
            $body = '账户余额充值';
        } else { // 商品购买
            $passback_params = 'goods';
            $sql = "SELECT goods_name FROM ecs_order_goods WHERE order_id = (SELECT order_id FROM ecs_order_info WHERE order_sn = '" . $order_sn . "')";
            $goods_name = $this->getORM()->queryRows($sql); // 取出当前订单的商品名称  只用第一个
            $body = $goods_name[0]['goods_name'];
        }

        //$wx_config = wxpay_config();
        $aselect = "select * from ecs_shop_config where code = 'app'";
        $aquery = $this->getORM()->queryRow($aselect);
        //        $aunser = mysqli_fetch_assoc($aquery);
        $aresult = unserialize($aquery['value']);
        ////填写微信分配的开放平台账号ID https://open.weixin.qq.com
        $option['appid'] = $aresult['appid'];
        //填写微信支付分配的商户号
        $option['mchid'] = $aresult['appsw'];
        //填写微信支付结果回调地址
        $option['notify_url'] = $aresult['appgethome'];
        //填写微信商户支付密钥
        $option['key'] = $aresult['appkey'];

        $wxpaysdk = new WxpayAppSDK($option);

        $params['body'] = $body; //商品描述
        $params['out_trade_no'] = $order_sn; //自定义的订单号
        $params['total_fee'] = $money * 100; //订单金额 只能为整数 单位为分
        $params['attach'] = $passback_params;

        $data = $wxpaysdk->getAppPaySign($params);

        return array('status' => 'succ', 'message' => '', 'response' => $data);
    }

    public function doPaymentAliWap($out_trade_no, $total_amount, $body, $quit_url, $passback_params)
    {
        // 支付宝移动支付

        header('Content-type:text/html; Charset=utf-8');

        //$ali_config = alipay_config();
        $zselect = "select * from ecs_shop_config where code = 'zhifu'";

        $zquery = $this->getORM()->queryRow($zselect);
        $zresult = unserialize($zquery['value']);

        $appid = $zresult['zappid']; //4https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
        $returnUrl = $zresult['alipay_h5_url']; //付款成功后的同步回调地址   这个为空就行
        $geteawayUrl = $zresult['zwg']; //3支付宝网关

        $notifyUrl = $zresult['zgethome']; //2付款成功后的异步回调地址
        $outTradeNo = $out_trade_no; //你自己的商品订单号
        $payAmount = $total_amount; //付款金额，单位:元
        $orderName = $body; //订单标题
        $quitUrl = $quit_url;

        $passback_params = $passback_params;
        if ($passback_params != 'deposit') { //不是余额充值既为订单付款
            $passback_params = 'goods';
        }
        $signType = 'RSA2'; //签名算法类型，支持RSA2和RSA，推荐使用RSA2
        $rsaPrivateKey = $zresult['zsy']; //1商户私钥，填写对应签名算法类型的私钥，如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310
        /*** 配置结束 ***/

        $aliPay = new AlipayService();
        $aliPay->setAppid($appid);
        $aliPay->setReturnUrl($returnUrl);
        $aliPay->setNotifyUrl($notifyUrl);
        $aliPay->setRsaPrivateKey($rsaPrivateKey);
        $aliPay->setTotalFee($payAmount);
        $aliPay->setOutTradeNo($outTradeNo);
        $aliPay->setOrderName($orderName);
        $aliPay->setQuitUrl($quitUrl);
        $aliPay->setPassbackParams($passback_params);
        $aliPay->setGatewayUrl($geteawayUrl);

        $sHtml = $aliPay->doPay();

        return $sHtml;
    }

    public function doPaymentWxMp($order_sn, $money, $openid, $passback_params = '')
    {
        // 微信小程序支付
        if ($passback_params == 'deposit') {
            $body = "账户余额充值";
        } else {
            $passback_params = 'goods';
            $sql = "SELECT goods_name FROM ecs_order_goods WHERE order_id = (SELECT order_id FROM ecs_order_info WHERE order_sn = '" . $order_sn . "')";
            $goods_name = $this->getORM()->queryRows($sql);
            $body = $goods_name[0]['goods_name'];
        }
        //$wx_config = wxpay_config();
        $select = "select * from ecs_shop_config where code = 'small'";
        $query = $this->getORM()->queryRows($select);
        $result = unserialize($query[0]['value']);


        $wxpay = new wxMpPay($result['id'], $result['sw'], $result['gethome'], $result['key'], $openid, $body, $order_sn, $money, $passback_params);
        $data = $wxpay->Pay(); // 获取接口信息
        if ($data['state'] == 1) {
            return array(
                "timeStamp" => $data['timeStamp'],
                "nonceStr" => $data['nonceStr'],
                "package" => $data['package'],
                "paySign" => $data['paySign'],
                "out_trade_no" => $data['out_trade_no'],
                "res" => true,
            );
        } else if ($data['state'] === 0) {
            return array("res" => false, "msg" => "支付配置错误");
        }
    }

    public function getPayConfig($code)
    {
        // 获取支付配置信息
        $sql = "select * from ecs_shop_config where code = '" . $code . "'";
        $data = $this->getORM()->queryRow($sql);
        $data = unserialize($data["value"]); // 数据转换
        return $data;
    }

    public function getweixinConfig()
    {
        // 获取支付配置信息
        $sql = "select * from ecs_shop_config where code = 'hfive'";
        $data = $this->getORM()->queryRow($sql);
        $data = unserialize($data["value"]); // 数据转换
        return $data;
    }

    //微信浏览器H5支付
    public function wechatPay($order_sn, $money, $openid, $passback_params, $code)
    {
        $sql = "select order_id,order_sn,order_amount,user_id from ecs_order_info where order_sn = " . $order_sn;
        $orders = $this->getORM()->queryRow($sql);

        $sql = "select goods_name from ecs_order_goods where order_id = " . $orders['order_id'];
        $goods = $this->getORM()->queryRow($sql);
        $this->user_model = new UserModel();
        $hresult = $this->user_model->wxLogin();
        ////填写微信分配的开放平台账号ID https://open.weixin.qq.com
        $option['appid'] = $hresult['hfiveid'];  //运营宝的appid
        //填写微信支付分配的商户号
        $option['mchid'] = $hresult['hfivesw']; //运营宝的商户号
        //填写微信支付结果回调地址
        $option['notify_url'] = $hresult['hfivegethome'];
        //填写微信商户支付密钥
        $option['key'] = $hresult['hfivekey'];
        $option['appsecret'] = $hresult['hfivesecret'];

        $option['redirect_url'] = $hresult['hfivepage'];

        $sql = "select openid_h5 from ecs_users where user_id = {$orders['user_id']}";
        $res = $this->getORM()->queryRow($sql);
        if (!empty($code) && $code != 'undefined' && empty($res['openid_h5'])) {
            //通过code获取access_token
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $option['appid'] . '&secret=' . $option['appsecret'] . '&code=' . $code . '&grant_type=authorization_code';
            $ret_oa_json = $this->curl_get_contents($url);
            $resultjson = json_decode($ret_oa_json);
            $access_token = $resultjson->access_token;
            $openid = $resultjson->openid;
            $sql = "update ecs_users set  openid_h5 = '" . $openid . "' where  user_id  = '" . $orders['user_id'] . "'";
            $this->getORM()->query($sql);
        }
        if (empty($res['openid_h5'])) {
            $host_url = \PhalApi\DI()->config->get('app');
            $host_url['host_url'] = $host_url['host_url'] . "h5/apiCart/pay/main?orderId=" . $order_sn;
            $host_url['host_url'] = urlencode($host_url['host_url']);
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $option['appid'] . "&redirect_uri=" . $host_url['host_url'] . "&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
            return array('status' => 'code', 'url' => $url);
        } else {
            $openid = $res['openid_h5'];
        }

        if (!empty($option['appid'])) {
            $_SESSION['appid'] = $option['appid'];
        } else {
            $option['appid'] = $_SESSION['appid'];
        }
        $wxpaysdk = new wxH5Pay($option['appid'], $option['mchid'], $option['notify_url'], $option['key']); // 回调地址需要更改

        $OrderModel = new OrderModel();
        $order_data = $OrderModel->pay_detail($order_sn, $orders['user_id']);
        if ($money <= 0) {
            $money = $order_data['total_fee'];
        }
        $params['body'] = $goods['goods_name']; //商品描述
        $params['out_trade_no'] = $orders['order_sn']; //自定义的订单号
        $params['total_fee'] = $money * 100; //订单金额 只能为整数 单位为分   传来的单位为元
        $params['trade_type'] = 'JSAPI'; //交易类型 JSAPI | NATIVE | APP | WAP
        $params['openid'] = $openid;
        $params['attach'] = $passback_params; // 原样返回的数据 进行支付类型的判断

        $result = $wxpaysdk->unifiedOrder($params, $option['key']);
        $redirect_url = $option['redirect_url'];

        return array('res' => true, 'result' => $result, 'redirect_url' => $redirect_url);
    }

    public function curl_get_contents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:26.0) Gecko/20100101 Firefox/26.0");
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    /**
     * 建行龙支付请求数据组装
     * @param $orderNum
     * @param $price
     * @param $ip
     * @return array
     */
    public function BankCcbPay($order_sn, $money, $user_id)
    {
        $payment = new PaymentModel;
        $ccb_config = $payment->getPayConfig('pay_ccb');
        if (empty($ccb_config)) {
            return array("res" => false, "msg" => "支付配置错误");
        }
        $sql = "select * from ecs_order_info where  order_sn = '" . $order_sn . "'";
        $order_info = $this->getORM()->queryRow($sql);
        if (empty($order_info)) {
            return array("res" => false, "msg" => "订单数据异常。");
        }
//        if($money != $order_info['goods_amount']){
//            return array("res" => false, "msg" => "支付金额与订单金额不一致。");
//        }
        // 商户代码, 由建行统一分配
//        $MERCHANTID = '105000159435126';
        $MERCHANTID = $ccb_config['merchant'];
        // 商户柜台代码, 由建行统一分配
//        $POSID = '050835675';
        $POSID = $ccb_config['pos'];
        // 分行代码, 由建行统一指定
//        $BRANCHID = '310000000';
        $BRANCHID = $ccb_config['branch'];
        // 订单号, 由商户提供，最长 30 位
        $ORDERID = $order_sn;
        // 付款金额
        $PAYMENT = $money;
        // 币种 01-人民币
        $CURCODE = '01';
        // 交易码, 由建行统一分配为 520100
        $TXCODE = '520100';
//        $TXCODE = '410321';
        // 备注 1, 一般作为商户自定义备注信息使用，可在对账单中显示。
        $REMARK1 = '';
        // 备注 2, 一般作为商户自定义备注信息使用，可在对账单中显示。
        $REMARK2 = '';
        // 接口类型, 分行业务人员在 P2 员工渠道后台设置防钓鱼的开关。1-防钓鱼接口
        $TYPE = 1;
        // 公钥后 30 位, 商户从建行商户服务平台下载，截取后 30 位。仅作为源串参加 MD5 摘要，不作为参数传递
//        $PUB = '04982426d55dd12009afa309020111';
        $PUB = $ccb_config['pub'];
        // 网关类型, 默认送 0
        $GATEWAY = 0;
        // 客户在商户系统中的 IP，即客户登陆（访问）商户系统时使用的 ip
//        $CLIENTIP = '117.186.147.218';//服务器的外网IP
//        $CLIENTIP = '47.103.121.22';//服务器的外网IP --测试站
        $CLIENTIP = '47.92.49.1';//服务器的外网IP---正式站
        // 客户在商户系统中注册的信息，中文需使用 escape 编码
        $REGINFO = '';
        // 客户购买的商品, 中文需使用 escape 编码
        $PROINFO = '';
        // 商户 URL, 商户送空值即可
        $REFERER = '';
        $CCB_IBSVersion = 'V6';
        // 商户客户端的intent�filter/schema, comccbpay+商户代码+商户自定义的标示app的字符串(只能为字母或数字), 例comccbpay105320148140002alipay,
        // 建行移动端文档就要求这么拼接, IOS文档却直接写取你的应用程序的URL Schemes即可, 你们自己看文档要求吧
        $THIRDAPPINFO = 'comccbpay' . $MERCHANTID . 'myAPP';
//        $THIRDAPPINFO = 'myAPP';

        // 支付方式位图, 10位位图，1为开，0为关, 第一位：支付宝, 第二位：微信,第三位：银联支付（保留位，暂不开放）其余位数预留。例如支持支付宝和微信支付则上送1100000000该字段不参与 MAC计算
        $PAYMAP = '0000000000';

        // md5加密参数
        $md5Params = [
            'MERCHANTID' => $MERCHANTID,
            'POSID' => $POSID,
            'BRANCHID' => $BRANCHID,
            'ORDERID' => $ORDERID,
            'PAYMENT' => $PAYMENT,
            'CURCODE' => $CURCODE,
            'TXCODE' => $TXCODE,
            'REMARK1' => $REMARK1,
            'REMARK2' => $REMARK2,
            'TYPE' => $TYPE,
            'PUB' => $PUB,
            'GATEWAY' => $GATEWAY,
            'CLIENTIP' => $CLIENTIP,
            'REGINFO' => $REGINFO,
            'PROINFO' => $PROINFO,
            'REFERER' => $REFERER,
            'THIRDAPPINFO' => $THIRDAPPINFO,
            // 'TIMEOUT' => ''
        ];
        $md5Query = http_build_query($md5Params);
        // MAC 校验域, 采用标准 MD5 算法
        $MAC = md5($md5Query);
        // 请求参数
        $urlParams = [
            'MERCHANTID' => $MERCHANTID,
            'POSID' => $POSID,
            'BRANCHID' => $BRANCHID,
            'ORDERID' => $ORDERID,
            'PAYMENT' => $PAYMENT,
            'CURCODE' => $CURCODE,
            'TXCODE' => $TXCODE,
            'REMARK1' => $REMARK1,
            'REMARK2' => $REMARK2,
            'TYPE' => $TYPE,
            'GATEWAY' => $GATEWAY,
            'CLIENTIP' => $CLIENTIP,
            'REGINFO' => $REGINFO,
            'PROINFO' => $PROINFO,
            'REFERER' => $REFERER,
            'THIRDAPPINFO' => $THIRDAPPINFO,
            'CCB_IBSVersion' => $CCB_IBSVersion,
//            'PT_STYLE' => '2',
//            'QRCODE' => '',/**/
//            'CHANNEL' => '1',
            'MAC' => $MAC,
//            'PAYMAP' => '0000000000'
        ];
        $orderStr = http_build_query($urlParams);
        $url = 'https://ibsbjstar.ccb.com.cn/CCBIS/B2CMainPlatP1_EPAY?' . $orderStr;
        $back = $this->send_post($url);
        preg_match("/action=[\'|\"](.*)[\'|\"]\s/", $back, $back_url);
        if (empty($back_url)) {
            return array("res" => false, "msg" => "支付参数异常。");
        }
        return [
            // 我这里只是返回url需要拼接的参数, https://ibsbjstar.ccb.com.cn/CCBIS/ccbMain?加上$orderStr就是完整的商户下单请求地址
            'orderStr' => $back_url[1]
//            'orderStr' => $url
        ];
    }

    function send_post($url, $post_data)
    {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }


    public function BankCcbPayNotify($address, $service_port, $send_data, $order_sn)
    {
//        ini_set("display_errors", "On");
//        error_reporting(E_ALL | E_STRICT);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $result = socket_connect($socket, $address, $service_port);
        if ($result === false) {
            echo "socket连接失败: ($result) " . socket_strerror($result) . "\n";
            exit;
        }
        // 发送命令
        $string = '';
        socket_write($socket, $send_data, strlen($send_data));
        while ($out = socket_read($socket, 2048)) {
            $string .= $out;
        }
        socket_close($socket);
        if (strstr($string, '|', true) == 'Y') {
            echo "验证成功";

            $sql = "update ecs_order_info set pay_time='" . time() . "', order_status = '1',pay_status = '2',pay_name='龙支付',lastmodify = '" . time() . "'  WHERE order_sn = '" . $order_sn . "'";
            $this->getORM()->queryRow($sql);
            return true;
        } else {
            echo "验证失败";
            return false;
        }
    }

    public function doPaymentMpH5Jsapi($order_sn, $money, $openid, $passback_params, $code)
    {
        $sql = "select * from ecs_account_log where order_sn = " . $order_sn;
        $orders = $this->getORM()->queryRow($sql);
        $this->user_model = new UserModel();

        $hresult = $this->user_model->wxLogin();
        ////填写微信分配的开放平台账号ID https://open.weixin.qq.com
        $option['appid'] = $hresult['hfiveid'];  //运营宝的appid
        //填写微信支付分配的商户号
        $option['mchid'] = $hresult['hfivesw']; //运营宝的商户号
        //填写微信支付结果回调地址
        $option['notify_url'] = $hresult['hfivegethome'];
        //填写微信商户支付密钥
        $option['key'] = $hresult['hfivekey'];
        $option['appsecret'] = $hresult['hfivesecret'];

        $option['redirect_url'] = $hresult['hfivepage'];

        $sql = "select openid_h5 from ecs_users where user_id = {$orders['user_id']}";
        $res = $this->getORM()->queryRow($sql);
        if (!empty($code) && $code != 'undefined' && empty($res['openid_h5'])) {
            //通过code获取access_token
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $option['appid'] . '&secret=' . $option['appsecret'] . '&code=' . $code . '&grant_type=authorization_code';
            $ret_oa_json = $this->curl_get_contents($url);
            $resultjson = json_decode($ret_oa_json);
            $access_token = $resultjson->access_token;
            $openid = $resultjson->openid;
            $sql = "update ecs_users set  openid_h5 = '" . $openid . "' where  user_id  = '" . $orders['user_id'] . "'";
            $this->getORM()->query($sql);
        }
        if (empty($res['openid_h5'])) {
            $host_url = \PhalApi\DI()->config->get('app');
            //$host_url['host_url'] =   "http://ecshop.yunyingbao.net/h5/apiMember/deposit/main?orderId=" . $order_sn;
            $host_url['host_url'] = $host_url['host_url'] . "h5/apiMember/deposit/main?orderId=" . $order_sn;
            $host_url['host_url'] =  urlencode($host_url['host_url']);
            $url  = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $option['appid'] . "&redirect_uri=" . $host_url['host_url'] . "&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect
";
            return array('status' => 'code', 'url' => $url);
        } else {
            $openid = $res['openid_h5'];
        }
        if (!empty($option['appid'])) {
            $_SESSION['appid']   = $option['appid'];
        } else {
            $option['appid'] = $_SESSION['appid'];
        }
        $wxpaysdk = new wxH5Pay($option['appid'], $option['mchid'], $option['notify_url'], $option['key']); // 回调地址需要更改
        $params['body'] = '充值'; //商品描述
        $params['out_trade_no'] = $orders['order_sn']; //自定义的订单号
        $params['total_fee'] = $money * 100; //订单金额 只能为整数 单位为分   传来的单位为元
        $params['trade_type'] = 'JSAPI'; //交易类型 JSAPI | NATIVE | APP | WAP
        $params['openid'] = $openid;
        $params['attach'] = $passback_params; // 原样返回的数据 进行支付类型的判断
        $result = $wxpaysdk->unifiedOrder($params, $option['key']);
        $redirect_url = $option['redirect_url'];


        return array('res' => true, 'result' => $result, 'redirect_url' => $redirect_url);
    }
}