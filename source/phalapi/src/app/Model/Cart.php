<?php

namespace  App\Model;

use App\Model\Goods as GoodsModel;
use App\Model\User as UserModel;

use PhalApi\Model\NotORMModel as NotORM;


class Cart extends NotORM
{
    protected function getTableKey($table)
    {
        return 'rec_id';
    }


    function update_cart_nums($goods_id, $num = 1, $user_id, $product_id)
    {
        $goodsModel = new GoodsModel;
        // 先检测该属性的库存
        if ($product_id == '0') {  // 无属性
            $sql = "select goods_number as num from ecs_goods where goods_id = '" . $goods_id . "'";
        } else {
            $sql = "select product_number as num, goods_attr from ecs_products where product_id = '" . $product_id . "'";
        }
        $goods_num = $this->getORM()->queryRow($sql); // 查出数量

        if ((int)$goods_num['num'] < (int)$num) {
            $this->msg = '库存不足';
            return false;
        }
        // 更新时对当前商品的单间进行重新计算
        if ($product_id == '0') {
            $price = $goodsModel->get_final_price($goods_id, $num, $user_id);
        } else {
            // 对有规格的进行处理
            $goods_attr = $goods_num['goods_attr'];
            $spec = explode('|', $goods_attr);
            $price = $goodsModel->get_final_price($goods_id, $num, $user_id, true, $spec);
        }
        $sql = "update ecs_cart set goods_number='" . $num . "', goods_price = '" . $price . "' where goods_id ='" . $goods_id . "' and product_id = '" . $product_id . "' and user_id='" . $user_id . "'";
        $res = $this->getORM()->query($sql, $params);
        return $res;
    }

    function delete_cart($rec_id, $num = 1, $user_id)
    {
        $sql = "delete from ecs_cart where rec_id ='" . $rec_id . "' and user_id='" . $user_id . "'";
        $res = $this->getORM()->query($sql, '');
        return $res;
    }

    function clear_cart($user_id)
    {
        $sql = "DELETE FROM ecs_cart " .
            " WHERE user_id = '" . $user_id . "' AND rec_type = '0' and is_checked='true'";
        return $this->getORM()->queryRow($sql);
    }

