<?php

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

class Comment extends NotORM
{

    public function getGoodsDetail($order_id, $goods_id)
    {
        // 商品评价时的商品信息
        $sql = "SELECT a.goods_name,a.goods_number AS number,a.goods_price AS retail_price,a.goods_attr AS goods_specifition_name_value,a.goods_id AS id,b.goods_thumb AS list_pic_url
                FROM ecs_order_goods AS a
                LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                WHERE a.order_id =  {$order_id} AND a.goods_id IN ({$goods_id})";
        $data = $this->getORM()->queryRows($sql);
        $data[0]['list_pic_url'] = goods_img_url($data[0]['list_pic_url']);
        return $data[0];
    }

    public function addGoodsComment($user_id, $goods_id, $order_id, $comment, $comment_rank, $anonymous)
    {
        // 添加评论
        $sql = "SELECT email,user_name FROM ecs_users WHERE user_id={$user_id} ";
        $data = $this->getORM()->queryRows($sql);
        if ($anonymous == true) {
            // 匿名
            $sql = "INSERT INTO ecs_comment SET comment_type='0',id_value={$goods_id},email='" . $data[0]['email'] . "',content='" . $comment . "',comment_rank={$comment_rank},add_time='" . time() . "',ip_address='" . $_SERVER["REMOTE_ADDR"] . "',status='0',user_id='0',order_id={$order_id},app_user_id={$user_id} ";
            $this->getORM()->queryRows($sql);
            return true;
        }
        $sql = "INSERT INTO ecs_comment SET comment_type='0',id_value={$goods_id},email='" . $data[0]['email'] . "',user_name='" . $data[0]['user_name'] . "',content='" . $comment . "',comment_rank={$comment_rank},add_time='" . time() . "',ip_address='" . $_SERVER["REMOTE_ADDR"] . "',status='0',user_id={$user_id},order_id={$order_id},app_user_id={$user_id} ";
        $this->getORM()->queryRows($sql);
        return true;
    }

    public function getGoodsComment($goods_id, $page)
    {
        $page_size = pagesize();
        $page = ($page - 1) * $page_size;
        // 获取当前商品的评论信息
        $sql = "select avg(comment_rank) as score from ecs_comment where id_value = " . $goods_id . " and comment_rank >0"; // 获取当前商品的总平均分
        $score = $this->getORM()->queryRow($sql);
        $sql = "select * from ecs_comment where id_value = " . $goods_id . " and status = '1' order by add_time desc limit {$page},{$page_size} ";
        $comment_data = $this->getORM()->queryRows($sql);

        $sql = "select count(id_value) as num from ecs_comment where id_value = " . $goods_id . "";
        $comm_count = $this->getORM()->queryRow($sql);

        $page_total = ceil($comm_count['num'] / $page_size);

        if (empty($comment_data)) { // 无数据时
            return array("score" => 0, "data" => [], "page_total" => 1);
        }
        return array("score" => round($score['score'], 1), "data" => $comment_data, "page_total" => $page_total);
    }
}
