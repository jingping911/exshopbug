<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;


class Vcode extends NotORM
{
    protected  function getTableName($id)
    {
        return 'vcode';
    }

    protected function getTableKey($table)
    {
        return 'sms_id';
    }
    /**
     * 检测手机号码是否正确
     *
     */
    function is_moblie($moblie)
    {
        return  preg_match("/^1[345789]\d{9}$/", $moblie);
    }

    //检查手机号和发送的内容并生成生成短信队列
    function get_contents($phones, $msg)
    {
        if (empty($phones) || empty($msg)) {
            return false;
        }
        $msg .= "【" . $GLOBALS['_CFG']['default_sms_sign'] . "】";

        $phone_key = 0;
        $i = 0;
        $phones = explode(',', $phones);
        foreach ($phones as $key => $value) {
            // 打平台单次请求每次不超过200个手机号
            if ($i < 200) {
                $i++;
            } else {
                $i = 0;
                $phone_key++;
            }

            if ($this->is_moblie($value)) {
                $phone[$phone_key][] = $value;
            } else {
                $i--;
            }
        }
        if (!empty($phone)) {
            foreach ($phone as $phone_key => $val) {
                if (EC_CHARSET != 'utf-8') {
                    $phone_array[$phone_key]['phones'] = implode(',', $val);
                    $phone_array[$phone_key]['content'] = iconv('gb2312', 'utf-8', $msg);
                } else {
                    $phone_array[$phone_key]['phones'] = implode(',', $val);
                    $phone_array[$phone_key]['content'] = $msg;
                }
            }
            return $phone_array;
        } else {
            return false;
        }
    }

    //查询是否已有通行证
    function has_registered()
    {
        $sql = "SELECT `value`
                FROM ecs_shop_config  WHERE `code` = 'certificate'";

        $result = $this->getORM()->queryRow($sql);
        if (empty($result)) return false;

        $result = unserialize($result['value']);

        if (!$result['yunqi_code']) return false;

        return true;
    }

    /**
     * 获取云起证书信息
     * @param   string  $key
     * @return  string
     */
    function get_certificate_info($key, $code = 'certificate')
    {
        $sql = "select value from ecs_shop_config where code='" . $code . "'";
        $row = $this->getORM()->queryRow($sql);
        if (!$row) return false;
        $certificate = unserialize($row['value']);
        return isset($certificate[$key]) ? $certificate[$key] : false;
    }
    private function ksort($data)
    {
        if (empty($data)) return $data;
        ksort($data);
        foreach ($data as $key => &$val) {
            if (is_array($val)) {
                $val = $this->ksort($val);
            }
        }
        return $data;
    }

    /**
     * 获取前半小时短信发送次数
     * @param   string  $key
     * @return  string
     */
    function get_sms_number($phone)
    {
        $time = time()-1800;
        $sql = "select count(sms_id) as number from ecs_vcode where mobile='" . $phone . "' and add_time>".$time;
        $row = $this->getORM()->queryRow($sql);
        if ($row['number']<5) {
            return false;
        }else{
            return true;
        }
    }

