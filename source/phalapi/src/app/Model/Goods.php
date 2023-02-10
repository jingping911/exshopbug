<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;
use App\Model\Category as CategoryModel;
use App\Model\ShopConfig as ShopConfigModel;
use App\Model\User as UserModel;
use App\Model\Order as OrderModel;
use App\Model\CollectGoods as CollectGoodsModel;


class Goods extends NotORM
{
    protected  function getTableName($id)
    {
        return 'goods';
    }

    protected function getTableKey($table)
    {
        return 'goods_id';
    }

    /**
     * @param $goods_id
     * @return array
     * 秒杀商品详情页的信息
     */
    public function spikeGoodsSum($goods_id)
    {
        $sql = "select spike_sum as shop_price,start_time,active,end_time from ecs_goods where goods_id = '$goods_id'";
        $result = $this->getORM()->queryRow($sql);
        //计算倒计时
        if (time() >= $result['start_time'] && time() < $result['end_time']) { //秒杀开始的时间
            $date =  $result['end_time'] - time();
            $day = floor($date / 3600 / 24);
            $hour = floor(($date / 3600) % 24);
            $minute = floor((($date / 60) % 60));
            $second = floor($date % 60);
            $default  = 1;
        } else { //秒杀未开始的时间
            $date =  $result['start_time'] - time();
            $day = floor($date / 3600 / 24);
            $hour = floor(($date / 3600) % 24);
            $minute = floor((($date / 60) % 60));
            $second = floor($date % 60);
            $default  = 0;
        }
        $array = array('day' => $day, 'hour' => $hour, 'minute' => $minute, 'second' => $second, 'shop_price' => $result['shop_price'], 'active' => $result['active'], 'default' => $default);
        return $array;
    }

    public function getGoodsInfo($goods_id, $user_id)
    {
        $collect_goods_model = new CollectGoodsModel();
        $Shop_config_model = new ShopConfigModel();
        $goods_data =  $this->getORM()->where(array('goods_id'=>$goods_id,'is_on_sale'=>'1'))->fetchOne();
        if ($goods_data['promote_price'] == '0.00'){
            $goods_data['promote_price'] = '0';
        }
//        $goods_data['shop_price'] = $goods_data['spike_sum'];

        if($goods_data['goods_thumb'] != ''){
            $goods_data['goods_thumb'] = goods_img_url($goods_data['goods_thumb']);
        }
        if ($goods_data['goods_img'] != '') {
            $goods_data['goods_img'] = goods_img_url($goods_data['goods_img']);
        }
        if ($goods_data['original_img'] != '') {
            $goods_data['original_img'] = goods_img_url($goods_data['original_img']);
        }
        if ($user_id != '') {
            $cdata = $collect_goods_model->get_collect_goods_id($user_id, $goods_id);
            if (!$cdata) {
                $goods_data['collect'] = false;
            } else {
                $goods_data['collect'] = true;
            }
        } else {
            $goods_data['collect'] = false;
        }

        //$goods_data['shop_price'] = sprintf("%.2f", $this->get_price($goods_data['goods_id'], $user_id));
		$goods_data['shop_price'] = $this->get_price($goods_data['goods_id'], $user_id);
        $goods_data["show_marketprice"] = $Shop_config_model->get_show_marketprice();
        $goods_data["goods_desc"] = replacePicUrl($goods_data["goods_desc"], getUrl()); // 匹配替换商品的描述的图片的地址
        return $goods_data;
    }



