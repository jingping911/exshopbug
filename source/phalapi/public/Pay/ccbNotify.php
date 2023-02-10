<?php
require '../../../ecshop/data/config.php';

$db_msg = array(
    'db_host' => $db_host,
    'db_user' => $db_user,
    'db_pwd'  => $db_pass,
    'db_name' => $db_name
);

$wx = new ccbNotify();
$wx->Notify($db_msg);

class ccbNotify
{
    /**
     * 微信通知中转
     */
    public function Notify($db_msg)
    {
        // 链接数据库

        $conn = mysqli_connect($db_msg['db_host'], $db_msg['db_user'], $db_msg['db_pwd'], $db_msg['db_name']);

        $sql = "select * from ecs_shop_config where code = 'pay_ccb'";

        $res = mysqli_query($conn, $sql);

        $row = mysqli_fetch_assoc($res);

        $wx_config = unserialize($row['value']);

        $url = $wx_config['pay_ccb_url']; // 直接取数据库erfx
//        $url = 'https://api.ecshop.test2.shopex123.com/?service=App.Pay.BankCcbPayNotify'; // 直接取数据库

        $res = $this->curl_get_contents($url, $_GET);

        return $res;
    }
    public function curl_get_contents($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'RMDesign');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}