    public  function signup_sms($user_name)
    {

        $sql = "select value from  ecs_shop_config  WHERE code = 'sms_set_update'";
        $res = $this->getORM()->queryRow($sql);
        $result = unserialize($res['value']); //数据转换
        //等于1 开始其他短信
        if ($result['status'] == 1) {

            $vcode =  $this->set_vcode($user_name);
            $result['vcode'] = $vcode;
            $result['mobile'] = $user_name;


            $res =  $this->AliSendSms($result);

            if ($res->Code == 'OK') {
                return true;
            } else {
                return false;
            }
        }
        //使用ecshop 自带的短信
        if ($result['status'] == 2) {
            $time = time() + 300;
            $type = 'signup';
            $shop = 'ABCDEFG123456789';
            $md5 = $time . $type . $user_name . $shop;
            $sign =  strtoupper(md5($md5));
            //            $url = "http://shop.kaiyuykt.com/user.php?act=send&user_name=".$user_name."&t=".$time."&s=".$sign."&g=signup";
            $data = \PhalApi\DI()->config->get('app');
            $url = $data['host_url'] . "user.php?act=send&user_name=" . $user_name . "&t=" . $time . "&s=" . $sign . "&g=signup";
            $status =  $this->http_request($url);
        }

        //        $res =  <<<EOF
        /*<?xml version="1.0" encoding="utf-8" ?><returnsms>*/
        // <returnstatus>Success</returnstatus>
        // <message>ok</message>
        // <remainpoint>49</remainpoint>
        // <taskID>27735652</taskID>
        // <successCounts>1</successCounts></returnsms>
        //EOF;

        return $status;
    }
    public function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $info = curl_exec($curl);
        curl_close($curl);
        return $info;
    }

    public function set_vcode($mobile)
    {
        $vcode = rand(100000, 999999);
        $data = array(
            'mobile' => $mobile,
            'vcode' => $vcode,
            'add_time' => time(),
        );

        $orm = $this->getORM();
        $orm->insert($data);
        return $vcode;
    }

    public function check_vcode($mobile, $vcode)
    {
        $check_time = time() - 120;

        $sql = "select sms_id from ecs_vcode where mobile='" . $mobile . "' and vcode='" . $vcode . "' and add_time >'" . $check_time . "'";
        $check_data = $this->getORM()->queryAll($sql);


        if ($check_data) {
            return true;
        }

        return false;
    }

    public function get_vcode()
    {
        $randStr = str_shuffle('1234567890');
        $rand = substr($randStr, 0, 6);
        return $rand;
    }


    /**
     * 阿里云发送短信
     */
    function AliSendSms($result)
    {

        $params = array();

        // *** 需用户填写部分 ***
        // fixme 必填：是否启用https
        $security = false;

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = $result['accessKeyId'];
        $accessKeySecret = $result['accessKeySecret'];

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $result['mobile'];

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $result['SignName'];

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $result['TemplateCode'];

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = array(
            "code" => $result['vcode'],
        );

        // fixme 可选: 设置发送短信流水号
        // $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        // $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求


        // 此处可能会抛出异常，注意catch
        $content = $this->AliRequest(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            )),
            $security
        );

        return $content;
    }

    /**
     * 生成签名并发起请求
     *
     * @param $accessKeyId string AccessKeyId (https://ak-console.aliyun.com/)
     * @param $accessKeySecret string AccessKeySecret
     * @param $domain string API接口所在域名
     * @param $params array API具体参数
     * @param $security boolean 使用https
     * @param $method boolean 使用GET或POST方法请求，VPC仅支持POST
     * @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
     */
    public function AliRequest($accessKeyId, $accessKeySecret, $domain, $params, $security = false, $method = 'POST')
    {
        $apiParams = array_merge(array(
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0, 0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $accessKeyId,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $params);
        ksort($apiParams);

        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "${method}&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&", true));

        $signature = $this->encode($sign);

        $url = ($security ? 'https' : 'http') . "://{$domain}/";

        try {
            $content = $this->fetchContent($url, $method, "Signature={$signature}{$sortedQueryStringTmp}");
            return json_decode($content);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    private function fetchContent($url, $method, $body)
    {
        $ch = curl_init();

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            $url .= '?' . $body;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if ($rtn === false) {
            // 大多由设置等原因引起，一般无法保障后续逻辑正常执行，
            // 所以这里触发的是E_USER_ERROR，会终止脚本执行，无法被try...catch捕获，需要用户排查环境、网络等故障
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);

        return $rtn;
    }
    public function appUpdate()
    {
        $sql = "select * from ecs_app_update ";
        $data = $this->getORM()->queryRow($sql);
        return $data;
    }
}
