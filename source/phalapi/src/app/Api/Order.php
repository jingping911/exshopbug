<?php

namespace App\Api;

use PhalApi\Api;
use App\Model\Order as OrderModel;
use App\Model\Cart as CartModel;
use App\Model\Address as AddressModel;
use App\Model\Shipping as ShippingModel;

/**
 * 订单相关接口服务
 * @package App\Api
 */
class Order extends Api
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderModel();
    }

    public function getRules()
    {
        return array(
            'orderDetailListApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'address_id' => array('name' => 'address_id', 'require' => false, 'min' => 0, 'max' => 5, 'desc' => 'address_id'),
                'shipping_id' => array('name' => 'shipping_id', 'require' => false, 'min' => 0, 'max' => 5, 'desc' => 'shipping_id'),
                'tax_type' => array("name" => "tax_type", "require" => false, "min" => 0, "max" => 5, "desc" => "发票类型"),
                'ral' => array('name' => 'ral', 'require' => false, 'min' => 0, 'max' => 15, 'desc' => '是否是积分商品'),
                'integralGoodsid' => array('name' => 'integralGoodsid', 'require' => false, 'min' => 0, 'max' => 15, 'desc' => '积分商品id'),
                'super' => array('name' => 'super', 'require' => false, 'min' => 0, 'max' => 15, 'desc' => '是否是'),
                'superGoodsid' => array('name' => 'superGoodsid', 'require' => false, 'min' => 0, 'max' => 50, 'desc' => '超值礼包id'),
                'p_type' => array('name' => 'p_type', 'require' => false, 'min' => 0, 'max' => 50, 'desc' => '配送类型'),
                'goods_id' => array('name' => 'goods_id', 'require' => false, 'min' => 0, 'max' => 50, 'desc' => '商品id'),
                'result_spike' => array('name' => 'result_spike', 'require' => false, 'min' => 0, 'max' => 50, 'desc' => '是否是秒杀商品'),
                'pintuan' => array("name" => "pintuan", "require" => false, "min" => 0, "max" => 12, "desc" => "是否为拼团商品"),
                'pintuanGoodsId' => array("name" => "pintuanGoodsId", "require" => false, "min" => 0, "max" => 15, "desc" => "拼团商品id"),
                'number' => array("name" => "number", "require" => false, "min" => 0, "max" => 15, "desc" => "数量"),
            ),
            'getDiscountApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'product_id' => array('name' => 'product_id', 'require' => false, 'min' => 0, 'max' => 15, 'desc' => '商品id'),
                'result_spike' => array('name' => 'result_spike', 'require' => false, 'min' => 0, 'max' => 50, 'desc' => '是否是秒杀商品'),
                'goods_id' => array('name' => 'goods_id', 'require' => false, 'min' => 0, 'max' => 50, 'desc' => '商品id'),
                //                'pintuan' => array("name"=>"pintuan","require"=>false,"min"=>0,"max"=>12,"desc"=>"是否为拼团商品"),
            ),
            'getOrderEndApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
            ),


            'orderSaveCartApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'address_id' => array('name' => 'address_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'address_id'),
                'shipping_id' => array('name' => 'shipping_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'shipping_id'),
                'bonus_id' => array('name' => 'bonus_id', 'require' => false, 'min' => 0, 'max' => 32, 'desc' => '红包id'),
                'point' => array('name' => 'point', 'require' => false, 'min' => 0, 'max' => 32, 'desc' => '积分'),
                'inv_payee' => array("name" => 'inv_payee', "require" => false, "min" => 0, "max" => 32, "desc" => "发票抬头"),
                'tax' => array("name" => 'tax', "require" => false, "min" => 0, "max" => 32, "desc" => "发票税额"),
                'inv_type' => array("name" => 'inv_type', "require" => false, "min" => 0, "max" => 32, "desc" => "发票类型"),
                'tax_type' => array("name" => 'tax_type', "require" => false, "min" => 0, "max" => 32, "desc" => "发票选择类型"),
                'pack_fee' => array("name" => "pack_fee", "require" => false, "min" => 0, "max" => 32, "desc" => "包装盒费用"),
                'pack_name' => array("name" => "pack_name", "require" => false, "min" => 0, "max" => 32, "desc" => "包装盒名"),
                'ral' => array("name" => "ral", "require" => false, "min" => 0, "max" => 32, "desc" => "是否积分兑换"),
                'integralGoodsid' => array("name" => "integralGoodsid", "require" => false, "min" => 0, "max" => 32, "desc" => "积分商品id"),
                'superpack' => array("name" => "superpack", "require" => false, "min" => 0, "max" => 32, "desc" => "是否超值礼包"),
                'superGoodsid' => array("name" => "superGoodsid", "require" => false, "min" => 0, "max" => 32, "desc" => "礼包id"),
                'o_time' => array("name" => "o_time", "require" => false, "min" => 0, "max" => 32, "desc" => "配送时间"),
                'delivery_name' => array("name" => "delivery_name", "require" => false, "min" => 0, "max" => 32, "desc" => "二级菜单名称"),
                'delivery_id' => array("name" => "delivery_id", "require" => false, "min" => 0, "max" => 32, "desc" => "一级菜单的id"),
                'result_spike' => array("name" => "result_spike", "require" => false, "min" => 0, "max" => 32, "desc" => "是否是秒杀商品"),
                'goods_id' => array('name' => 'goods_id', 'require' => false, 'min' => 0, 'desc' => '商品id'),
                'pintuan' => array("name" => "pintuan", "require" => false, "min" => 0, "max" => 32, "desc" => "是否为拼团商品"),
                'pintuanGoodsId' => array("name" => "pintuanGoodsId", "require" => false, "min" => 0, "max" => 32, "desc" => "拼团商品id"),
                'pintuanNumber' => array("name" => "pintuanNumber", "require" => false, "min" => 0, "max" => 32, "desc" => "拼团商品数量"),
                'productId' => array("name" => "productId", "require" => false, "min" => 0, "max" => 32, "desc" => "商品的属性ID"),
                'pintuanOrderId' => array("name" => "pintuanOrderId", "require" => false, "min" => 0, "max" => 32, "desc" => "拼单ID"),
                'p_type' => array("name" => "p_type", "require" => false, "min" => 0, "max" => 32, "desc" => "配送类型"),
                'tax_num' => array("name" => "tax_num", "require" => false, "min" => 0, "max" => 64, "desc" => "纳税人识别号"),
                'msg' => array("name" => "msg", "require" => false, "min" => 0, "max" => 255, "desc" => "订单留言")
            ),

            'payDetail' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'order_id' => array('name' => 'order_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'order_id'),
            ),
            'GetDeliveryTimeApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
            ),
            'DeliveryTimeApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'delivery_id' => array('name' => 'delivery_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => '配送时间id'),
            ),
            'orderlistActionApi' => array(
                'status' => array("name" => 'status', "require" => true, "min" => 1, "desc" => "分类码"),
                'user_id' => array("name" => 'user_id', "require" => true, "min" => 1, "desc" => "会员id"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'page'  => array("name" => "page", "require" => true, "min" => 1, "desc" => "页数")
            ),
            'LogisticsInfoApi' => array(
                'order_id' => array("name" => 'order_id', "require" => true, "min" => 1, "desc" => "订单ID"),
                'user_id' => array("name" => 'user_id', "require" => true, "min" => 1, "desc" => "会员id"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),

            'packageListApi' => array(
                'user_id' => array("name" => 'user_id', "require" => true, "min" => 1, "desc" => "会员id"),
            ),
            'orderdetailGetApi' => array(
                'user_id' => array("name" => "user_id", "require" => true, "min" => 1, "desc" => "会员ID"),
                'orderid'   => array("name" => "orderId", "require" => true, "min" => 1, "desc" => "订单号"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
            ),
            'cancelOrderApi' => array(
                'order_id' => array('name' => 'order_id', 'require' => true, 'min' => 1, 'desc' => '订单ID'),
                'user_id'  => array('name' => 'user_id', 'require' => true, 'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token')
            ),
            'receiveOrderApi' => array(
                'order_id' => array('name' => 'order_id', 'require' => true, 'min' => 1, 'desc' => '订单id'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token')
            ),
            "submitAfterSaleApi" => array(
                'orderId' => array('name' => 'orderId', 'require' => true, 'min' => 1, 'desc' => '订单号'),
                'note' => array('name' => 'note', 'require' => true, 'min' => 1, 'desc' => '备注'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'desc' => 'token'),
                'goods_num' => array('name' => 'goods_num', 'require' => true, 'min' => 1, 'desc' => '商品数量'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'desc' => '用户ID'),
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'min' => 1, 'desc' => '商品id'),
            ),
            "aftersalelistActionApi" => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'page'  => array("name" => "page", "require" => true, "min" => 1, "desc" => "页数")
            ),
            "taxListApi" => array(
                "user_id" => array("name" => 'user_id', "require" => true, "min" => 1, "desc" => "用户id"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'desc' => 'token')
            ),
             "getOrderStatusApi" => array(
                 'orderId' => array('name' => 'orderId', 'require' => true, 'min' => 1, 'desc' => '订单号'),
                "user_id" => array("name" => 'user_id', "require" => true, "min" => 1, "desc" => "用户id"),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'desc' => 'token')
            )

        );
    }

    /**
     * 订单确认页面
     * @desc 订单确认页
     */
    public function orderDetailListApi()
    {

        $cart_model = new CartModel();
        $user_addr_model = new AddressModel();
        $shipping_model = new ShippingModel();

        $user_id = intval($this->user_id);
        $token = $this->token;
        $address_id = intval($this->address_id);
        $shipping_id = intval($this->shipping_id);
        $tax_type = intval($this->tax_type); // 获取发票的类型
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $ral = $this->ral;
        $super = $this->super;
        $integralGoodsid = $this->integralGoodsid;
        $superGoodsid = $this->superGoodsid;
        $p_type = $this->p_type;
        $result_spike = $this->result_spike; //是否是秒杀商品
        $goods_id = $this->goods_id;
        $pintuan = $this->pintuan;
        $pintuanGoodsId = $this->pintuanGoodsId;
        $number = $this->number;
        if ($ral != 'true' && $super != 'true' && $result_spike != 'true' && $pintuan != 'true') {
            $cart_goods =  $cart_model->get_cart_goods_checkout($user_id, '', '', "", '', '', '', '', '', '');
        } elseif ($ral == 'true') {
            $cart_goods['goods_list'] =  $cart_model->get_integralgoods($integralGoodsid);
        } elseif ($super == 'true') {
            $cart_goods['goods_list'] =  $cart_model->get_packafegoods($superGoodsid, $user_id);
        } elseif ($result_spike == 'true') { //是否是秒杀商品
            $cart_goods['goods_list'] = $cart_model->spikeShopGoods($result_spike, $goods_id);
        } else if ($pintuan == "true") {
            // 拼团
            $res = $cart_model->get_pintuan_goods($pintuanGoodsId);
            if ($res["status"] == "succ") {
                $cart_goods["goods_list"] = $res["data"];
            } else if ($res["status"] == "fail") {
                return array("cart_init" => false, "info" => $res["msg"]);
            }
        }
        //        $cart_goods =  $cart_model->get_cart_goods_checkout($user_id);

        $goods_status =  $cart_model->get_goods_status($cart_goods['goods_list']);

        if (!$cart_goods) {
            return array('cart_init' => false, 'info' => $cart_goods);
        }

        if ($address_id == '') {
            $addr_list = $user_addr_model->getUserAddressLast($user_id);
            $address_id = $addr_list['address_id'];
            $consignee =  $addr_list;
        } else {
            $addr_list = $user_addr_model->get_address_by_id($user_id, $address_id);
            $consignee =  $addr_list;
        }
        if ($shipping_id == '') {
            $data = $shipping_model->shipping_list_top($user_id, $address_id);
            $shipping_id = $data['0']['shipping_id'];
        }
        //        if($ral =='true'){
        //            $total =  $this->model->goods_fee($integralGoodsid);
        //        }else{
        //            $total = $this->model->order_fee($order, $user_id, $consignee,$shipping_id,$ral,$integralGoodsid);
        //        }

        if ($result_spike == 'true') {
            $total = $this->model->seckill_fee($goods_id, $number,$consignee,$shipping_id);
            $total["goods_price"] = $cart_goods['goods_list']['info']['goods_price'];
            $cart_goods["total"]["goods_amount"] = $total["goods_price"] + $total['shipping_fee'];
        } else if ($pintuan == "true") {
            $total = $this->model->pintuan_fee($pintuanGoodsId, $number,$consignee,$shipping_id);
            $cart_goods['goods_list'][0]["goods_number"] = $number;
            $cart_goods['goods_list'][0]["goods_price"] = $total["goods_price"];
            $cart_goods["total"]["goods_amount"] = $total["goods_price"] + $total['shipping_fee'];
        } else {
            $total = $this->model->order_fee($order, $user_id, $consignee, $shipping_id, $ral, $integralGoodsid, $super, $superGoodsid, '', '', '', '', '');
        }

        //        if(!empty($shipping_id) && $p_type !='true'){
        //            $shipping_fee = $this->model->shipping_id_fee($shipping_id);
        ////            $shipping_fee= 0;
        //        }
        //        if($pintuan == "true"){
        //            $total = $this->model->pintuan_fee($pintuanGoodsId,$number);
        //            $cart_goods['goods_list'][0]["goods_number"] = $number;
        //            $cart_goods['goods_list'][0]["goods_price"] = $total["goods_price"];
        //            $cart_goods["total"]["goods_amount"] = $total["goods_price"];
        //        }else{
        //            $total = $this->model->order_fee($order, $user_id, $consignee,$shipping_id,$ral,$integralGoodsid,$super,$superGoodsid);
        //        }
        if (!empty($shipping_id) && $p_type != 'true') {
            $total['shipping_fee'] = $this->model->shipping_id_fee($shipping_id);
        }
        // 计算税
        if ($tax_type != '') {
            $data = $this->taxListApi();

            $rate = $data[$tax_type][1];

            $tax = round($total['goods_price'] * ($rate / 100), 2); // 计算税

        }

        // 计算当前订单可使用的积分数
        $integral = $this->model->sumIntegral($cart_goods['goods_list']);


        $cart_goods['total']['shipping_fee'] = $total['shipping_fee'];
        $cart_goods['total']['goods_status'] = $goods_status;
        $cart_goods['total']['total_fee'] = round($total['shipping_fee'] + $total['goods_price'] + $tax, 2);
        $cart_goods['total']['goods_price'] = $total['goods_price'];
        $cart_goods['total']['saving'] = $total['saving'];
        $cart_goods['total']['tax'] = $tax;
        $cart_goods['total']['integral'] = $integral['max']; // 当前订单可使用的最大积分数



        $cart_goods['address_list'] = $addr_list;
        return array('cart_init' => true, 'info' => $cart_goods);
    }
    /**
     * 折扣优惠
     * @desc 订单可享受的折扣优惠
     */
    public  function getDiscountApi()
    {
        $product_id =  $this->product_id;
        $user_id =  $this->user_id;
        $result_spike = $this->result_spike;
        $goods_id = $this->goods_id;
        $data = $this->model->getDiscount($product_id, $user_id, $result_spike, $goods_id);

        return $data;
    }

    /**
     * 订单截止时间
     * @desc 订单截止时间
     */
    public  function getOrderEndApi()
    {
        $data = $this->model->getOrder_end();
        return $data;
    }

    /**
     * 订单提交
     * @desc 订单提交页面
     */
    public function orderSaveCartApi()
    {
        $user_id = intval($this->user_id);
        $address_id = intval($this->address_id);
        $shipping_id = intval($this->shipping_id);
        $bonus_id = intval($this->bonus_id);
        $point = intval($this->point);
        $p_type = $this->p_type;
        $token = $this->token;
        $inv_payee = $this->inv_payee;
        $inv_type = $this->inv_type;
        $tax = $this->tax;
        $tax_type = $this->tax_type;
        $pack_name = $this->pack_name;
        $pack_fee = $this->pack_fee;
        $ral = $this->ral;
        $integralGoodsid = $this->integralGoodsid;
        $super = $this->superpack;
        $superGoodsid = $this->superGoodsid;
        $o_time = $this->o_time;
        $delivery_id = $this->delivery_id; //一级菜单的id
        $delivery_name = $this->delivery_name; //二级菜单的名称
        $result_spike = $this->result_spike;
        $goods_id = $this->goods_id;
        $pintuan = $this->pintuan; // 是否为拼团商品
        $pintuanGoodsId = $this->pintuanGoodsId; // 拼团的商品ID
        $pintuanNumber = $this->pintuanNumber; // 拼团的商品数量
        $productId = $this->productId; // 商品的属性ID
        $pintuanOrderId = $this->pintuanOrderId; // 拼单的父ID
        $tax_num = $this->tax_num; // 纳税人识别号
        $msg = $this->msg; // 留言内容

        if ($tax_type == 'n') {  // 是否选择了发票
            $tax_data = [];
        } else {
            $tax_data = ['inv_payee' => $inv_payee, "inv_type" => $inv_type, "tax" => $tax, "tax_num" => $tax_num];
        }

        $order_id = $this->model->gen_order($user_id, $address_id, $shipping_id, $bonus_id, $point, $tax_data, $pack_name, $pack_fee, $ral, $integralGoodsid, $o_time, $super, $superGoodsid, $delivery_id, $delivery_name, $result_spike, $goods_id, $p_type, $pintuan, $pintuanGoodsId, $pintuanNumber, $productId, $pintuanOrderId, $msg);

        if (!$order_id) {
            return array('gen_order' => false, 'info' => $this->model->msg);
        }
        return array('gen_order' => true, 'info' => array('order_id' => $order_id));
    }

    /**
     * 商品包装
     * @desc 商品包装接口
     */
    public function packageListApi()
    {
        $data =  $this->model->packageList();
        return $data;
    }

    /**
     * 订单物流
     * @desc 物流信息
     */
    public function LogisticsInfoApi()
    {
        $user_id = intval($this->user_id);
        $order_id = $this->order_id;
        $token = $this->token;   // 密码参数
        $data = $this->model->get_logistics_info($order_id);
        return $data;
    }


    /**
     * 订单列表
     * @desc 订单列表
     */
    public function orderlistActionApi()
    {
        $status = intval($this->status);  //intval()数型转换
        $user_id = intval($this->user_id);
        $page = intval($this->page);
        $token = $this->token;   // 密码参数
        $this->checkLogin();
        $data = $this->model->getOrderListAction($user_id, $status, $page);
        $data['order_list'] = array_reverse($data['order_list']);
        return $data;
    }

    /**
     * 订单配送时间
     * @desc 配送时间
     */
    function  GetDeliveryTimeApi()
    {

        $user_id = intval($this->user_id);

        $data = $this->model->GetDeliveryTimeApi();

        return $data;
    }

    /**
     * 判断订单配送剩余数量
     * @desc 配送剩余数量
     */
    function DeliveryTimeApi()
    {

        $user_id = intval($this->user_id);
        $delivery_id = $this->delivery_id;

        $data = $this->model->DeliveryTimeApi($delivery_id);

        return $data;
    }
    /**
     * 订单详情
     * @desc 订单详情
     */
    public function orderdetailGetApi()
    {
        $order_id = $this->orderid;
        $user_id = intval($this->user_id);
        $token = $this->token;   // 密码参数
        $this->checkLogin();
        if (!filter_character($order_id)&&$order_id){
            return array("msg" => "订单错误");
        }
        $data = $this->model->getOrderDetail($order_id, $user_id);


        return $data;
    }

    /**
     * 取消订单
     * @desc 取消订单
     */
    public function cancelOrderApi()
    {
        //        $order_id = intval($this->order_id);
        $order_id = $this->order_id;
        $user_id = intval($this->user_id);
        $token = $this->token;
        $this->checkLogin();

        $res = $this->model->getCancelOrderRes($order_id, $user_id);

        return $res;
    }

    /**
     * 确认收货
     * @desc 确认收货
     */
    public function receiveOrderApi()
    {
        $order_id = $this->order_id;
        $user_id = intval($this->user_id);
        $token = $this->token;
        $this->checkLogin();

        $res = $this->model->getReceiveOrderRes($order_id, $user_id);
        return $res;
    }

    /**
     * 售后服务
     * @desc 售后服务
     */
    public function submitAfterSaleApi()
    {
        $order_id = $this->orderId;
        $note = $this->note;
        $token = $this->token;
        $goods_id = $this->goods_id;
        $goods_num = $this->goods_num;
        $user_id = $this->user_id;
        $this->checkLogin();
        $res = $this->model->submitAfterSale($order_id, $note, $goods_id, $goods_num, $user_id);

        return $res;
    }

    /**
     * 售后服务列表
     * @desc 售后服务列表
     */
    public function aftersalelistActionApi()
    {
        $user_id = intval($this->user_id);
        $page = intval($this->page);
        $token = $this->token;   // 密码参数
        $this->checkLogin();

        $res = $this->model->getReturnListAction($user_id, $page);
        return $res;
    }

    /**
     * 发票
     * @desc 发票信息获取
     */
    public function taxListApi()
    {
        $user_id = $this->user_id;
        $token = $this->token;
        $this->checkLogin();

        $return = [];
        $di = \PhalApi\DI()->notorm->shop_config;

        $data = $di->where("code", 'invoice_type')->fetchOne();

        $data = unserialize($data['value']);  // 获得发票类型和对应的税率


        foreach ($data['type'] as $k => $v) {
            if ($data['type'][$k] != '') {
                $return[$k + 1] = array($data['type'][$k], $data['rate'][$k]);
            }
        }

        return $return;
    }
    /**
     * 查看订单状态
     * @desc 查看该订单状态
     */
    public function getOrderStatusApi()
    {
        $order_id = $this->orderId;;
        $this->checkLogin();

        $res = $this->model->getOrderStatus($order_id);
        return $res;
    }
}
