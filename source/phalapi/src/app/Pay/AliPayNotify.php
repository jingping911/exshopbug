<?php

namespace App\Pay;

use App\Pay\AlipayTradeAppPayRequest;
use App\Pay\AopClient as AopClient;
use PhalApi\Model\NotORMModel as NotORM;
use App\Model\Payment as PaymentModel;
use App\Model\Order as Order;

/**
 * 支付宝回调验证
 */
class AliPayNotify extends NotORM
{

    public function Notify($data)
    {
        $order = new Order();
        $aop = new AopClient();
        $request = new AlipayTradeAppPayRequest();

        $payment = new PaymentModel;
        $ali_config = $payment->getPayConfig('zhifu');

        $aop->gatewayUrl = $ali_config['zwg'];
        $aop->appId = $ali_config['zappid'];
        $aop->rsaPrivateKey = $ali_config['zsy']; //请填写开发者私钥去头去尾去回车，一行字符串

        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $ali_config['zgy']; //请填写支付宝公钥，一行字符串

        $param = $data; //接受post数据，参考【支付结果异步通知】
        // file_put_contents('./alipay6666.log', var_export($param, true), FILE_APPEND);
        unset($param["service"]); // 去掉多余参数
        $res = $aop->rsaCheckV1($param, '', 'RSA2');
        // file_put_contents('./alipay1111.log', var_export($res, true), FILE_APPEND);
        if (!$res) {
            // log_error("支付宝notify通知",$param['out_trade_no']."参数验证失败");
            //        return error("参数验证失败");
            echo '<pre>';
            var_dump('参数验证失败');
            exit;
        }

        file_put_contents('./alipay1.log', var_export($param, true), FILE_APPEND);
        if ($param['passback_params'] == 'deposit') { // 余额充值结果回调走这里
            if ($param['out_trade_no'] && $param['total_amount']) {
                $sql = "select * from ecs_account_log where order_sn = '" . $param['out_trade_no'] . "'";
                $res = $this->getORM()->queryRow($sql);
                if (!$res) {
                    echo "无该订单号~~~";
                    exit;
                }
                if ($res['state'] == 'true') {
                    echo "该订单已支付";
                    exit;
                }
                // 验证订单金额与回调金额是否一致
                if ($res["user_money"] != $param["total_amount"]) {
                    echo "订单金额不一致";
                    exit;
                }
                // 一旦成功立即更新状态
                $sql = "update ecs_account_log set change_desc = '余额充值成功',user_money = '" . $param['total_amount'] . "', state = 'true' where order_sn = '" . $param['out_trade_no'] . "' and user_id = '" . $res['user_id'] . "'"; // 更新当前的信息
                $this->getORM()->queryRow($sql); // 更新当前充值日志

                $date = date("Y-m-d H:i:s", time());
                // 进行日志记录，如未充值到账，请查找日志处理
                $log = "订单:{$param["out_trade_no"]}验证通过,订单金额:{$res["user_money"]},回调金额:{$param["total_amount"]},记录时间:{$date}.\n";
                file_put_contents('./AliPay.log', $log, FILE_APPEND); // 记录转换为数组的数据  便于查看相应的参数

                $sql = "update ecs_users set user_money = (user_money + " . $param['total_amount'] . ") where user_id = " . $res['user_id'] . "";
                $this->getORM()->queryRow($sql); // 对用户账户进行充值

            }

            return;
        }
        if ($param['out_trade_no'] && $param['total_amount']) {
            $sql = "select * from ecs_order_info where order_sn = '" . $param['out_trade_no'] . "' ";
            $res = $this->getORM()->queryRow($sql);
            if (!$res) {
                echo "无该订单号~~~";
                exit;
            }
            if ($res['pay_status'] == '2') {
                echo "该订单已支付";
                exit;
            }
            $sql = "update ecs_order_info set pay_time='" . time() . "', order_status = '1',pay_status = '2',pay_name = '支付宝',lastmodify = '" . time() . "'  WHERE order_sn = '" . $param['out_trade_no'] . "'";
            $this->getORM()->queryRow($sql);

            $order->erp_yue($param['out_trade_no']);

            $sql = "select user_id from  ecs_order_info  WHERE order_sn = '" . $param['out_trade_no'] . "'"; // 获得用户ID
            $data = $this->getORM()->queryRow($sql);

            $sql = "insert into ecs_account_log set user_id = '" . $data['user_id'] . "',user_money = '-" . $param['total_amount'] . "',frozen_money = '0',rank_points = '0',pay_points = '0',change_time = '" . time() . "',change_desc = '支付宝支付订单 " . $param['out_trade_no'] . "',change_type='99' "; // 记录该次消费
            $this->getORM()->queryRow($sql); // 记录本次消费
            $order->sms_order_payed($param['out_trade_no']); //消费者支付订单时发商家:
            $order->sms_order_payed_to_customer($param['out_trade_no']); //消费者支付订单时发消费者

        }
    }
}
