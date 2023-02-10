<?php

namespace App\Api;

use PhalApi\Api;
use App\Model\Brand as BrandModel;


/**
 * 品牌相关接口服务
 * @package App\Api
 */
class Brand extends Api
{
    protected $model;

    public function __construct()
    {
        $this->model = new BrandModel();
    }

    public function getRules()
    {
        return array(
            'brandlistActionApi' => array(
                'page' => array("name" => "page", "require" => true, "min" => 1, "desc" => "页数"),
            ),
            'brandDetaiLactionApi' => array(
                "page" => array("name" => "page", "require" => true, "min" => 1, "desc" => "页数"),
                "id"   => array("name" => "id", "require" => true, "min" => 1, "desc" => "品牌ID"),
                "user_id"   => array("name" => "user_id", "require" => false, "min" => 1, "desc" => "用户ID"),
            ),
        );
    }
    /**
     * 品牌列表
     * @desc 品牌列表
     * @return array
     */
    public function brandlistActionApi()
    {
        $page = intval($this->page);

        $res = $this->model->getBrandListAction($page);

        return $res;
    }


    /**
     * 品牌详情
     * @desc 品牌详情
     * @return array
     */
    public function brandDetaiLactionApi()
    {
        $page = intval($this->page);
        $id = intval($this->id);
        $user_id = intval($this->user_id);
        $res = $this->model->getBrandDetailAction($page, $id, $user_id);
        return $res;
    }

    /**
     * 品牌相关商品列表
     * @desc 品牌相关商品列表
     * @return array
     */
    public function brandGoodsList()
    {
        return ['品牌商品列表'];
    }
}
