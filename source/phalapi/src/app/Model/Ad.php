<?php

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

class Ad extends NotORM
{

    public function getBanner()
    { // 这个是新的
        $domain = $_SERVER['HTTP_HOST'];
        $sql = "SELECT * FROM `ecs_banner` ORDER BY ranking asc limit 5";
        $data = $this->getORM()->queryRows($sql);
        // $data = $this->getORM()->where("is_show",1)->limit(5)->fetchAll();
        foreach ($data as $k => $v) {
            $data[$k]['ad_code'] = goods_img_url($data[$k]['img_src']);
        }
        return $data;
    }
}
