<?php

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

class ShopConfig extends NotORM
{
    public function getLogo()
    {
        $sql = "select val from ecs_app_config where k='app_logo'";
        return $this->getORM()->queryRows($sql);
        // return $this->getORM()->select("value")->where("code","shop_log")->fetchOne();
    }
    public function index_prompt()
    {
        $sql = "select * from ecs_index_prompt where status = 'true' order by sort asc limit 3";
        $data =  $this->getORM()->queryRows($sql);
        foreach ($data as $key => $val) {
            $data[$key]['image_url'] = goods_img_url($val['image_url']);
        }
        return $data;
    }
    public function get_show_marketprice()
    {
        $sql = "select value from ecs_shop_config where code='show_marketprice'";
        $data = $this->getORM()->queryRows($sql);
        return $data[0]['value'];
    }

    public function getShopConfig($code)
    {
        // 获取配置信息
        $sql = "select * from ecs_shop_config where code = '" . $code . "'";
        $data = $this->getORM()->queryRow($sql);
        $data = unserialize($data["value"]); // 数据转换
        return $data;
    }

    public function getLiveSettings()
    {
        $live_settings = $this->getShopConfig('live_setting');

        return array(
            'is_open' => $live_settings['is_open'] == '1' ? true : false,
            'images'   => $live_settings['images']
        );
    }

    public function getLiveList($page, $page_total)
    {

        $page_size = 10;


        // 需要返回的数据
        $data = array(
            'is_open' => false,
            'live_list' => array()
        );

        // 获取当前设置
        $live_settings = $this->getShopConfig('live_setting');

        // 请求的地址
        $url = "https://marketing.shopex.cn/frontapi/broadcast/rAliPayNotify.phpoom/list";

        // 是否开启
        if ($live_settings['is_open'] == '1') {
            // 对直播数据进行查询
            $params = array(
                'method'       => 'front.broadcast.list',
                'app_key'       => $live_settings['app_key'],
                'timestamp'     => date('Y-m-d H:i:s', time()),
                'page'          => $page,
                'pageSize'      => $page_size
            );

            $params['sign'] = $this->genSign($params, $live_settings['app_secret']);

            // 对请求地址进行拼接
            $url = $url . '?' . http_build_query($params);

            $tmp = $this->http_get($url);

            $live_list = json_decode($tmp, true);

            if ($live_list['errcode'] == 0) {
                $total_page = ceil($live_list['data']['total_count'] / $page_size);

                $data = array(
                    'status'         => 'success',
                    'live_list'      => $live_list['data']['list'],
                    'total_page'     => intval($total_page),
                    'curr_page'      => intval($page)
                );
                return $data;
            }

            return array(
                'status'  => 'fail',
                'message' => $live_list['errmsg']
            );
        }

        return array(
            'status'  => 'fail',
            'message' => '暂未开启直播设置'
        );
    }


    private function genSign($params, $app_secret)
    {
        //移除sign参数
        unset($params['sign']);
        unset($params['app_secret']);
        //参数名称的ASCII码表的顺序排序
        ksort($params);
        $string = '';
        foreach ($params as $k => $v) {
            //空值不参与签名
            if (!empty($v)) {
                $string .= $k . $v;
            }
        }
        //前后拼接app_secret
        $string = $app_secret . $string . $app_secret;
        $string_md5 = md5($string);
        return $string_md5;
    }

    public function http_get($url, $aHeader = '')
    {

        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($sError = curl_error($ch)) {
            die($sError);
        }
        curl_close($ch);

        return $output;
    }
}
