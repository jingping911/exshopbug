<?php

namespace  App\Model;

use App\Model\Cart as CartModel;
use App\Model\Goods as GoodsModel;
use App\Model\User as UserModel;
use App\Model\Address as AddressModel;
use App\Model\Shipping as ShippingModel;
use App\Model\Accountlog as AccountlogModel;
use App\Model\Vcode as VcodeModel;

use PhalApi\Model\NotORMModel as NotORM;


class Order extends NotORM
{
    protected  function getTableName($id)
    {
        return 'order_info';
    }

    protected function getTableKey($table)
    {
        return 'order_id';
    }

    function pay_detail($order_id, $user_id)
    {
        $sql = "select *,SUM(" . $this->order_due_field() . ") AS total_fee from ecs_order_info where order_sn='" . $order_id . "' and user_id='" . $user_id . "'";
        $odata = $this->getORM()->queryRow($sql);

        return $odata;
    }

    function pay_detailJsapi($order_sn, $user_id)
    {
        $sql = "select * from ecs_account_log where order_sn='" . $order_sn . "' and user_id='" . $user_id . "'";
        $Jdata = $this->getORM()->queryRow($sql);
        return $Jdata;
    }

    function order_due_field($alias = '')
    {
        //        return $this->order_amount_field($alias) .
        //                " - {$alias}money_paid - {$alias}surplus - {$alias}integral_money" .
        //                " - {$alias}bonus ";
        return $this->order_amount_field($alias) .
            " - {$alias}integral_money" .
            " - {$alias}bonus ";
    }

    function order_amount_field($alias = '')
    {
        return "   {$alias}goods_amount + {$alias}tax + {$alias}shipping_fee" .
            " + {$alias}insure_fee + {$alias}pay_fee + {$alias}pack_fee" .
            " + {$alias}card_fee - {$alias}goods_discount_fee - {$alias}discount";
    }