    function get_cart_goods_checkout($user_id, $ral, $integralGoodsid, $super, $superGoodsid, $result_spike, $goods_id, $pintuan, $pintuanGoodsId, $pintuanNumber)
    {

        $this->goods_model = new GoodsModel();
        $this->user_model = new UserModel();
        $goods_list = array();
        $sql = "SELECT value from ecs_shop_config where code IN ('can_invoice','use_integral','use_bonus')";
        $result = $this->getORM()->queryRows($sql);
        $total = array(
            'goods_price'  => 0, // 本店售价合计（有格式）
            'market_price' => 0, // 市场售价合计（有格式）
            'saving'       => 0, // 节省金额（有格式）
            'save_rate'    => 0, // 节省百分比
            'goods_amount' => 0, // 本店售价合计（无格式）
            'check_goods_price' => 0, // 选中商品价格（无格式）
            'check_subtotal' => 0, // 选中商品价格（无格式）
            'can_invoice' =>$result[0]['value'],//移动端是否使用发票
            'use_integral'=>$result[1]['value'],//移动端是否使用积分
            'use_bonus'=>$result[2]['value'],///移动端是否使用红包
        );
        /* 循环、统计 */
        /**
            购物车获取数据
         **/
        if ($ral != 'true' && $super != 'true' && $result_spike != 'true' && $pintuan != 'true') {
            $sql = "SELECT *, IF(parent_id, parent_id, goods_id) AS pid " .
                " FROM ecs_cart " .
                " WHERE user_id = '" . $user_id . "' AND rec_type = '0'  and  is_checked='true'" .
                " ORDER BY pid, parent_id";
            $res = $this->getORM()->queryAll($sql, $params);
        } else {
            $sql = "select * from ecs_goods where goods_id='" . $integralGoodsid . "'";
            $res = $this->getORM()->queryAll($sql, $params);
        }


        if ($super == 'true') {
            $sql = "select u.*,r.* from ecs_users as u left join ecs_user_rank as r  on  u.user_rank = r.rank_id where u.user_id ='" . $user_id . "'";
            $user_data = $this->getORM()->queryRow($sql);
            /* 取得礼包信息 */
            $package = $this->get_package_info($superGoodsid, $user_data['user_rank'], $user_data['discount'] / 100);

            $res[0]['goods_id'] = $superGoodsid;
            $res[0]['goods_name'] = $package['package_name'];
            $res[0]['market_price'] = $package['package_price'];
            $res[0]['goods_price'] = $package['package_price'];
            $res[0]['goods_number'] = 1;
            $res[0]['goods_name'] = $package['package_name'];
        }

        if ($result_spike == 'true') {
            $sql = "select * from ecs_goods where goods_id = '$goods_id'";
            $user_data = $this->getORM()->queryRow($sql);
            //            $package = $this->get_package_info($superGoodsid,$user_data['user_rank'],$user_data['discount']/100);

            $res[0]['goods_id'] = $goods_id;
            $res[0]['goods_price'] = $user_data['spike_sum'];
            $res[0]['goods_number'] = 1;
            $res[0]['goods_name'] = $user_data['goods_name'];
        }

        // 拼团的商品信息
        if ($pintuan == 'true') {
            $sql = "select goods_id,goods_name,market_price,pt_price as goods_price,goods_number from ecs_goods where goods_id = '$pintuanGoodsId' and is_pintuan = '1'";
            $pt_data = $this->getORM()->queryRow($sql);

            $res[0]["goods_id"] = $pintuanGoodsId;
            $res[0]["goods_name"] = $pt_data["goods_name"];
            $res[0]["market_price"] = $pt_data["market_price"];
            $res[0]["goods_price"] = $pt_data["goods_price"];
            $res[0]["goods_number"] = $pintuanNumber;
        }
        if (count($res) == 0) {
            return false;
        }

        /* 用于统计购物车中实体商品和虚拟商品的个数 */
        $virtual_goods_count = 0;
        $real_goods_count    = 0;
        $check_goods_price    = 0;
        $goods_count    = 0;

        foreach ($res as $key => $item) {
            $item['goods_number'] = intval($item['goods_number']);
            $goods_count = $goods_count + intval($item['goods_number']);
            $goods_sql = "SELECT promote_price FROM ecs_goods WHERE goods_id = '".$item['goods_id']."'";
            $data = $this->getORM()->queryRow($goods_sql);
//            if ($data['promote_price'] == '0.00'){
//                $data['promote_price'] = '0';
//                $item['goods_price']  = $item['goods_price'];
//            }else {
//                $item['goods_price']  = $data['promote_price'];
//            }
            $total['goods_price']  += $item['goods_price'] * $item['goods_number'];
            $total['market_price'] += $item['market_price'] * $item['goods_number'];

            $item['subtotal']     = $item['goods_price'] * $item['goods_number'];

            if ($item['is_checked'] == 'true') {
                $total['check_subtotal']     = $item['goods_price'] * $item['goods_number'];
                $total['check_goods_price']  += $item['goods_price'] * $item['goods_number'];
            }

            $item['market_price'] = $item['market_price'];
            //对多选属性进行相应处理

            if (!empty($item['goods_attr_combine'])) {
                $temps = explode(',', $item['goods_attr_combine']);
                if (is_array($temps)) {
                    foreach ($temps as $k => $val) {
                        $t = explode(',', $val);
                        $sql = "select attr_value from ecs_goods_attr where goods_attr_id = " . $t[0];
                        $size = $this->getORM()->queryRows($sql, $params);
                        $size = current($size);
                        $a['size'][$t[0]] = $size;
                        $a['num'][$t[0]] = $t[1];
                        $item['goods_attr_combine'] = $a;
                    }
                }
            }

            /* 统计实体商品和虚拟商品的个数 */
            if ($item['is_real']) {
                $real_goods_count++;
            } else {
                $virtual_goods_count++;
            }



            if (trim($item['goods_attr_id']) != '') {
                $tmp = explode(',', $item['goods_attr_id']);
                $sql = "SELECT attr_value,attr_price FROM  ecs_goods_attr WHERE goods_attr_id " .
                    $this->getORM()->db_create_in($tmp);
                $attr_list = $this->getORM()->queryAll($sql, $params);
                foreach ($attr_list as $i => $j) {
                    $item['sku_str'] .= ' [' . $j['attr_value'] . '] ';
                }
            }

            /* 增加是否在购物车里显示商品图 */
            $sql = "SELECT `goods_thumb` FROM ecs_goods WHERE `goods_id`='{$item['goods_id']}'";
            $goods_thumb = $this->getORM()->queryRows($sql, $params);
            $goods_thumb = current($goods_thumb);
            $item['goods_thumb'] = goods_img_url($goods_thumb['goods_thumb']);


            if ($item['extension_code'] == 'package_buy') {
                $sql = "select * from  ecs_goods_activity where act_id ='" . $item['goods_id'] . "'";
                $package_image = $this->getORM()->queryRow($sql);
                $item['goods_thumb'] = goods_img_url($package_image['package_image']);
                $item['package_goods_list'] =  $this->goods_model->get_package_goods($item['goods_id']);
            }
            $goods_list[] = $item;
        }


        $total['goods_amount'] = $total['goods_price'];
        $total['saving']       = $total['market_price'] - $total['goods_price'];
        if ($total['market_price'] > 0) {
            $total['save_rate'] = $total['market_price'] ? round(($total['market_price'] - $total['goods_price']) *
                100 / $total['market_price']) . '%' : 0;
        }
        $total['goods_price']  = $total['goods_price'];
        $total['check_goods_price']  = $total['check_goods_price'];
        $total['market_price'] = $total['market_price'];
        $total['real_goods_count']    = $real_goods_count;
        $total['goods_count']    = $goods_count;
        $total['virtual_goods_count'] = $virtual_goods_count;


        return array('goods_list' => $goods_list, 'total' => $total);
    }

