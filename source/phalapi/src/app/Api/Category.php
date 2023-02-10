<?php

namespace App\Api;

use App\Model\Category as CategoryModel;
use App\Model\Goods as GoodsModel;
use PhalApi\Api;

/**
 * 一级分类接口服务
 * @package App\Api
 */
class Category extends Api
{

    public function __construct()
    {
        $this->model = new CategoryModel();
        $this->goods_model = new GoodsModel();
    }

    public function getRules()
    {
        return array(
            'categoryListApi' => array(
                'cat_id' => array('name' => 'cat_id', 'require' => false, 'min' => 0, 'max' => 50, 'desc' => '分类ID'),
            ),
            'CategoryList' => array(
                'cat_id' => array('name' => 'cat_id', 'require' => false, 'min' => 0, 'max' => 50, 'desc' => '分类ID'),
            ),
            'categorySecondaryApi' => array(
                "cat_id" => array("name" => 'cat_id', 'require' => false, 'min' => 0, "max" => 50, "desc" => "一级分类ID"),
            ),
            'categorylistNavListApi' => array(
                "cat_id" => array("name" => "cat_id", "require" => false, "min" => 0, "max" => 50, "desc" => "二级分类ID"),
            ),
            "categorylistGoodsListApi" => array(
                "cat_id" => array("name" => "cat_id", "require" => false, "min" => 1, "max" => 50, "desc" => "二级分类的ID"),
                "order" => array("name" => "order", "require" => false, "min" => 0, "max" => 50, "desc" => "排序"),
                "user_id" => array("name" => "user_id", "require" => false, "min" => 1, "max" => 50, "desc" => "用户ID"),
                "type" => array("name" => "type", "require" => false, "min" => 1, "max" => 50, "desc" => "排序类型"),
                "page" => array("name" => "page", "require" => false, "min" => 1, "max" => 50, "desc" => "当前页数"),
            ),
        );
    }

    /**
     * 分类
     * @desc 分类展示接口
     * @return array
     */
    public function categoryListApi()
    {
        $cat_id = intval($this->cat_id);
        $data = $this->model->getCategoryList($cat_id);

        return $data;
    }

    /**
     * 分类物品
     * @desc 分类物品
     * @return array
     */
    public function categoryList()
    {
        $cat_id = intval($this->cat_id);
        $data = $this->goods_model->getGoodsList($cat_id);
        $data['this_cat']['cat_id'] = $cat_id;
        return $data;
    }

    /**
     * 二级分类
     * @desc 二级分类展示
     * @return array
     */

    public function categorySecondaryApi()
    {
        $cat_id = intval($this->cat_id);
        $data = $this->model->getCategorySecond($cat_id);
        return $data;
    }

    /**
     * 二级分类导航
     * @desc 二级分类-同父级分类下的所有子分类
     *
     */
    public function categorylistNavListApi()
    {
        $cat_id = intval($this->cat_id);
        $data = $this->model->getCategoryListNavList($cat_id);
        return $data;
    }

    /**
     * 当前分类的物品
     * @desc 当前所属分类下的商品
     *
     */
    public function categorylistGoodsListApi()
    {

        $cat_id = intval($this->cat_id);
        $user_id = intval($this->user_id);
        $page = intval($this->page);
        $type = $this->type;
        $order = $this->order;
        $data = $this->model->getCategoryListGoods($cat_id, $page, $order, $type, $user_id);

        return $data;
    }
}
