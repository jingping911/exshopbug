<?php

namespace App\Api;

use PhalApi\Api;
use App\Model\Shipping as ShippingModel;

/**
 * 配送方式接口服务
 * @package App\Api
 */
class Shipping extends Api
{
    protected $model;

    public function __construct()
    {
        $this->model = new ShippingModel();
    }

    public function getRules()
    {
        return array(
            'shippingListApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),

                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'address_id' => array('name' => 'address_id', 'require' => false, 'min' => 0, 'max' => 32, 'desc' => 'address_id'),
            ),
            'shippingListBottomApi' => array(
                'delivery_id' => array('name' => 'delivery_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'delivery_id'),
            ),
            'shippingListTopApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 6, 'max' => 32, 'desc' => 'token'),
                'address_id' => array('name' => 'address_id', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'address_id'),
            ),

        );
    }
    /**
     * 配送方式列表
     * @desc 根据订单取得相应的配送方式
     */
    public function shippingListApi()
    {

        $data = $this->model->available_shipping_list();

        return array('shipping_list' => true, 'info' => $data);
    }

    /**
     * @return array
     * 查询一级菜单
     */
    public function shippingListTopApi()
    {
        $user_id = intval($this->user_id);
        $token = $this->token;
        $address_id = $this->address_id;
        $data = $this->model->shipping_list_top($user_id, $address_id);
        if (!$data) {
            return array('shipping_list' => false, 'info' => $data);
        }
        return array('shipping_list' => true, 'info' => $data);
    }

    /**
     * @return array
     * 查询出二级菜单下的一级菜单
     */
    public function shippingListBottomApi()
    {
        $delivery_id = $this->delivery_id;
        $data = $this->model->shipping_list_bottom($delivery_id);
        return $data;
    }
}