    function get_integralgoods($goods_id)
    {
        $sql = 'SELECT eg.* , g.goods_name,g.goods_thumb ' .
            'FROM ecs_exchange_goods AS eg ' .
            'LEFT JOIN ecs_goods AS g ON g.goods_id = eg.goods_id ' .
            'WHERE eg.goods_id = ' . $goods_id;
        $data =  $this->getORM()->queryRow($sql);
        $data['goods_thumb'] = goods_img_url($data['goods_thumb']);

        return array('info' => $data);
    }

    function get_goods_status($data)
    {
        $i = 0;
        foreach ($data as $key => $val) {
            $sql = "select delivery_status from  ecs_goods  where goods_id = '" . $val['goods_id'] . "'";
            $goods_data = $this->getORM()->queryRow($sql);
            if ($goods_data['delivery_status'] == 'false') {
                $i++;
            }
        }
        if ($i != 0) {
            return false;
        } else {
            return true;
        }
    }

    function spikeShopGoods($result_spike, $goods_id)
    {
        $sql = "select goods_id,goods_name,goods_thumb,spike_sum as package_price,spike_count,active from ecs_goods where goods_id = '$goods_id'";
        $user_data = $this->getORM()->queryRow($sql);
        if ($user_data['spike_count'] == 0) {
        }
        $url = \PhalApi\DI()->config->get('app');
        $host_url =  $url['host_url'];
        if ($user_data['active'] == 'true') {
            $data = array(
                'goods_id' => $goods_id,
                'goods_name' => $user_data['goods_name'],
                'goods_thumb' =>$host_url.''.$user_data['goods_thumb'],
                'package_price' => $user_data['package_price'],
                'spike_count' => $user_data['spike_count'],
                'goods_price' => $user_data['package_price'],
            );
            return array("info" => $data);
        }
    }

    function get_packafegoods($goods_id, $user_id)
    {
        $sql = "select u.*,r.* from ecs_users as u left join ecs_user_rank as r  on  u.user_rank = r.rank_id where u.user_id ='" . $user_id . "'";
        $user_data = $this->getORM()->queryRow($sql);
        /* 取得礼包信息 */
        $package = $this->get_package_info($goods_id, $user_data['user_rank'], $user_data['discount'] / 100);
        //        if (empty($package))
        //        {
        //            $this->msg = '礼包不存在';
        //            return false;
        //        }
        $data = array(
            'goods_id' => $goods_id,
            'goods_name' => $package['package_name'],
            'goods_thumb' => $package['original_img'],
            'package_price' => $package['package_price'],
        );
        //    var_dump($package);die;

        return array('info' => $data);
    }


