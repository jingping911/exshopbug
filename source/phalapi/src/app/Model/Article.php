<?php

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

class Article extends NotORM
{
    protected function getTableKey($table)
    {
        return 'article_id';
    }

    public function getRecommendArticleList($page)
    {
        $page_size = getConfigPageSize();
        $offset = ($page - 1) * $page_size;

        $sql = "select * from ecs_article where is_open='1' and is_recommend='1' order by article_type,add_time desc limit {$offset},{$page_size} ";

        $articles = $this->getORM()->queryAll($sql);
        $sql = "select count(article_id) as total from ecs_article where is_open='1' and is_recommend='1' ";
        $page_total = $this->getORM()->queryAll($sql);

        $page_total = $page_total[0]['total'];
        if ($articles) {
            foreach ($articles as $k => $article) {
                $articles[$k]['id'] = $article['article_id'];
                $article[$k]['scene_pic_url'] = goods_img_url($article['article_pic']);
            }
            return [
                'data' => $articles,
                'total' => $page_total,
                'page' => $page,
            ];
        } else {
            return [
                'data' => [],
                'total' => 0,
                'page' => $page,
            ];
        }
    }

    public function getArticleDetail($article_id)
    {
        return $this->get($article_id);
    }

    public function getTopArticle()
    {
        $data = $this->getORM()->where("is_index", 1)->where("is_open", 1)->limit(5)->fetchAll();
        foreach ($data as $k => $v) {
            if ($data[$k]['article_pic'] == '') {
                $data[$k]['article_pic'] = default_article_img();
            } else if (is_url($data[$k]['article_pic']) == false) {
                $data[$k]['article_pic'] = goods_img_url($data[$k]['article_pic']);
            }
        }
        return $data;
    }

    public function getTopicArticle($page)
    {
        $page_size = getConfigPageSize();
        $offset = ($page - 1) * $page_size;

        $sql = "SELECT article_id,title,article_pic FROM ecs_article WHERE is_open='1' AND is_index='1' ORDER BY add_time DESC LIMIT {$offset},{$page_size}";
        $data = $this->getORM()->queryRows($sql);

        $page_total = $this->getORM()->select("count(article_id) as total")->where("is_open", 1)->where("is_index", 1)->fetchAll();
        $page_total = $page_total[0]['total'];

        if ($data) {
            foreach ($data as $k => $v) {
                if ($data[$k]['article_pic'] == '') {
                    $data[$k]['article_pic'] = default_article_img();
                }
                $data[$k]['scene_pic_url'] = goods_img_url($data[$k]['article_pic']);

                $data[$k]['id'] = $data[$k]['article_id'];
            }
            return array(
                "data" => $data,
                "total" => ceil(($page_total) / $page_size),
                "page" => $page,
            );
        } else {
            return array(
                "data" => [],
                "total" => 0,
                "page" => $page,
            );
        }
    }

    public function getTopicDetail($id)
    {
        $data = $this->getORM()->where("article_id", $id)->select("content")->fetchOne();
        if ($data) {
            return $data;
        } else {
            return ['无效的文章ID'];
        }
    }

    public function getArticleGoodsListModel($id)
    {
        $sql = "SELECT goods_id FROM ecs_goods_article WHERE article_id = '$id'";
        $data = $this->getORM()->queryAll($sql);
        foreach ($data as $key => $value) {
            $sql = "SELECT * FROM ecs_goods WHERE goods_id = '".$value['goods_id']."'";
            $result = $this->getORM()->queryRow($sql);
            $data[$key]['goods_thumb'] = empty($result['goods_thumb']) ? default_category_img() : goods_img_url($result['goods_thumb']);
            $data[$key]['goods_name'] = $result['goods_name'];
            if ($result['promote_end_date'] > time()){
                $data[$key]['promote_price'] = $result['promote_price'];
            }else {
                $data[$key]['promote_price'] = false;
            }
            $data[$key]['shop_price'] = $result['shop_price'];
        }
        return $data;
    }
}