    function GoodsDiscountApi($user_id, $goods_id)
    {
        $this->user_model = new UserModel();
        $this->order_model = new OrderModel();
        $this->goods_model = new CollectGoodsModel();
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

        /* 查询购物车商品 */

        $sql = "SELECT goods_id,cat_id  FROM  ecs_goods  WHERE goods_id ='" . $goods_id . "'";
        $goods_list = $this->getORM()->queryRow($sql);
        if (count($goods_list) == 0) {
            return 0;
        }

        $info = "";
        $favourable_name = array();

        /* 循环计算每个优惠活动的折扣 */
        foreach ($favourable_list as $favourable) {
            $total_amount = 0;
            if ($favourable['act_range'] == 0) {
                $discount =  $favourable;
            } elseif ($favourable['act_range'] == 1) {
                /* 找出分类id的子分类id */
                $id_list = array();
                $raw_id_list = explode(',', $favourable['act_range_ext']);
                foreach ($raw_id_list as $id) {
                    $id_list = array_merge($id_list, array_keys($this->order_model->cat_list($id, 0, false)));
                }
                $ids = join(',', array_unique($id_list));
                if (strpos(',' . $ids . ',', ',' . $goods_list['cat_id'] . ',') !== false) {
                    $discount =  $favourable;
                }
            } elseif ($favourable['act_range'] == 2) {

                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods_list['brand_id'] . ',') !== false) {
                    $discount =  $favourable;
                }
            } elseif ($favourable['act_range'] == 3) {

                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods_list['goods_id'] . ',') !== false) {
                    $discount =  $favourable;
                }
            } else {
                continue;
            }

            if ($discount['act_type'] == 2) {
                $zhe  = ($favourable['act_type_ext'] / 100) * 10;
                $info = "该商品已参加" . $discount['act_name'] . "活动，享受" . $zhe . "折优惠,尽快下单哦";
            } elseif ($discount['act_type'] == 1) {
                $info = "该商品已参加" . $discount['act_name'] . "活动，满" . $discount['min_amount'] . "减" . $discount['act_type_ext'] . "优惠,尽快下单哦";
            }
        }

        return array('discount' => $info);
    }

    public function getGoodsProductId($goods_id)
    {
        $sql = "SELECT product_id as productId FROM ecs_products WHERE goods_id={$goods_id}";
        $product_id = $this->getORM()->queryRows($sql);
        return $product_id[0]['productId'];
    }


    public function getGoodsList($cat_id)
    {
        $this->cate_model = new CategoryModel();
        $pcate_data =  $this->cate_model->getORM()->where(array('cat_id' => $cat_id))->fetchOne();

        $cate_data =  $this->cate_model->getORM()->where(array('parent_id' => $pcate_data['parent_id']))->fetchAll();

        $return = array();
        foreach ($cate_data as $key => $item) {
            $return['cat_list'][$item['cat_id']] = array(
                'cat_name' => $item['cat_name'],
                'cat_id' => $item['cat_id'],
            );
            if ($item['cate_img'] != '') {
                $return['cat_list'][$item['cat_id']]['banner'] = goods_img_url($item['cate_img']);
            } else {
                $return['cat_list'][$item['cat_id']]['banner'] = '';
            }
        }

        $data =  $this->getORM()->where(array('cat_id' => $cat_id, 'is_on_sale' => '1'))->fetchAll();

        foreach ($data as $i => $j) {
            $return['goods_list'][$j['goods_id']] = array(
                'goods_name' => $j['goods_name'],
                'littleTitle' => $j['goods_name'],
                'id' => $j['goods_id'],
                'shop_price' => $j['shop_price'],
                'promote_price' => $j['promote_price'],
                'market_price' => $j['market_price']
            );

            if($j['goods_thumb'] != ''){
                $return['goods_list'][$j['goods_id']]['pic'] = goods_img_url($j['goods_thumb']);
            } else {
                $return['goods_list'][$j['goods_id']]['pic'] = '';
            }
        }

        return $return;
    }

    public function get_products_info($goods_id, $spec_goods_attr_id)
    {
        $return_array = array();

        if (empty($spec_goods_attr_id) || !is_array($spec_goods_attr_id) || empty($goods_id)) {
            return $return_array;
        }

        $goods_attr_array = $this->sort_goods_attr_id_array($spec_goods_attr_id);



        if (isset($goods_attr_array['sort'])) {
            $goods_attr = implode('|', $goods_attr_array['sort']);

            $sql = "SELECT * FROM  ecs_products WHERE goods_id = '$goods_id' AND goods_attr = '$goods_attr' LIMIT 0, 1";
            $return_array = $this->getORM()->queryAll($sql, $params);
            $return_array = current($return_array);
        }
        return $return_array;
    }


    public function sort_goods_attr_id_array($goods_attr_id_array, $sort = 'asc')
    {
        if (empty($goods_attr_id_array)) {
            return $goods_attr_id_array;
        }

        //重新排序
        $sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id
                FROM ecs_attribute AS a
                LEFT JOIN ecs_goods_attr AS v
                    ON v.attr_id = a.attr_id
                    AND a.attr_type = 1
                WHERE v.goods_attr_id " . $this->getORM()->db_create_in($goods_attr_id_array) . "
                ORDER BY a.attr_id $sort";

        $row = $this->getORM()->queryAll($sql, $params);

        $return_arr = array();
        foreach ($row as $value) {
            $return_arr['sort'][]   = $value['goods_attr_id'];

            $return_arr['row'][$value['goods_attr_id']]    = $value;
        }

        return $return_arr;
    }







    public function get_goods_properties($goods_id)
    {
        /* 对属性进行重新排序和分组 */
        $sql = "SELECT attr_group " .
            "FROM  ecs_goods_type AS gt, ecs_goods AS g " .
            "WHERE g.goods_id=? AND gt.cat_id=g.goods_type";
        $params = array($goods_id);

        $grp = $this->getORM()->queryRows($sql, $params);


        if (!empty($grp) && $grp[0]['attr_group'] != '') {
            $groups = explode("\n", strtr($grp, "\r", ''));
        }


        /* 获得商品的规格 */
        $sql = "SELECT a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, ".
                    "g.goods_attr_id, g.attr_value, g.attr_price " .
                'FROM ecs_goods_attr AS g ' .
                'LEFT JOIN ecs_attribute AS a ON a.attr_id = g.attr_id ' .
                "WHERE g.goods_id =? " .
                'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';

        $params = array($goods_id);


        $res = $this->getORM()->queryAll($sql, $params);

        $arr['pro'] = array();     // 属性
        $arr['spe'] = array();     // 规格
        $arr['lnk'] = array();     // 关联的属性
        $arr['gg']  = array();     // 规格 cx
        $arr['gg1'] = array();
        $arr['productLookList'] = array(); // 都在看

        foreach ($res as $k => $v) {
            if ($res[$k]['attr_type'] == 0) {
            } else {
                if ($res[$k]['attr_type'] != 2) {


                    $arr['gg'][$res[$k]['attr_id']]['skuName']   = $res[$k]['attr_name'];
                    $arr['gg'][$res[$k]['attr_id']]['skuNameId'] = $res[$k]['attr_id'];
                    $arr['gg'][$res[$k]['attr_id']]['activeSkuVal'] = $res[$k]['attr_group'];
                    $arr['gg'][$res[$k]['attr_id']]['skuValList'][] = array(
                        'skuVal'    =>  $res[$k]['attr_value'],
                        'skuValId'  =>  $res[$k]['goods_attr_id']
                    );
                }
            }
        }

        foreach($arr['gg'] as $k => $v){
            $arr['gg1'][] = $arr['gg'][$k];
        }

        foreach ($res as $row) {
            $row['attr_value'] = str_replace("\n", '<br />', $row['attr_value']);

            if ($row['attr_type'] == 0) {

                $arr['pro'][$row['attr_id']]['name']  = $row['attr_name'];
                $arr['pro'][$row['attr_id']]['value'] = $row['attr_value'];
            } else {
                $arr['spe'][$row['attr_id']]['attr_type'] = $row['attr_type'];
                $arr['spe'][$row['attr_id']]['name']     = $row['attr_name'];
                $arr['spe'][$row['attr_id']]['values'][] = array(
                    'label'        => $row['attr_value'],
                    'price'        => $row['attr_price'],
                    'format_price' => abs($row['attr_price']),                                                            'id'           => $row['goods_attr_id']
                );
            }
            if ($row['is_linked'] == 1) {
                /* 如果该属性需要关联，先保存下来 */
                $arr['lnk'][$row['attr_id']]['name']  = $row['attr_name'];
                $arr['lnk'][$row['attr_id']]['value'] = $row['attr_value'];
            }
        }
        /* 都在看 */
        $sql = "SELECT cat_id FROM ecs_goods WHERE goods_id={$goods_id}";
        $cat_id = $this->getORM()->queryRows($sql);
        $cat_id = $cat_id[0]['cat_id'];
        $sql = "SELECT goods_id AS id,goods_name AS name,goods_thumb AS list_pic_url,shop_price AS retail_price, active, spike_sum, is_pintuan, pt_price FROM ecs_goods WHERE cat_id={$cat_id} AND is_delete = 0 ORDER BY sales_volume_count DESC LIMIT 6";
        $look_list = $this->getORM()->queryRows($sql);

        foreach ($look_list as $k => $v) {
            $look_list[$k]['list_pic_url'] = goods_img_url($look_list[$k]['list_pic_url']);
        }
        $arr['productLookList'] = $look_list;

        /* 库存 */
        $sql = "SELECT product_number FROM ecs_products WHERE goods_id={$goods_id}";
        $product_number = $this->getORM()->queryRows($sql);
        $product_number = $product_number[0]['product_number'];
        $arr['product_number'] = $product_number;





        return $arr;
    }

    function spec_price($spec)
    {
        if (!empty($spec)) {
            if (is_array($spec)) {
                foreach ($spec as $key => $val) {
                    $spec[$key] = addslashes($val);
                }
            } else {
                $spec = addslashes($spec);
            }

            $where = $this->getORM()->db_create_in($spec, 'goods_attr_id');
            //            if(is_array($spec))
            //            {
            //                foreach ($spec as $val)
            //                {
            //                    $res = $val;
            //                }
            //            }
            //            $spec = explode('|',$res);

            $sql = "SELECT SUM(attr_price) AS attr_price FROM ecs_goods_attr WHERE " . $where;
            $price = $this->getORM()->queryRows($sql);
            $price = current($price);
            $price = $price['attr_price'];
        } else {
            $price = 0;
        }

        return $price;
    }

    function get_final_price($goods_id, $goods_num = '1',$user_id, $is_spec_price = false, $spec = array())
    {
        $final_price   = '0'; //商品最终购买价格
        $volume_price  = '0'; //商品优惠价格
        $promote_price = '0'; //商品促销价格
        $user_price    = '0'; //商品会员价格

        //取得商品优惠价格列表
        $price_list   = $this->get_volume_price_list($goods_id, '1');

        if (!empty($price_list)) {
            foreach ($price_list as $value) {
                if ($goods_num >= $value['number']) {
                    $volume_price = $value['price'];
                }
            }
        }

        $this->user_model = new UserModel();
        $user = $this->user_model->get_rank_discount($user_id);

        //取得商品促销价格列表
        /* 取得商品信息 */
        $sql = "SELECT g.promote_price, g.promote_start_date, g.promote_end_date, " .
            "IFNULL(mp.user_price, g.shop_price * '" . $user['discount'] . "') AS shop_price " .
            " FROM ecs_goods AS g " .
            " LEFT JOIN ecs_member_price AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '" . $user['user_rank'] . "' " .
            " WHERE g.goods_id = '" . $goods_id . "'" .
            " AND g.is_delete = 0";
        $goods = $this->getORM()->queryRows($sql, $params);
        $goods = current($goods);

        /* 计算商品的促销价格 */
        if ($goods['promote_price'] > 0) {
            $promote_price = $this->bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
        } else {
            $promote_price = 0;
        }
        //取得商品会员价格列表  取得当前额会员价格
        $user_price    = $this->get_price($goods_id, $user_id);


        //比较商品的促销价格，会员价格，优惠价格
        if (empty($volume_price) && empty($promote_price)) {
            //如果优惠价格，促销价格都为空则取会员价格
            $final_price = $user_price;
        } elseif (!empty($volume_price) && empty($promote_price)) {
            //如果优惠价格为空时不参加这个比较。
            $final_price = min($volume_price, $user_price);
        } elseif (empty($volume_price) && !empty($promote_price)) {
            //如果促销价格为空时不参加这个比较。
            $final_price = min($promote_price, $user_price);
        } elseif (!empty($volume_price) && !empty($promote_price)) {
            //取促销价格，会员价格，优惠价格最小值
            $final_price = min($volume_price, $promote_price, $user_price);
        } else {
            $final_price = $user_price;
        }

        //如果需要加入规格价格
        if ($is_spec_price) {
            if (!empty($spec)) {
                $spec_price   = $this->spec_price($spec);
                $final_price += $spec_price;
            }
        }

        //返回商品最终购买价格
        return $final_price;
    }

    public function get_volume_price_list($goods_id, $price_type = '1')
    {
        $volume_price = array();
        $temp_index   = '0';

        $sql = "SELECT `volume_number` , `volume_price`" .
            " FROM ecs_volume_price " .
            " WHERE `goods_id` = '" . $goods_id . "' AND `price_type` = '" . $price_type . "'" .
            " ORDER BY `volume_number`";

        $res = $this->getORM()->queryAll($sql, $params);
        foreach ($res as $k => $v) {
            $volume_price[$temp_index]                 = array();
            $volume_price[$temp_index]['number']       = $v['volume_number'];
            $volume_price[$temp_index]['price']        = $v['volume_price'];
            $volume_price[$temp_index]['format_price'] = $v['volume_price'];
            $temp_index++;
        }
        return $volume_price;
    }

    function is_spec($goods_attr_id_array, $sort = 'asc')
    {
        if (empty($goods_attr_id_array)) {
            return $goods_attr_id_array;
        }

        //重新排序
        $sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id
                FROM ecs_attribute AS a
                LEFT JOIN ecs_goods_attr AS v
                    ON v.attr_id = a.attr_id
                    AND a.attr_type = 1
                WHERE v.goods_attr_id " . $this->getORM()->db_create_in($goods_attr_id_array) . "
                ORDER BY a.attr_id ";


        $row = $this->getORM()->queryAll($sql, $params);


        $return_arr = array();
        foreach ($row as $value) {
            $return_arr['sort'][]   = $value['goods_attr_id'];

            $return_arr['row'][$value['goods_attr_id']]    = $value;
        }

        if (!empty($return_arr)) {
            return true;
        } else {
            return false;
        }
    }
    function bargain_price($price, $start, $end)
    {
        if ($price == 0) {
            return 0;
        } else {
            $time = time();
            if ($time >= $start && $time <= $end) {
                return $price;
            } else {
                return 0;
            }
        }
    }

    function get_goods_attr_info($arr, $type = 'pice')
    {
        $attr   = '';

        if (!empty($arr)) {
            $fmt = "%s:%s[%s] \n";

            $sql = "SELECT a.attr_name, ga.attr_value, ga.attr_price " .
                "FROM ecs_goods_attr AS ga, " .
                "ecs_attribute AS a " .
                "WHERE " . $this->getORM()->db_create_in($arr, 'ga.goods_attr_id') . " AND a.attr_id = ga.attr_id";
            $res = $this->getORM()->queryAll($sql, $params);

            foreach ($res as $key => $item) {
                $attr_price = round(floatval($item['attr_price']), 2);
                $attr .= sprintf($fmt, $item['attr_name'], $item['attr_value'], $attr_price);
            }

            $attr = str_replace('[0]', '', $attr);
        }

        return $attr;
    }

    function get_package_goods($package_id)
    {
        $sql = "SELECT pg.goods_id, g.goods_name, pg.goods_number, p.goods_attr, p.product_number, p.product_id
                FROM ecs_package_goods AS pg
                    LEFT JOIN ecs_goods AS g ON pg.goods_id = g.goods_id
                    LEFT JOIN ecs_products AS p ON pg.product_id = p.product_id
                WHERE pg.package_id = '$package_id'";
        if ($package_id == 0) {
            $sql .= " AND pg.admin_id = '$_SESSION[admin_id]'";
        }

        $attr_list = $this->getORM()->queryRows($sql);

        if (count($resource) == 0) {
            return array();
        }

        $row = array();
        /* 生成结果数组 取存在货品的商品id 组合商品id与货品id */
        $good_product_str = '';
        foreach ($attr_list as $key => $_row) {
            if ($_row['product_id'] > 0) {
                /* 取存商品id */
                $good_product_str .= ',' . $_row['goods_id'];

                /* 组合商品id与货品id */
                $_row['g_p'] = $_row['goods_id'] . '_' . $_row['product_id'];
            } else {
                /* 组合商品id与货品id */
                $_row['g_p'] = $_row['goods_id'];
            }

            //生成结果数组
            $row[] = $_row;
        }

        $good_product_str = trim($good_product_str, ',');

        /* 释放空间 */
        unset($resource, $_row, $sql);

        /* 取商品属性 */
        if ($good_product_str != '') {
            $sql = "SELECT goods_attr_id, attr_value FROM " . $GLOBALS['ecs']->table('goods_attr') . " WHERE goods_id IN ($good_product_str)";
            $result_goods_attr = $this->getORM()->queryRows($sql);

            $_goods_attr = array();
            foreach ($result_goods_attr as $key => $value) {
                $_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
            }
        }

        /* 过滤货品 */
        $format[0] = '%s[%s]--[%d]';
        $format[1] = '%s--[%d]';
        foreach ($row as $key => $value) {
            if ($value['goods_attr'] != '') {
                $goods_attr_array = explode('|', $value['goods_attr']);

                $goods_attr = array();
                foreach ($goods_attr_array as $_attr) {
                    $goods_attr[] = $_goods_attr[$_attr];
                }

                $row[$key]['goods_name'] = sprintf($format[0], $value['goods_name'], implode('，', $goods_attr), $value['goods_number']);
            } else {
                $row[$key]['goods_name'] = sprintf($format[1], $value['goods_name'], $value['goods_number']);
            }
        }

        return $row;
    }

    public function checkGoodsAttr($goods_id)
    {
        // 获取商品的具体配置信息
        $sql = "SELECT goods_attr,product_id FROM ecs_products WHERE goods_id = {$goods_id}";
        $goods_attr = $this->getORM()->queryRows($sql);
        return $goods_attr;
        $goods_attr_array = explode("|",$goods_attr[0]['goods_attr']);


    }

    public function getGoodsSku($goods_id, $goods_attr = 'b.goods_attr', $goods_com = '', $product_id = '')
    {
        // 返回配置信息
        /**
         * a => goods
         * b => products
         * c => attribute
         * d => goods_attr
         */
        if ($goods_com == 'y') {
            $sql = "SELECT b.product_sn AS goodsCode,a.goods_id AS goodsId,a.shop_price AS price,b.product_id AS product_id,b.product_number AS stockNum,
                c.attr_name AS skuNames,c.attr_id AS skuNameIds,c.attr_type,d.attr_value AS skuVals,d.goods_attr_id AS skuValIds,d.attr_price AS attr_price
                FROM ecs_goods as a
                LEFT JOIN ecs_products as b ON a.goods_id = b.goods_id  AND b.product_id = {$product_id}
                LEFT JOIN ecs_goods_attr as d ON a.goods_id = d.goods_id AND d.goods_attr_id = {$goods_attr}
                LEFT JOIN ecs_attribute as c ON c.attr_id = d.attr_id
                WHERE a.goods_id = {$goods_id}";



            $data = $this->getORM()->queryRows($sql);
            return $data;
        }
        $sql = "SELECT b.product_sn AS goodsCode,a.goods_id AS goodsId,a.shop_price AS price,b.product_id AS product_id,b.product_number AS stockNum,
                c.attr_name AS skuNames,c.attr_id AS skuNameIds,c.attr_type,d.attr_value AS skuVals,d.goods_attr_id AS skuValIds,d.attr_price AS attr_price
                FROM ecs_goods as a
                LEFT JOIN ecs_products as b ON a.goods_id = b.goods_id
                LEFT JOIN ecs_goods_attr as d ON a.goods_id = d.goods_id AND d.goods_attr_id = {$goods_attr}
                LEFT JOIN ecs_attribute as c ON c.attr_id = d.attr_id
                WHERE a.goods_id = {$goods_id} order by product_id";


                $data = $this->getORM()->queryRows($sql);
        //var_dump($data);die;

        return $data;

    }

    public function searchGoods($words, $order = 'asc', $page, $num, $user_id)
    {
        $page_size = pagesize();
        $offset = ($page - 1) * $page_size;
        if ($words == '') { // 没有关键字的时候

            if ($num == '' && $order == '') {
                $sql = "SELECT goods_id AS id,goods_name AS name,shop_price AS retail_price,
                        goods_thumb AS list_pic_url FROM ecs_goods WHERE is_delete = 0 ORDER BY shop_price 
                        LIMIT {$offset},{$page_size}";
            } else if ($num == '') {
                $sql = "SELECT goods_id AS id,goods_name AS name,shop_price AS retail_price,
                       goods_thumb AS list_pic_url FROM ecs_goods WHERE is_delete = 0 ORDER BY shop_price $order
                        LIMIT {$offset},{$page_size}";
            } else {
                $sql = "select goods_id AS id,goods_name AS name,shop_price AS retail_price,
                       goods_thumb AS list_pic_url FROM ecs_goods WHERE is_delete = 0 order by sales_volume_count $num limit $offset,$page_size";
            }
            $res = $this->getORM()->queryRows($sql);
            foreach ($res as $k => $v) {
                $res[$k]['retail_price'] = $this->get_price($res[$k]['id'], $user_id);
                $res[$k]['list_pic_url'] = goods_img_url($res[$k]['list_pic_url']);
            }
            // 拿到总页数
            $sql = "select count(*) as num from ecs_goods";
            $num = $this->getORM()->queryRow($sql);
            $total = ceil($num['num'] / $page_size);
            $data["total_page"] = $total;
            $data["res"] = $res;
            return $data;
        }
        $sql = "SELECT goods_id AS id,goods_name AS name,shop_price AS retail_price,goods_thumb AS list_pic_url,promote_price AS promote_price FROM ecs_goods WHERE is_on_sale = 1 AND is_delete = 0 and goods_name LIKE '%{$words}%' ORDER BY shop_price {$order} LIMIT {$offset},{$page_size}";
        $res = $this->getORM()->queryRows($sql);



        if (!$res) {
            return 'false';
        }
        foreach ($res as $k => $v) {
            $res[$k]['retail_price'] = $this->get_price($res[$k]['id'], $user_id);
            $res[$k]['list_pic_url'] = goods_img_url($res[$k]['list_pic_url']);
            $res[$k]['promote_price'] = $res[$k]['promote_price'];
        }
        $sql = "select count(*) as num from ecs_goods where goods_name LIKE '%{$words}%'";
        $num = $this->getORM()->queryRow($sql);
        $total = ceil($num['num'] / $page_size);
        $data["total_page"] = $total;
        $data["res"] = $res;
        return $data;
    }

    public function hotSearch()
    {
        // 热搜关键词
        $sql = "SELECT keyword FROM ecs_keywords ORDER BY count DESC LIMIT 3";
        $res = $this->getORM()->queryRows($sql);
        return $res;
    }
    public function getNewGoods($user_id)
    {
        $Shop_config_model = new ShopConfigModel();
        $data = $this->getORM()->where(array('is_on_sale' => 1, 'is_delete' => 0, 'is_new' => 1, 'goods_number > ?' => 0))->order('add_time DESC')->limit(6)->fetchAll();

        foreach ($data as $k => $v) {
            $data[$k]['goods_thumb'] = goods_img_url($data[$k]['goods_thumb']);
            //$data[$k]['shop_price'] = sprintf("%.2f", $this->get_price($v['goods_id'], $user_id));
            if ($data[$k]['promote_price'] == '0.00'){
                $data[$k]['promote_price'] = '0';
                $data[$k]['shop_price'] = $this->get_price($v['goods_id'], $user_id);
            }else {
                $data[$k]['shop_price']  = $data[$k]['promote_price'];
            }
            $data[$k]["show_marketprice"] = $Shop_config_model->get_show_marketprice();
			//$data[$k]['shop_price'] = $this->get_price($v['goods_id'], $user_id);
            //$data[$k]['shop_price']= sprintf("%.2f",$this->get_final_price($v['goods_id'], 1,$user_id, true, array()));
        }
        return $data;
    }

    /**
     * @param $words
     * H5搜索数据，后台搜索引擎调取数据
     */
    public function searchKeyWordsModel($words)
    {
        if ($words != '') {
            $date = date("Y-m-d", time());
            $searchengine = 'ecshop';
            $keyword = $words;
            $count = '1';
            $sql = "SELECT date FROM ecs_keywords WHERE date = '$date'";
            $getList = $this->getORM()->queryRow($sql);
            if (empty($getList)) {
                $sql = "INSERT INTO ecs_keywords(date,searchengine,keyword,count) values('$date','$searchengine','$keyword','$count')";
                $this->getORM()->queryRow($sql);
            } else {
                $sql = "UPDATE ecs_keywords SET count = count+1 WHERE date = '$date'";
                $this->getORM()->queryRow($sql);
            }
        }
    }

    //限时秒杀
    public function getSpikeGoods()
    {

        $data = $this->getORM()->where(array('active' => 'true', 'end_time > ?' => time()))->order('end_time DESC')->limit(6)->fetchAll();
        foreach ($data as $k => $v) {
            $data[$k]['goods_thumb'] = goods_img_url($data[$k]['goods_thumb']);
        }
        if (empty($data)) {
            return '';
        }
        foreach ($data as $k => $v) {
            $date =  $v['end_time'] - time();
            $day = floor($date / 3600 / 24);
            $hour = floor(($date / 3600) % 24);
            $minute = floor((($date / 60) % 60));
            $second = floor($date % 60);

            $data[$k]["day"] = $day;
            $data[$k]["hour"] = $hour;
            $data[$k]["min"] = $minute;
            $data[$k]["sec"] = $second;
        }
        return $data;
    }

    public function timediff($begin_time, $end_time)
    {
        if ($begin_time < $end_time) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        //计算天数
        $timediff = $endtime - $starttime;
        $days = intval($timediff / 86400);
        //计算小时数
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        //计算分钟数
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        //计算秒数
        $secs = $remain % 60;
        $res = array("day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs);
        return $res;
    }

    //拼团购买
    public function getPinTuanGoods($user_id)
    {
        $data = $this->getORM()->where(array('is_pintuan' => 1))->order('add_time DESC')->limit(6)->fetchAll();
        foreach ($data as $k => $v) {
            $data[$k]['goods_thumb'] = goods_img_url($data[$k]['goods_thumb']);
            $data[$k]['shop_price'] = sprintf("%.2f", $this->get_final_price($v['goods_id'], 1, $user_id, true, array()));
        }

        return $data;
    }

    public function getNewGoodsList($order, $page, $isHot, $spike, $pintuan, $isNew, $type, $user_id)
    {
        // 新品首发
        $page_size = pagesize();
        $offset = ($page - 1) * $page_size;
        if ($type == 'price') {
            $sort = 'retail_price';
        } else if ($type == 'sales') {
            $sort = 'virtual_sales';
        } else {
            $sort = 'sort_order';
        }
        if ($order != '' && $spike == "" && $pintuan == "" && $isHot == '') { //如果为空默认显示首页，不为空显示价格
            $sql = "SELECT *  FROM ( SELECT goods_name AS name, goods_id AS id, shop_price AS retail_price, goods_thumb AS list_pic_url, active, spike_sum, is_pintuan, pt_price
                    FROM ecs_goods where is_on_sale = 1 and is_delete = 0 and goods_number>0 ORDER BY add_time DESC LIMIT {$offset},{$page_size} ) AS a  ORDER BY {$sort} {$order} ";
        } elseif ($order != "" && $isHot == "" && $spike != "" && $pintuan == "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,active,start_time,end_time,spike_count,spike_sum as retail_price from ecs_goods where 
                   is_on_sale = 1 and is_delete = 0 and goods_number>0 and active = 'true' and end_time >= " . time() . " order by spike_sum {$order} limit " . $offset . "," . $page_size;
        } elseif ($order != "" && $isHot == "" && $spike == "" && $pintuan != "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,pt_price as retail_price, active, spike_sum, is_pintuan, pt_price from ecs_goods where
                    is_on_sale = 1 and is_delete = 0 and goods_number>0 and is_pintuan = '1' order by pt_price {$order} limit " . $offset . "," . $page_size;
        } elseif ($order == "" && $isHot != "" && $spike != "" && $pintuan == "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,active,start_time,end_time,spike_count,spike_sum as retail_price,sales_volume_count as num from ecs_goods where 
                   is_on_sale = 1  and is_delete = 0 and goods_number>0 and active = 'true' and end_time >= " . time() . " order by num {$isHot} limit " . $offset . "," . $page_size;
        } elseif ($order == "" && $isHot != "" && $spike == "" && $pintuan != "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,pt_price as retail_price,sales_volume_count as num, active, spike_sum, is_pintuan, pt_price from ecs_goods where 
                   is_on_sale = 1 and is_delete = 0 and goods_number>0 and is_pintuan = '1' order by num {$isHot} limit " . $offset . "," . $page_size;
        } else {
            $sql = "SELECT goods_name AS name, goods_id AS id, shop_price AS retail_price, goods_thumb AS list_pic_url, active, spike_sum, is_pintuan, pt_price FROM ecs_goods where
                    is_on_sale = 1 and  is_hot=1 and is_delete = 0 and goods_number>0 order by {$sort} {$order} LIMIT {$offset},{$page_size}";
        }

        if ($order != "" && $isHot != "" && $spike == "" && $pintuan != "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,pt_price as retail_price,sales_volume_count as num, active, spike_sum, is_pintuan, pt_price from ecs_goods where 
                   is_on_sale = 1 and is_delete = 0 and goods_number>0 and is_pintuan = '1' order by {$sort} {$order} limit " . $offset . "," . $page_size;
        }

        if ($order != "" && $isHot != "" && $spike != "" && $pintuan == "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,active,start_time,end_time,spike_count,spike_sum as retail_price,sales_volume_count as num from ecs_goods where 
                   is_on_sale = 1  and is_delete = 0 and goods_number>0 and active = 'true' and end_time >= " . time() . " order by {$sort} {$order} limit " . $offset . "," . $page_size;
        }

        if ($isHot != '1' && $order == "" && $spike == '' && $pintuan == '') {
            $sql = "SELECT *  FROM ( SELECT goods_name AS name, goods_id AS id, shop_price AS retail_price, goods_thumb AS list_pic_url,sales_volume_count AS num, active, spike_sum, is_pintuan, pt_price
                    FROM ecs_goods where is_on_sale = 1 and is_delete = 0 and goods_number>0 ORDER BY add_time DESC LIMIT {$offset},{$page_size} ) AS a ORDER BY num {$isHot} ";
        }
        if ($isHot != '1' && $order != "" && $spike == '' && $pintuan == '') {
            $sql = "SELECT *  FROM ( SELECT goods_name AS name, goods_id AS id, shop_price AS retail_price, goods_thumb AS list_pic_url,sales_volume_count AS num, active, spike_sum, is_pintuan, pt_price 
                    FROM ecs_goods where is_on_sale = 1 and is_delete = 0 and goods_number>0 order by {$sort} {$order} LIMIT {$offset},{$page_size} ) AS a ORDER BY num {$isHot} ";
        }

        if ($order == "" && $isHot == "" && $spike != "" && $pintuan == "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,active,start_time,end_time,spike_count,spike_sum as retail_price from ecs_goods where 
                   is_on_sale = 1 and is_delete = 0 and goods_number>0 and active = 'true' and end_time >= " . time() . " order by spike_sum {$order} limit " . $offset . "," . $page_size;
        }


        if ($order != "" && $isHot == "" && $spike != "" && $pintuan == "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,active,start_time,end_time,spike_count,spike_sum as retail_price from ecs_goods where 
                   is_on_sale = 1 and is_delete = 0 and goods_number>0 and active = 'true' and end_time >= " . time() . " order by {$sort} {$order}  limit " . $offset . "," . $page_size;
        }

        if ($order == "" && $isHot == "" && $spike == "" && $pintuan != "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,pt_price as retail_price,spike_count, active, spike_sum, is_pintuan, pt_price from ecs_goods where
                    is_on_sale = 1 and is_delete = 0 and goods_number>0 and is_pintuan = '1' limit " . $offset . "," . $page_size;
        }
        if ($order != "" && $isHot == "" && $spike == "" && $pintuan != "") {
            $sql = "select goods_id as id,goods_name as name,goods_thumb as list_pic_url,pt_price as retail_price,spike_count, active, spike_sum, is_pintuan, pt_price from ecs_goods where
                    is_on_sale = 1 and is_delete = 0 and goods_number>0 and is_pintuan = '1' ORDER by {$sort} {$order} limit " . $offset . "," . $page_size;
        }

        if ($isHot == '' && $order == "" && $spike == '' && $pintuan == '' && $isNew == '1') {
            $sql = "SELECT *  FROM ( SELECT goods_name AS name, goods_id AS id, shop_price AS retail_price, goods_thumb AS list_pic_url,sales_volume_count AS num, active, spike_sum, is_pintuan, pt_price 
                    FROM ecs_goods where is_on_sale = 1 and is_new = 1 and is_delete = 0 and goods_number>0 ORDER BY {$sort} {$order} LIMIT {$offset},{$page_size} ) AS a ";
        }
        if ($isHot == '' && $order != "" && $spike == '' && $pintuan == '' && $isNew == '1') {
            $sql = "SELECT *  FROM ( SELECT goods_name AS name, goods_id AS id, shop_price AS retail_price, goods_thumb AS list_pic_url,sales_volume_count AS num, active, spike_sum, is_pintuan, pt_price 
                    FROM ecs_goods where is_on_sale = 1 and is_new = 1 and is_delete = 0 and goods_number>0 ORDER BY {$sort} {$order} LIMIT {$offset},{$page_size} ) AS a ";
        }
        $data = $this->getORM()->queryRows($sql);
        foreach ($data as $k => $v) {
            $data[$k]['list_pic_url'] = empty($data[$k]['list_pic_url']) ? default_category_goodsImage() : goods_img_url($data[$k]['list_pic_url']);
            //内蒙古修改
            //            $data[$k]['list_pic_url'] =  goods_img_url($data[$k]['list_pic_url']);
            //获取会员的商品价格
            $data[$k]['retail_price'] = $this->get_price($data[$k]['id'], $user_id);
        }
        return $data;
    }


    public  function integralgoods($page)
    {
        $page_size = pagesize();
        $offset = ($page - 1) * $page_size;
        /* 获取数据 */
        $sql = 'SELECT eg.* , g.goods_name,g.goods_thumb as list_pic_url ' .
            'FROM ecs_exchange_goods AS eg ' .
            'LEFT JOIN ecs_goods AS g ON g.goods_id = eg.goods_id ' .
            'WHERE is_exchange = 1  LIMIT ' . $offset . ',' . $page_size;
        $data =  $this->getORM()->queryRows($sql);
        foreach ($data as $k => $v) {
            $data[$k]['list_pic_url'] = goods_img_url($data[$k]['list_pic_url']);
        }
        $sql = 'SELECT COUNT(*)  as record_count FROM ecs_exchange_goods AS eg ' .
            'LEFT JOIN ecs_goods AS g ON g.goods_id = eg.goods_id ' .
            'WHERE is_exchange = 1 ';
        $pagetotal =  $this->getORM()->queryRow($sql);

        return array('pagetotal' => ceil($pagetotal['record_count'] / $page_size), 'info' => $data);
    }
    public function getGoodsintegral($goods_id)
    {
        $sql = "select * from ecs_exchange_goods where is_exchange = 1 and goods_id ='" . $goods_id . "'";
        $data = $this->getORM()->queryRow($sql);
        return $data;
    }

    public function  getPayPointsApi($user_id, $goods_id)
    {
        $sql = "select * from  ecs_exchange_goods where goods_id ='" . $goods_id . "'";
        $data = $this->getORM()->queryRow($sql);
        $sql  = "select * from  ecs_users where user_id='" . $user_id . "'";
        $user_data = $this->getORM()->queryRow($sql);
        if ($data['exchange_integral'] > $user_data['pay_points']) {
            return false;
        }
        return true;
    }

    public function GetSuperPackageApi($page)
    {
        $where = " and  start_time < '" . time() . "' and end_time > '" . time() . "'";
        $page_size = pagesize();
        $offset = ($page - 1) * $page_size;
        /* 获活动数据 */
        $sql = "SELECT act_id, act_name AS package_name, start_time, end_time,package_image, is_finished, ext_info " .
            " FROM ecs_goods_activity WHERE act_type = 4" . $where . " order by act_id desc LIMIT " . $offset . "," . $page_size;
        $data  = $this->getORM()->queryRows($sql);
        foreach ($data as $k => $v) {
            $data[$k]['package_image'] = goods_img_url($data[$k]['package_image']);
            $data[$k]['ext_info'] = unserialize($data[$k]['ext_info']);
        }
        $sql = "SELECT COUNT(*) as count  FROM ecs_goods_activity  WHERE act_type = 4 " . $where;
        $pagetotal = $this->getORM()->queryRow($sql);

        return array('pagetotal' => ceil($pagetotal['count'] / $page_size), 'info' => $data);
    }

    public  function indexintegralgoods()
    {
        /* 获取数据 */
        $sql = 'SELECT eg.* , g.goods_name,g.goods_thumb as list_pic_url ' .
            'FROM ecs_exchange_goods AS eg ' .
            'LEFT JOIN ecs_goods AS g ON g.goods_id = eg.goods_id ' .
            'WHERE is_exchange = 1  LIMIT 6';
        $data =  $this->getORM()->queryRows($sql);
        foreach ($data as $k => $v) {
            $data[$k]['list_pic_url'] = goods_img_url($data[$k]['list_pic_url']);
        }

        return $data;
    }
    public function getHotGoods($user_id)
    {
        $Shop_config_model = new ShopConfigModel();
        $data = $this->getORM()->where(array('is_on_sale' => 1, 'is_delete' => 0, 'is_hot' => 1, 'goods_number > ?' => 0))->order('click_count DESC')->limit(6)->fetchAll();
        foreach ($data as $k => $v) {
            $data[$k]['goods_thumb'] = goods_img_url($data[$k]['goods_thumb']);
            //            $data[$k]['shop_price']= sprintf("%.2f",$this->get_final_price($v['goods_id'], 1,$user_id, true, array()));
            //$data[$k]['shop_price'] = sprintf("%.2f", $this->get_price($v['goods_id'], $user_id));
			$data[$k]['shop_price'] = $this->get_price($v['goods_id'], $user_id);
            $data[$k]["show_marketprice"] = $Shop_config_model->get_show_marketprice();
        }
        return $data;
    }


    public function getappdownload()
    {
        $sql = "select * from ecs_app_config where k = 'appdownload'";
        $data = $this->getORM()->queryRow($sql);
        return $data['val'];
    }

    public function indexSuperPackageApi()
    {
        $where = " and  start_time < '" . time() . "' and end_time > '" . time() . "'";
        /* 获活动数据 */
        $sql = "SELECT act_id, act_desc, act_name AS package_name, start_time, end_time,package_image, is_finished, ext_info " .
            " FROM ecs_goods_activity WHERE act_type = 4" . $where . " order by act_id desc LIMIT 6";
        $data  = $this->getORM()->queryRows($sql);
        foreach ($data as $k => $v) {
            $data[$k]['package_image'] = goods_img_url($data[$k]['package_image']);
            $data[$k]['ext_info'] = unserialize($data[$k]['ext_info']);
        }
        return $data;
    }

    public function kefu_tel()
    {
        $sql = "select val from ecs_app_config where k = 'kefu_tel'";
        $result = $this->getORM()->queryRow($sql);
        return $result;
    }

    public function getGoodsCollect($goods_id, $user_id)
    {
        // 获取用户对当前商品的收藏状态
        $sql = "SELECT * FROM ecs_collect_goods WHERE goods_id = {$goods_id} AND user_id = {$user_id}";
        $res = $this->getORM()->queryRows($sql);
        if ($res) {
            return true;
        }
        return false;
    }

    public function setGoodsAddCollect($goods_id, $user_id)
    {
        // 设置商品是否收藏  collect_goods
        $sql = "SELECT rec_id FROM ecs_collect_goods WHERE goods_id = {$goods_id} AND user_id = {$user_id}";
        $res = $this->getORM()->queryRows($sql);
        if ($res[0]['rec_id'] != '') {
            // 已收藏就删除
            $sql = "DELETE FROM ecs_collect_goods WHERE rec_id = " . $res[0]['rec_id'] . "";
            $this->getORM()->queryRows($sql);
            return false;
        }
        $sql = "INSERT INTO ecs_collect_goods SET user_id = {$user_id},goods_id={$goods_id},add_time=" . time() . ",is_attention=0";
        $res = $this->getORM()->queryRows($sql);

        return true;
    }

    //随机购
    public function getGoodsCasualInfo()
    {
        //随机取商品id
        $sql = "SELECT goods_id,goods_name,goods_thumb,shop_price,goods_number FROM ecs_goods WHERE goods_id >= ((SELECT MAX(goods_id) FROM ecs_goods)-(SELECT MIN(goods_id) FROM ecs_goods)) * RAND() + (SELECT MIN(goods_id) FROM ecs_goods)  LIMIT 20";
        $res = $this->getORM()->queryRows($sql);

        $goods_data = array();
        foreach ($res as $k => $v) {
            $goods_data[$k]['url'] = goods_img_url($v['goods_thumb']);
            $goods_data[$k]['goods_id'] = $v['goods_id'];
            $goods_data[$k]['goods_name'] = $v['goods_name'];
            $goods_data[$k]['shop_price'] = $v['shop_price'];
            $goods_data[$k]['goods_number'] = $v['goods_number'];
            $goods_data[$k]['heat_number'] = rand(10, 10000);
        }
        return $goods_data;
    }

    public function goods_number($user_id)
    {
        $sql = "select count(*) from ecs_cart where user_id = '$user_id'";
        $result = $this->getORM()->queryRow($sql);

        return $result;
    }

    public function getActiveStatus($goods_id)
    {
        // 获取拼团&秒杀状态及数据
        // 拼团
        $sql = "select is_pintuan,pt_price from ecs_goods where goods_id = '$goods_id'";
        $pintuan = $this->getORM()->queryRow($sql);
        // 秒杀
        return array(
            "pintuan" => $pintuan,
        );
    }

    public function getGoodSpikeShop($goods_id)
    {
        $mysql = "select spike_count from ecs_goods where goods_id = '$goods_id'";
        $default = $this->getORM()->queryRow($mysql);
        if ($default['spike_count'] <= 0) {
            return array("status" => "fail", "msg" => "库存不足");
        }
    }

    public function getGoodsPintuan($goods_id, $order_id = '0')
    {
        // 商品是否可拼团
        if ($order_id != '0') {
            // 拼单商品是否一致
            $sql = "select goods_id from ecs_order_goods where order_id = '$order_id'";
            $order_data = $this->getORM()->queryRow($sql);
            if ($order_data["goods_id"] != $goods_id) {
                // 验证失败
                return array("status" => "fail", "msg" => "");
            }
        }
        $sql = "select is_pintuan from ecs_goods where goods_id = '$goods_id'";
        $res = $this->getORM()->queryRow($sql);
        if ($res["is_pintuan"] == "1") {
            return array("status" => "succ", "msg" => "");
        } else if ($res["is_pintuan"] == "0") {
            return array("status" == "fail", "msg" => "");
        }
    }


    public function getPtGoodsOrder($goods_id, $type, $page = 1)
    {
        // 获取当前商品的
        if ($type == "goods") {
            // 商品详情页只显示前两条拼团数据b
            $sql = "SELECT a.consignee,a.order_id,a.add_time  FROM ecs_order_info AS a 
                    JOIN ecs_order_goods AS b
                    ON a.order_id = b.order_id AND b.goods_id = '$goods_id' AND a.order_type = 1 AND a.pt_id = 0 LIMIT 3";
        } else if ($type == "list") {
            // 商品全部拼团页
            // 先取得商品信息
            $sql = "select goods_id,shop_price,goods_thumb,goods_name,pt_price from ecs_goods where goods_id = '$goods_id'";
            $goods_data = $this->getORM()->queryRow($sql);
            $goods_data['goods_thumb'] = empty($goods_data['goods_thumb']) ? default_category_goodsImage() : goods_img_url($goods_data['goods_thumb']);
            $page_size = 12;
            $offset = ($page - 1) * $page_size;
            $sql = "SELECT a.consignee,a.order_id,a.add_time  FROM ecs_order_info AS a 
                        JOIN ecs_order_goods AS b 
                        ON a.order_id = b.order_id AND b.goods_id = '$goods_id' AND a.order_type = 1 AND a.pt_id = 0 LIMIT {$offset},{$page_size}";
        }
        $data = $this->getORM()->queryRows($sql);
        // 计算当前拼团所剩时间
        $time = time();
        foreach ($data as $k => $v) {
            $sec = intval(86400 - ($time - $v["add_time"]));
            $hour = intval($sec / 3600); // 小时
            $min = intval(($sec - $hour * 3600) / 60);
            $second = intval($sec - $hour * 3600 - $min * 60);
            $data[$k]["hour"] = $hour;
            $data[$k]["min"] = $min;
            $data[$k]["sec"] = $second;
            $data[$k]["ads"] = $sec;
        }

        $sql = "select count(*) as num from ecs_order_info as a join ecs_order_goods as b on a.order_id = b.order_id and a.pt_id = 0 and a.order_type = 1 where b.goods_id = '$goods_id'";

        $pt_num = $this->getORM()->queryRow($sql);

        if ($type == "goods") {
            return array("pt_list" => $data, "num" => $pt_num);
        } else if ($type == "list") {
            return array("pt_list" => $data, "goods_data" => $goods_data, "pagetotal" => ceil($pt_num["num"] / 12));
        }
    }
    // 版权设置
    public function getappcopyright()
    {
        $sql = "select copyright_one,copyright_two from ecs_copyright_modify ";
        $data = $this->getORM()->queryRow($sql);
        return $data;
    }

    /**
     * @param $goods_id
     * @param $user_id
     * @return mixed
     * 根据用户id取得会员等级，并且取得商品的会员价格
     */
    public function get_price($goods_id, $user_id)
    {
        // 查询当前的价格
        $goods_price_sql = "select shop_price,promote_price from ecs_goods where goods_id = '" . $goods_id . "'";
        $goods_data = $this->getORM()->queryRow($goods_price_sql);

        if (isset($user_id) && !empty($user_id)) {
            $sql = "SELECT user_rank,user_id FROM ecs_users WHERE user_id = '$user_id'";
            $res = $this->getORM()->queryRow($sql);
            $user_rank = $res['user_rank'];
            $sql = "SELECT user_price,user_rank FROM ecs_member_price WHERE user_rank = '$user_rank' AND goods_id = '$goods_id'";
            $price = $this->getORM()->queryRow($sql);
            // 等级金额设置可为零
            if (!empty($price)) {
                return  $this->price_format($price['user_price']);
            }

            // 如果当前用户的等级为 非特殊等级，则按照原价返回
            if ($user_rank == '0') {
                if($goods_data['promote_price'] != '0'){
                    return $this->price_format($goods_data['promote_price']);
                }else{
                    return $this->price_format($goods_data['shop_price']);
                }
            }
            // 如果当前查询结果为空 则表示按照当前用户的等级折扣率计算
            if (empty($price)) {
                if($goods_data['promote_price'] != '0'){
                    $price = $goods_data['promote_price'];
                    return  $this->price_format($price);
                }else{
                // 获得当前用户等级等级折扣率
                $userRankSql = "SELECT discount FROM ecs_user_rank WHERE rank_id = '" . $user_rank . "'";
                $user_rank_msg = $this->getORM()->queryRow($userRankSql);
                $price = $goods_data['shop_price'];
                $discount = $user_rank_msg['discount'] / 100; // 当前的比例

                // 对当前的金额进行计算
                //$price = round(($price * $discount), 2);
                return  $this->price_format($price*$discount);
                }
            }
        }

        return $this->price_format($goods_data['shop_price']);
    }
	//格式化价格
	public function price_format($price, $change_price = true)
	{
	    $sql318="select value from ecs_shop_config where id=318";
		$sql303="select value from ecs_shop_config where code='currency_format';";
		//取价格显示设置
		$config318=$this->getORM()->queryRow($sql318);
		//下面这行没用到
		$config303=$this->getORM()->queryRow($sql303);
		
		if($price==='')
	    {
	        $price=0;
	    }
	    if ($change_price)
	    {
	        switch ((int)$config318['value'])
	        {
	            case 0:
	                $price = number_format($price, 2, '.', '');
	                break;
	            case 1: // 保留不为 0 的尾数
	                $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));
	
	                if (substr($price, -1) == '.')
	                {
	                    $price = substr($price, 0, -1);
	                }
	                break;
	            case 2: // 不四舍五入，保留1位
	                $price = substr(number_format($price, 2, '.', ''), 0, -1);
	                break;
	            case 3: // 直接取整
	                $price = intval($price);
	                break;
	            case 4: // 四舍五入，保留 1 位
	                $price = number_format($price, 1, '.', '');
	                break;
	            case 5: // 先四舍五入，不保留小数
	                $price = round($price);
	                break;
	        }
	    }
	    else
	    {
	        $price = number_format($price, 2, '.', '');
	    }
		
		return $price;
	}

    public function genDefauleData($data, $user_id)
    {
        if (empty($data)) {
            return array();
        }

        $sku_arr = array();
        foreach ($data as $key => $val) {
            $sku_arr[] = $val['skuValList'][0]['skuValId']; // 默认取第一个
        }


        if (count($sku_arr) == 1) {
            $sku_str = $sku_arr[0];
            $sku_str1 = $sku_arr[0];
        } else {
            $sku_str = implode('|', $sku_arr);
            $sku_str1 = implode(',', $sku_arr);
        }

        // 对数据处理

        // 对当前的属性进行价格与库存查询
        $stockSql = "select product_number,product_id from ecs_products where goods_attr = '" . $sku_str . "'";
        $stock = $this->getORM()->queryRow($stockSql);

        // 查询价格
        $priceSql = "select sum(attr_price) as price,goods_id from ecs_goods_attr where goods_attr_id in (" . $sku_str1 . ")";
        $price = $this->getORM()->queryRow($priceSql);

        // 获取商品价格
        $user_price = $this->get_price($price['goods_id'], $user_id);

        $goods_price = $price['price'] + $user_price;

        return array(
            'stock' => empty($stock['product_number']) ? 0 : $stock['product_number'],
            //'price' => round($goods_price, 2),
			'price' => $goods_price,
            'product_id' => $stock['product_id']
        );
    }

    /**
     * @param int $page
     * @return array
     * 首页拼团跳转的页面，取得所有拼团的商品
     */
    public function getPtGoodsList($page = 1)
    {
        $page_size = 12;
        $offset = ($page - 1) * $page_size;
        $sql = "SELECT goods_id,goods_name,shop_price,goods_thumb as list_pic_url,pt_price FROM ecs_goods WHERE is_pintuan = '1' LIMIT {$offset},{$page_size}";
        $res = $this->getORM()->queryAll($sql);

        foreach ($res as $k => $v) {
            $res[$k]['list_pic_url'] = empty($res[$k]['list_pic_url']) ? default_category_goodsImage() : goods_img_url($res[$k]['list_pic_url']);
        }

        $sql = "select count(*) as num from ecs_goods WHERE is_pintuan = '1'";

        $pt_num = $this->getORM()->queryRow($sql);


        return array('list' => $res, "pagetotal" => ceil($pt_num["num"] / 12));
    }

    /**
     * @param int $page
     * @return array
     * 首页新品跳转的页面，取得所有新品
     */
    public function getSpikeGoodsList($page = 1)
    {
        $page_size = 12;
        $offset = ($page - 1) * $page_size;
        $sql = "SELECT goods_id,goods_name,shop_price,goods_thumb as list_pic_url,goods_number FROM ecs_goods WHERE is_new = '1' and is_delete ='0' LIMIT {$offset},{$page_size}";
        $res = $this->getORM()->queryAll($sql);

        foreach ($res as $k => $v) {
            $res[$k]['list_pic_url'] = empty($res[$k]['list_pic_url']) ? default_category_goodsImage() : goods_img_url($res[$k]['list_pic_url']);
        }

        $sql = "select count(*) as num from ecs_goods WHERE is_new = '1'";

        $spike_sum = $this->getORM()->queryRow($sql);


        return array('list' => $res, "pagetotal" => ceil($spike_sum["num"] / 12));
    }

    /**
     * @param int $page
     * @return array
     * 人气推荐的商品，用做首页跳转
     */
    public function gethotGoodsList($page = 1)
    {
        $page_size = 12;
        $offset = ($page - 1) * $page_size;
        $sql = "SELECT goods_id,goods_name,shop_price,goods_thumb as list_pic_url,goods_number FROM ecs_goods WHERE is_hot = '1' and is_delete ='0' LIMIT {$offset},{$page_size}";
        $res = $this->getORM()->queryAll($sql);

        foreach ($res as $k => $v) {
            $res[$k]['list_pic_url'] = empty($res[$k]['list_pic_url']) ? default_category_goodsImage() : goods_img_url($res[$k]['list_pic_url']);
        }

        $sql = "select count(*) as num from ecs_goods WHERE is_hot = '1'";

        $hot_sum = $this->getORM()->queryRow($sql);


        return array('list' => $res, "pagetotal" => ceil($hot_sum["num"] / 12));
    }

    public function getMiaoShaGoodsList($page = 1)
    {
        $page_size = 12;
        $offset = ($page - 1) * $page_size;
        $sql = "SELECT goods_id,goods_name,shop_price,goods_thumb as list_pic_url,goods_number FROM ecs_goods WHERE active = 'true' LIMIT {$offset},{$page_size}";
        $res = $this->getORM()->queryAll($sql);

        foreach ($res as $k => $v) {
            $res[$k]['list_pic_url'] = empty($res[$k]['list_pic_url']) ? default_category_goodsImage() : goods_img_url($res[$k]['list_pic_url']);
        }

        $sql = "select count(*) as num from ecs_goods WHERE is_hot = '1'";

        $hot_sum = $this->getORM()->queryRow($sql);


        return array('list' => $res, "pagetotal" => ceil($hot_sum["num"] / 12));
    }

    /**
     * 获取商品的附件
     * @return array
     */
    public function getGoodsFileList($goods_id = 0)
    {
        $file_list = [];

        $sql = "SELECT file_url,file_name from ecs_goods_upload where goods_id = '".$goods_id."' order by file_id desc";
        $file_list = $this->getORM()->queryRows($sql);

        if($file_list) {
            foreach ($file_list as $key => $val) {
                $file_list[$key]['file_url'] = getUrl(). $val['file_url'];
            }
        }


        return $file_list ? $file_list : [];
    }
}
