<?php

namespace App\Pay;

use PhalApi\Model\NotORMModel as NotORM;
use App\Model\Payment as PaymentModel;
use App\Model\Order as Order;

class WeChatNotify extends NotORM
{
    /**
     * 异步通知
     * @return mixed
     */
    public function Notify()
    {
        $order = new Order();
        // 获取微信回调的数据
        $notifiedData = file_get_contents('php://input');

        //XML格式转换
        $xmlObj = simplexml_load_string($notifiedData, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xmlObj = json_decode(json_encode($xmlObj), true); // 这里是最终转化后得到的数据

        $payment = new PaymentModel;
        $wx_config = $payment->getPayConfig('hfive');

        // key
        $key = $wx_config['hfivekey'];

        file_put_contents('./wechat4.log', var_export($notifiedData, true), FILE_APPEND); // 记录原始返回的xml数据  便于验证
        //是否成功支付
        if ($xmlObj['return_code'] == "SUCCESS" && $xmlObj['result_code'] == "SUCCESS") {
            file_put_contents('./wechat5.log', var_export($xmlObj, true), FILE_APPEND); // 记录转换为数组的数据  便于查看相应的参数
            //把签名去掉
            $xmlSign = $xmlObj['sign'];
            unset($xmlObj['sign']);

            $sign = $this->appgetSign($xmlObj, $key);

            $data = $xmlObj;
            if ($sign === $xmlSign) {
                //验证通过，确认已经支付

                // 进行订单的验证及状态的更改
                if ($data['attach'] == 'deposit') {
                    // 这个就是余额充值啦
                    $sql = "select * from ecs_account_log where order_sn = '" . $data['out_trade_no'] . "'";
                    $res = $this->getORM()->queryRow($sql);
                    if (!$res) {
                        echo "无该订单号~~~";
                        exit;
                    }
                    if ($res['state'] == 'true') {
                        echo "该订单已支付";
                        exit;
                    }
                    // 再判断金额是否一致
                    $total_fee = $data["total_fee"] / 100;
                    if ($res["user_money"] != $total_fee) {
                        echo "订单金额不一致";
                        exit;
                    }

                    // 验证通过立即更改订单状态
                    $sql = "update ecs_account_log set change_desc = '余额充值成功',user_money = '" . $data['total_fee'] / 100 . "', state = 'true' where order_sn = '" . $data['out_trade_no'] . "' and user_id = '" . $res['user_id'] . "'"; // 更新当前的信息
                    $this->getORM()->queryRow($sql); // 更新当前充值日志

                    $date = date("Y-m-d H:i:s", time());
                    // 进行日志记录，如未充值到账，请查找日志处理
                    $log = "订单:{$data["out_trade_no"]}验证通过,订单金额:{$res["user_money"]},回调金额:{$total_fee},记录时间:{$date}.\n";
                    file_put_contents('./wechatPay.log', $log, FILE_APPEND); // 记录转换为数组的数据  便于查看相应的参数

                    $sql = "update ecs_users set user_money = (user_money + " . $data['total_fee'] / 100 . ") where user_id = " . $res['user_id'] . "";
                    $this->getORM()->queryRow($sql); // 对用户账户进行充值

                } else if ($data['attach'] == 'goods') {
                    // 这个是商品的订单支付
                    $sql = "select * from ecs_order_info where order_sn = '" . $data['out_trade_no'] . "' ";
                    $res = $this->getORM()->queryRow($sql);
                    if (!$res) {
                        echo "无该订单号~~~";
                        exit;
                    }
                    if ($res['pay_status'] == '2') {
                        echo "该订单已支付";
                        exit;
                    }
                    $sql = "update ecs_order_info set pay_time='" . time() . "', order_status = '1',pay_status = '2',pay_name='微信',lastmodify = '" . time() . "'  WHERE order_sn = '" . $data['out_trade_no'] . "'";
                    $this->getORM()->queryRow($sql);

                    $order->erp_yue($data['out_trade_no']);

                    $sql = "select user_id from  ecs_order_info  WHERE order_sn = '" . $data['out_trade_no'] . "'"; // 获得用户ID
                    $res = $this->getORM()->queryRow($sql);

                    $sql = "insert into ecs_account_log set user_id = '" . $res['user_id'] . "',user_money = '-" . $data['total_fee'] / 100 . "',frozen_money = '0',rank_points = '0',pay_points = '0',change_time = '" . time() . "',change_desc = '微信支付订单 " . $data['out_trade_no'] . "',change_type='99',order_sn='" . $data['out_trade_no'] . "' "; // 记录该次消费
                    $this->getORM()->queryRow($sql); // 记录本次消费
                    $order->sms_order_payed($data['out_trade_no']); //消费者支付订单时发商家:
                    $order->sms_order_payed_to_customer($data['out_trade_no']); //消费者支付订单时发消费者
                }

                //告诉微信不用重复通知
                echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                exit;
            }
        }
        return array("res" => false, "data" => '');
    }

    /*
     * 格式化参数格式化成url参数  生成签名sign
     */
    private function appgetSign($Obj, $appwxpay_key)
    {

        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }

        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->ToUrlParams($Parameters);

        //签名步骤二：在string后加入KEY

        if ($appwxpay_key) {
            $String = $String . "&key=" . $appwxpay_key;
        }

        //签名步骤三：MD5加密
        $String = md5($String);

        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);

        return $result_;
    }

    private function ToUrlParams($Parameters)
    {
        $buff = "";
        foreach ($Parameters as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
}
