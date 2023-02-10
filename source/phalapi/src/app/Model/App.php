<?php


namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

class App  extends NotORM
{
    public function getGuidePages()
    {
        $sql = "select * from ecs_app_config";
        $data1 = $this->getORM()->queryRows($sql);
        foreach ($data1 as $k => $v) {
            if ($v['k'] == 'img1') {
                $data[0]['src'] = $v['val'];
            }
            if ($v['k'] == 'img2') {
                $data[1]['src'] = $v['val'];
            }
            if ($v['k'] == 'img3') {
                $data[2]['src'] = $v['val'];
            }
            if ($v['k'] == 'img4') {
                $data[3]['src'] = $v['val'];
            }

            if ($v['k'] == 'img_url1') {
                $data[0]['url'] = $v['val'];
            }
            if ($v['k'] == 'img_url2') {
                $data[1]['url']  = $v['val'];
            }
            if ($v['k'] == 'img_url3') {
                $data[2]['url'] = $v['val'];
            }
            if ($v['k'] == 'img_url4') {
                $data[3]['url'] = $v['val'];
            }
        }
        //        $data =  array_merge($data,$url);
        return $data;
    }
}
