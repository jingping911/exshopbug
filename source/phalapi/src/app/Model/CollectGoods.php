<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;


class CollectGoods extends NotORM
{
    protected  function getTableName($id)
    {
        return 'collect_goods';
    }

    protected function getTableKey($table)
    {
        return 'rec_id';
    }

    public function get_collect_list($user_id)
    {
        $data =  $this->getORM()->where(array('user_id' => $user_id))->fetchAll();
        if (count($data) == 0) {
            return false;
        }
        foreach ($data as $key => $item) {
            $sql = "select goods_id,goods_name,goods_thumb,shop_price from ecs_goods where goods_id='" . $item['goods_id'] . "'";
            $tmp = $this->getORM()->queryRow($sql);
            $data[$key]['goods_name'] = $tmp['goods_name'];
            $data[$key]['shop_price'] = $tmp['shop_price'];
            $data[$key]['goods_id'] = $tmp['goods_id'];
            $data[$key]['goods_thumb'] = goods_img_url($tmp['goods_thumb']);
        }
        return $data;
    }


    public function get_collect_goods_id($user_id, $goods_id)
    {
        $data =  $this->getORM()->where(array('goods_id' => $goods_id, 'user_id' => $user_id))->fetchOne();
        return $data;
    }

    public function del($user_id, $goods_id)
    {
        $data =  $this->getORM()->where(array('goods_id' => $goods_id, 'user_id' => $user_id))->fetchOne();
        if (!$data) {
            $this->msg = '未收藏';
            return false;
        }
        $return =  $this->getORM()->where(array('goods_id' => $goods_id, 'user_id' => $user_id))->delete();
        return $return;
    }

    public function add($user_id, $goods_id)
    {

        $data =  $this->getORM()->where(array('goods_id' => $goods_id, 'user_id' => $user_id))->fetchOne();
        if ($data) {
            $this->msg = '已收藏';
            return false;
        }

        $data['user_id'] = $user_id;
        $data['goods_id'] = $goods_id;
        $data['add_time'] = time();

        $return = $this->getORM()->insert($data);


        return $return;
    }
}
