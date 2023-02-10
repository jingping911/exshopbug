<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;


class Shipping extends NotORM
{

    protected function getTableKey($table)
    {
        return 'shipping_id';
    }

    function shipping_fee($shipping_id, $goods_weight, $goods_amount, $goods_number, $shipping_area_id)
    {
        $sql = "select * from ecs_shipping_area where shipping_id = '".$shipping_id."' AND shipping_area_id = '".$shipping_area_id."'";
        $configure_data = $this->getORM()->queryRow($sql);
        $configure_list = unserialize($configure_data['configure']);


        $configure = array();
        foreach ($configure_list as $key => $item) {
            $configure[$item['name']] = $item['value'];
        }

        if ($configure['free_money'] > 0 && $goods_amount >= $configure['free_money']) {
            return 0;
        } else {
            @$fee = $configure['base_fee'];
            $configure['fee_compute_mode'] = !empty($configure['fee_compute_mode']) ? $configure['fee_compute_mode'] : 'by_weight';
            if ($configure['fee_compute_mode'] == 'by_number') {
                $fee = $goods_number * $configure['item_fee'];
            } else {
                if ($goods_weight > 1) {
                    $fee += (ceil(($goods_weight - 1))) * $configure['step_fee'];
                }
            }
            return $fee;
        }
    }

    /**
     * @return array
     * 查询一级菜单
     */
    function available_shipping_list()
    {
        $sql = "select * from ecs_delivery_method where parent_id = 0 and is_show = 'true' order by sort_order";
        $result = $this->getORM()->queryRows($sql);
        //        $sel = "select * from ecs_delivery_method where type = 1";
        //        $res = $this->getORM()->queryRow($sel);
        //        return array("result"=>$result,"res"=>$res);
        return $result;
    }


    function shipping_list_top($user_id, $address_id)
    {
        $sql = "select * from ecs_user_address where user_id = '" . $user_id . "' and address_id = '" . $address_id . "'";
        $consignee = $this->getORM()->queryRow($sql);
        $region_id_list = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

        $sql = 'SELECT s.shipping_id as delivery_id, s.shipping_code, s.shipping_name as delivery_name, ' .
            's.shipping_desc, s.insure, s.support_cod, a.configure ' .
            'FROM ecs_shipping AS s, ecs_shipping_area AS a, ecs_area_region AS r ' .
            'WHERE r.region_id ' . $this->getORM()->db_create_in($region_id_list) .
            ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 ORDER BY s.shipping_order';
        return $this->getORM()->queryRows($sql);
    }

    /**
     * @param $delivery_id
     * @return array
     * 查询出一级菜单下的二级菜单
     */
    function shipping_list_bottom($delivery_id)
    {
        $sql = "select * from ecs_delivery_method where parent_id = '$delivery_id' and is_show = 'true' order by sort_order";
        $result = $this->getORM()->queryRows($sql);
        if (empty($result)) {
            return false;
        } else {
            return $result;
        }
    }
}
