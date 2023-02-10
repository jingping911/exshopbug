<?php
require '../../../ecshop/data/config.php';

$db_msg = array(
    'db_host' => $db_host,
    'db_user' => $db_user,
    'db_pwd'  => $db_pass,
    'db_name' => $db_name
);

$wx = new wxNotify();
$wx->Notify($db_msg);

class wxNotify
{
    /**
     * 微信通知中转
     */
    public function Notify($db_msg)
    {
        // 链接数据库

        $conn = mysqli_connect($db_msg['db_host'], $db_msg['db_user'], $db_msg['db_pwd'], $db_msg['db_name']);
        
        $sql = "select * from ecs_shop_config where code = 'hfive'";

        $res = mysqli_query($conn, $sql);

        $row = mysqli_fetch_assoc($res);

        $wx_config = unserialize($row['value']);

        // 获取微信回调的数据
        $notifiedData = file_get_contents('php://input');

        $url = $wx_config['hfivehome']; // 直接取数据库

        $res = $this->curl_get_contents($url, $notifiedData);
        file_put_contents('./wechat.log', var_export($res, true), FILE_APPEND); // 记录返回数据  便于查看
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
