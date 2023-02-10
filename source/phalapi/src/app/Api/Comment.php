<?php

namespace App\Api;

use App\Model\Comment as CommentModel;
use PhalApi\Api;

/**
 * 商品评论接口服务
 * @package App\Api
 */
class Comment extends Api
{
    public function __construct()
    {
        $this->model = new CommentModel();
    }
    public function getRules()
    {
        return array(
            'goodsdetailGetApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '用户名'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'goodsId' => array('name' => 'goodsId', 'require' => true, 'min' => 1, 'desc' => '商品的ID'),
                'orderId' => array('name' => 'orderId', 'require' => true, 'min' => 1, 'desc' => '订单的ID'),
            ),
            "addGoodsCommentApi" => array(
                'orderId' => array('name' => 'orderId', 'require' => true, 'min' => 1, 'desc' => "订单id"),
                'goodsId' => array('name' => 'goodsId', 'require' => true, 'min' => 1, 'desc' => '商品ID'),
                'comment' => array('name' => 'comment', 'require' => true, 'min' => 1, 'desc' => '评论内容'),
                'comment_rank' => array('name' => 'comment_rank', 'require' => true, 'min' => 1, 'desc' => '评论等级'),
                'anonymous' => array('name' => 'anonymous', 'require' => false, 'desc' => '是否匿名'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 32, 'desc' => 'token'),
                'user_id' => array('name' => 'user_id', 'require' => true, 'min' => 1, 'desc' => '用户ID'),
            ),
        );
    }

    /**
     * 评论的商品详情
     * @desc 商品评论
     */
    public function goodsdetailGetApi()
    {
        $order_id = intval($this->orderId);
        $goods_id = intval($this->goodsId);
        $user_id = intval($this->user_id);
        $token = $this->token; // 密码参数
        $this->checkLogin();

        $data = $this->model->getGoodsDetail($order_id, $goods_id);

        return $data;
    }

    /**
     * 对商品的评论
     * @desc 评论
     */
    public function addGoodsCommentApi()
    {
        $goods_id = intval($this->goodsId);
        $order_id = $this->orderId;
        $comment = $this->comment;
        $comment_rank = $this->comment_rank;
        $anonymous = $this->anonymous;
        $user_id = intval($this->user_id);
        $token = $this->token; // 密码参数
        $this->checkLogin();

        $res = $this->model->addGoodsComment($user_id, $goods_id, $order_id, $comment, $comment_rank, $anonymous);
        return array('status' => $res);
    }
}