    function get_cart_goods($user_id, $is_checked = 'false')
    {
        $this->goods_model = new GoodsModel();
        $this->user_model = new UserModel();

        $goods_list = array();
        $total = array(
            'goods_price'  => 0, // 本店售价合计（有格式）
            'market_price' => 0, // 市场售价合计（有格式）
            'saving'       => 0, // 节省金额（有格式）
            'save_rate'    => 0, // 节省百分比
            'goods_amount' => 0, // 本店售价合计（无格式）
            'check_goods_price' => 0, // 选中商品价格（无格式）
            'check_subtotal' => 0, // 选中商品价格（无格式）
        );

        /* 循环、统计 */
        /**
            购物车获取数据
         **/
        if ($is_checked == 'false') {
            $sql = "SELECT *, IF(parent_id, parent_id, goods_id) AS pid " .
                " FROM ecs_cart " .
                " WHERE user_id = '" . $user_id . "' AND rec_type = '0'" .
                " ORDER BY pid, parent_id";
        } else {
            $sql = "SELECT *, IF(parent_id, parent_id, goods_id) AS pid " .
                " FROM ecs_cart " .
                " WHERE user_id = '" . $user_id . "' AND rec_type = '0' and is_checked='true'" .
                " ORDER BY pid, parent_id";
        }
        $res = $this->getORM()->queryAll($sql, $params);
        $id = $res['goods_id'];
        $sql = "SELECT `promote_price` FROM ecs_goods WHERE `goods_id`='{$res['goods_id']}'";
        $data = $this->getORM()->queryRows($sql, $params);
        if (count($res) == 0) {
            return false;
        }
        /* 用于统计购物车中实体商品和虚拟商品的个数 */
        $virtual_goods_count = 0;
        $real_goods_count    = 0;
        $check_goods_price    = 0;
        $goods_count    = 0;

        foreach ($res as $key => $item) {
            $sql = "SELECT `goods_thumb`,`promote_price` FROM ecs_goods WHERE `goods_id`='{$item['goods_id']}'";
            $goods_thumb = $this->getORM()->queryRows($sql, $params);
            $goods_thumb = current($goods_thumb);
            $item['goods_thumb'] = goods_img_url($goods_thumb['goods_thumb']);
            $item['promote_price']  = $goods_thumb['promote_price'];

//            if ($goods_thumb['promote_price'] == '0.00'){
//                $goods_thumb['promote_price'] = '0';
//            }
//
//            if ($goods_thumb['promote_price']){
//                $item['goods_price']  = $goods_thumb['promote_price'];
//            }else {
//                $item['goods_price']  = $item['goods_price'];
//            }

            $item['goods_number'] = intval($item['goods_number']);
            $goods_count = $goods_count + intval($item['goods_number']);
            $total['goods_price']  += $item['goods_price'] * $item['goods_number'];
            $total['market_price'] += $item['market_price'] * $item['goods_number'];
            $item['subtotal']     = $item['goods_price'] * $item['goods_number'];

            if ($item['is_checked'] == 'true') {
                $total['check_subtotal']     = $item['goods_price'] * $item['goods_number'];
                $total['check_goods_price']  += $item['goods_price'] * $item['goods_number'];
            }
            $item['goods_price']  = $item['goods_price'];
            $item['market_price'] = $item['market_price'];
            //对多选属性进行相应处理

            if (!empty($item['goods_attr_combine'])) {
                $temps = explode(',', $item['goods_attr_combine']);
                if (is_array($temps)) {
                    foreach ($temps as $k => $val) {
                        $t = explode(',', $val);
                        $sql = "select attr_value from ecs_goods_attr where goods_attr_id = " . $t[0];
                        $size = $this->getORM()->queryRows($sql, $params);
                        $size = current($size);
                        $a['size'][$t[0]] = $size;
                        $a['num'][$t[0]] = $t[1];
                        $item['goods_attr_combine'] = $a;
                    }
                }
            }

            /* 统计实体商品和虚拟商品的个数 */
            if ($item['is_real']) {
                $real_goods_count++;
            } else {
                $virtual_goods_count++;
            }

            if (trim($item['goods_attr_id']) != '') {
                $tmp = explode(',', $item['goods_attr_id']);
                $sql = "SELECT attr_value,attr_price FROM  ecs_goods_attr WHERE goods_attr_id " .
                    $this->getORM()->db_create_in($tmp);
                $attr_list = $this->getORM()->queryAll($sql, $params);
                foreach ($attr_list as $i => $j) {
                    $item['sku_str'] .= ' [' . $j['attr_value'] . '] ';
                }
            }
            /* 增加是否在购物车里显示商品图 */



            if ($item['extension_code'] == 'package_buy') {
                $sql = "select * from ecs_goods_activity where act_id ='" . $item['goods_id'] . "'";
                $package = $this->getORM()->queryRow($sql);
                $item['goods_thumb'] = goods_img_url($package['package_image']);
                $item['package_goods_list'] =  $this->goods_model->get_package_goods($item['goods_id']);
            }

            $goods_list[] = $item;

        }

        $total['goods_amount'] = $total['goods_price'];
        $total['saving']       = $total['market_price'] - $total['goods_price'];
        if ($total['market_price'] > 0) {
            $total['save_rate'] = $total['market_price'] ? round(($total['market_price'] - $total['goods_price']) *
                100 / $total['market_price']) . '%' : 0;
        }
        $total['goods_price']  = $total['goods_price'];
        $total['check_goods_price']  = $total['check_goods_price'];
        $total['market_price'] = $total['market_price'];
        $total['real_goods_count']    = $real_goods_count;
        $total['goods_count']    = $goods_count;
        $total['virtual_goods_count'] = $virtual_goods_count;


        /* 设置checkout为false */
        //        if($is_checked == 'false' && $user_id != ''){
        //            $sql = "update ecs_cart set is_checked='false' where user_id = '" . $user_id . "'";
        //			//var_dump($sql);
        //            $this->getORM()->queryRow($sql);
        //        }
        return array('goods_list' => $goods_list, 'total' => $total);
    }

    function get_totalPrice($user_id)
    {
        $sql = "select goods_attr_id from ecs_cart where user_id = $user_id";
        $res = $this->getORM()->queryRow($sql);
        if (trim($res['goods_attr_id']))

            $sql = "SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount" . " FROM  ecs_cart WHERE is_checked = 'true' and user_id = '" . $user_id . "'";
        $data =  $this->getORM()->queryRow($sql);
        //        if(empty($data['number'])){
        //            $sql = "SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount" ." FROM  ecs_cart WHERE  user_id = '".$user_id."'";
        //            $data =  $this->getORM()->queryRow($sql);
        //        }
        if (empty($data['number'])) {
            $data['amount'] = 0;
        }
        return $data['amount'];
    }


