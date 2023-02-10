<?php

namespace App\Model;

use App\Model\Goods as Goods;
use App\Model\ShopConfig as ShopConfigModel;
use PhalApi\Model\NotORMModel as NotORM;

class Category extends NotORM
{
    protected function getTableName($id)
    {
        return 'category';
    }

    protected function getTableKey($table)
    {
        return 'cat_id';
    }

    public function getCategoryList($cat_id)
    {

        $this->goods_model = new Goods();

        //$data = $this->getORM()->where(array('parent_id' => '0', 'is_show' => 1, 'show_in_nav' => 1))->fetchAll();
        //        var_dump($data);exit;
$sql = "SELECT * FROM ecs_category WHERE parent_id = 0 AND is_show = 1 AND show_in_nav = 1 ORDER BY sort_order asc";
        $data = $this->getORM()->queryRows($sql);
        $return = array();
        foreach ($data as $key => $item) {
            $return['cat_list'][] = array(
                'cat_name' => $item['cat_name'],
                'cat_id' => $item['cat_id'],
                 'sort_order' => $item['sort_order']
            );
            if ($item['cate_img'] != '') {
                $item['cat_list']['pic'] = goods_img_url($item['cate_img']);
            } else {
                $item['cat_list']['pic'] = '';
            }
        }
        if ($cat_id == '') {
            $cat_id = $data[0]['cat_id'];
        }

        $tmp = $this->getORM()->where(array('parent_id' => $cat_id))->fetchAll();
        foreach ($tmp as $i => $j) {
            $tmp = $this->getORM()->where(array('parent_id' => $item['cat_id']))->fetchAll();
            $return['cat_item']['child_menu'][] = array(
                'cat_name' => $j['cat_name'],
                'cat_id' => $j['cat_id'],
                'littleTitle' => $j['cat_name'],
                'pic' => $j['cate_img']
            );
            if ($j['cate_img'] != '') {
                $return['cat_item']['pic'] = goods_img_url($j['cate_img']);
            } else {
                $return['cat_item']['pic'] = default_category_img();
            }
        }

        $tmp = $this->getORM()->where(array('cat_id' => $cat_id))->fetchOne();

        if ($tmp['cate_img'] == null) {
            $tmp['cate_img'] = default_category_banner();
        }
        $tmp['cate_img'] = goods_img_url($tmp['cate_img']);

        $return['this_cat'] = array(
            'cat_name' => $tmp['cat_name'],
            'cat_id' => $tmp['cat_id'],
            'banner_url' => $tmp['cate_img'],
        );
        if ($tmp['cate_img'] != '') {
            $return['this_cat']['pic'] = $tmp['cate_img'];
        }

        return $return;
    }

    public function getCategorySecond($cat_id)
    {
        // 获取二级分类
        $sql = "SELECT * FROM ecs_category WHERE parent_id=$cat_id and is_show = 1";
        $banner_url = $this->getORM()->where("cat_id", $cat_id)->select("cate_img")->fetchOne();
        $data = $this->getORM()->queryRows($sql);

        if ($banner_url['cate_img'] == null) {
            $banner_url = default_category_banner();
        } else {
            $banner_url['banner_url'] = goods_img_url($banner_url['cate_img']);
        }

        foreach ($data as $k => $v) {
            if ($data[$k]['cate_img'] == '') {
                $data[$k]['cate_img'] = default_category_img();
                $data[$k]['pic'] = $data[$k]['cate_img'];
                continue;
            }
            $data[$k]['pic'] = goods_img_url($data[$k]['cate_img']);
        }

        $return = ["data" => $data, "banner_url" => $banner_url];
        return $return;
    }

    public function getCategoryListNavList($cat_id)
    {
        // 二级分类导航
        $sql = "SELECT cat_id AS id,cat_name AS name,cat_desc AS front_desc,parent_id FROM ecs_category WHERE cat_id=$cat_id and is_show = 1";
        $now_nav = $this->getORM()->queryRows($sql);
        $nav_list = $this->getORM()->where(array("parent_id" => $now_nav[0]['parent_id'], "is_show" => 1))->select("cat_id as id,cat_name as name")->fetchAll();

        //        $nav_list = $this->getORM()->where("parent_id", $now_nav[0]['parent_id'])->select("cat_id as id,cat_name as name")->fetchAll();
        return ['currentNav' => $now_nav[0], "navData" => $nav_list];
    }

