<?php

namespace App\Api;

use App\Model\Cart as CartModel;
use PhalApi\Api;

/**
 * 购物车相关接口服务
 * @package App\Api
 */
class Cart extends Api
{
    protected $model;

    public function __construct()
    {
        $this->model = new CartModel();
    }

    public function getRules()
    {
        return array(
            'goodsAddCartApi' => array(
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '商品ID'),
                'sku' => array('name' => 'sku', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => '货品列表'),
                'num' => array('name' => 'num', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '货品数量'),
                'checked' => array('name' => 'checked', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => '是否选择'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
                'product_id' => array('name' => 'product_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '商品属性'),
            ),
            'goodsAddCartFastApi' => array(
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '商品ID'),
                'sku' => array('name' => 'sku', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => '货品列表'),
                'num' => array('name' => 'num', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '货品数量'),
                'checked' => array('name' => 'checked', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => '是否选择'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
                'product_id' => array('name' => 'product_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '商品属性'),
            ),
            'cartUpdateNumApi' => array(
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '商品ID'),
                'number' => array('name' => 'number', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '货品数量'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
                'product_id' => array('name' => 'product_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '商品属性'),
            ),
            'cartDelApi' => array(
                'id' => array('name' => 'id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '购物车id'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
            ),
            'cartListApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
            ),
            'cartCheckApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
                'id' => array('name' => 'id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'rec_id'),
                'ischecked' => array('name' => 'ischecked', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'ischecked'),
            ),

            'AddPackageCartApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'package_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
            ),

            'cartCheckAllApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
                'ischecked' => array('name' => 'ischecked', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'ischecked'),
            ),
        );
    }

    /**
     * 购物车列表
     * @desc 购物车列表
     * @return array
     */
    public function cartListApi()
    {
        $user_id = intval($this->user_id);
        $token = $this->token;
        $this->checkLogin();

        $cart_goods = $this->model->get_cart_goods($user_id);
        if (!$cart_goods) {
            return array('add_cart' => false, 'info' => $cart_goods);
        } else {
            return array('add_cart' => true, 'info' => $cart_goods);
        }
    }
    /**
     * 更新购物车数量
     * @desc 直接更改购物车商品数量接口
     */
    public function cartUpdateNumApi()
    {
        $goods_id = intval($this->goods_id);
        $user_id = intval($this->user_id);
        $token = $this->token;
        $num = intval($this->number);
        $product_id = intval($this->product_id);
        $this->checkLogin();

        $res = $this->model->update_cart_nums($goods_id, $num, $user_id, $product_id);
        $totalPrice = $this->model->get_totalPrice($user_id);
        if ($res) {
            return array('update_cart' => true, 'msg' => '', 'totalPrice' => $totalPrice);
        } else {
            return array('update_cart' => false, 'msg' => $this->model->msg, 'totalPrice' => $totalPrice);
        }
    }

    /**
     * 删除购物车数据
     * @desc 删除购物车数据
     * @return array
     */
    public function cartDelApi()
    {
        $id = intval($this->id);
        $user_id = intval($this->user_id);
        $token = $this->token;
        $this->checkLogin();

        $res = $this->model->delete_cart($id, $num, $user_id);
        if ($res) {
            return array('delete_cart' => true, 'msg' => '');
        } else {
            return array('delete_cart' => false, 'msg' => $this->model->msg);
        }
    }

    /**
     * 更新购物车数据
     * @desc 更新购物车
     * @return array
     */
    public function goodsAddCartApi()
    {
        $goods_id = intval($this->goods_id);
        $user_id = intval($this->user_id);
        $token = $this->token;
        $sku = $this->sku;
        $num = intval($this->num);
        $product_id = intval($this->product_id);

        $this->checkLogin();

        $res = $this->model->addto_cart($goods_id, $num, $this->sku, $user_id, $token, $product_id);
        if ($res) {
            return array('add_cart' => true, 'msg' => '');
        } else {
            return array('add_cart' => false, 'msg' => $this->model->msg);
        }
    }

    /**
     * 更新购物车数据  立即购买
     * @desc 更新购物车
     * @return array
     */
    public function goodsAddCartFastApi()
    {
        $goods_id = intval($this->goods_id);
        $user_id = intval($this->user_id);
        $token = $this->token;
        $sku = $this->sku;
        $num = intval($this->num);
        $product_id = intval($this->product_id);
        $this->checkLogin();

        $res = $this->model->addto_cart($goods_id, $num, $this->sku, $user_id, $token, $product_id);


        if ($res) {
            $this->model->doFastcheck($goods_id, $user_id, $num, $product_id);   // 如果有库存才会去更改数据库内的信息
            return array('add_cart' => true, 'msg' => '');
        } else {
            return array('add_cart' => false, 'msg' => $this->model->msg);
        }
    }

    /**
     * 超值礼包添加购物车
     * @desc 超值礼包添加购物车
     * @return array
     */
    public function  AddPackageCartApi()
    {
        $goods_id = intval($this->goods_id);
        $user_id = intval($this->user_id);
        $token = $this->token;
        $data =  $this->model->AddPackageCartApi($goods_id, $user_id, $token);

        return  array('data' => $data, 'msg' => $this->model->msg);
    }

    /**
     * 购物车勾选
     * @desc 购物车勾选
     * @return array
     */
    public function cartCheckApi()
    {
        $rec_id = intval($this->id);
        $user_id = intval($this->user_id);
        $ischecked = intval($this->ischecked);
        $token = $this->token;
        $this->checkLogin();

        $res = $this->model->cartCheck($ischecked, $user_id, $rec_id);
        if ($res) {
            return array('check_cart' => true, 'data' => $res, 'msg' => '');
        } else {
            return array('check_cart' => false, 'data' => $res, 'msg' => $this->model->msg);
        }
    }

    /**
     * 购物车全选
     * @desc 购物车全选
     * @return array
     */
    public function cartCheckAllApi()
    {
        $user_id = intval($this->user_id);
        $ischecked = intval($this->ischecked);
        $token = $this->token;
        $this->checkLogin();

        $res = $this->model->cartCheck($ischecked, $user_id);
        if ($res) {
            return array('check_cart' => true, 'data' => $res, 'msg' => '');
        } else {
            return array('check_cart' => false, 'data' => $res, 'msg' => $this->model->msg);
        }
    }
}
