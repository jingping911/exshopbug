<?php

namespace App\Model;

use App\Model\Goods as GoodsModel;
use PhalApi\Model\NotORMModel as NotORM;

class Brand extends NotORM
{
    protected function getTableKey($table)
    {
        return 'brand_id';
    }

    public function getIndexBrand()
    {
        // 返回首页展示品牌
        $data = $this->getORM()->where("is_index", 1)->limit(4)->fetchAll();
        foreach ($data as $k => $v) {
            if (is_url($data[$k]['brand_logo']) == false) {
                $data[$k]['brand_logo'] = goods_img_url("data/brandlogo/" . $data[$k]['brand_logo']);
            }
        }
        return $data;
    }

    public function getGoodsBrand($goods_id)
    {
        // 商品详情页面商品品牌
        $this->goods = new GoodsModel();
        $goods = $this->goods->getORM()->where("goods_id", $goods_id)->select("brand_id")->fetchOne();
        $brand = $this->getORM()->where("brand_id", $goods['brand_id'])->select("brand_id as id,brand_name as name")->fetchOne();
        return $brand;
    }

    public function getBrandListAction($page)
    {
        // 首页的品牌列表
        $page_size = getConfigPageSize();
        $offset = ($page - 1) * $page_size;
        $sql = "SELECT brand_id AS id,brand_logo AS app_list_pic_url,brand_name AS name FROM ecs_brand WHERE is_show = '1' LIMIT {$offset},{$page_size}";
        $data = $this->getORM()->queryRows($sql);

        $sql = "SELECT COUNT(brand_id) AS num FROM ecs_brand";
        $num = $this->getORM()->queryRows($sql);
        $total = ceil($num[0]['num'] / $page_size);

        foreach ($data as $k => $v) {
            if ($data[$k]['app_list_pic_url'] == '') { // 没有就使用默认的图片
                $data[$k]['app_list_pic_url'] = default_category_img();
                continue;
            }
            if (is_url($data[$k]['app_list_pic_url']) == false) {
                $data[$k]['app_list_pic_url'] = goods_img_url("data/brandlogo/" . $data[$k]['app_list_pic_url']);
            }
        }

        return ['data' => $data, "total" => $total];
    }

    public function getBrandDetailAction($page, $id, $user_id)
    {
        // 品牌的详情页
        $page_size = getConfigPageSize();
        $offset = ($page - 1) * $page_size;
        $sql = "SELECT * FROM ecs_brand WHERE brand_id={$id} AND is_show = '1'";
        $data = $this->getORM()->queryRows($sql);

        if ($data[0]['brand_logo'] == '') {
            $data[0]['brand_logo'] = default_category_img();
        } else
        if (is_url($data[0]['brand_logo']) == false) {
            $data[0]['brand_logo'] = goods_img_url("data/brandlogo/" . $data[0]['brand_logo']);
        }
        $data[0]['list_pic_url'] = $data[0]['brand_logo'];

        // 当前的品牌下的商品
        $this->model = new GoodsModel();
        $sql = "SELECT goods_id,goods_name AS name,shop_price AS retail_price,original_img , goods_thumb AS list_pic_url FROM ecs_goods WHERE brand_id = {$id}  AND is_delete = 0 LIMIT {$offset},{$page_size}";
        $res = $this->getORM()->queryRows($sql);
        foreach ($res as $k => $v) {
            if ($res[$k]['original_img']) {
                $res[$k]['list_pic_url'] = goods_img_url($res[$k]['original_img']);
            } else {
                $res[$k]['list_pic_url'] = goods_img_url($res[$k]['list_pic_url']);
            }
            $res[$k]['retail_price'] = sprintf("%.2f", $this->model->get_final_price($v['goods_id'], 1, $user_id, true, array()));
        }
        $sql = "SELECT COUNT(goods_id) AS num FROM ecs_goods WHERE brand_id = {$id}";
        $num = $this->model->getORM()->queryRows($sql);
        return ['data' => $data[0], 'goodsList' => $res, "pagetotal" => ceil($num[0]['num'] / $page_size)];
    }
}
