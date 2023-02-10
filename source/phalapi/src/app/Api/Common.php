<?php

namespace App\Api;

use PhalApi\Api;

/**
 * 首页接口服务
 * @package App\Api
 */
class Common extends Api
{
    /**
     * 首页
     * @desc Home
     * @return array
     */
    function Index()
    {
        $data = file_get_contents('https://jinjiajin.net/vue-app/json/index/index.json');
        $data = json_decode($data, true);
        return $data;
    }
}