    function get_package_info($id, $user_rank, $discount)
    {
        //        global $ecs, $db,$_CFG;
        //        $id = is_numeric($id)?intval($id):0;
        $now = time();

        $sql = "SELECT act_id AS id,  act_name AS package_name, goods_id , goods_name, package_image, start_time, end_time, act_desc, ext_info" .
            " FROM ecs_goods_activity WHERE act_id='$id' AND act_type = 4";

        $package = $this->getORM()->queryRow($sql);

        /* 将时间转成可阅读格式 */
        if ($package['start_time'] <= $now && $package['end_time'] >= $now) {
            $package['is_on_sale'] = "1";
        } else {
            $package['is_on_sale'] = "0";
        }
        $package['start_time'] = date('Y-m-d H:i', $package['start_time']);
        $package['end_time']   = date('Y-m-d H:i', $package['end_time']);
        $row = unserialize($package['ext_info']);
        unset($package['ext_info']);
        if ($row) {
            foreach ($row as $key => $val) {
                $package[$key] = $val;
            }
        }

        $sql = "SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, " .
            " g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, g.is_real, " .
            " IFNULL(mp.user_price, g.shop_price * '$discount') AS rank_price " .
            " FROM ecs_package_goods AS pg " .
            "   LEFT JOIN ecs_goods AS g " .
            "   ON g.goods_id = pg.goods_id " .
            " LEFT JOIN ecs_member_price AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$user_rank' " .
            " WHERE pg.package_id = " . $id . " " .
            " ORDER BY pg.package_id, pg.goods_id";

        $goods_res = $this->getORM()->queryRow($sql);

        $market_price        = 0;
        $real_goods_count    = 0;
        $virtual_goods_count = 0;

        foreach ($goods_res as $key => $val) {
            //            $goods_res[$key]['goods_thumb']         = get_image_path($val['goods_id'], $val['goods_thumb'], true);
            //            $goods_res[$key]['market_price_format'] = price_format($val['market_price']);
            //            $goods_res[$key]['rank_price_format']   = price_format($val['rank_price']);
            $market_price += $val['market_price'] * $val['goods_number'];
            /* 统计实体商品和虚拟商品的个数 */
            if ($val['is_real']) {
                $real_goods_count++;
            } else {
                $virtual_goods_count++;
            }
        }

        if ($real_goods_count > 0) {
            $package['is_real']            = 1;
        } else {
            $package['is_real']            = 0;
        }

        $package['goods_list']            = $goods_res;
        $package['market_package']        = $market_price;
        //        $package['market_package_format'] = price_format($market_price);
        //        $package['package_price_format']  = price_format($package['package_price']);

        return $package;
    }


    function AddPackageCartApi($goods_id, $user_id, $token)
    {
        $sql = "select u.*,r.* from ecs_users as u left join ecs_user_rank as r  on  u.user_rank = r.rank_id where u.user_id ='" . $user_id . "'";
        $user_data = $this->getORM()->queryRow($sql);
        /* 取得礼包信息 */
        $package = $this->get_package_info($goods_id, $user_data['user_rank'], $user_data['discount'] / 100);
        if (empty($package)) {
            $this->msg = '礼包不存在';
            return false;
        }
        $num = 1;
        /* 初始化要插入购物车的基本件数据 */
        $parent = array(
            'user_id'       => $user_id,
            'session_id'    => $token,
            'goods_id'      => $goods_id,
            'goods_sn'      => '',
            'goods_name'    => addslashes($package['package_name']),
            'market_price'  => $package['market_package'],
            'goods_price'   => $package['package_price'],
            'goods_number'  => $num,
            'goods_attr'    => '',
            'goods_attr_id' => '',
            'is_real'       => $package['is_real'],
            'extension_code' => 'package_buy',
            'is_gift'       => 0,
            'is_checked'       => true,
            'rec_type'      => 0
        );



        /* 如果数量不为0，作为基本件插入 */
        if ($num > 0) {
            $num = 0;
            /* 检查该商品是否已经存在在购物车中 */
            $sql = "SELECT goods_number FROM ecs_cart WHERE  goods_id = '" . $goods_id . "' " .
                " AND parent_id = 0 AND extension_code = 'package_buy' " .
                " AND rec_type = 0";

            $row = $this->getORM()->queryRow($sql);

            if ($row) //如果购物车已经有此物品，则更新
            {
                //                $num += $row['goods_number'];
                $num  = '1';
                if ($num > 0) {
                    $sql = "UPDATE ecs_cart SET goods_number = '" . $num . "'" .
                        " WHERE  goods_id = '$goods_id' " .
                        " AND parent_id = 0 AND extension_code = 'package_buy' " .
                        " AND rec_type = 0";
                    $sql = "UPDATE ecs_cart SET is_checked = 'false' WHERE  user_id = '$user_id'";
                    $this->getORM()->queryRow($sql);
                    $sql = "UPDATE ecs_cart SET is_checked = 'true' WHERE  goods_id='" . $goods_id . "' and  user_id = '$user_id'";
                    $this->getORM()->queryRow($sql);
                    $this->getORM()->queryRow($sql);
                }
            } else //购物车没有此物品，则插入
            {
                $sql = "UPDATE ecs_cart SET is_checked = 'false' WHERE  user_id = '$user_id'";
                $this->getORM()->queryRow($sql);
                $this->getORM()->insert($parent);
                //                $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
            }
        }
        $sql = "update ecs_cart set is_checked = 'true' where goods_id = $goods_id and user_id = $user_id";
        $this->getORM()->queryRow($sql);

        /* 把赠品删除 */
        $sql = "DELETE FROM ecs_cart WHERE user_id = '" . $user_id . "' AND is_gift <> 0";
        $this->getORM()->queryRow($sql);

        return true;
    }