    public function getCategoryListGoods($cat_id, $page, $order, $type, $user_id)
    {
        $Shop_config_model = new ShopConfigModel();
        $this->goods_model = new Goods();
        $page_size = getConfigPageSize();
        $offset = ($page - 1) * $page_size;
        if ($type == 'price') {
            $sort = 'retail_price';
        } else if ($type == 'sales') {
            $sort = 'virtual_sales';
        } else {
            $sort = 'sort_order';
        }
        $sql = "SELECT goods_id AS id,goods_thumb AS list_pic_url,goods_name AS name,shop_price AS retail_price,market_price,virtual_sales 
        FROM ecs_goods 
        WHERE 
        cat_id={$cat_id} and is_on_sale = 1 and is_delete =0 and goods_number>0 order by {$sort} {$order} LIMIT {$offset},{$page_size}";
        $goods_list = $this->getORM()->queryRows($sql);
        $show_marketprice = $Shop_config_model->get_show_marketprice();
        foreach ($goods_list as $k => $v){
            $goods_list[$k]['list_pic_url'] = goods_img_url($v['list_pic_url']);
            $goods_list[$k]["show_marketprice"] = $show_marketprice;
            $goods_list[$k]['retail_price'] = $v['retail_price'];
        }

            $sql = "SELECT * FROM ecs_goods_cat WHERE cat_id = '$cat_id'";
            $res = $this->getORM()->queryRows($sql);
            foreach ($res as $key => $value) {
                $mysql = "SELECT goods_id AS id,goods_thumb AS list_pic_url,goods_name AS name,shop_price AS retail_price,market_price,virtual_sales 
                            FROM ecs_goods 
                            WHERE 
                            goods_id = '" . $value['goods_id'] . "' and is_delete = 0 order by {$sort} {$order} LIMIT {$offset},{$page_size}";

                $result = $this->getORM()->queryRow($mysql);
                if ($result) {
                    $result['list_pic_url'] = goods_img_url($result['list_pic_url']);
                    $result["show_marketprice"] = $show_marketprice;
                    $result['retail_price'] = $result['retail_price'];
                    //$result['retail_price'] = sprintf("%.2f", $this->goods_model->get_final_price($value['id'], 1, $user_id, true, array()));
                    $goods_list[] = $result;
                }
            }

        $sql = "SELECT cat_id AS id,cat_name AS name,cat_desc AS front_desc,parent_id FROM ecs_category WHERE cat_id=$cat_id and is_show = 1";
        $now_nav = $this->getORM()->queryRows($sql);
        $sql = "SELECT COUNT(goods_id) AS num FROM ecs_goods WHERE cat_id={$cat_id}";
        $page_total = $this->getORM()->queryRows($sql);
        return ['currentNav' => $now_nav[0], "data" => $goods_list, "pagetotal" => ceil($page_total[0]['num'] / $page_size)];
    }

    public function getIndexTop()
    {
        // 获取首页的顶部品牌展示

        $data = $this->getORM()->where("is_top", 1)->select("cat_id as id,cat_name,cate_img")->limit(11)->fetchAll();
        foreach ($data as $k => $v) {
            if ($data[$k]['cate_img'] == '') { // 当没有设置时使用默认的图标
                $data[$k]['cate_img'] = default_category_img();
                $data[$k]['type'] = 'cat';
                continue;
            }
            if ($k <= 4) {
                $data1[$k] = $data[$k];

                $data1[$k]['cate_img'] = goods_img_url($data[$k]['cate_img']);
            } else {
                $data2[$k] = $data[$k];
                $data2[$k]['cate_img'] = goods_img_url($data[$k]['cate_img']);
            }

            //            $data[$k]['cate_img'] = goods_img_url($data[$k]['cate_img']);

            $data[$k]['type'] = 'cat';
        }
        $select_data =  array(
            'data1' => $data1,
            'data2' => $data2
        );
        return $select_data;
    }
    public function getNewCategory()
    { // 好物
        $category = $this->getORM()->where("is_goods", 1)->fetchAll();
        $this->goods_model = new Goods();
        $data = array();
        foreach ($category as $k => $v) {
            $goods_list = $this->goods_model->getORM()->where("cat_id", $category[$k]['cat_id'])->limit(5)->fetchAll();
            foreach ($goods_list as $key => $v) {
                $goods_list[$key]['goods_thumb'] = goods_img_url($goods_list[$key]['goods_thumb']);
            }
            $data[$k] = ['type' => "cat", "code" => 1, "name" => $category[$k]['cat_name'], "goodsList" => $goods_list, "id" => $category[$k]['cat_id']];
        }
        return $data;
    }
}