    function checkBonus($user_id, $bonus_id)
    {

        $sql = "select * from ecs_user_bonus eub left join ecs_bonus_type ebt on eub.bonus_type_id = ebt.type_id where user_id = '" . $user_id . "' and order_id = '0' and bonus_id='" . $bonus_id . "' ";
        $tmp = $this->getORM()->queryRow($sql);
        return $tmp;
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


    function gen_order($user_id, $address_id, $shipping_id, $bonus_id = '', $point = 0, $tax_data = [], $pack_name, $pack_fee, $ral, $integralGoodsid, $o_time, $super, $superGoodsid, $delivery_id, $delivery_name, $result_spike, $goods_id, $p_type, $pintuan, $pintuanGoodsId, $pintuanNumber, $productId, $pintuanOrderId, $msg)
    {

        $this->cart_model = new CartModel();
        if ($ral == 'false' && $super == 'false' && $result_spike == 'false' && $pintuan == 'false') {
            $cart_goods =  $this->cart_model->get_cart_goods_checkout($user_id, '', '', '', '', '', '', '', '', '');
            if (!$cart_goods) {
                $this->msg = '购物车为空';
                return false;
            }
            if (!$this->flow_cart_stock($user_id)) {
                $this->msg = '商品库存不足';
                return false;
            }
        } else if ($ral == 'true') {
            $sql = "select * from  ecs_goods where goods_id ='" . $integralGoodsid . "'";
            $data = $this->getORM()->queryRow($sql);
            if ($data['goods_number'] < 1) {
                $this->msg = '商品库存不足';
                return false;
            }
        }

        /**
            红包判断
         **/
        if ($bonus_id != '') {
            $res = $this->checkBonus($user_id, $bonus_id);
            if (!$res) {
                $this->msg = '红包错误/被使用';
                return false;
            }
        }
        $order_data = $this->get_order_data($user_id, $address_id, $shipping_id, $bonus_id, $point, $pack_name, $pack_fee, $ral, $integralGoodsid, $o_time, $super, $superGoodsid, $delivery_id, $delivery_name, $result_spike, $goods_id, $p_type, $pintuan, $pintuanGoodsId, $pintuanNumber, $pintuanOrderId, $msg);

        $count =   $order_data['goods_amount'];
        /* 购物最小金额限制 */
        $sql = "select * from ecs_shop_config where code='min_goods_amount'";
        $min_amount = $this->getORM()->queryRow($sql);

        $min_amount = $min_amount['value'];
        if ($ral != 'true' && $super != 'true') {
            if ($min_amount != '0') {
                if ($count < $min_amount) {
                    $this->msg = '商品总额未达到最低限购金额,限购金额为:￥' . $min_amount . "（不含配送费）";
                    return false;
                }
            }
        }
        $this->getORM()->insert($order_data);
        $order_id = $this->getORM()->insert_id();

        if ($delivery_id != "") {
            $sel = "select delivery_name from ecs_delivery_method where delivery_id = '$delivery_id'";
            $result = $this->getORM()->queryRow($sel);
            $location = $result['delivery_name'] . $delivery_name;
            $update = "update ecs_order_info set shipping_name = '$location' where order_id = '$order_id'";
            $this->getORM()->queryRow($update);
        }

        if ($ral != 'true' && $super != 'true' && $result_spike != 'true' && $pintuan != 'true') {
            $sql = "INSERT INTO ecs_order_goods ( " .
                "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, " .
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) " .
                " SELECT '" . $order_id . "', goods_id, goods_name, goods_sn, product_id, goods_number, market_price, " .
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id" .
                " FROM ecs_cart " .
                " WHERE user_id = '" . $user_id . "' AND rec_type = '0' AND is_checked='true'";
            $select_sql = "select goods_id, goods_name, goods_sn, product_id, goods_number, market_price,goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id
                          from ecs_cart where user_id = '$user_id' and rec_type = '0' and is_checked = 'true'";
            $query = $this->getORM()->queryAll($select_sql);
            foreach ($query as $k => $v) {
                $update_count = "update ecs_goods set sales_volume_count = (sales_volume_count+" . $query[$k]['goods_number'] . ") where goods_id = '" . $query[$k]['goods_id'] . "'";
                $this->getORM()->queryRow($update_count);
            }
        } else  if ($ral == 'true') {
            $sql = "select * from ecs_goods where  goods_id='" . $integralGoodsid . "'";
            $goods_data = $this->getORM()->queryRow($sql);
            $sql = "INSERT INTO ecs_order_goods ( " .
                "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, " .
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) " .
                "value ('" . $order_id . "','" . $goods_data['goods_id'] . "','" . $goods_data['goods_name'] . "','" . $goods_data['goods_sn'] . "',0,1,'" . $goods_data['market_price'] . "','" . $goods_data['shop_price'] . "',' ','" . $goods_data['is_real'] . "',' ',0,0,' ')";
        } else  if ($super == 'true') {

            $sql = "select u.*,r.* from ecs_users as u left join ecs_user_rank as r  on  u.user_rank = r.rank_id where u.user_id ='" . $user_id . "'";
            $user_data = $this->getORM()->queryRow($sql);
            /* 取得礼包信息 */
            $package = $this->get_package_info($superGoodsid, $user_data['user_rank'], $user_data['discount'] / 100);
            $sql = "INSERT INTO ecs_order_goods ( " .
                "order_id, act_id, goods_name, goods_sn, product_id, goods_number, market_price, " .
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) " .
                "value ('" . $order_id . "','" . $superGoodsid . "','" . $package['package_name'] . "',' ',0,1,'" . $package['market_package'] . "','" . $package['package_price'] . "',' ','" . $package['is_real'] . "','package_buy',0,0,' ')";
        } else if ($result_spike == 'true') {
            //222222222222222222222222222
            $select = "select * from ecs_goods where goods_id = '$goods_id'";
            $user_data = $this->getORM()->queryRow($select);
            $goods_name = $user_data['goods_name'];
            $goods_sn = $user_data['goods_sn'];
            //$goods_price = $user_data['market_price'];
            $goods_number = $user_data['spike_sum'];
            $goods_is_real = $user_data['is_real'];
            $goods_extension_code = $user_data['extension_code'];

            $user_addr_model = new AddressModel();
            $addr_list = $user_addr_model->get_address_by_id($user_id, $address_id);
            $consignee =  $addr_list;
            $total = $this->seckill_fee($goods_id,$number,$consignee,$shipping_id);
            // 更改秒杀订单金额
            $update_spike_price = "update ecs_order_info set shipping_fee = '" . $total['shipping_fee'] . "',goods_amount = '" . $total['goods_price'] . "' where order_id = '" . $order_id . "'";
            $this->getORM()->queryRow($update_spike_price);

            $insert = "insert into ecs_order_goods(order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price,goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) value ('$order_id','$goods_id','$goods_name','$goods_sn','0','1','$goods_price','$goods_number',' ','$goods_is_real','$goods_extension_code','0','0',' ')";
            $this->getORM()->queryRow($insert);
            $update_count = "update ecs_goods set spike_count = (spike_count-1),sales_volume_count = (sales_volume_count+1) where goods_id = '$goods_id'";
            $this->getORM()->queryRow($update_count);
            if ($user_data['spike_count'] <= 0) {
                $this->msg = "库存不足";
                return false;
            }
            $update = "update ecs_order_info set order_type = '2' where order_id = '$order_id'";
            $this->getORM()->queryRow($update);
        } else if ($pintuan == 'true') {
            // 拼团购买的商品信息
            if ($productId != "null") {
                $sql = "select * from ecs_products where product_id = '$productId' and goods_id = '$pintuanGoodsId'";
                $attr_data = $this->getORM()->queryRow($sql);
                $goods_attr_id = $attr_data["goods_attr"];
            } else {
                $productId = 0;
                $goods_attr_id = 'n|u|l|l';
            }
            // 判断是否为拼单
            if ($pintuanOrderId != '' && !empty($pintuanOrderId)) {
                // 为拼单的订单 将一起拼单的状态更改
                $sql = "update ecs_order_info set pt_id = '$order_id' where order_id = '$pintuanOrderId'";
                $this->getORM()->queryRow($sql);
            }
            $sql = "select goods_sn,goods_name,market_price,pt_price from ecs_goods where goods_id = '$pintuanGoodsId'";
            $pt_data = $this->getORM()->queryRow($sql);

            $user_addr_model = new AddressModel();
            $addr_list = $user_addr_model->get_address_by_id($user_id, $address_id);
            $consignee =  $addr_list;
            $total = $this->pintuan_fee($pintuanGoodsId, $pintuanNumber,$consignee,$shipping_id);
            // 更改拼团订单金额
            $update_pt_price = "update ecs_order_info set shipping_fee = '" . $total['shipping_fee'] . "',goods_amount = '" . $total['goods_price'] . "' where order_id = '" . $order_id . "'";

            $this->getORM()->queryRow($update_pt_price);

            $sql = "INSERT INTO ecs_order_goods SET order_id = '$order_id',
                goods_id = '$pintuanGoodsId', goods_name = '" . $pt_data['goods_name'] . "',
                goods_sn = '" . $pt_data['goods_sn'] . "', product_id = '$productId', goods_number = '$pintuanNumber',
                market_price = '" . $pt_data['market_price'] . "', goods_price = '" . $pt_data['pt_price'] . "', 
                is_real = 1, goods_attr_id = '$goods_attr_id', goods_attr = ' '
                ";
            $update_count = "update ecs_goods set sales_volume_count = (sales_volume_count+$pintuanNumber) where goods_id = '$pintuanGoodsId'";
            $this->getORM()->queryRow($update_count);
        }
        // var_dump($sql);
        $this->getORM()->queryRow($sql);



        $sql = "select * from ecs_order_info where order_id='" . $order_id . "'";
        $odata = $this->getORM()->queryRow($sql);
        if ($super != 'true' && $ral != 'true') {
            //清除购物车
            $this->cart_model->clear_cart($user_id);
        }
        // var_dump($odata);
        // exit();
        // 是否有红包
        if ($bonus_id != '') {
            $this->use_bonus($user_id, $bonus_id, $order_id); // 将红包进行使用处理
        }

        // 计算当前积分抵扣的金额
        if (intval($point) > 0) {
            $sql = "SELECT value FROM ecs_shop_config WHERE `code` = 'integral_scale'";
            $integral_scale = $this->getORM()->queryRow($sql);
            $inte_money = round($point * ($integral_scale['value'] / 100), 2); // 计算可以抵扣的金额

            if ($bonus_id != '') { // 如果有红包计算相应的金额
                $sql = "SELECT a.type_money FROM ecs_bonus_type AS a LEFT JOIN ecs_user_bonus AS b ON b.bonus_type_id = a.type_id WHERE b.bonus_id = '$bonus_id' ";
                $bonus_data = $this->getORM()->queryRow($sql);
                if ($bonus_data['type_money'] > $odata['goods_amount']) { // 红包金额大于订单金额
                    $odata['goods_amount'] = 0; // 商品金额为零
                    $inte_money = 0; // 积分兑换金额为零
                } else {
                    $odata['goods_amount'] = $odata['goods_amount'] - $bonus_data['type_money'];
                }
            }

            $surplus = round(($odata['goods_amount'] + $odata['shipping_fee'] + $tax_data['tax'] - $inte_money), 2);

            $sql = "UPDATE ecs_order_info SET surplus = '" . $surplus . "', integral='" . $point . "',integral_money='" . $inte_money . "'WHERE order_sn = '" . $odata['order_sn'] . "'";
            $this->getORM()->queryRow($sql);
            // 减去账户相对应的积分值
            if ($inte_money > 0) { // 只有消耗积分时才进行减去积分操作
                $sql = "SELECT pay_points FROM ecs_users WHERE user_id = '" . $odata['user_id'] . "' ";
                $user_points = $this->getORM()->queryRow($sql);
                $pay_points = $user_points['pay_points'] - $point;
                $sql = "UPDATE ecs_users SET pay_points = '" . $pay_points . "' WHERE user_id = '" . $odata['user_id'] . "'";
                $this->getORM()->queryRow($sql);
                // 记录到账户日志中
                $sql = "INSERT INTO ecs_account_log SET user_id = '" . $odata['user_id'] . "',user_money=0,frozen_money=0,rank_points=0,pay_points='-" . $point . "',change_time='" . time() . "',change_desc='支付订单 " . $odata['order_sn'] . "',change_type=99";
                $this->getORM()->queryRow($sql);
            }
        }
        if ($ral == 'true') {
            $sql = "UPDATE ecs_users SET pay_points = pay_points -'" . $odata['integral'] . "' WHERE user_id = '" . $odata['user_id'] . "'";
            $this->getORM()->queryRow($sql);
            // 记录到账户日志中
            $sql = "INSERT INTO ecs_account_log SET user_id = '" . $odata['user_id'] . "',user_money=0,frozen_money=0,rank_points=0,pay_points='-" . $odata['integral'] . "',change_time='" . time() . "',change_desc='支付订单 " . $odata['order_sn'] . "',change_type=99";
            $this->getORM()->queryRow($sql);
        }

        // 将发票信息写入订单 并将税额加入订单总金额
        if (count($tax_data) != 0) {

            $di = \PhalApi\DI()->notorm->shop_config;

            $data = $di->where("code", 'invoice_content')->fetchOne();
            $sql = "UPDATE ecs_order_info SET inv_type = '" . $tax_data['inv_type'] . "',inv_payee = '" . $tax_data['inv_payee'] . "',tax = '" . $tax_data['tax'] . "', inv_content = '" . $data['value'] . "',tax_num = '" . $tax_data['tax_num'] . "' WHERE order_sn = '" . $odata['order_sn'] . "'";
            $this->getORM()->queryRow($sql);
        }

        // 扣除库存
        $sql = "select value from ecs_shop_config where code = 'stock_dec_time'";
        $stock_dec_time = $this->getORM()->queryRow($sql);
        if ($stock_dec_time['value'] == '1') {
            // 代表下单时进行扣除库存操作
            $sql = "SELECT b.product_id,b.goods_number,b.goods_id FROM ecs_order_info AS a LEFT JOIN ecs_order_goods AS b ON a.order_id = b.order_id WHERE order_sn = '" . $odata['order_sn'] . "'";
            $order_goods = $this->getORM()->queryRows($sql);
            foreach ($order_goods as $k => $v) {
                $sql = "UPDATE ecs_products SET product_number = product_number - '" . $v['goods_number'] . "' WHERE product_id = '" . $v['product_id'] . "'";
                $this->getORM()->queryRow($sql); // 更新物料数量
                $sql = "UPDATE ecs_goods SET goods_number = goods_number - {$v['goods_number']} WHERE goods_id = {$v['goods_id']}";
                $this->getORM()->queryRow($sql); // 更新总库存
            }
        }

        $this->taoda($odata['order_sn']);
        $this->erp($odata['order_sn']);
        return $odata['order_sn'];
    }

    public function erp($order)
    {
        $data = \PhalApi\DI()->config->get('app');
        $url = $data['host_url'] . "flow.php?step=get_erp&order_sn=" . $order;
        $status =  file_get_contents($url);
        return $status;
    }

    public function taoda($order)
    {
        $data = \PhalApi\DI()->config->get('app');
        $url = $data['host_url'] . "flow.php?step=get_taoda&order_sn=" . $order;
        $status =  file_get_contents($url);
        return $status;
    }

    function getPrompt()
    {
        $sql = "select * from ecs_app_config";
        $data =  $this->getORM()->queryAll($sql);
        foreach ($data as $k => $v) {
            if ($v['k'] == 'prompt_image') {
                $prompt_image = $v['val'];
            }
            if ($v['k'] == 'prompt_image_url') {
                $prompt_image_url = $v['val'];
            }
            if ($v['k'] == 'prompt_image_status') {
                $prompt_image_status = $v['val'];
            }
            if ($v['k'] == 'prompt_bonus_id') {
                $prompt_bonus_id = $v['val'];
            }
        }
        $retutn['prompt_image'] = $prompt_image;
        $retutn['prompt_image_url'] = $prompt_image_url;
        $retutn['prompt_bonus_id'] = $prompt_bonus_id;
        $retutn['prompt_image_status'] = $prompt_image_status;
        //        var_dump($retutn);die;
        return $retutn;
    }
    /**
        购车库存判断
     **/
    function flow_cart_stock($user_id)
    {
        $sql = "SELECT g.goods_name, g.goods_number as stock, c.product_id,c.goods_number as nums  " .
            "FROM ecs_goods AS g, ecs_cart AS c " .
            "WHERE g.goods_id = c.goods_id AND c.user_id = '" . $user_id . "' AND c.is_checked = 'true'";
        $row = $this->getORM()->queryAll($sql);
        //        var_dump($row);exit;
        foreach ($row as $key => $item) {
            if (intval($item['nums']) > intval($item['stock'])) {
                return false;
            }
        }

        return true;
    }

    /*
     * 商品包装
     * */
    function  packageList()
    {
        $sql = 'SELECT * FROM  ecs_pack';
        $res = $this->getORM()->queryRows($sql);
        return $res;
    }
    function get_order_data($user_id, $address_id, $shipping_id, $bonus_id, $point, $pack_name, $pack_fee, $ral, $integralGoodsid, $o_time, $super, $superGoodsid, $delivery_id, $delivery_name, $result_spike, $goods_id, $p_type, $pintuan, $pintuanGoodsId, $pintuanNumber, $pintuanOrderId, $msg)
    {
        $this->address_model = new AddressModel();

        $consignee = $this->address_model->get_address_by_id($user_id, $address_id);
        if ($pack_name == 'undefined') {
            $pack_name = "";
        }

        $order = array(
            'shipping_id'     => $shipping_id, //??
            'bonus_id'     => $bonus_id, //??
            'pay_id'          => '4', //??
            'pack_id'         => 0,
            'card_id'         => 0,
            'card_message'    => '',
            'surplus'         => 0,
            'integral'        => 0,
            //'need_inv'        => 0,
            'inv_type'        => '',
            'inv_payee'       => '',
            'inv_content'     => '',
            'postscript'      => $msg,
            'how_oos'         => '',
            //'need_insure'     => 0,
            'user_id'         => $user_id,
            'add_time'        => time(),
            'lastmodify'      => time(),
            'order_status'    => 0,
            'shipping_status' => 0,
            'pay_status'      => 0,
            'pack_name'      => $pack_name,
            'pack_fee'      => $pack_fee,
            'best_time'      => $o_time,
            'agency_id'       => 0
        );

        $total = $this->order_fee($order, $user_id, $consignee, $shipping_id, $ral, $integralGoodsid, $super, $superGoodsid, $result_spike, $goods_id, $pintuan, $pintuanGoodsId, $pintuanNumber);
        if (!empty($shipping_id) && $p_type != 'true') {
            $total['shipping_fee'] = $this->shipping_id_fee($shipping_id);
        }

        $order['bonus']        = $total['bonus'];
        $order['goods_amount'] = $total['goods_price'];
        if ($total['discount'] == '') {
            $total['discount'] = 0;
        }
        if ($ral == 'true') {
            $sql = "select * from ecs_exchange_goods  where goods_id ='" . $integralGoodsid . "'";
            $data = $this->getORM()->queryRow($sql);
            $order['integral'] = $data['exchange_integral'];
            $order['extension_code'] = 'exchange_goods';
        }
        $order['consignee']     = $consignee['consignee'];
        $order['country']      = $consignee['country'];
        $order['province']      = $consignee['province'];
        $order['city']      = $consignee['city'];
        $order['district']      = $consignee['district'];
        $order['mobile']      = $consignee['mobile'];
        $order['email']      = $consignee['email'];
        $order['address']      = $consignee['address'];
        if (empty($total['shipping_fee'])) {
            $total['shipping_fee'] = 0;
        }
        $order['shipping_fee']      = $total['shipping_fee'];



        $order['discount']     = $total['discount'];
        $order['surplus']      = $total['surplus'];
        $order['tax']          = $total['tax'];
        $order['pay_fee'] = $total['pay_fee'];
        //$order['cod_fee'] = $total['cod_fee'];
        $order['card_fee']      = $total['card_fee'];
        $order['order_sn'] = $this->get_order_sn(); //获取新订单号
        // $this->use_bonus($user_id,$bonus_id,$order['order_sn']); // 需传递 order_id  传递order_sn
        if ($pintuan == 'true') {
            // 拼团订单
            $order["order_type"] = '1';
            if ($pintuanOrderId == "") {
                $pintuanOrderId = 0;
            }
            $order["pt_id"] = $pintuanOrderId; // 记录拼单的ID
        }

        return $order;
    }



    function get_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((float) microtime() * 1000000);

        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    function goods_fee($integralGoodsid)
    {
        $sql = "select * from ecs_goods where goods_id='" . $integralGoodsid . "'";
        $total = $this->getORM()->queryRow($sql);
        $return['goods_price'] = $total['shop_price'];
        return $return;
    }

    function shipping_id_fee($shipping_id)
    {
        $sql = "select cost from ecs_delivery_method where delivery_id = $shipping_id";
        $fee = $this->getORM()->queryRow($sql);
        return $fee["cost"];
    }

    function order_fee($order, $user_id, $consignee, $shipping_id, $ral, $integralGoodsid, $super, $superGoodsid, $result_spike, $goods_id, $pintuan, $pintuanGoodsId, $pintuanNumber)
    {
        $this->cart_model = new CartModel();
        $goods =  $this->cart_model->get_cart_goods_checkout($user_id, $ral, $integralGoodsid, $super, $superGoodsid, $result_spike, $goods_id, $pintuan, $pintuanGoodsId, $pintuanNumber);
        $goods_count = $goods['total']['goods_count'];
        $goods = $goods['goods_list'];
        $total  = array(
            'real_goods_count' => 0,
            'gift_amount'      => 0,
            'goods_price'      => 0,
            'market_price'     => 0,
            'discount'         => 0,
            'pack_fee'         => 0,
            'card_fee'         => 0,
            'shipping_fee'     => 0,
            'shipping_insure'  => 0,
            'integral_money'   => 0,
            'bonus'            => 0,
            'surplus'          => 0,
            'cod_fee'          => 0,
            'pay_fee'          => 0,
            'tax'              => 0
        );
        $weight = 0;

        /* 商品总价 */
        foreach ($goods as $val) {
            /* 统计实体商品的个数 */
            if ($val['is_real']) {
                $total['real_goods_count']++;
            }
            if (trim($val['goods_attr_id']) != '') {
                $tmp = explode('|', $val['goods_attr_id']);
                $sql = "SELECT attr_value,attr_price FROM  ecs_goods_attr WHERE goods_attr_id " .
                    $this->getORM()->db_create_in($tmp);
                $attr_list = $this->getORM()->queryAll($sql, $params);
            }

            $total['goods_price']  += $val['goods_price'] * $val['goods_number'];
            $total['market_price'] += $val['market_price'] * $val['goods_number'];
        }
        $total['saving']    = $total['market_price'] - $total['goods_price'];
        $total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;

        $total['goods_price_formated']  = $total['goods_price'];
        $total['market_price_formated'] = $total['market_price'];
        $total['saving_formated']       = $total['saving'];


        /* 折扣 */
        if ($order['extension_code'] != 'group_buy') {

            if (count($goods) == 1) {
                $product_id =  $goods[0]['goods_id'];
            } else {

                foreach ($goods as $key => $val) {
                    $product_id .= $val['goods_id'] . ',';
                }
                $product_id = substr($product_id, 0, -1);
            }

            //            var_dump($product_id);die;
            $discount = $this->compute_discount($user_id, 'true', $product_id, $result_spike, $pintuan);

            $total['discount'] = $discount['discount'];
            if ($total['discount'] > $total['goods_price']) {
                $total['discount'] = $total['goods_price'];
            }
        }
        $total['discount_formated'] = $total['discount'];


        $total['tax_formated'] = $total['tax'];

        /* 包装费用 */
        if (!empty($order['pack_id'])) {
            $total['pack_fee']      = $this->pack_fee($order['pack_id'], $total['goods_price']);
        }
        $total['pack_fee_formated'] = $total['pack_fee']; //包装费

        /* 贺卡费用 */
        if (!empty($order['card_id'])) {
            $total['card_fee']      = $this->card_fee($order['card_id'], $total['goods_price']);
        }
        $total['card_fee_formated'] = $total['card_fee'];

        /* 红包 */


        if (!empty($order['bonus_id'])) {
            $bonus          = $this->bonus_info($order['bonus_id']);
            $total['bonus'] = $bonus['type_money'];
        }

        $total['bonus_formated'] = $total['bonus'];

        /* 线下红包 */
        if (!empty($order['bonus_kill'])) {
            $bonus          = $this->bonus_info(0, $order['bonus_kill']);
            $total['bonus_kill'] = $order['bonus_kill'];
            $total['bonus_kill_formated'] = $total['bonus_kill'];
        }



        /* 配送费用 */
        $shipping_cod_fee = NULL;
        if ($shipping_id > 0 && $total['real_goods_count'] > 0) {
            $region['country']  = $consignee['country'];
            $region['province'] = $consignee['province'];
            $region['city']     = $consignee['city'];
            $region['district'] = $consignee['district'];

            $shipping_info = $this->shipping_area_info($shipping_id, $region);

            $productId = $_POST['productId'];
            if (!empty($shipping_info)) {
                if ($order['extension_code'] == 'group_buy') {
                    $weight_price = $this->cart_weight_price($user_id, $goods_count);
                } else {
                    $weight_price = $this->cart_weight_price($user_id, $goods_count);
                }
                // 查看购物车中是否全为免运费商品，若是则把运费赋为零
                $sql = 'SELECT count(*) as allcount FROM ecs_cart' . " WHERE  `user_id` = '" . $user_id . "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";
                $shipping_count_data = $this->getORM()->queryRow($sql);
                $shipping_count = $shipping_count_data['allcount'];
                $this->shipping_model = new ShippingModel();
                if (isset($productId) && !empty($productId)) {
                    $total['shipping_fee'] = ($shipping_count == 0 AND $weight_price['free_shipping'] == 1) ?0 :$this->shipping_model->shipping_fee($shipping_id, $weight_price['weight'], $total['goods_price
     '], $weight_price['number'],$shipping_info['shipping_area_id']);
                } else {
                    $total['shipping_fee'] = ($shipping_count == 0 AND $weight_price['free_shipping'] == 1) ?0 :$this->shipping_model->shipping_fee($shipping_id, $weight_price['weight'], $total['goods_price
     '], $weight_price['number'],$shipping_info['shipping_area_id']);
                }

                $total['shipping_insure'] = 0;


                if ($shipping_info['support_cod']) {
                    $shipping_cod_fee = $shipping_info['pay_fee'];
                }
            }
        }

        $total['shipping_fee_formated']    = $total['shipping_fee']; //运费
        $total['shipping_insure_formated'] = $total['shipping_insure'];

        // 购物车中的商品能享受红包支付的总额
        $bonus_amount_tmp = $this->compute_discount($user_id, '', '', '', '', '');
        $bonus_amount = $bonus_amount_tmp['discount'];
        // 红包和积分最多能支付的金额为商品总额
        $max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;


        /* 计算订单总额 */
        if ($order['extension_code'] == 'group_buy' && $group_buy['deposit'] > 0) {
            $total['amount'] = $total['goods_price'];
        } else {
            $total['amount'] = $total['goods_price'] - $total['discount'] + $total['tax'] + $total['pack_fee'] + $total['card_fee'] +
                $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];

            // 减去红包金额
            $use_bonus        = min($total['bonus'], $max_amount); // 实际减去的红包金额

            if (isset($total['bonus_kill'])) {
                $use_bonus_kill   = min($total['bonus_kill'], $max_amount);
                $total['amount'] -=  $price = number_format($total['bonus_kill'], 2, '.', ''); // 还需要支付的订单金额
            }

            $total['bonus']   = $use_bonus;
            $total['bonus_formated'] = $total['bonus'];

            $total['amount'] -= $use_bonus; // 还需要支付的订单金额
            $max_amount      -= $use_bonus; // 积分最多还能支付的金额

        }

        /* 余额 */
        $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
        if ($total['amount'] > 0) {
            if (isset($order['surplus']) && $order['surplus'] > $total['amount']) {
                $order['surplus'] = $total['amount'];
                $total['amount']  = 0;
            } else {
                $total['amount'] -= floatval($order['surplus']);
            }
        } else {
            $order['surplus'] = 0;
            $total['amount']  = 0;
        }
        $total['surplus'] = $order['surplus'];
        $total['surplus_formated'] = $order['surplus'];

        /* 积分 */
        $order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
        if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0) {
            $integral_money = $order['integral'];

            // 使用积分支付
            $use_integral            = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
            $total['amount']        -= $use_integral;
            $total['integral_money'] = $use_integral;
            $order['integral']       = $use_integral;
        } else {
            $total['integral_money'] = 0;
            $order['integral']       = 0;
        }
        $total['integral'] = $order['integral'];
        $total['integral_formated'] = $total['integral_money'];




        $total['pay_fee_formated'] = $total['pay_fee'];

        $total['amount']           += $total['pay_fee']; // 订单总额累加上支付费用
        $total['amount_formated']  = $total['amount'];

        /* 取得可以得到的积分和红包 */
        if ($order['extension_code'] == 'group_buy') {
            $total['will_get_integral'] = $group_buy['gift_integral'];
        } elseif ($order['extension_code'] == 'exchange_goods') {
            $total['will_get_integral'] = 0;
        } else {
            $total['will_get_integral'] = $this->get_give_integral($user_id);
        }
        $total['will_get_bonus']        = $order['extension_code'] == 'exchange_goods' ? 0 : $this->get_total_bonus($user_id);
        $total['formated_goods_price']  = $total['goods_price'];
        $total['formated_market_price'] = $total['market_price'];
        $total['formated_saving']       = $total['saving'];

        if ($order['extension_code'] == 'exchange_goods') {
            $sql = 'SELECT SUM(eg.exchange_integral)  as sum_ex_in' .
                'FROM  ecs_cart AS c, ecs_exchange_goods AS eg ' .
                "WHERE c.goods_id = eg.goods_id AND c.user_id= '" . $user_id . "' " .
                "  AND c.rec_type = '4' " .
                '  AND c.is_gift = 0 AND c.goods_id > 0 ' .
                'GROUP BY eg.goods_id';

            $exchange_integral = $this->getORM()->queryRows($sql);
            $exchange_integral = current($exchange_integral);
            $total['exchange_integral'] = $exchange_integral['sum_ex_in'];
        }
        return $total;
    }



    function cart_weight_price($user_id, $goods_count)
    {
        $package_row['weight'] = 0;
        $package_row['amount'] = 0;
        $package_row['number'] = 0;

        $packages_row['free_shipping'] = 1;

        /* 计算超值礼包内商品的相关配送参数 */
        $sql = 'SELECT goods_id, goods_number, goods_price FROM ecs_cart ' . " WHERE extension_code = 'package_buy' AND user_id = '" . $user_id . "'";
        $row = $this->getORM()->queryAll($sql);

        if ($row) {
            $packages_row['free_shipping'] = 0;
            $free_shipping_count = 0;

            foreach ($row as $val) {
                // 如果商品全为免运费商品，设置一个标识变量
                $sql = 'SELECT count(*) as allcount FROM ecs_package_goods  AS pg,  ecs_goods AS g ' .
                    "WHERE g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";
                $tmp = $this->getORM()->queryRows($sql);
                $shipping_count = $tmp[0]['allcount'];

                if ($shipping_count > 0) {
                    // 循环计算每个超值礼包商品的重量和数量，注意一个礼包中可能包换若干个同一商品
                    $sql = 'SELECT SUM(g.goods_weight * pg.goods_number) AS weight, ' .
                        'SUM(pg.goods_number) AS number FROM ecs_package_goods  AS pg, ecs_goods AS g ' .
                        "WHERE g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";

                    $goods_row = $this->getORM()->queryRows($sql);
                    $goods_row = current($goods_row);

                    $package_row['weight'] += floatval($goods_row['weight']) * $val['goods_number'];
                    $package_row['amount'] += floatval($val['goods_price']) * $val['goods_number'];
                    $package_row['number'] += intval($goods_row['number']) * $val['goods_number'];
                } else {
                    $free_shipping_count++;
                }
            }

            $packages_row['free_shipping'] = $free_shipping_count == count($row) ? 1 : 0;
        }

        /* 获得购物车中非超值礼包商品的总重量 */
        $sql    = 'SELECT SUM(g.goods_weight * c.goods_number) AS weight, ' .
            'SUM(c.goods_price * c.goods_number) AS amount, ' .
            'SUM(c.goods_number) AS number ' .
            'FROM ecs_cart AS c ' .
            'LEFT JOIN ecs_goods AS g ON g.goods_id = c.goods_id ' .
            "WHERE c.user_id = '" . $user_id . "' " .
            "AND rec_type = '0' AND g.is_shipping = 0 AND c.extension_code != 'package_buy' AND c.is_checked = 'true'";

        $row = $this->getORM()->queryRows($sql);
        $row = current($row);

        $packages_row['weight'] = floatval($row['weight']) + $package_row['weight'];
        $packages_row['amount'] = floatval($row['amount']) + $package_row['amount'];
        $packages_row['number'] = intval($row['number']) + $package_row['number'];
        $packages_row['number'] = $goods_count;

        /* 格式化重量 */
        $packages_row['formated_weight'] = $packages_row['weight'];

        return $packages_row;
    }

    function order_weight_price($order_id)
    {
        $sql = "SELECT SUM(g.goods_weight * o.goods_number) AS weight, " .
            "SUM(o.goods_price * o.goods_number) AS amount ," .
            "SUM(o.goods_number) AS number " .
            "FROM ecs_order_goods  AS o, ecs_goods AS g " .
            "WHERE o.order_id = '$order_id' " .
            "AND o.goods_id = g.goods_id";

        $row = $this->getORM()->queryRows($sql);
        $row = current($row);
        $row['weight'] = floatval($row['weight']);
        $row['amount'] = floatval($row['amount']);
        $row['number'] = intval($row['number']);

        /* 格式化重量 */
        $row['formated_weight'] = formated_weight($row['weight']);

        return $row;
    }

    /**
     * 取得购物车该赠送的积分数
     * @return  int     积分数
     */
    function get_give_integral()
    {
        $sql = "SELECT SUM(c.goods_number * IF(g.give_integral > -1, g.give_integral, c.goods_price)) as all_integral " .
            "FROM ecs_cart AS c, " .
            "ecs_goods AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND c.user_id = '" . $user_id . "' " .
            "AND c.goods_id > 0 " .
            "AND c.parent_id = 0 " .
            "AND c.rec_type = 0 " .
            "AND c.is_gift = 0";
        $tmp = $this->getORM()->queryRows($sql);
        $tmp = current($tmp);
        return intval($tmp['all_integral']);
    }

    /**
     * 取得某配送方式对应于某收货地址的区域信息
     * @param   int     $shipping_id        配送方式id
     * @param   array   $region_id_list     收货人地区id数组
     * @return  array   配送区域信息（config 对应着反序列化的 configure）
     */
    function shipping_area_info($shipping_id, $region_id_list)
    {
        $sql = 'SELECT s.shipping_code, s.shipping_name, ' .
            's.shipping_desc, s.insure, s.support_cod, a.configure,a.shipping_area_id ' .
            'FROM ecs_shipping AS s, ' .
            'ecs_shipping_area AS a, ' .
            'ecs_area_region AS r ' .
            "WHERE s.shipping_id = '$shipping_id' " .
            'AND r.region_id ' . $this->getORM()->db_create_in($region_id_list) .
            ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1';

        $row = $this->getORM()->queryRow($sql);

        if (!empty($row)) {
            $shipping_config = $this->unserialize_config($row['configure']);
            if (isset($shipping_config['pay_fee'])) {
                if (strpos($shipping_config['pay_fee'], '%') !== false) {
                    $row['pay_fee'] = floatval($shipping_config['pay_fee']) . '%';
                } else {
                    $row['pay_fee'] = floatval($shipping_config['pay_fee']);
                }
            } else {
                $row['pay_fee'] = 0.00;
            }
        }

        return $row;
    }

    function unserialize_config($cfg)
    {
        if (is_string($cfg) && ($arr = unserialize($cfg)) !== false) {
            $config = array();

            foreach ($arr as $key => $val) {
                $config[$val['name']] = $val['value'];
            }

            return $config;
        } else {
            return false;
        }
    }

    function use_bonus($user_id, $bonus_id, $order_id)
    {
        $sql = "UPDATE ecs_user_bonus  SET order_id = '" . $order_id . "', used_time = '" . time() . "' WHERE bonus_id = '$bonus_id' LIMIT 1";


        //$sql = "UPDATE ecs_user_bonus SET user_id = '$user_id' WHERE bonus_id = '".$bonus_id."' LIMIT 1";
        $tmp = $this->getORM()->queryRow($sql);
    }

    function bonus_info($bonus_id, $bonus_sn = '')
    {
        $sql = "SELECT t.*, b.* " .
            "FROM ecs_bonus_type AS t,ecs_user_bonus AS b " .
            "WHERE t.type_id = b.bonus_type_id ";
        if ($bonus_id > 0) {
            $sql .= "AND b.bonus_id = '$bonus_id'";
        } else {
            $sql .= "AND b.bonus_sn = '$bonus_sn'";
        }

        $tmp = $this->getORM()->queryRows($sql);
        $tmp = current($tmp);
        return $tmp;
    }

    function pack_fee($pack_id, $goods_amount)
    {
        $pack = $this->pack_info($pack_id);

        $val = (floatval($pack['free_money']) <= $goods_amount && $pack['free_money'] > 0) ? 0 : floatval($pack['pack_fee']);

        return $val;
    }

    function pack_info($pack_id)
    {
        $sql = "SELECT * FROM ecs_pack" .
            " WHERE pack_id = '$pack_id'";
        $tmp = $this->getORM()->queryRows($sql);
        $tmp = current($tmp);
        return $tmp;
    }

    function card_info($card_id)
    {
        $sql = "SELECT * FROM ecs_card  WHERE card_id = '$card_id'";
        $tmp = $this->getORM()->queryRows($sql);
        $tmp = current($tmp);
        return $tmp;
    }


    function card_fee($card_id, $goods_amount)
    {
        $card = $this->card_info($card_id);

        return ($card['free_money'] <= $goods_amount && $card['free_money'] > 0) ? 0 : $card['card_fee'];
    }


    function compute_discount($user_id, $status, $product_id, $result_spike, $pintuan)
    {
        $this->user_model = new UserModel();
        $this->goods_model = new GoodsModel();
        /* 查询优惠活动 */
        $user = $this->user_model->get_rank_discount($user_id);

        $now = time();
        $user_rank = ',' . $user['user_rank'] . ',';
        $sql = "SELECT *" .
            "FROM ecs_favourable_activity" .
            " WHERE start_time <= '$now'" .
            " AND end_time >= '$now'" .
            " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND act_type " . $this->getORM()->db_create_in(array(2, 1));

        $favourable_list = $this->getORM()->queryAll($sql);
        if (count($favourable_list) == 0) {
            return 0;
        }

        if ($product_id == 'undefined' || $product_id == false) {
            $product_id = 0;
        }
        /* 查询购物车商品 */
        if ($status == 'true' && $result_spike != 'true' && $pintuan != 'true') {
            $sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id " .
                "FROM ecs_cart AS c, ecs_goods AS g " .
                "WHERE c.goods_id = g.goods_id " .
                "AND  c.goods_id in (" . $product_id . ") and c.user_id = '" . $user_id . "' " .
                "AND c.parent_id = 0 " .
                "AND c.is_gift = 0 " .
                "AND c.rec_type = '0'";
        } else {
            $sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id " .
                "FROM ecs_cart AS c, ecs_goods AS g " .
                "WHERE c.goods_id = g.goods_id " .
                "AND  c.user_id = '" . $user_id . "' " .
                "AND c.parent_id = 0 " .
                "AND c.is_gift = 0 " .
                "AND c.rec_type = '0'";
        }

        $goods_list = $this->getORM()->queryAll($sql);

        if (count($goods_list) == 0) {
            return 0;
        }

        /* 初始化折扣 */
        $discount = 0;
        $favourable_name = array();

        /* 循环计算每个优惠活动的折扣 */
        foreach ($favourable_list as $favourable) {
            $total_amount = 0;
            if ($favourable['act_range'] == 0) {
                foreach ($goods_list as $goods) {
                    $total_amount += $goods['subtotal'];
                }
            } elseif ($favourable['act_range'] == 1) {
                /* 找出分类id的子分类id */
                $id_list = array();
                $raw_id_list = explode(',', $favourable['act_range_ext']);
                foreach ($raw_id_list as $id) {
                    $id_list = array_merge($id_list, array_keys($this->cat_list($id, 0, false)));
                }

                $ids = join(',', array_unique($id_list));
                foreach ($goods_list as $goods) {

                    if (strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false) {
                        $total_amount += $goods['subtotal'];
                    }
                }
            } elseif ($favourable['act_range'] == 2) {
                foreach ($goods_list as $goods) {
                    if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['brand_id'] . ',') !== false) {
                        $total_amount += $goods['subtotal'];
                    }
                }
            } elseif ($favourable['act_range'] == 3) {
                foreach ($goods_list as $goods) {
                    if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false) {
                        $total_amount += $goods['subtotal'];
                    }
                }
            } else {
                continue;
            }

            /* 如果金额满足条件，累计折扣 */
            if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0)) {
                if ($favourable['act_type'] == 2) {
                    $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);

                    $favourable_name[] = $favourable['act_name'];
                } elseif ($favourable['act_type'] == 1) {
                    $discount += $favourable['act_type_ext'];

                    $favourable_name[] = $favourable['act_name'];
                }
            }
        }
        return array('discount' => $discount, 'name' => $favourable_name);
    }

    /**
     * 过滤和排序所有分类，返回一个带有缩进级别的数组
     *
     * @access  private
     * @param   int     $cat_id     上级分类ID
     * @param   array   $arr        含有所有分类的数组
     * @param   int     $level      级别
     * @return  void
     */
    function cat_options($spec_cat_id, $arr)
    {
        static $cat_options = array();

        if (isset($cat_options[$spec_cat_id])) {
            return $cat_options[$spec_cat_id];
        }

        if (!isset($cat_options[0])) {
            $level = $last_cat_id = 0;
            $options = $cat_id_array = $level_array = array();
            $data = false;
            if ($data === false) {
                while (!empty($arr)) {
                    foreach ($arr as $key => $value) {
                        $cat_id = $value['cat_id'];
                        if ($level == 0 && $last_cat_id == 0) {
                            if ($value['parent_id'] > 0) {
                                break;
                            }

                            $options[$cat_id]          = $value;
                            $options[$cat_id]['level'] = $level;
                            $options[$cat_id]['id']    = $cat_id;
                            $options[$cat_id]['name']  = $value['cat_name'];
                            unset($arr[$key]);

                            if ($value['has_children'] == 0) {
                                continue;
                            }
                            $last_cat_id  = $cat_id;
                            $cat_id_array = array($cat_id);
                            $level_array[$last_cat_id] = ++$level;
                            continue;
                        }

                        if ($value['parent_id'] == $last_cat_id) {
                            $options[$cat_id]          = $value;
                            $options[$cat_id]['level'] = $level;
                            $options[$cat_id]['id']    = $cat_id;
                            $options[$cat_id]['name']  = $value['cat_name'];
                            unset($arr[$key]);

                            if ($value['has_children'] > 0) {
                                if (end($cat_id_array) != $last_cat_id) {
                                    $cat_id_array[] = $last_cat_id;
                                }
                                $last_cat_id    = $cat_id;
                                $cat_id_array[] = $cat_id;
                                $level_array[$last_cat_id] = ++$level;
                            }
                        } elseif ($value['parent_id'] > $last_cat_id) {
                            break;
                        }
                    }

                    $count = count($cat_id_array);
                    if ($count > 1) {
                        $last_cat_id = array_pop($cat_id_array);
                    } elseif ($count == 1) {
                        if ($last_cat_id != end($cat_id_array)) {
                            $last_cat_id = end($cat_id_array);
                        } else {
                            $level = 0;
                            $last_cat_id = 0;
                            $cat_id_array = array();
                            continue;
                        }
                    }

                    if ($last_cat_id && isset($level_array[$last_cat_id])) {
                        $level = $level_array[$last_cat_id];
                    } else {
                        $level = 0;
                    }
                }
                //如果数组过大，不采用静态缓存方式
                //                if (count($options) <= 2000)
                //                {
                //                    write_static_cache('cat_option_static', $options);
                //                }
            } else {
                $options = $data;
            }
            $cat_options[0] = $options;
        } else {
            $options = $cat_options[0];
        }

        if (!$spec_cat_id) {
            return $options;
        } else {
            if (empty($options[$spec_cat_id])) {
                return array();
            }

            $spec_cat_id_level = $options[$spec_cat_id]['level'];

            foreach ($options as $key => $value) {
                if ($key != $spec_cat_id) {
                    unset($options[$key]);
                } else {
                    break;
                }
            }

            $spec_cat_id_array = array();
            foreach ($options as $key => $value) {
                if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                    ($spec_cat_id_level > $value['level'])
                ) {
                    break;
                } else {
                    $spec_cat_id_array[$key] = $value;
                }
            }
            $cat_options[$spec_cat_id] = $spec_cat_id_array;

            return $spec_cat_id_array;
        }
    }

    /**
     * 获得指定分类下的子分类的数组
     *
     * @access  public
     * @param   int     $cat_id     分类的ID
     * @param   int     $selected   当前选中分类的ID
     * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
     * @param   int     $level      限定返回的级数。为0时返回所有级数
     * @param   int     $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
     * @return  mix
     */
    function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
    {
        static $res = NULL;

        if ($res === NULL) {
            $data = false;
            if ($data === false) {
                $sql = "SELECT c.cat_id, c.cat_name, c.measure_unit, c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children " .
                    'FROM ecs_category' . " AS c " .
                    "LEFT JOIN ecs_category AS s ON s.parent_id=c.cat_id " .
                    "GROUP BY c.cat_id " .
                    'ORDER BY c.parent_id, c.sort_order ASC';
                $res = $this->getORM()->queryRows($sql);

                $sql = "SELECT cat_id, COUNT(*) AS goods_num " .
                    " FROM ecs_goods WHERE is_delete = 0 AND is_on_sale = 1 " .
                    " GROUP BY cat_id";
                $res2 = $this->getORM()->queryRows($sql);

                $sql = "SELECT gc.cat_id, COUNT(*) AS goods_num " .
                    " FROM ecs_goods_cat AS gc , ecs_goods AS g " .
                    " WHERE g.goods_id = gc.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 " .
                    " GROUP BY gc.cat_id";
                $res3 = $this->getORM()->queryRows($sql);

                $newres = array();
                foreach ($res2 as $k => $v) {
                    $newres[$v['cat_id']] = $v['goods_num'];
                    foreach ($res3 as $ks => $vs) {
                        if ($v['cat_id'] == $vs['cat_id']) {
                            $newres[$v['cat_id']] = $v['goods_num'] + $vs['goods_num'];
                        }
                    }
                }

                foreach ($res as $k => $v) {
                    $res[$k]['goods_num'] = !empty($newres[$v['cat_id']]) ? $newres[$v['cat_id']] : 0;
                }
                //如果数组过大，不采用静态缓存方式
                //                if (count($res) <= 1000)
                //                {
                //                    write_static_cache('cat_pid_releate', $res);
                //                }
            } else {
                $res = $data;
            }
        }

        if (empty($res) == true) {
            return $re_type ? '' : array();
        }

        $options = $this->cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

        $children_level = 99999; //大于这个分类的将被删除
        if ($is_show_all == false) {
            foreach ($options as $key => $val) {
                if ($val['level'] > $children_level) {
                    unset($options[$key]);
                } else {
                    if ($val['is_show'] == 0) {
                        unset($options[$key]);
                        if ($children_level > $val['level']) {
                            $children_level = $val['level']; //标记一下，这样子分类也能删除
                        }
                    } else {
                        $children_level = 99999; //恢复初始值
                    }
                }
            }
        }

        /* 截取到指定的缩减级别 */
        if ($level > 0) {
            if ($cat_id == 0) {
                $end_level = $level;
            } else {
                $first_item = reset($options); // 获取第一个元素
                $end_level  = $first_item['level'] + $level;
            }

            /* 保留level小于end_level的部分 */
            foreach ($options as $key => $val) {
                if ($val['level'] >= $end_level) {
                    unset($options[$key]);
                }
            }
        }

        if ($re_type == true) {
            $select = '';
            foreach ($options as $var) {
                $select .= '<option value="' . $var['cat_id'] . '" ';
                $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
                $select .= '>';
                if ($var['level'] > 0) {
                    $select .= str_repeat('&nbsp;', $var['level'] * 4);
                }
                $select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';
            }

            return $select;
        } else {
            foreach ($options as $key => $value) {
                $options[$key]['url'] = $this->build_uri('category', array('cid' => $value['cat_id']), $value['cat_name']);
            }

            return $options;
        }
    }

    /**
     * 重写 URL 地址
     *
     * @access  public
     * @param   string  $app        执行程序
     * @param   array   $params     参数数组
     * @param   string  $append     附加字串
     * @param   integer $page       页数
     * @param   string  $keywords   搜索关键词字符串
     * @return  void
     */
    function build_uri($app, $params, $append = '', $page = 0, $keywords = '', $size = 0)
    {
        static $rewrite = NULL;

        if ($rewrite === NULL) {
            $rewrite = intval($GLOBALS['_CFG']['rewrite']);
        }

        $args = array(
            'cid'   => 0,
            'gid'   => 0,
            'bid'   => 0,
            'acid'  => 0,
            'aid'   => 0,
            'sid'   => 0,
            'gbid'  => 0,
            'auid'  => 0,
            'sort'  => '',
            'order' => '',
        );

        extract(array_merge($args, $params));

        $uri = '';
        switch ($app) {
            case 'category':
                if (empty($cid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'category-' . $cid;
                        if (isset($bid)) {
                            $uri .= '-b' . $bid;
                        }
                        if (isset($price_min)) {
                            $uri .= '-min' . $price_min;
                        }
                        if (isset($price_max)) {
                            $uri .= '-max' . $price_max;
                        }
                        if (isset($filter_attr)) {
                            $uri .= '-attr' . $filter_attr;
                        }
                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                    } else {
                        $uri = 'category.php?id=' . $cid;
                        if (!empty($bid)) {
                            $uri .= '&amp;brand=' . $bid;
                        }
                        if (isset($price_min)) {
                            $uri .= '&amp;price_min=' . $price_min;
                        }
                        if (isset($price_max)) {
                            $uri .= '&amp;price_max=' . $price_max;
                        }
                        if (!empty($filter_attr)) {
                            $uri .= '&amp;filter_attr=' . $filter_attr;
                        }

                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                    }
                }

                break;
            case 'goods':
                if (empty($gid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'goods-' . $gid : 'goods.php?id=' . $gid;
                }

                break;
            case 'brand':
                if (empty($bid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'brand-' . $bid;
                        if (isset($cid)) {
                            $uri .= '-c' . $cid;
                        }
                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                    } else {
                        $uri = 'brand.php?id=' . $bid;
                        if (!empty($cid)) {
                            $uri .= '&amp;cat=' . $cid;
                        }
                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                    }
                }

                break;
            case 'article_cat':
                if (empty($acid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'article_cat-' . $acid;
                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                        if (!empty($keywords)) {
                            $uri .= '-' . $keywords;
                        }
                    } else {
                        $uri = 'article_cat.php?id=' . $acid;
                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                        if (!empty($keywords)) {
                            $uri .= '&amp;keywords=' . $keywords;
                        }
                    }
                }

                break;
            case 'article':
                if (empty($aid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'article-' . $aid : 'article.php?id=' . $aid;
                }

                break;
            case 'group_buy':
                if (empty($gbid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'group_buy-' . $gbid : 'group_buy.php?act=view&amp;id=' . $gbid;
                }

                break;
            case 'auction':
                if (empty($auid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'auction-' . $auid : 'auction.php?act=view&amp;id=' . $auid;
                }

                break;
            case 'snatch':
                if (empty($sid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'snatch-' . $sid : 'snatch.php?id=' . $sid;
                }

                break;
            case 'search':
                break;
            case 'exchange':
                if ($rewrite) {
                    $uri = 'exchange-' . $cid;
                    if (isset($price_min)) {
                        $uri .= '-min' . $price_min;
                    }
                    if (isset($price_max)) {
                        $uri .= '-max' . $price_max;
                    }
                    if (!empty($page)) {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order)) {
                        $uri .= '-' . $order;
                    }
                } else {
                    $uri = 'exchange.php?cat_id=' . $cid;
                    if (isset($price_min)) {
                        $uri .= '&amp;integral_min=' . $price_min;
                    }
                    if (isset($price_max)) {
                        $uri .= '&amp;integral_max=' . $price_max;
                    }

                    if (!empty($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                }

                break;
            case 'exchange_goods':
                if (empty($gid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'exchange-id' . $gid : 'exchange.php?id=' . $gid . '&amp;act=view';
                }

                break;
            default:
                return false;
                break;
        }

        if ($rewrite) {
            if ($rewrite == 2 && !empty($append)) {
                $uri .= '-' . urlencode(preg_replace('/[\.|\/|\?|&|\+|\\\|\'|"|,]+/', '', $append));
            }

            $uri .= '.html';
        }
        if (($rewrite == 2) && (strpos(strtolower(EC_CHARSET), 'utf') !== 0)) {
            $uri = urlencode($uri);
        }
        return $uri;
    }


    function get_total_bonus($user_id)
    {
        $day    = getdate();
        $today  = mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        /* 按商品发的红包 */
        $sql = "SELECT SUM(c.goods_number * t.type_money) as total " .
            "FROM ecs_cart AS c, " .
            "ecs_bonus_type AS t, "
            . "ecs_goods AS g " .
            "WHERE c.user_id = '" . $user_id . "' " .
            "AND c.is_gift = 0 " .
            "AND c.goods_id = g.goods_id " .
            "AND g.bonus_type_id = t.type_id " .
            "AND t.send_type = 1 " .
            "AND t.send_start_date <= '$today' " .
            "AND t.send_end_date >= '$today' " .
            "AND c.rec_type = '9'";

        $goods_total_data = $this->getORM()->queryRows($sql);
        $goods_total_data = current($goods_total_data);
        $goods_total = floatval($goods_total_data['total']);

        /* 取得购物车中非赠品总金额 */
        $sql = "SELECT SUM(goods_price * goods_number) as allamount " .
            "FROM ecs_cart" .
            " WHERE user_id =  '" . $user_id . "' " .
            " AND is_gift = 0 " .
            " AND rec_type = '0'";

        $amount_data = $this->getORM()->queryRows($sql);
        $amount_data = current($amount_data);
        $amount = floatval($amount_data['allamount']);
        /* 按订单发的红包 */
        $sql = "SELECT FLOOR('$amount' / min_amount) * type_money as order_total " .
            "FROM ecs_bonus_type " .
            " WHERE send_type = '2' " .
            " AND send_start_date <= '$today' " .
            "AND send_end_date >= '$today' " .
            "AND min_amount > 0 ";
        $order_total_data = $this->getORM()->queryRows($sql);
        $order_total_data = current($order_total_data);
        $order_total = floatval($order_total_data['order_total']);

        return $goods_total + $order_total;
    }


    function doPaymentBalance($order_id, $user_id, $pay_id, $platform)
    {
        $this->accountlog = new AccountlogModel();

        $order_data = $this->pay_detail($order_id, $user_id);

        if ((int)$order_data['surplus'] > 0) { // 使用了积分/红包等抵扣时 总金额为这个字段
            $order_data['total_fee'] = $order_data['surplus'];
        }
        if ($order_data['order_sn'] == '') {
            $this->msg = '订单不存在';
            return false;
        }
        if ($order_data['pay_status'] == '2') {
            $this->msg = '订单/已支付';
            return false;
        }



        $this->user_model = new UserModel();
        $mem_data = $this->user_model->get_user_info($user_id);
        //if($mem_data['user_money'] > $odata['
        // var_dump($order_data['total_fee'] > $mem_data['user_money']); //
        // return $mem_data;
        if ($order_data['total_fee'] > $mem_data['user_money']) {
            $this->msg = '预存款金额不足';
            return false;
        }

        $order['order_status'] = 1;
        $order['confirm_time'] = time();
        $order['pay_status']   = 2;
        $order['pay_time']     = time();
        $order['order_amount'] = 0;
        $order['lastmodify'] = time();

        $sql = "update  ecs_order_info set 
                order_status = '" . $order['order_status'] . "',
                confirm_time = '" . $order['confirm_time'] . "',
                pay_status = '" . $order['pay_status'] . "',
                pay_time = '" . $order['pay_time'] . "',
                order_amount = '" . $order_data['total_fee'] . "',
                money_paid = '" . $order_data['total_fee'] ."',
                pay_name = '预存款支付',
                platform = '" . $platform . "',
                lastmodify = '" . $order['lastmodify'] . "'
        where order_sn = '" . $order_id . "' and user_id='" . $user_id . "'";

        $res = $this->getORM()->queryRow($sql);

        $this->erp_yue($order_id);
        $this->taoda($order_id);

        $this->accountlog->log_account_change($user_id, $order_data['total_fee'] * (-1), 0, 0, 0, sprintf('移动端订单支付', $order_id), '99', $order_id);
        $this->insert_pay_log($order_data['order_id'], $order_data['total_fee']);

        return true;
    }

    function erp_yue($order)
    {
        $data = \PhalApi\DI()->config->get('app');
        $url = $data['host_url'] . "flow.php?step=pay_status&order_sn=" . $order;
        $status =  file_get_contents($url);
        return $status;
    }

    /* 查询优惠活动 */
    function  getDiscount($product_id, $user_id, $result_spike, $goods_id)
    {
        $this->cart_model = new CartModel();
        $goods =  $this->cart_model->get_cart_goods_checkout($user_id, $result_spike, $goods_id, '', '', '', '', '', '', '');
        $goods = $goods['goods_list'];
        // var_dump($goods);
        if (count($goods) == 1) {
            $product_id =  $goods[0]['goods_id'];
        } elseif (count($goods) > 1) {
            foreach ($goods as $key => $val) {
                $product_id .= $val['goods_id'] . ',';
            }
            $product_id = substr($product_id, 0, -1);
        } else if (count($goods) == 0) {
            //            var_dump($goods_id);die;
            $product_id = 0;
        }

        $data  = $this->compute_discount($user_id, 'true', $product_id, '', '');
        return $data;
    }

    /* 截单 */
    function  getOrder_end()
    {

        $dtime = time();
        $sql = "select val from ecs_app_config where k= 'end_time'";
        $end_time = $this->getORM()->queryRow($sql);
        $sql = "select val from ecs_app_config where k= 'yes_no'"; //是否开启
        $yes_no = $this->getORM()->queryRow($sql);
        $sql = "select val from ecs_app_config where k= 'prompt'";
        $prompt = $this->getORM()->queryRow($sql);
        if ($yes_no['val'] == '1') {
            if ($dtime > strtotime($end_time['val'])) {
                $data['status'] = "true";
                $data['end_time'] = $end_time['val'];
                $data['prompt'] = $prompt['val'];
            } else {
                return array(
                    'status' => 'fail',
                    'response' => ''
                );
            }
        } else {

            return  array(
                'status' => 'fail',
                'response' => ''
            );
        }
        return array(
            'status' => 'succ',
            'response' => $data
        );
    }


    function  doPayCard($order_sn, $user_id, $pay_type, $p_code, $s_code)
    {
        $res = [
            'status' => 'fail',
            'message' => '',
            'response' => [],
        ];
        $sql = "select * from ecs_order_info where order_sn ='" . $order_sn . "' and user_id='" . $user_id . "' and pay_status !='2'";
        $order = $this->getORM()->queryRow($sql);
        if (!$order) {
            $res['message'] = '订单不存在或已支付';
            return $res;
        }
        $uri = [
            'verification' => 'Api/Api/SecurityCode.ashx',
            'select' => 'Api/Api/GetCardMsg.ashx',
            'pay' => 'Api/Api/Record_XF.ashx'
        ];
        $url = "http://www.kaiyuykt.com/";
        $machno = "11118888";
        $comId = "1596";
        $key = "b44ae5c8bf08715a54ae0d17b6b25a00";
        $check_params = [
            'p_code' => $p_code,
            's_code' => $s_code,
            'key' => $key,
        ];

        $check_response = $this->check_ky_pay($uri['verification'], $url, $check_params);
        //        var_dump($check_response);die;
        if ($check_response['respCode'] != 'Success') {
            $res['message'] = '云卡通卡号错误或卡片不可用';
            return $res;
        }
        $select_params = [
            'comId' => $comId,
            'p_code' => $p_code,
            'key' => $key,
        ];
        $select_response = $this->select_card($uri['select'], $url, $select_params);
        if ($select_response['respCode'] != 'Success') {
            $res['message'] = '云卡通卡号安全码错误';
            return $res;
        }

        if ($select_response['respDesc']['money'] < $order['order_amount']) {
            $res['message'] = '余额不足';
            return $res;
        }
        $noce_str = md5(time());
        $pay_params = [
            'comId' => $comId, //单位编号
            'p_code' => $p_code, //人员编号
            'money' => $order['order_amount'], //消费金额(次数)
            'opType' => '1', //消费类型
            'nonce_str' => $noce_str, //随机字符串
            'machno' => $machno, //机具号
            'key' => $key, //key
        ];
        $pay_response = $this->pay($uri['pay'], $url, $pay_params);
        $pay_params_s = serialize($pay_params);
        $pay_response_s = serialize($pay_response);
        if ($pay_response['respCode'] != 'Success') {
            $status = 'fail';
        } else {
            $status = 'succ';
        }

        $sql = "insert into ykt_pay_log (user_id,order_id,params,response,status,created_at) values ('" . $user_id . "','" . $order['order_id'] . "','" . $pay_params_s . "','" . $pay_response_s . "','" . $status . "','" . time() . "')";
        $this->getORM()->query($sql);
        $pay_response['respCode'] = 'Success';
        if ($pay_response['respCode'] == 'Success') {
            $order = $this->finish_order($order['order_id']);

            $this->accountlog = new AccountlogModel();
            $this->accountlog->log_account_change($user_id, $order['surplus'] * (-1), 0, 0, 0, sprintf('订单支付', $order_sn));

            $res['status'] = 'succ';
            $res['message'] = '支付成功';
            $res['response'] = $order;
            return $res;
        } else {
            $res['message'] = '云卡通卡号付款失败';
            return $res;
        }
    }


    /* 凯宇检查卡号安全码 */
    public function check_ky_pay($uri, $url, $params)
    {
        $url = $url . $uri;
        $response = $this->curl($url, $params);
        return $response;
    }

    /* 卡户查询接口 */
    public function select_card($uri, $url, $params)
    {
        $url = $url . $uri;
        $response = $this->curl($url, $params);
        return $response;
    }
    /* 用户支付接口 */
    public function pay($uri, $url, $params)
    {
        $url = $url . $uri;
        $response = $this->curl($url, $params);
        return $response;
    }

    public function curl($url, $post_data)
    {
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //设置链接
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //设置是否返回信息
        curl_setopt($ch, CURLOPT_POST, 1); //设置为POST方式
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置是否返回头信息

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); //POST数据
        $response = curl_exec($ch); //接收返回信息
        //        if (curl_errno($ch)) {//出错则显示错误信息
        //            print curl_error($ch);
        //        }
        curl_close($ch); //关闭curl链接

        $response = json_decode($response, true);
        return $response;
    }


    public function finish_order($order_id)
    {

        $sql = "update ecs_order_info set order_status='1',confirm_time='" . time() . "',pay_status='2',pay_time ='" . time() . "' where order_id='" . $order_id . "'";
        $sql_res = $this->getORM()->query($sql);

        $sql = "select * from ecs_order_info where order_id='" . $order_id . "'";
        $order = $this->getORM()->queryRow($sql);
        $order['order_id'] = $order['order_sn'];
        return $order;
    }


    function insert_pay_log($id, $amount, $type = 1, $is_paid = 0)
    {
        $sql = "INSERT INTO ecs_pay_log (order_id, order_amount, order_type, is_paid)" .
            " VALUES  ('" . $id . "', '" . $amount . "', '" . $type . "', '" . $is_paid . "')";
        return $this->getORM()->queryRow($sql);
    }



    public function getAllOrderNum($user_id)
    {
        // 获得当前用户的全部订单数量
        $return = []; //返回的数据


        $sql = "SELECT COUNT(order_id) AS num FROM ecs_order_info WHERE user_id = {$user_id}";
        $all_order = $this->getORM()->queryRows($sql);

        // 获得当前用户未付款的订单数
        //        $sql = "SELECT COUNT(order_id) AS num FROM ecs_order_info WHERE pay_status = 0 AND order_status !='2'  AND user_id = {$user_id}";
        $sql = "SELECT COUNT(order_id) AS num FROM ecs_order_info WHERE (order_status = 0 OR order_status = 1) AND pay_status = 0 AND shipping_status = 0  AND user_id = {$user_id}";
        $no_payment = $this->getORM()->queryRows($sql);

        // 获得当前用户的待收货的订单
        $sql = "SELECT COUNT(order_id) AS num FROM ecs_order_info WHERE shipping_status = 1 AND pay_status != 0 AND user_id = {$user_id}";
        $shipping_status = $this->getORM()->queryRows($sql);

        // 获得当前用户的退货数
        $sql = "SELECT COUNT(order_id) AS num FROM ecs_order_info WHERE order_status = 4 AND pay_status != 0 AND shipping_status = 4 AND user_id = {$user_id}";
        $order_status = $this->getORM()->queryRows($sql);

        // 获得当前用户的待发货数
        $sql = "SELECT COUNT(order_id) AS num FROM ecs_order_info WHERE shipping_status = 0 AND pay_status != 0 AND user_id = {$user_id}";
        $fahuo_status = $this->getORM()->queryRows($sql);


        /**
         * a => 全部订单数
         * b => 待付款订单数
         * c => 待收货订单数
         * d => 退货订单数
         * e => 待发货订单数
         */

        $return['a'] = $all_order[0]['num'];
        $return['b'] = $no_payment[0]['num'];
        $return['c'] = $shipping_status[0]['num'];
        $return['d'] = $order_status[0]['num'];
        $return['e'] = $fahuo_status[0]['num'];

        return $return;
    }

    //获取物流信息
    public function get_logistics_info($order_id)
    {

        if (empty($order_id)) {
            $res['message'] = '订单号错误';
            return $res;
        }
        $sql = "select value from  ecs_shop_config  WHERE code = 'logistics_trace'";
        $res =  $this->getORM()->queryRow($sql);
        $result = unserialize($res['value']); //数据转换
        //        $key = 'a99d972e-404d-4413-95e6-f81f3f8b55da';//客户授权key
        //        $user_id = '1387399';
        $url = 'http://api.kdniao.com/api/dist';    //实时查询请求地址
        $sql = "select order_id,invoice_no from  ecs_order_info where order_sn=" . $order_id;
        $logisticCode = $this->getORM()->queryRow($sql);
        //        if(empty($logisticCode['invoice_no'])){
        //            $info  = array('status'=>false,'msg'=>'物流单号为空');
        //            return $info;
        //        }
        // LogisticCode 物流单号   ShipperCode 物流公司编号
        $requestData = "{'OrderCode':'','ShipperCode':'" . $result['shippercode'] . "','LogisticCode':'" . $logisticCode['invoice_no'] . "'}";
        $datas = array(
            'EBusinessID' => $result['logistics_userId'],
            'RequestType' => '8001',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $result['logistics_key']);
        $result = $this->sendPost($url, $datas);
        $arr = json_decode($result);
        //        if($arr->Success==false){
        //            $info  = array('status'=>false,'msg'=>'接口参数错误');
        //            return $info;
        //        }
        $traces = $arr->Traces;
        $sql = "SELECT * FROM ecs_order_action WHERE order_id = '$logisticCode[order_id]' ORDER BY log_time asc,action_id asc";
        $order_log = $this->getORM()->queryRows($sql);
        //        0= "未发货" 3="配货中" 1="已发货" 2="收货确认" 4= "已发货(部分商品)" 5= "发货中"
        foreach ($order_log as  $k => $v) {
            if ($v['shipping_status'] == '0') {
                $log[$k]['AcceptStation'] = '未发货';
            } elseif ($v['shipping_status'] == '1') {
                $log[$k]['AcceptStation'] = '已发货';
            } elseif ($v['shipping_status'] == '3') {
                $log[$k]['AcceptStation'] = '配货中';
            } elseif ($v['shipping_status'] == '5') {
                $log[$k]['AcceptStation'] = '发货中';
            }
            $log[$k]['AcceptTime'] = date("Y-m-d H:i", $v['log_time']);
            //            $log[$k]['status'] ='DELIVERING';
        }

        $data = array_merge($log, $traces);
        $data = array_reverse($data);
        if (empty($data)) {
            $data[0]['AcceptStation'] = '商家还未发货';
        }

        $res['response'] = $data;
        $res['invoice_no'] = $logisticCode['invoice_no'];
        return $res;
    }

    function sendPost($url, $datas)
    {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if (empty($url_info['port'])) {
            $url_info['port'] = 80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader .= "Host:" . $url_info['host'] . "\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        $httpheader .= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets .= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }


    public function getOrderListAction($user_id, $status, $page)
    {
        /**
         * 0:全部
         * 1:待付款
         * 2:待发货
         * 3:待收货
         * 4:待评价
         * 5:退换货
         * 6:秒杀
         * 7.拼团
         */
        $page_size = pagesize();
        $page = ($page - 1) * $page_size;

        if ($status == 0) {
            $return = array();
            $sql = "SELECT order_sn AS order_id,user_id , order_status,pay_status,shipping_status ,order_id AS id,money_paid,order_amount AS total_amount
                    FROM ecs_order_info WHERE user_id = {$user_id}  order by add_time desc limit {$page},{$page_size} ";
            $data = $this->getORM()->queryRows($sql);


            foreach ($data as $k => $v) {
                $sql = "SELECT a.goods_name AS name,a.goods_id,b.promote_price,a.extension_code,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.goods_thumb AS list_pic_url,a.act_id
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                        WHERE order_id = " . $data[$k]['id'] ."";
                $goods_data = $this->getORM()->queryRows($sql);
                foreach ($goods_data as $a => $d) {
                    if ($d['act_id'] != 0) {
                        $sql = "select * from  ecs_goods_activity where act_id ='" . $d['act_id'] . "'";
                        $order_goods = $this->getORM()->queryRow($sql);
                        $goods_data[$a]['list_pic_url'] = goods_img_url($order_goods['package_image']);
                    } else {
                        $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                    }
                }
                // pay_detail
                $total_fee = $this->pay_detail($data[$k]['order_id'], $user_id);

                $data[$k]['total_amount'] = $total_fee['total_fee']; // 计算总金额
                $data[$k]['goods_list'] = $goods_data;
                if (($data[$k]['order_status'] == '0' || $data[$k]['order_status'] == '1') && $data[$k]['shipping_status'] == '0' && $data[$k]['pay_status'] == '0') {
                    $data[$k]['type'] = 'unpaid';

                    $data[$k]['type_status'] = '等待付款';
                }
                if ($data[$k]['order_status'] == '1' && $data[$k]['shipping_status'] == '0' && $data[$k]['pay_status'] == '2') {
                    $data[$k]['type'] = 'back';
                    $data[$k]['type_status'] = '等待发货';
                }
                if ($data[$k]['order_status'] == '1' && $data[$k]['shipping_status'] == '3' && $data[$k]['pay_status'] == '2') {
                    $data[$k]['type'] = 'unreceived';
                    $data[$k]['type_status'] = '配货中';
                }
                if ($data[$k]['order_status'] == '5' && $data[$k]['shipping_status'] == '5' && $data[$k]['pay_status'] == '2') {
                    $data[$k]['type'] = 'unreceived';
                    $data[$k]['type_status'] = '发货中';
                }
                if ($data[$k]['order_status'] == '5' && $data[$k]['shipping_status'] == '1' && $data[$k]['pay_status'] == '2') {
                    $data[$k]['type'] = 'unreceived';
                    $data[$k]['type_status'] = '已发货';
                }
                if ($data[$k]['order_status'] == '5' && $data[$k]['shipping_status'] == '2' && $data[$k]['pay_status'] == '2') {
                    $data[$k]['type'] = 'received';
                    $data[$k]['type_status'] = '已收货';
                }
                if (($data[$k]['order_status'] == '4' || $data[$k]['order_status'] == '1') && ($data[$k]['shipping_status'] == '2'
                        || $data[$k]['shipping_status'] == '1' || $data[$k]['shipping_status'] == '5') &&
                    ($data[$k]['pay_status'] == '2' || $data[$k]['pay_status'] == '0')) {
                    $data[$k]['type'] = 'cancelled';
                    $data[$k]['type_status'] = '退换货';
                }
                if($data[$k]['order_status'] == '4' && $data[$k]['shipping_status'] == '0' && $data[$k]['pay_status'] == '0')
                {
                    $data[$k]['type'] = 'cancelled';
                    $data[$k]['type_status'] = '已退货';
                }
//                if($data[$k]['order_status'] == '4' && $data[$k]['shipping_status'] == '2' && $data[$k]['pay_status'] == '2')
//                {
//                    $data[$k]['type'] = 'cancelled';
//                    $data[$k]['type_status'] = '退换货';
//                }
//                if($data[$k]['order_status'] == '4' && $data[$k]['shipping_status'] == '5' && $data[$k]['pay_status'] == '2')
//                {
//                    $data[$k]['type'] = 'cancelled';
//                    $data[$k]['type_status'] = '退换货';
//                }
                if ($data[$k]['order_status'] == '2' && $data[$k]['shipping_status'] == '0' && $data[$k]['pay_status'] == '0') {
                    $data[$k]['type'] = 'cancelled';
                    $data[$k]['type_status'] = '订单取消';
                }
                if ($data[$k]['order_status'] == '6' && $data[$k]['shipping_status'] == '4' && $data[$k]['pay_status'] == '2') {
                    $data[$k]['type'] = 'unreceived';
                    $data[$k]['type_status'] = '部分已发货';
                }


                if ($status == 5) {
                    $data[$k]['type'] = 5;
                    $sql = "select * from ecs_return_goods where order_sn='" . $data[$k]['order_id'] . "' and user_id ='" . $user_id . "'";
                    $r_data  =  $this->getORM()->queryRow($sql);
                    $data[$k]['return_status'] = $r_data['return_status'];
                }

                //                if ($status == 6){
                //                    $data[$k]['type'] = 6;
                //                    $sql = "select * from ecs_order_info where order_sn='".$data[$k]['order_id']."' and user_id ='".$user_id."'";
                //                    $r_data  =  $this->getORM()->queryRow($sql);
                //                    $data[$k]['return_status'] = $r_data['return_status'];
                //                }

            }
            $return['order_list'] = array_reverse($data);

            $sql = "SELECT COUNT(order_sn) AS num FROM ecs_order_info WHERE user_id = {$user_id} ";
            $page_total = $this->getORM()->queryRows($sql);

            $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);
            return $return;
        }
        if ($status == 1) {
            $sql = "SELECT order_sn AS order_id,order_amount AS total_amount,user_id AS pay_status,shipping_status ,order_id AS id 
                    FROM ecs_order_info WHERE user_id = {$user_id} AND (order_status = 0 OR order_status = 1) AND pay_status = 0 AND shipping_status = 0 order by add_time limit {$page},{$page_size} ";

            $data = $this->getORM()->queryRows($sql);
            foreach ($data as $k => $v) {
                $sql = "select * from  ecs_order_goods where order_id ='" . $data[$k]['id'] . "'";
                $order_goods = $this->getORM()->queryRow($sql);
                if ($order_goods['act_id'] != 0) {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.package_image AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods_activity AS b ON a.act_id = b.act_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                } else {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.goods_thumb AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                }

                $goods_data = $this->getORM()->queryRows($sql);
                foreach ($goods_data as $a => $d) {
                    $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                }

                // pay_detail
                $total_fee = $this->pay_detail($data[$k]['order_id'], $user_id);
                $data[$k]['total_amount'] = $total_fee['total_fee']; // 计算总金额

                $data[$k]['goods_list'] = $goods_data;
                $data[$k]['type'] = 'unpaid';
                $data[$k]['type_status'] = '等待付款';
            }
            $return['order_list'] = $data;

            $sql = "SELECT COUNT(order_sn) AS num FROM ecs_order_info WHERE user_id = {$user_id} AND pay_status = 0 ";
            $page_total = $this->getORM()->queryRows($sql);

            $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);

            return $return;
        }
        if ($status == 2) {
            $sql = "SELECT order_sn AS order_id,order_amount AS total_amount,user_id, pay_status,shipping_status ,order_id AS id,money_paid 
                    FROM ecs_order_info WHERE user_id = {$user_id} AND (shipping_status = 0 OR shipping_status = 3 OR shipping_status = 5 ) AND pay_status = 2
                    
                    order by add_time desc limit {$page},{$page_size}
                    ";
            $data = $this->getORM()->queryRows($sql);
            foreach ($data as $k => $v) {
                $sql = "select * from  ecs_order_goods where order_id ='" . $data[$k]['id'] . "'";
                $order_goods = $this->getORM()->queryRow($sql);
                if ($order_goods['act_id'] != 0) {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.package_image AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods_activity AS b ON a.act_id = b.act_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                } else {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.goods_thumb AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                }
                $goods_data = $this->getORM()->queryRows($sql);
                foreach ($goods_data as $a => $d) {
                    $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                }
                $data[$k]['goods_list'] = $goods_data;
                $data[$k]['type'] = 'back';

                // pay_detail
                $total_fee = $this->pay_detail($data[$k]['order_id'], $user_id);
                $data[$k]['total_amount'] = $total_fee['total_fee']; // 计算总金额

                if ($data[$k]['shipping_status'] == 3) {
                    $data[$k]['type_status'] = '配货中';
                } else {
                    $data[$k]['type_status'] = '等待发货';
                }
            }
            $return['order_list'] = array_reverse($data);

            $sql = "SELECT COUNT(order_sn) AS num FROM ecs_order_info WHERE user_id = {$user_id} AND (shipping_status = 0 OR shipping_status = 3 OR shipping_status = 5) AND pay_status = 2 ";
            $page_total = $this->getORM()->queryRows($sql);

            $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);

            return $return;
        }
        if ($status == 3) {
            $sql = "SELECT order_sn AS order_id,order_amount AS total_amount,user_id , pay_status,shipping_status ,order_id AS id,money_paid 
                    FROM ecs_order_info WHERE user_id = {$user_id} AND shipping_status = 1 AND order_status = 5 AND pay_status = 2
                    order by add_time desc limit {$page},{$page_size}
                    ";
            $data = $this->getORM()->queryRows($sql);
            foreach ($data as $k => $v) {
                $sql = "select * from  ecs_order_goods where order_id ='" . $data[$k]['id'] . "'";
                $order_goods = $this->getORM()->queryRow($sql);
                if ($order_goods['act_id'] != 0) {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.package_image AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods_activity AS b ON a.act_id = b.act_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                } else {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.goods_thumb AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                }
                $goods_data = $this->getORM()->queryRows($sql);
                foreach ($goods_data as $a => $d) {
                    $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                }
                $data[$k]['goods_list'] = $goods_data;
                $data[$k]['type'] = 'unreceived';

                // pay_detail
                $total_fee = $this->pay_detail($data[$k]['order_id'], $user_id);
                $data[$k]['total_amount'] = $total_fee['total_fee']; // 计算总金额
                $data[$k]['type_status'] = '已发货';
                if ($status == 5) {
                    $data[$k]['type'] = 5;
                    $sql = "select * from ecs_return_goods where order_sn='" . $data[$k]['order_id'] . "' and user_id ='" . $user_id . "'";
                    $r_data  =  $this->getORM()->queryRow($sql);
                    $data[$k]['return_status'] = $r_data['return_status'];
                }
            }

            $return['order_list'] = array_reverse($data);

            $sql = "SELECT COUNT(order_sn) AS num FROM ecs_order_info WHERE user_id = {$user_id} AND shipping_status = 1 AND pay_status = 2 ";
            $page_total = $this->getORM()->queryRows($sql);

            $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);
            return $return;
        }
        if ($status == 4) {
            $sql = "SELECT order_sn AS order_id,order_amount AS total_amount,user_id , pay_status,shipping_status ,order_id AS id,money_paid 
                    FROM ecs_order_info WHERE user_id = {$user_id} AND order_status = 5 AND shipping_status = 2 AND pay_status = 2
                    order by add_time desc limit {$page},{$page_size}
                    ";
            $data = $this->getORM()->queryRows($sql);
            foreach ($data as $k => $v) {
                if ($this->checkJudge($data[$k]['id']) == 'true') { // 检测订单的所有商品是已评论过的
                    unset($data[$k]);
                    continue;
                }
                $sql = "select * from  ecs_order_goods where order_id ='" . $data[$k]['id'] . "'";
                $order_goods = $this->getORM()->queryRow($sql);
                if ($order_goods['act_id'] != 0) {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.package_image AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods_activity AS b ON a.act_id = b.act_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                } else {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.goods_thumb AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                }
                $goods_data = $this->getORM()->queryRows($sql);
                foreach ($goods_data as $a => $d) {
                    $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                }
                $data[$k]['goods_list'] = $goods_data;
                $data[$k]['type'] = 'received';

                // pay_detail
                $this->pay_detail($data[$k]['order_id'], $user_id);
                //$data[$k]['total_amount'] = $total_fee['total_fee']; // 计算总金额

                $data[$k]['type_status'] = '待评价';
            }
            $return['order_list'] = array_reverse(array_values($data));

            $sql = "SELECT COUNT(order_sn) AS num FROM ecs_order_info WHERE user_id = {$user_id} AND order_status = 5 AND shipping_status = 2 AND pay_status = 2 ";
            $page_total = $this->getORM()->queryRows($sql);

            $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);

            return $return;
        }

        if ($status == 5) {
            $sql = "SELECT order_sn AS order_id,order_amount AS total_amount,user_id , pay_status,shipping_status ,order_id AS id,money_paid,order_status 
                    FROM ecs_order_info WHERE user_id = {$user_id} 
                    AND (order_status = 4 OR order_status = 1)
                    AND (shipping_status = 1 OR shipping_status = 5)
                    AND (pay_status = 2 OR pay_status = 0) 
                    order by add_time desc limit {$page},{$page_size}
                    ";
            $data = $this->getORM()->queryRows($sql);
            foreach ($data as $k => $v) {
                $sql = "select * from  ecs_order_goods where order_id ='" . $data[$k]['id'] . "'";
                $order_goods = $this->getORM()->queryRow($sql);
                if ($order_goods['act_id'] != 0) {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.package_image AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods_activity AS b ON a.act_id = b.act_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                } else {
                    $sql = "SELECT a.goods_name AS name,a.goods_id,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.goods_thumb AS list_pic_url
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                        WHERE order_id = " . $data[$k]['id'] . "";
                }
                $goods_data = $this->getORM()->queryRows($sql);
                foreach ($goods_data as $a => $d) {
                    $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                }
                $data[$k]['goods_list'] = $goods_data;
                $data[$k]['type'] = 'unreceived';

                // pay_detail
                $total_fee = $this->pay_detail($data[$k]['order_id'], $user_id);
                $data[$k]['total_amount'] = $total_fee['order_amount']; // 计算总金额order_amount
                if($data[$k]['pay_status'] == 0 && $data[$k]['shipping_status'] == 0){
                    $data[$k]['type_status'] = '已退货';
                }else{
                    $data[$k]['type_status'] = '退换货';
                }
                if ($status == 5) {
                    $data[$k]['type'] = 5;
                    $sql = "select * from ecs_return_goods where order_sn='" . $data[$k]['order_id'] . "' and user_id ='" . $user_id . "'";
                    $r_data  =  $this->getORM()->queryRow($sql);
                    $data[$k]['return_status'] = $r_data['return_status'];
                }
            }

            $return['order_list'] = array_reverse($data);

            $sql = "SELECT COUNT(order_sn) AS num FROM ecs_order_info WHERE user_id = {$user_id}
            AND (shipping_status = 2 OR  shipping_status = 1)
            AND (pay_status = 0 OR pay_status = 2)
            AND (order_status = 4 OR order_status = 1)";
            $page_total = $this->getORM()->queryRows($sql);

            $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);

            return $return;
        }

        if ($status == 6) {

            $sql = "SELECT a.order_sn AS order_id,
                           a.goods_amount AS price,
                           a.user_id AS member_id,a.pay_status,
                           a.shipping_status,a.order_id AS id,
                           c.goods_thumb AS list_pic_url,
                           a.pack_fee,
                           c.goods_number AS number
                    FROM ecs_order_info AS a 
                    LEFT JOIN ecs_order_goods AS b 
                    ON a.order_id = b.order_id 
                    LEFT JOIN ecs_goods AS c 
                    ON b.goods_id = c.goods_id
                    WHERE user_id = {$user_id} AND a.order_type = 2
                    ORDER BY a.add_time DESC limit {$page},{$page_size}
                    ";

            $data = $this->getORM()->queryRows($sql);
            //            var_dump($data);exit;
            foreach ($data as $k => $v) {
                $total_fee = $this->pay_detail($data[$k]['order_id'], $user_id);
                $data[$k]['total_amount'] = $total_fee['total_fee'];
                $data[$k]['type_status'] = '秒杀商品';
                $data[$k]['type'] = 'spike';
                $sql = "select a.*,b.* from ecs_order_goods AS a LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id where a.order_id='{$v['id']}'";
                $order_goods = $this->getORM()->queryAll($sql);
                //                var_dump($order_goods);
                //                die;
                foreach ($order_goods as $k1 => $v1) {
                    //                    var_dump($v1['spike_sum']);
                    //                    var_dump($v1['price']);
                    //                    die;
                    $data[$k]['goods_list'][$k1] = $v1;
                    $data[$k]['goods_list'][$k1]['list_pic_url'] = goods_img_url($data[$k]["list_pic_url"]);
                    $data[$k]['goods_list'][$k1]['price'] = $v1['spike_sum'];
                    $data[$k]['goods_list'][$k1]['number'] = $v1['goods_number'];
                }
                //                $data[$k]["list_pic_url"] = goods_img_url($data[$k]["list_pic_url"]);
                //                $data[$k]['goods_list'][$k]["list_pic_url"] = goods_img_url($data[$k]["list_pic_url"]);
                //                $data[$k]['goods_list'][$k]['number'] = $data[$k]['number'];
            }
            $sql = "SELECT COUNT(order_sn) AS num FROM ecs_order_info WHERE user_id = {$user_id} and order_type = '2' ";
            $page_total = $this->getORM()->queryRows($sql);
            $return['order_list'] = array_reverse(array_values($data));
            $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);
            return $return;
        }

        if ($status == 7) {

            $sql = "SELECT a.order_sn AS order_id,
                           a.goods_amount AS price,
                           a.user_id AS member_id,a.pay_status,
                           a.shipping_status,a.order_id AS id,
                           c.goods_thumb AS list_pic_url,
                           a.pack_fee,
                           c.goods_number AS number
                    FROM ecs_order_info AS a 
                    LEFT JOIN ecs_order_goods AS b 
                    ON a.order_id = b.order_id 
                    LEFT JOIN ecs_goods AS c 
                    ON b.goods_id = c.goods_id
                    WHERE user_id = {$user_id} AND a.order_type = 1
                    ORDER BY a.add_time DESC limit {$page},{$page_size}
                    ";

            $data = $this->getORM()->queryRows($sql);
            foreach ($data as $k => $v) {
                $total_fee = $this->pay_detail($data[$k]['order_id'], $user_id);
                $data[$k]['total_amount'] = $total_fee['total_fee'];
                $data[$k]['type_status'] = '拼团商品';
                $data[$k]['type'] = 'spike';
                $sql = "select a.*,b.* from ecs_order_goods AS a LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id where a.order_id='{$v['id']}'";
                $order_goods = $this->getORM()->queryAll($sql);
                foreach ($order_goods as $k1 => $v1) {
                    $data[$k]['goods_list'][$k1] = $v1;
                    $data[$k]['goods_list'][$k1]['list_pic_url'] = goods_img_url($data[$k]["list_pic_url"]);
                    $data[$k]['goods_list'][$k1]['price'] = $v1['spike_sum'];
                    $data[$k]['goods_list'][$k1]['number'] = $v1['goods_number'];
                }
            }
            $sql = "SELECT COUNT(order_sn) AS num FROM ecs_order_info WHERE user_id = {$user_id} and order_type = '1' ";
            $page_total = $this->getORM()->queryRows($sql);
            $return['order_list'] = array_reverse(array_values($data));
            $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);
            return $return;
        }
    }

    public function getReturnListAction($user_id, $page, $status)
    {

        $page_size = pagesize();
        $page = ($page - 1) * $page_size;

        $return = array();
        $sql = "SELECT order_sn AS order_id,user_id AS order_status,pay_status,shipping_status ,order_id AS id,money_paid
                    FROM ecs_order_info WHERE user_id = {$user_id} and apply_for_status='true'  order by add_time desc limit {$page},{$page_size} ";

        $data = $this->getORM()->queryRows($sql);


        foreach ($data as $k => $v) {
            $sql = "SELECT a.goods_name AS name,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.goods_id,b.goods_thumb AS list_pic_url,act_id
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                        WHERE order_id = " . $data[$k]['id'] . "";
            $goods_data = $this->getORM()->queryRows($sql);
            foreach ($goods_data as $a => $d) {
                if ($d['act_id'] == 0)
                    $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                else {
                    $str = "select act_id,package_image from ecs_goods_activity where act_id = {$d['act_id']}";
                    $img_url = $this->getORM()->queryRow($str);
                    $goods_data[$a]['list_pic_url'] = goods_img_url($img_url['package_image']);
                }
            }

            // pay_detail
            $total_fee = $this->pay_detail($data[$k]['order_id'], $user_id);

            $data[$k]['total_amount'] = $total_fee['total_fee']; // 计算总金额

            $data[$k]['goods_list'] = $goods_data;

            $sql = "select * from ecs_return_goods where order_sn='" . $data[$k]['order_id'] . "' and user_id ='" . $user_id . "'";
            $return_data  = $this->getORM()->queryRow($sql);
            if ($return_data['return_status'] == 'wait') {
                $data[$k]['type'] = 'wait';
                $data[$k]['type_status'] = '审核中';
            }
            if ($return_data['return_status'] == 'error') {
                $data[$k]['type'] = 'error';
                $data[$k]['type_status'] = '审核拒绝';
            }
            if ($return_data['return_status'] == 'succ') {
                $data[$k]['type'] = 'succ';
                $data[$k]['type_status'] = '审核已通过';
            }
        }
        $return['order_list'] = $data;

        $sql = "SELECT COUNT(order_sn) AS num FROM ecs_return_goods WHERE user_id = {$user_id} ";
        $page_total = $this->getORM()->queryRows($sql);

        $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);
        return $return;
    }
    public function checkJudge($order_id)
    {
        // 检测该订单商品是否都已经评论
        $sql = "SELECT COUNT(order_id) AS num FROM ecs_order_goods WHERE order_id = {$order_id} ";
        $order_goods_num = $this->getORM()->queryRows($sql); // 获取订单包含的商品数量

        $sql = "SELECT COUNT(order_id) AS num FROM ecs_comment WHERE order_id = {$order_id} ";
        $comment_num = $this->getORM()->queryRows($sql); // 获取订单评论数量

        if ($order_goods_num[0]['num'] == $comment_num[0]['num']) {
            return 'true'; // 全部商品已评论
        }
        return 'false'; // 还有部分商品未评论
    }

    public function getOrderDetail($order_id, $user_id)
    {
        // 订单详情页面
        $return = array();

        $sql = "SELECT surplus,bonus,pack_fee,integral_money,extension_code,inv_type,tax,discount,goods_amount,order_sn,apply_for_status,order_amount AS total_amount,user_id AS member_id,order_status,pay_status,shipping_status ,order_id AS id,
                address,consignee,mobile,country,province,city,district,add_time,lastmodify AS last_modified,shipping_fee,pay_name,order_type,pt_id FROM ecs_order_info WHERE order_sn = {$order_id}";


        $total_fee = $this->pay_detail($order_id, $user_id); // 订单的总金额
        //        var_dump($sql);exit;
        $data = $this->getORM()->queryRows($sql);
        if ($data[0]['surplus'] > 0) { // 使用了红包或积分等
            $total_fee['total_fee'] = $data[0]['surplus'];
        }
        /**
         * ------ 此处为订单金额信息输出 ----
         * 支付方式
         * 运费
         * 总金额
         * 红包金额
         * 积分抵扣金额
         * 发票类型
         * 发票税额
         * 商品总金额
         */
        $return['a'] = array(
            'path' => $data[0]['pay_name'],
            'cost' => $total_fee['total_fee'],
            'deliver_cost' => $data[0]['shipping_fee'],
            'bonus' => $data[0]['bonus'],
            'integral_money' => $data[0]['integral_money'],
            'inv_type' => $data[0]['inv_type'],
            'tax' => $data[0]['tax'],
            'goods_amount' => $data[0]['goods_amount'],
            'pack_fee' => $data[0]['pack_fee']
        );

        // 省市区
        $sql = "SELECT
                a.region_name AS country,
                b.region_name AS province,
                c.region_name AS city,
                d.region_name AS district 
            FROM
                ecs_region AS a,
                ecs_region AS b,
                ecs_region AS c,
                ecs_region AS d 
            WHERE
                a.region_id = " . $data[0]['country'] . " 
                AND b.region_id = " . $data[0]['province'] . " 
                AND c.region_id = " . $data[0]['city'] . " 
                AND d.region_id = " . $data[0]['district'] . "";
        $addr = $this->getORM()->queryRows($sql);

        foreach ($data as $k => $v) {
            // 该订单中的物品
            $sql = "SELECT a.goods_name,a.goods_number AS number,a.goods_price AS retail_price,a.goods_attr AS goods_specifition_name_value,a.extension_code,a.goods_id AS id,b.goods_thumb AS list_pic_url
                    FROM ecs_order_goods AS a
                    LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                    WHERE order_id = " . $data[$k]['id'] . "";
            $goods_data = $this->getORM()->queryRows($sql);
            foreach ($goods_data as $a => $d) {
                $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                if ($d['extension_code'] == 'package_buy') {
                    $sql = "select package_image from ecs_goods_activity where act_id ='" . $d['id'] . "'";
                    $goods_img = $this->getORM()->queryRow($sql);
                    $goods_data[$a]['list_pic_url'] = goods_img_url($goods_img['package_image']);
                }
            }
            $return['orderGoods'] = $goods_data;
        }
        $return['orderInfo'] = $data[0];
        $return['orderInfo']['order_id'] = $data[0]['order_sn'];
        $return['orderInfo']['add_time'] = date("Y-m-d H:i:s", $data[0]['add_time']);
        $return['orderInfo']['full_region'] = "" . $addr[0]['province'] . " " . $addr[0]['city'] . " " . $addr[0]['district'] . " ";

        $sql = "select * from ecs_return_goods where order_sn='" . $order_id . "' and user_id ='" . $user_id . "'";
        $r_data  =  $this->getORM()->queryRow($sql);
        $return['orderInfo']['return_status'] = $r_data['return_status'];

        // 是否为拼团商品
        if ($data[0]["order_type"] == '1') {
            // 拼团商品
            if ($data[0]["pt_id"] != "0") {
                // 拼团成
                $return["orderInfo"]["pintuan_status_text"] = "拼团成功";
            }
            if ($data[0]["pt_id"] == "0") {
                $return["orderInfo"]["pintuan_status_text"] = "拼团中";
            }
        }

        // 订单状态
        if (($data[0]['order_status'] == '0' || $data[0]['order_status'] == '1') && $data[0]['pay_status'] == '0') {
            $return['orderInfo']['order_status_text'] = '未支付';
            return $return;
        }
        if ($data[0]['order_status'] == '1' && $data[0]['pay_status'] == '2' && $data[0]['shipping_status'] == '0') {
            $return['orderInfo']['order_status_text'] = '待发货';
            return $return;
        }
        if ($data[0]['order_status'] == '1' && $data[0]['shipping_status'] == '3' && $data[0]['pay_status'] == '2') {
            $return['orderInfo']['order_status_text'] = "配货中";
            return $return;
        }
        if ($data[0]['order_status'] == '5' && $data[0]['shipping_status'] == '5' && $data[0]['pay_status'] == '2') {
            $return['orderInfo']['order_status_text'] = "发货中";
            return $return;
        }
        if ($data[0]['order_status'] == '5' && $data[0]['shipping_status'] == '1' && $data[0]['pay_status'] == '2') {
            $return['orderInfo']['order_status_text'] = "已发货";
            return $return;
        }

        if ($data[0]['order_status'] == '2' && $data[0]['pay_status'] == '0' &&  $data[0]['shipping_status'] == '0') {
            $return['orderInfo']['order_status_text'] = "订单已取消";
            return $return;
        }
        if ($data[0]['order_status'] == '5' && $data[0]['shipping_status'] == '2' && $data[0]['pay_status'] == '2') {
            foreach ($goods_data as $k => $v) {
                $sql = "SELECT	* FROM ecs_comment WHERE id_value = " . $goods_data[$k]['id'] . " AND order_id = " . $data[0]['id'] . " ";
                $res = $this->getORM()->queryRows($sql);
                if ($res[0] != '') { // 已评论
                    $goods_data[$k]['judge'] = '1';
                    continue;
                }
                $goods_data[$k]['judge'] = '0';
            }
            $return['orderGoods'] = $goods_data;
            $return['orderInfo']['order_status_text'] = "已收货";
            return $return;
        }
        if ($data[0]['order_status'] == '4' && $data[0]['shipping_status'] == '2' && $data[0]['pay_status'] == '2') {
            $return['orderInfo']['order_status_text'] = "退换货";
            return $return;
        }
        if ($data[0]['order_status'] == '6' && $data[0]['shipping_status'] == '4' && $data[0]['pay_status'] == '2') {
            $return['orderInfo']['order_status_text'] = "部分发货";
            return $return;
        }

        return $return;
    }

    public function getCancelOrderRes($order_id, $user_id)
    {
        // 取消订单
        $sql = "SELECT * FROM ecs_order_info WHERE user_id = {$user_id} AND order_sn = {$order_id} ";
        $data = $this->getORM()->queryRows($sql);
        if ($data[0]['shipping_status'] == '0' && $data[0]['pay_status'] == '0') {
            $sql = "UPDATE ecs_order_info SET order_status = '2' WHERE user_id = {$user_id} AND order_sn = {$order_id} ";
            $res = $this->getORM()->queryRows($sql);

            // 将库存回退
            $sql = "select value from ecs_shop_config where code = 'stock_dec_time'";
            $stock_dec_time = $this->getORM()->queryRow($sql);
            if ($stock_dec_time['value'] == '1') {
                // 代表下单时进行扣除库存操作 此时取消则进行库存增加操作
                $sql = "SELECT b.product_id,b.goods_number,b.goods_id FROM ecs_order_info AS a LEFT JOIN ecs_order_goods AS b ON a.order_id = b.order_id WHERE order_sn = '" . $order_id . "'";
                $order_goods = $this->getORM()->queryRows($sql);
                foreach ($order_goods as $k => $v) {
                    $sql = "UPDATE ecs_products SET product_number = (product_number + '" . $v['goods_number'] . "') WHERE product_id = '" . $v['product_id'] . "'";
                    $this->getORM()->queryRow($sql); // 更新物料数量
                    $sql = "UPDATE ecs_goods SET goods_number = (goods_number + {$v['goods_number']}) WHERE goods_id = {$v['goods_id']}";
                    $this->getORM()->queryRow($sql); // 更新总库存
                }
            }

            return array('status' => true);
        }

        return array('status' => false);
    }

    public function getReceiveOrderRes($order_id, $user_id)
    {
        // 确认收货
        $sql = "SELECT * FROM ecs_order_info WHERE user_id = {$user_id} AND order_sn = {$order_id} ";
        $data = $this->getORM()->queryRow($sql);

        if ($data['order_status'] == '5' && $data['pay_status'] == '2') {
            $sql = "UPDATE ecs_order_info SET shipping_status = '2' WHERE user_id = {$user_id} AND order_sn = {$order_id} ";
            $res = $this->getORM()->queryRows($sql);
            return array('status' => true);
        }
        return array('status' => false);
    }

    public function GetDeliveryTimeApi()
    {
        $sql = "SELECT *  FROM ecs_order_delivery_time where 1";
        $data = $this->getORM()->queryRows($sql);
        foreach ($data as $key => $val) {
            $o_time = explode('-', $val['o_time']);
            if (time() > strtotime($o_time[0]) && time() > strtotime($o_time[1])) {
                unset($data[$key]);
            }
        }

        return $data;
    }
    public  function  DeliveryTimeApi($delivery_id)
    {

        $sql = "SELECT *  FROM ecs_order_delivery_time where id ='" . $delivery_id . "'";
        $data = $this->getORM()->queryRow($sql);
        //        $o_time = explode('-',$data['o_time']);
        $end_time = strtotime(date('Y-m-d')) + '86399';
        $sql = "select count(order_id) as count from ecs_order_info where add_time >'" . strtotime(date('Y-m-d')) . "' and add_time < '" . $end_time . "' and best_time ='" . $data['o_time'] . "' ";
        $order_count =  $this->getORM()->queryRow($sql);
        if ($order_count['count'] > $data['quantity_order']) {
            //        if(101>$data['quantity_order']){
            return array(
                'status' => 'fail',
                'message' => '该时间段配送单量已满，请选择其他时间段',
                'response' => array(),
            );
        }

        return array(
            'status' => 'succ',
            'message' => '',
            'response' => array(),
        );
    }

    public function submitAfterSale($order_id, $note, $goods_id, $goods_num, $user_id)
    {
        //        $this->accountlog = new AccountlogModel();
        $goods_data_id = explode(',', $goods_id);
        $goods_data_num = explode(',', $goods_num);
        $sql = "select * from ecs_order_info where order_sn = " . $order_id;
        $data = $this->getORM()->queryRow($sql);
        if (empty($data)) {
            return array(
                'status' => 'fail',
                'message' => '订单不存在',
                'response' => array(),
            );
        }
        $rand = "1234567890";
        mt_srand(10000000 * (float)microtime());
        for ($i = 0, $str = '', $lc = strlen($rand) - 1; $i < 5; $i++) {
            $rand_code .= $rand[mt_rand(0, $lc)];
        }
        $return_id =  time() . $rand_code;

        foreach ($goods_data_id as $key => $val) {
            if (!empty($val)) {
                $sql = "select * from ecs_order_goods where order_id ='" . $data['order_id'] . "' and  goods_id ='" . $val . "'";
                $goods_data  =  $this->getORM()->queryRow($sql);
                if ($goods_data['goods_number'] < $goods_data_num[$key]) {
                    return array(
                        'status' => 'fail',
                        'message' => '退换数量超过订单数量',
                        'response' => array(),
                    );
                }
                $sql = "select * from ecs_return_goods where order_sn='" . $order_id . "' and user_id ='" . $user_id . "' and  goods_id ='" . $val . "'";
                $return_data  = $this->getORM()->queryRows($sql);
                if (!empty($return_data)) {
                    return array(
                        'status' => 'fail',
                        'message' => '该商品已经申请请勿重复提交',
                        'response' => array(),
                    );
                }

                $sql = "insert into ecs_return_goods (return_id,order_id,return_text,order_sn,user_id,goods_id,goods_name,num,ctime) values ('" . $return_id . "','" . $data['order_id'] . "','" . $note . "','" . $order_id . "','" . $user_id . "','" . $val . "','" . $goods_data['goods_name'] . "','" . $goods_num[$key] . "','" . time() . "')";
                $this->getORM()->queryRows($sql);


                $sql = "update ecs_order_info set order_status = '4', apply_for_status ='true' where order_sn ='" . $order_id . "'";
                $this->getORM()->queryRows($sql);
                // 需将申请的信息填写入ecs_order_action 表中
                // 查出当前订单的订单状态
                $sql = "select * from ecs_order_info where order_sn = '" . $order_id . "'";
                $data = $this->getORM()->queryRow($sql);

                // 记录到订单信息中
                $sql = "insert into ecs_order_action set order_id = '" . $data['order_id'] . "',action_user = '" . $data['consignee'] . "',order_status = '4',shipping_status = '" . $data['shipping_status'] . "',pay_status='" . $data['pay_status'] . "',action_note='" . $note . "',log_time='" . time() . "'";
                $this->getORM()->queryRow($sql);  // 记录到订单信息中

                //                $sql = "select parent_id from ecs_users where user_id=$user_id";
                //                $sth = $this->getORM()->queryRow($sql);
                //                if($sth){
                //
                //                }

                /*
                 * 将返利收回
                 * */
                //                $sql = "select parent_id from ecs_users where user_id = $user_id"; //判断这个账户是否是分享注册的账户
                //                $sth = $this->getORM()->queryRow($sql);
                //                if($sth){
                //                    $sql = "select value from ecs_shop_config where code = 'affiliate'";
                //                    $ss =  $this->getORM()->queryRow($sql);
                //                    $tmp = unserialize($ss['value']);
                //
                //                    $level_point_all = $tmp['config']['level_point_all']; //积分返利
                //                    $level_money_all = $tmp['config']['level_money_all']; //现金返利
                //                    $cash = $level_money_all * ($data['goods_amount']+$data['shipping_fee']+$data['insure_fee']+$data['pay_fee']+$data['pack_fee']+$data['card_fee']) * 0.01;
                //
                ////            $sql = "select order_id from ecs_order_info where order_sn='$order_id'";
                ////            $sth =  $this->getORM()->queryRow($sql);
                ////            $sql = "select give_integral from ecs_goods where goods_id = (select goods_id from ecs_order_goods where order_id =".$sth['order_id']." )";
                ////            $sth =  $this->getORM()->queryRow($sql);
                ////            if($sth)
                ////            exit;
                //
                //                }
                ////                var_dump((int)$sth['parent_id']);
                ////                exit;
                //                if($data['apply_for_status'] == 'true')
                //                {
                //                    $this->accountlog->log_account_change((int)$sth['parent_id'],$cash*(-1),0,0,0,sprintf('返利收回',$order_id));
                //                }


                return array(
                    'status' => 'succ',
                    'message' => '申请成功',
                    'response' => array(),
                );
            }
        }




        //        // 用户提交的售后
        //        $sql = "SELECT * FROM ecs_order_info WHERE order_sn = {$order_id} ";
        //        $data = $this->getORM()->queryRows($sql);
        //
        //        $sql = "INSERT INTO ecs_order_action SET order_id=".$data[0]['order_id'].",action_user='".$data[0]['consignee']."',order_status=".$data[0]['order_status'].",shipping_status=".$data[0]['shipping_status'].",pay_status=".$data[0]['pay_status'].",action_note='".$note."',log_time='".time()."' ";
        //        $this->getORM()->queryRows($sql);
        //        return true;
    }

    public function sumIntegral($cart_goods)
    {
        // 计算当前订单所能使用的最大积分数
        $integral = 0;
        $sql = "SELECT value FROM ecs_shop_config WHERE `code` = 'integral_scale'";
        $integral_scale = $this->getORM()->queryRow($sql);
        foreach ($cart_goods as $k => $v) {
            $sql = "SELECT integral FROM ecs_goods WHERE goods_id = '" . $v['goods_id'] . "'";
            $data = $this->getORM()->queryRow($sql);
            $integral += $data['integral'];
        }
        return ['max' => intval(($integral * 100) / $integral_scale['value']), 'inte_money' => $integral];
    }

    public function other_data($order_id, $user_id)
    {
        // 订单支付页面其他数据
        $sql = "SELECT goods_name FROM ecs_order_goods WHERE order_id = (SELECT order_id FROM ecs_order_info WHERE order_sn = '" . $order_id . "')";
        $goods_name = $this->getORM()->queryRows($sql); // 取出当前订单的商品名称  只用第一个

        //        $aliPayWapUrl = alipay_h5_url();  // 支付宝H5支付地址
        $payment_mod = new Payment();
        $ali_h5_url = $payment_mod->getPayConfig("zhifu");

        $aliPayWapUrl = $ali_h5_url["zid"];
        return ['goods_name' => $goods_name[0]['goods_name'], 'url' => $aliPayWapUrl];
    }

    public function createOrder($user_id, $money)
    {
        // 创建余额充值订单
        $order_sn = date('YmdHis') . rand(10000, 99999);
        $sql = "insert into ecs_account_log set user_id = '" . $user_id . "',user_money = '" . $money . "',frozen_money = '0',rank_points = '0',pay_points = '0',change_time = '" . time() . "',change_desc = '余额充值:" . $money . "元(订单未支付)',order_sn='" . $order_sn . "',change_type='99' "; // 记录该次消费
        $this->getORM()->queryRow($sql); // 记录本次消费

        return $order_sn;  // 仅返回订单号
    }

    public function seckill_fee($goods_id, $number,$consignee,$shipping_id)
    {
        //111111111111111111111
        $return = [];
        // 计算秒杀的商品金额
        $sql = "select spike_sum from ecs_goods where goods_id = '$goods_id'";
        $spike_data = $this->getORM()->queryRow($sql);

        $shipping_cod_fee = NULL;
        if ($shipping_id > 0) {
            $region['country']  = $consignee['country'];
            $region['province'] = $consignee['province'];
            $region['city']     = $consignee['city'];
            $region['district'] = $consignee['district'];

            $shipping_info = $this->shipping_area_info($shipping_id, $region);
            if (!empty($shipping_info)) {
                // 查看购物车中是否全为免运费商品，若是则把运费赋为零
                $sql = 'SELECT is_shipping,goods_weight FROM ecs_goods' . " WHERE `goods_id` = ".$goods_id ;
                $shipping_count_data = $this->getORM()->queryRow($sql);

                if($shipping_count_data['is_shipping']){
                    $shipping_count = 0;
                }else{
                    $shipping_count = 1;
                }
                $this->shipping_model = new ShippingModel();
                $total['shipping_fee'] = ($shipping_count == 0) ?0 :$this->shipping_model->shipping_fee($shipping_id, $shipping_count_data['goods_weight'], $spike_data["spike_sum"]
                    , $number,$shipping_info['shipping_area_id']);
            }
        }
        $return['shipping_fee']    = $total['shipping_fee']; //运费
        $return["goods_price"] = floatval($spike_data["spike_sum"]);
        $return["saving"] = 0; // 拼团保价设置为0
        return $return;
    }

    public function pintuan_fee($pintuanGoodsId, $number,$consignee,$shipping_id)
    {
        $return = [];
        // 计算拼团的商品金额
        $sql = "select pt_price as goods_price from ecs_goods where goods_id = '$pintuanGoodsId'";
        $pt_data = $this->getORM()->queryRow($sql);



        $shipping_cod_fee = NULL;
        if ($shipping_id > 0) {
            $region['country']  = $consignee['country'];
            $region['province'] = $consignee['province'];
            $region['city']     = $consignee['city'];
            $region['district'] = $consignee['district'];

            $shipping_info = $this->shipping_area_info($shipping_id, $region);
            if (!empty($shipping_info)) {
                // 查看购物车中是否全为免运费商品，若是则把运费赋为零
                $sql = 'SELECT is_shipping,goods_weight FROM ecs_goods' . " WHERE `goods_id` = ".$pintuanGoodsId ;
                $shipping_count_data = $this->getORM()->queryRow($sql);

                if($shipping_count_data['is_shipping']){
                    $shipping_count = 0;
                }else{
                    $shipping_count = 1;
                }
                $this->shipping_model = new ShippingModel();
                $total['shipping_fee'] = ($shipping_count == 0) ?0 :$this->shipping_model->shipping_fee($shipping_id, $shipping_count_data['goods_weight'], $pt_data["goods_price"]
                    , $number,$shipping_info['shipping_area_id']);
            }
        }
        $return['shipping_fee']    = $total['shipping_fee']; //运费
        $return["goods_price"] = floatval($pt_data["goods_price"]) * intval($number);
        $return["saving"] = 0; // 拼团保价设置为0
        return $return;
    }

    public function getPaymentIsUse()
    {
        $is_use = array();

        $sql = "select code,value from ecs_shop_config where type = 'peizhi'";

        $data = $this->getORM()->queryRows($sql);
        foreach ($data as $key => $row) {
            if ($row['code'] == 'small') {
                $value1 = unserialize($row['value']);
            }
            if ($row['code'] == 'hfive') {
                $value2 = unserialize($row['value']);
            }
            if ($row['code'] == 'app') {
                $value3 = unserialize($row['value']);
            }
            if ($row['code'] == 'zhifu') {
                $value4 = unserialize($row['value']);
            }
            if ($row['code'] == 'pay_ccb') {
                $value5 = unserialize($row['value']);
            }
        }
        //小程序支付
        if ($value1['is_use'] == 'on') {
            $is_use['small_is_use'] = 'on';
        } else {
            $is_use['small_is_use'] = 'off';
        }

        //h5支付
        if ($value2['is_use'] == 'on') {
            $is_use['hfive_is_use'] = 'on';
        } else {
            $is_use['hfive_is_use'] = 'off';
        }

        //app支付
        if ($value3['is_use'] == 'on') {
            $is_use['app_is_use'] = 'on';
        } else {
            $is_use['app_is_use'] = 'off';
        }

        //支付宝支付
        if ($value4['is_use'] == 'on') {
            $is_use['zhifu_is_use'] = 'on';
        } else {
            $is_use['zhifu_is_use'] = 'off';
        }
        //龙支付
        if ($value5['is_use_ccb'] == 'on') {
            $is_use['ccb_is_use'] = 'on';
        } else {
            $is_use['ccb_is_use'] = 'off';
        }
        //余额支付
        $surplus = "SELECT value from ecs_shop_config where code ='use_surplus'";//查询是否开启使用余额
        $use_surplus = $this->getORM()->queryRow($surplus);
        $return = $use_surplus['value'];
        $is_use['use_surplus'] =$return;

        return $is_use;
    }

    /**
     * @param $order_id
     * @param $user_id
     * @return bool|false|\mix|string
     * 商家接收新订单
     */
    public function sms_order_placed($order_id, $user_id)
    {
        $model = new VcodeModel();
        $mobile = "SELECT mobile,consignee FROM ecs_order_info WHERE order_sn = '$order_id'";
        $static = $this->getORM()->queryRow($mobile);

        $sql = "SELECT * FROM ecs_shop_config WHERE code = 'sms_order_placed'";
        $data = $this->getORM()->queryRow($sql);

        $shop_mobile = "SELECT value FROM ecs_shop_config WHERE code = 'sms_shop_mobile'";
        $sms_shop_mobile = $this->getORM()->queryRow($shop_mobile);

        if ($data['value'] == '1' && $sms_shop_mobile['value'] != '') {
            $user_name = $sms_shop_mobile['value'];
            $sql = "select value from  ecs_shop_config  WHERE code = 'sms_set_update'";
            $res = $this->getORM()->queryRow($sql);
            $result = unserialize($res['value']); //数据转换
            //等于1 阿里云
            if ($result['status'] == 1) {
                $vcode =  $model->set_vcode($user_name);
                $result['vcode'] = $vcode;
                $result['mobile'] = $user_name;
                $res =  $model->AliSendSms($result);
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
                $data = \PhalApi\DI()->config->get('app');
                $url = $data['host_url'] . "user.php?act=send_one&user_name=" . $user_name . "&consignee=" . $static['consignee'] . "&mobile=" . $static['mobile'] . "&t=" . $time . "&s=" . $sign . "&g=signup";
                $status =  file_get_contents($url);
                return $status;
            }
        }
    }

    /**
     * @param $order_id
     * @return bool|false|\mix|string
     * 消费者支付订单时发商家
     */
    public function sms_order_payed($order_id)
    {
        $model = new VcodeModel();
        $mobile = "SELECT mobile,consignee FROM ecs_order_info WHERE order_sn = '$order_id'";
        $static = $this->getORM()->queryRow($mobile);

        $sql = "SELECT * FROM ecs_shop_config WHERE code = 'sms_order_payed'";
        $data = $this->getORM()->queryRow($sql);

        $shop_mobile = "SELECT value FROM ecs_shop_config WHERE code = 'sms_shop_mobile'";
        $sms_shop_mobile = $this->getORM()->queryRow($shop_mobile);

        if ($data['value'] == '1' && $sms_shop_mobile['value'] != '') {
            $user_name = $sms_shop_mobile['value'];
            $sql = "select value from  ecs_shop_config  WHERE code = 'sms_set_update'";
            $res = $this->getORM()->queryRow($sql);
            $result = unserialize($res['value']); //数据转换
            //等于1 阿里云
            if ($result['status'] == 1) {
                $vcode = $model->set_vcode($user_name);
                $result['vcode'] = $vcode;
                $result['mobile'] = $user_name;
                $res =  $model->AliSendSms($result);
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
                $data = \PhalApi\DI()->config->get('app');
                $url = $data['host_url'] . "user.php?act=send_two&user_name=" . $user_name . "&consignee=" . $static['consignee'] . "&mobile=" . $static['mobile'] . "&order_id=" . $order_id . "&t=" . $time . "&s=" . $sign . "&g=signup";
                $status =  file_get_contents($url);
                return $status;
            }
        }
    }

    /**
     * @param $order_id
     * @return bool|false|\mix|string
     * 消费者支付订单时发消费者
     */
    public function sms_order_payed_to_customer($order_id)
    {
        $model = new VcodeModel();
        $mobile = "SELECT mobile,consignee,goods_amount,discount,shipping_fee FROM ecs_order_info WHERE order_sn = '$order_id'";
        $static = $this->getORM()->queryRow($mobile);

        $sql = "SELECT * FROM ecs_shop_config WHERE code = 'sms_order_payed_to_customer'";
        $data = $this->getORM()->queryRow($sql);

        $shop_mobile = "SELECT value FROM ecs_shop_config WHERE code = 'sms_shop_mobile'";
        $sms_shop_mobile = $this->getORM()->queryRow($shop_mobile);

        if ($data['value'] == '1' && $sms_shop_mobile['value'] != '') {
            $user_name = $static['mobile'];
            $sql = "select value from  ecs_shop_config  WHERE code = 'sms_set_update'";
            $res = $this->getORM()->queryRow($sql);
            $result = unserialize($res['value']); //数据转换
            //等于1 阿里云
            if ($result['status'] == 1) {
                $vcode =  $model->set_vcode($user_name);
                $result['vcode'] = $vcode;
                $result['mobile'] = $user_name;
                $res =  $model->AliSendSms($result);
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
                $data = \PhalApi\DI()->config->get('app');
                $url = $data['host_url'] . "user.php?act=send_three&user_name=" . $user_name . "&goods_amount=" . $static['goods_amount'] . "&shipping_fee=" . $static['shipping_fee'] . "&discount=" . $static['discount'] . "&order_id=" . $order_id . "&t=" . $time . "&s=" . $sign . "&g=signup";
                $status =  file_get_contents($url);
                return $status;
            }
        }
    }

    //查看该订单状态
    public function getOrderStatus($order_id)
    {

        $sql = "SELECT order_id FROM ecs_order_info WHERE  pay_status= 2  and order_sn= ".$order_id;

        $data = $this->getORM()->queryRow($sql);
        if($data){
            return true;
        }
        return false;
    }
}