    function cartCheck($ischecked, $user_id, $rec_id = '')
    {
        if ($rec_id == '') {
            if ($ischecked  == 0) {
                $sql = "update ecs_cart set is_checked='false' where user_id = '" . $user_id . "'";
                $this->getORM()->queryRow($sql);
            } else {
                $sql = "update ecs_cart set is_checked='true' where user_id = '" . $user_id . "'";
                $this->getORM()->queryRow($sql);
            }
        } else {
            if ($ischecked  == 0) {
                $sql = "update ecs_cart set is_checked='false' where user_id = '" . $user_id . "' and rec_id = '" . $rec_id . "'";
                $this->getORM()->queryRow($sql);
                //                var_dump('123');
            } else {
                $sql = "update ecs_cart set is_checked='true' where user_id = '" . $user_id . "' and rec_id = '" . $rec_id . "'";
                $this->getORM()->queryRow($sql);
            }
        }

        /**
            获取选择购物车
         **/
        $data = $this->get_cart_goods($user_id, 'true');
        if (!$data) {
            $data['total']['goods_price']  = 0;
            $data['total']['check_goods_price']  = 0;
            $data['total']['market_price'] = 0;
            $data['total']['real_goods_count'] = 0;
            $data['total']['goods_count'] = 0;
            $data['total']['virtual_goods_count'] = 0;
        }

        return $data;
    }


    function doFastcheck($goods_id, $user_id, $num, $product_id)
    {
        if ($product_id == 'null') {
            $product_id = '0';
        }
        $goodsModel = new GoodsModel;

        $sql = "update ecs_cart set is_checked = 'false' where user_id='" . $user_id . "'";
        $this->getORM()->queryRow($sql);

        // 立即购买时，对当前商品的单品金额进行检测更新
        if ($product_id == '0') {
            $price = $goodsModel->get_final_price($goods_id, $num, $user_id);
        } else {
            // 查询当前的的商品属性
            $sql = "select goods_attr from ecs_products where product_id = '" . $product_id . "'";
            $goods_attr_msg = $this->getORM()->queryRow($sql);

            $goods_attr = $goods_attr_msg['goods_attr'];
            $spec = explode('|', $goods_attr);

            $price = $goodsModel->get_final_price($goods_id, $num, $user_id, true, $spec);
        }

        // 如果该用户的购物车内有该商品则将该商品的数量置为他所点击立即购买的数量
        $sql = "update ecs_cart set is_checked = 'true',goods_number = '" . $num . "', goods_price = '" . $price . "' where user_id='" . $user_id . "' and goods_id='" . $goods_id . "' and product_id = '" . $product_id . "'";
        $this->getORM()->queryRow($sql);
        return true;
    }



    function addto_cart($goods_id, $num = 1, $spec = array(), $user_id, $token, $product_id = '0', $parent = 0, $rec_type = 0, $spec2 = array(), $e = array())
    {
        $this->goods_model = new GoodsModel();
        $this->user_model = new UserModel();

        $i = 0;


        /**
         * 检测加入购物车的数量是否超过库存
         */
        // 当前属性的库存数量

        if ($product_id == 'null') {
            $product_id = '0';
        }
        // $spec = str_replace(',','|',$spec);

        if ($product_id === '0') {  // 可能传'0'
            $sql = "select goods_number as num from ecs_goods where goods_id = '" . $goods_id . "'";
        } else if ($spec) {
            $sql = "select product_number as num from ecs_products where product_id = '$product_id'";
        } else {
            $sql = "select goods_number as num from ecs_goods where goods_id = '$goods_id'";
        }


        $pro_num = $this->getORM()->queryRow($sql);
        // if(empty($pro_num) || !$pro_num){
        //     $pro_num['num'] = 0;
        // }
        // var_dump($pro_num);
        // 该用户购物车内该商品的数量
        $sql = "select goods_number as num from ecs_cart where goods_id = '" . $goods_id . "' and user_id = '" . $user_id . "' and product_id = '" . $product_id . "'";
        $cart_num = $this->getORM()->queryRow($sql);
        //        var_dump($sql);
        //        echo "1111111".'<br>';
        //        var_dump($cart_num);
        //        exit;

        if ($cart_num == null) {
            $cart_num['num'] = 0;
        }
        if ((int)$pro_num['num'] > (int)($cart_num['num']) + $num) {
            $cart_num['num'] + $num;
        }
        if ((int)$pro_num['num'] < (int)($cart_num['num']) + $num) {
            $this->msg = '库存不足';
            return false;
        }

        $_parent_id = $parent;

        $user = $this->user_model->get_rank_discount($user_id);
        $discount = $user['discount'];


        /* 取得商品信息 */
        $sql = "SELECT g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, " .
            "g.market_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, " .
            "g.promote_end_date, g.goods_weight, g.integral, g.extension_code, " .
            "g.goods_number, g.is_alone_sale, g.is_shipping," .
            "IFNULL(mp.user_price, g.shop_price * '$discount') AS shop_price " .
            " FROM ecs_goods AS g " .
            " LEFT JOIN ecs_member_price AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            " WHERE g.goods_id = ?" .
            " AND g.is_delete = 0";
        $params = array($goods_id);
        $goods = $this->getORM()->queryRows($sql, $params);

        // var_dump($goods);
        if (count($goods) == 0) {
            $this->msg = '商品不存在';
            return false;
        }

        $goods = current($goods);

        /* 如果是作为配件添加到购物车的，需要先检查购物车里面是否已经有基本件 */
        if ($parent > 0) {
            $sql = "SELECT COUNT(*) FROM ecs_cart WHERE goods_id=? AND user_id=? AND extension_code <> 'package_buy'";
            $params = array($parent, $user_id);
            $packet = $this->getORM()->queryRows($sql, $params);

            if (count($packet) == 0) {
                $this->msg = '配件不存在';
                return false;
            }
        }

        /* 是否正在销售 */
        if ($goods['is_on_sale'] == 0) {
            $this->msg = '商品已经下架';
            return false;
        }

        /* 不是配件时检查是否允许单独销售 */
        if (empty($parent) && $goods['is_alone_sale'] == 0) {
            $this->msg = '配件不允许单独销售';
            return false;
        }

        /* 如果商品有规格则取规格商品信息 配件除外 */
        $sql = "SELECT * FROM ecs_products WHERE goods_id = ? LIMIT 0, 1";

        $params = array($goods_id);
        $prod = $this->getORM()->queryRows($sql, $params);
        $prod = current($prod);

        // var_dump($prod);
        $spec = explode(',', $spec);


        if ($this->goods_model->is_spec($spec) && !empty($prod)) {
            $product_info = $this->goods_model->get_products_info($goods_id, $spec);
        }


        if (empty($product_info)) {
            $product_info = array('product_number' => '', 'product_id' => 0);
        }

        /* 检查：库存 */
        if ($num > $pro_num['num']) {
            $this->msg = '库存不够';
            return false;
        }

        /* 计算商品的促销价格 */
        $spec_price             = $this->goods_model->spec_price($spec);
        $goods_price            = $this->goods_model->get_final_price($goods_id, $num, $user_id, true, $spec);
        $goods['market_price'] += $spec_price;
        $goods_attr             = $this->goods_model->get_goods_attr_info($spec);
        $goods_attr_id          = join(',', $spec);

        if ($product_id == '') {
            $product_id = $product_info['product_id'];
        }
        /* 初始化要插入购物车的基本件数据 */
        $parent = array(
            'user_id'       => $user_id,
            'session_id'    => $token,
            'goods_id'      => $goods_id,
            'goods_sn'      => addslashes($goods['goods_sn']),
            'product_id'    => $product_id,
            'goods_name'    => addslashes($goods['goods_name']),
            'market_price'  => $goods['market_price'],
            'goods_attr'    => addslashes($goods_attr),
            'goods_attr_id' => $goods_attr_id,
            'is_real'       => $goods['is_real'],
            'extension_code' => $goods['extension_code'],

            'is_gift'       => 0,
            'is_shipping'   => $goods['is_shipping'],
            'rec_type'      => $rec_type
        );
        //var_dump($parent);exit;
        /* 如果该配件在添加为基本件的配件时，所设置的“配件价格”比原价低，即此配件在价格上提供了优惠， */
        /* 则按照该配件的优惠价格卖，但是每一个基本件只能购买一个优惠价格的“该配件”，多买的“该配件”不享 */
        /* 受此优惠 */
        $basic_list = array();
        $sql = "SELECT parent_id, goods_price " .
            "FROM ecs_group_goods " .
            " WHERE goods_id = '$goods_id'" .
            " AND goods_price < '$goods_price'" .
            " AND parent_id = '$_parent_id'" .
            " ORDER BY goods_price";

        $res = $this->getORM()->queryAll($sql, $params);

        foreach ($res as $key => $item) {
            $basic_list[$item['parent_id']] = $item['goods_price'];
        }


        /* 取得购物车中该商品每个基本件的数量 */
        $basic_count_list = array();
        if ($basic_list) {
            $sql = "SELECT goods_id, SUM(goods_number) AS count " .
                "FROM ecs_cart " .
                " WHERE session_id = '" . $token . "'" .
                " AND parent_id = 0" .
                " AND extension_code <> 'package_buy' " .
                " AND goods_id " . $this->getORM()->db_create_in(array_keys($basic_list)) .
                " GROUP BY goods_id";

            $res = $this->getORM()->queryAll($sql, $params);

            foreach ($res as $key => $item) {
                $basic_count_list[$item['goods_id']] = $item['count'];
            }
        }

        /* 取得购物车中该商品每个基本件已有该商品配件数量，计算出每个基本件还能有几个该商品配件 */
        /* 一个基本件对应一个该商品配件 */
        if ($basic_count_list) {
            $sql = "SELECT parent_id, SUM(goods_number) AS count " .
                "FROM ecs_cart " .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND goods_id = '$goods_id'" .
                " AND extension_code <> 'package_buy' " .
                " AND parent_id " . $this->getORM()->db_create_in(array_keys($basic_count_list)) .
                " GROUP BY parent_id";

            $res = $this->getORM()->queryAll($sql, $params);

            foreach ($res as $key => $item) {
                $basic_count_list[$item['goods_id']] = $item['count'];
            }
        }

        /* 循环插入配件 如果是配件则用其添加数量依次为购物车中所有属于其的基本件添加足够数量的该配件 */
        foreach ($basic_list as $parent_id => $fitting_price) {
            /* 如果已全部插入，退出 */
            if ($num <= 0) {
                break;
            }

            /* 如果该基本件不再购物车中，执行下一个 */
            if (!isset($basic_count_list[$parent_id])) {
                continue;
            }

            /* 如果该基本件的配件数量已满，执行下一个基本件 */
            if ($basic_count_list[$parent_id] <= 0) {
                continue;
            }

            /* 作为该基本件的配件插入 */
            $parent['goods_price']  = max($fitting_price, 0) + $spec_price; //允许该配件优惠价格为0
            $parent['goods_number'] = min($num, $basic_count_list[$parent_id]);
            $parent['parent_id']    = $parent_id;
            $parent['goods_attr_combine'] = $co;

            /* 添加 */

            //$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
            $this->getORM()->insert($parent);
            /* 改变数量 */
            $num -= $parent['goods_number'];
        }

        /* 如果数量不为0，作为基本件插入 */
        if ($num > 0) {
            /* 检查该商品是否已经存在在购物车中 */
            if ($product_id == '') {
                $sql = "SELECT goods_number FROM ecs_cart " .
                    " WHERE user_id = '" . $user_id . "' AND goods_id = '$goods_id' " .
                    " AND parent_id = 0 AND goods_attr = '" . $this->goods_model->get_goods_attr_info($spec) . "' " .
                    " AND extension_code <> 'package_buy' " .
                    " AND rec_type = '0'";
            } else {
                $sql = "SELECT goods_number FROM ecs_cart " .
                    " WHERE user_id = '" . $user_id . "' AND goods_id = '$goods_id' " .
                    " AND parent_id = 0 AND product_id = '" . $product_id . "' AND goods_attr = '" . $this->goods_model->get_goods_attr_info($spec) . "' " .
                    " AND extension_code <> 'package_buy' " .
                    " AND rec_type = '0'";
            }


            $row = $this->getORM()->queryRows($sql, $params);

            $row = current($row);

            if ($row) //如果购物车已经有此物品，则更新
            {
                $num += $row['goods_number'];

                if ($this->goods_model->is_spec($spec) && !empty($prod)) {
                    $goods_storage = $product_info['product_number'];
                } else {
                    $goods_storage = $goods['goods_number'];
                }

                $goods_price = $this->goods_model->get_final_price($goods_id, $num, $user_id, true, $spec);
                if ($product_id == '') {
                    $sql = "UPDATE ecs_cart SET goods_number = '$num'" .
                        " , goods_price = '$goods_price' , is_checked = '1' " .
                        " WHERE user_id = '" . $user_id . "' AND goods_id = '$goods_id' " .
                        " AND parent_id = 0 AND goods_attr = '" . $this->goods_model->get_goods_attr_info($spec) . "' " .
                        " AND extension_code <> 'package_buy' " .
                        "AND rec_type = '0'";
                    // var_dump($sql);echo 123;exit;
                } else {
                    $sql = "UPDATE ecs_cart SET goods_number = '$num'" .
                        " , goods_price = '$goods_price' , is_checked = '1' " .
                        " WHERE user_id = '" . $user_id . "' AND goods_id = '$goods_id' " .
                        " AND parent_id = 0 AND product_id = '" . $product_id . "' AND goods_attr = '" . $this->goods_model->get_goods_attr_info($spec) . "' " .
                        " AND extension_code <> 'package_buy' " .
                        "AND rec_type = '0'";
                }

                $this->getORM()->queryRows($sql);
            } else //购物车没有此物品，则插入
            {

                $goods_price = $this->goods_model->get_final_price($goods_id, $num, $user_id, true, $spec);

                $parent['goods_price']  = max($goods_price, 0);
                $parent['goods_number'] = $num;
                //$parent['goods_attr_combine'] = $co;
                $parent['parent_id']    = 0;
                $parent['is_checked'] = 1;


                $this->getORM()->insert($parent);

                //$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');
            }
        }

        /* 把赠品删除 */
        $sql = "DELETE FROM ecs_cart WHERE user_id = '" . $user_id . "' AND is_gift <> 0";
        $this->getORM()->queryRows($sql);



        return true;
    }

    public function get_pintuan_goods($goods_id)
    {
        // 返回拼团商品

        $sql = "select is_pintuan from ecs_goods where goods_id = '$goods_id'";
        $is_pintuan = $this->getORM()->queryRow($sql);

        if ($is_pintuan["is_pintuan"] == '1') {
            $sql = "select * from ecs_goods where goods_id = '$goods_id'";
            $data = $this->getORM()->queryRow($sql);
            $data['goods_thumb'] = goods_img_url($data['goods_thumb']);
            return array("status" => "succ", "data" => array("0" => $data));
        } else {
            return array("status" => "fail", "msg" => "非拼团商品");
        }
    }
}
