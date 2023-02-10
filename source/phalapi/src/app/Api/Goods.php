<?php

namespace App\Api;

use App\Lib\Goods as GoodsLib;
use App\Model\Brand as BrandModel;
use App\Model\Cart as CartModel;
use App\Model\Goods as GoodsModel;
use App\Model\Order as OrderModel;
use PhalApi\Api;
use App\Model\User as UserModel;

/**
 * 商品相关接口服务
 * @package App\Api
 */
class Goods extends Api
{
    protected $model;
    public function __construct()
    {
        $this->model = new GoodsModel();
        $this->brand = new BrandModel();
        $this->cart = new CartModel();
        $this->user = new UserModel();

        $this->lib = new GoodsLib();
    }

    public function getRules()
    {
        return array(
            'goodsDetailActionApi' => array(
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'id'),
                'user_id' => array('name' => 'user_id', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'pages' => array('name' => 'pages', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => '页数'),
                'platform' => array('name' => 'platform', 'require' => true, 'min' => 1, 'max' => 10, 'desc' => '来源')
            ),
            'GoodsDiscountApi' => array(
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'id'),
                'user_id' => array('name' => 'user_id', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
                'pages' => array('name' => 'pages', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => '页数'),
            ),
            'newgoodsGoodsListApi' => array(
                'order' => array('name' => 'order', 'require' => false, 'desc' => '排序'),
                'type' => array('name' => 'type', 'require' => false, 'desc' => '排序类型'),
                'page' => array('name' => 'page', 'require' => true, 'desc' => '页数'),
                'isHot' => array('name' => 'isHot', 'require' => false, 'desc' => '销量'),
                'isNew' => array('name' => 'isNew', 'require' => false, 'desc' => '新品'),
                'spike' => array("name" => "spike", "require" => false, "desc" => "秒杀商品"),
                'pintuan' => array("name" => 'pintuan', "require" => false, "desc" => "拼团商品"),
                'user_id' => array('name' => 'user_id', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => 'user_id'),
            ),
            'integralgoodsGoodsList' => array(
                'page' => array('name' => 'page', 'require' => true, 'desc' => '页数'),
            ),
            'SuperPackageApi' => array(
                'page' => array('name' => 'page', 'require' => true, 'desc' => '页数'),
            ),
            'getGoodsintegral' => array(
                'goods_id' => array('name' => 'goods_id', 'require' => true, 'desc' => '商品id'),
            ),
            'getPayPointsApi' => array(
                'user_id' => array("name" => "user_id", "require" => false, "desc" => "用户id"),
                'goods_id' => array("name" => "goods_id", "require" => true, "desc" => "商品id"),
            ),
            "userjudgeCommentListApi" => array(
                'goods_id' => array("name" => "goods_id", "require" => true, "desc" => "商品id"),
                'user_id' => array("name" => "user_id", "require" => false, "desc" => "用户id"),
                'pages' => array("name" => "pages", "require" => true, "desc" => "页数"),
            ),
            //            'goodsSpikeActionApi' => array(
            //                'spike' => array("name" => "spike", "require" => true, "desc" => "秒杀商品"),
            //                'page' => array("name" => "page","require" => true, "desc" => "分页"),
            //            ),
            "getGoodsPintuanApi" => array(
                "goods_id" => array("name" => "goods_id", "require" => true, "desc" => "商品id"),
                "order_id" => array("name" => "order_id", "require" => false, "desc" => "拼单的id"),
                'user_id' => array('name' => 'user_id', 'require' => true, 'desc' => '用户id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
            ),
            "getGoodSpikeApi" => array(
                "goods_id" => array("name" => "goods_id", "require" => true, "desc" => "商品id"),
                "order_id" => array("name" => "order_id", "require" => false, "desc" => "拼单的id"),
                'user_id' => array('name' => 'user_id', 'require' => true, 'desc' => '用户id'),
                'token' => array('name' => 'token', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => 'TOKEN'),
            ),
            "pintuanListApi" => array(
                "goods_id" => array("name" => "goods_id", "require" => true, "desc" => "商品ID"),
                "page" => array("name" => "page", "require" => true, "desc" => "页数"),
            ),
            "pintuanGoodsListApi" => array(
                //                "goods_id" => array("name"=>"goods_id","require"=>true,"desc"=>"商品ID"),
                "page" => array("name" => "page", "require" => true, "desc" => "页数"),
            ),
            "spikeGoodsListApi" => array(
                "page" => array("name" => "page", "require" => true, "desc" => "页数"),
            ),
            "hotGoodsListApi" => array(
                "page" => array("name" => "page", "require" => true, "desc" => "页数"),
            ),
            "miaoshaGoodsListApi" => array(
                "page" => array("name" => "page", "require" => true, "desc" => "页数"),
            )
        );
    }

    //    /**
    //     * 秒杀商品详情
    //     */
    //    public function goodsSpikeActionApi(){
    //        $page = $this -> page;
    //        $result = $this -> model -> goodsSpike($page);
    //        return array('data' => $result);
    //    }

    /**
     * 商品优惠
     * @desc 订单可享受的折扣优惠
     */
    public  function GoodsDiscountApi()
    {
        $user_id =  intval($this->user_id);
        $goods_id = intval($this->goods_id);
        $this->order_Model = new OrderModel();
        $data = $this->model->GoodsDiscountApi($user_id, $goods_id);

        return $data;
    }

    /**
     * 商品详情
     * @desc 商品详情
     * @return array
     */
    public function goodsDetailActionApi()
    {

        $goods_id = intval($this->goods_id);
        $user_id = intval($this->user_id);
        $platform = $this->platform;
        $goods_data = array();
        /**
    获取商品图片
         **/
        $image_model = \PhalApi\DI()->notorm->goods_gallery;
        $image_data = $image_model->where('goods_id', $goods_id)->order('img_id ASC')->fetchAll(); //查询语句 //查询语句

        $cart_goods = $this->cart->get_cart_goods($user_id);
        $visit = $this->user->get_user_visit($goods_id, $user_id, $platform);
        if ($cart_goods) {
            $goods_data['allnumber'] = intval($cart_goods['total']['goods_count']);
        } else {
            $goods_data['allnumber'] = 0;
        }

        $goods_image = array();
        $imgSrc = array();
        foreach ($image_data as $key => $item) {
            $item['img_url'] = goods_img_url($item['img_url']);
            $item['img_original'] = goods_img_url($item['img_original']);
            $goods_image[] = array(
                'img_id' => $item['img_id'],
                'img_desc' => $item['img_desc'],
                'img_url' => $item['img_url'],
                'img_original' => $item['img_original'],
            );
            $imgSrc[] = $item['img_url'];
        }

        $goods_data['info'] = $this->model->getGoodsInfo($goods_id, $user_id);

        $product_id = $this->model->getGoodsProductId($goods_id);

        $goods_data['info']['productId'] = $product_id;
        $attr = $this->model->get_goods_properties($goods_id);

        if (count($attr['spe']) > 0) {
            $goods_data['activeSkuValId'] = '';
            $activeSkuValId = array();
            foreach ($attr['spe'] as $key => $item) {
                if ($item['attr_type'] == 1) {
                    foreach ($item['values'] as $i => $j) {
                        if ($i == 0) {
                            $activeSkuValId[] = $j['id'];
                        }
                    }
                }
            }
            $goods_data['activeSkuValId'] = $activeSkuValId;
        } else {
            $goods_data['activeSkuValId'] = 'null';
        }

        $goods_data['attrlist'] = $attr['pro'];
        $goods_data['goodsSkuNameList'] = $attr['gg1']; // 物品配置
        $goods_data['productLookList'] = $attr['productLookList']; // 都在看
        $goods_data['defaultSkuStore'] = $goods_data['info']['goods_number']; // 展示的库存
        $data = \PhalApi\DI()->config->get('app');
        $goods_data['url_fenxiang'] = $data['host_url'] . "h5/apiShop/goods/main?id=" . $goods_id . "&parent_id=" . $user_id; // 分享链接

        /**
        获取商品配置信息
         */
        $pz = $this->lib->getGoodsSkuList($goods_id, $user_id);
        $goods_data['goodsSkuList'] = $pz;

        /**
         * 获取当前的默认配置信息
         */
        $goods_data['defaultSkuData'] = $this->model->genDefauleData($goods_data['goodsSkuNameList'], $user_id);

        /**
        获取商品评论
         **/
        // $dis_model = \PhalApi\DI()->notorm->comment;
        // $dis_count = $dis_model->where('id_value',$goods_id)->count();
        // $goods_data['info']['dis_count'] = $dis_count;

        // $goods_data['goods_image'] = $goods_image;
        // $goods_data['gallery'] = $imgSrc;
        // if($goods_data['gallery'] == null){ // 无展示图片 使用
        //     if(is_url($goods_data['info']['goods_img']) == false){
        //         $goods_data['info']['goods_img'] = goods_img_url($goods_data['info']['goods_img']);
        //     }
        //     $goods_data['gallery'][] = $goods_data['info']['goods_img'];
        // }

        $dis_model = \PhalApi\DI()->notorm->comment;
        $dis_count = $dis_model->where('id_value', $goods_id);

        $coun = $dis_model->where('id_value', $goods_id)->count();

        $sum = $dis_model->sum('comment_rank');

        $score = substr($sum / $coun, 0, 3);

        $goods_data['info']['score'] = $score;

        $goods_data['info']['dis_count'] = $dis_count;

        $pagesize = 5;

        $currentpage = intval($this->pages);

        $sumpage = ceil($coun / $pagesize); //共多少页

        $goods_data['info']['sumpage'] = $sumpage;

        $start = ($currentpage - 1) * $pagesize; //起始位置

        if ($start < 0) {
            $start = 0;
        }

        $result = $this->model->spikeGoodsSum($goods_id);
        if ($result['active'] == 'true') {
            $goods_data['info']['shop_price'] = $result['shop_price'];
            $goods_data['info']['time'] = $result;
        }

        $dis_count = $dis_model->where('id_value', $goods_id)->limit($start, $pagesize);

        $goods_data['goods_image'] = $goods_image;
        $goods_data['gallery'] = $imgSrc;
        if ($goods_data['gallery'] == null) { // 无展示图片 使用
            $goods_data['info']['goods_img'] = goods_img_url($goods_data['info']['goods_img']);
            $goods_data['gallery'][] = $goods_data['info']['goods_img'];
        }

        /**
        获取商品的品牌
         */
        $brand = $this->brand->getGoodsBrand($goods_id);
        $goods_data['brand'] = $brand;

        //客服电话
        $goods_data['kefu_tel']  = $this->model->kefu_tel();

        //客服qq
        $goods_data['kefu_qq'] = $this->user->getQq();
        /**
        获取商品的收藏状态
         */
        $collect = $this->model->getGoodsCollect($goods_id, $user_id);
        $goods_data['collected'] = $collect;

        /**
         * 获取商品拼团&秒杀状态
         */
        $active_status = $this->model->getActiveStatus($goods_id);
        $goods_data["active"] = $active_status;

        /**
         * 获取拼团的订单信息
         */

        if ($active_status['pintuan']['is_pintuan'] == '1') {
            $pt_data = $this->model->getPtGoodsOrder($goods_id, 'goods');
            $goods_data['pt_info']["pt_list"] = $pt_data['pt_list'];
            $goods_data['pt_info']["pt_num"] = $pt_data["num"];
        }
        $goods_data['issue'] = array(
            array("question" => "购买运费如何收取？", "answer" => "单笔订单金额（不含运费）满88元免邮费；不满88元，每单收取10元运费。（港澳台地区除外)"),
            array("question" => "使用什么快递发货？", "answer" => "默认使用顺丰快递发货（个别商品使用其他快递），配送范围覆盖全国大部分地区（港澳台地区除外）"),
            array("question" => "如何申请退货？", "answer" => "自收到商品之日起7日内，顾客可申请无忧退货，退款将原路返还，不同的银行处理时间不同"),
            array("question" => "如何开具发票？", "answer" => "如需开具普通发票，请在下单时选择“我要开发票”并填写相关信息"),
        );
        $kkk = $this->model->goods_number($user_id);
        $goods_data["allnumber"] = $kkk['count(*)'];
        $goods_data['hitCounts'] = $visit;

        // 查看下载文件
        $goods_data['file_list'] = $this->model->getGoodsFileList($goods_id);

        return $goods_data;
    }

    /**
     * 新品首发
     * @desc 新品首发展示
     */
    public function newgoodsGoodsListApi()
    {
        $order = $this->order;
        $type = $this->type;
        $isHot = $this->isHot;
        $page = intval($this->page);
        $spike = $this->spike;
        $isNew = $this->isNew;
        $pintuan = $this->pintuan;
        $user_id = intval($this->user_id);
        $res = $this->model->getNewGoodsList($order, $page, $isHot, $spike, $pintuan, $isNew, $type, $user_id);

        return array('data' => $res, 'pagetotal' => 2);
    }
    //获取商品积分
    function  getGoodsintegral()
    {
        $goods_id = $this->goods_id;
        $data = $this->model->getGoodsintegral($goods_id);
        return $data;
    }

    /**
     * 兑换积分商品
     * @desc用户能否兑换积分商品
     * @return array
     */
    function  getPayPointsApi()
    {
        $user_id  = $this->user_id;
        $goods_id = $this->goods_id;
        $data = $this->model->getPayPointsApi($user_id, $goods_id);

        return array('info' => $data);
    }


    /**
     * 超值礼包列表
     * @desc超值礼包列表
     * @return array
     */
    function  SuperPackageApi()
    {
        $page = $this->page;
        $data = $this->model->GetSuperPackageApi($page);
        return $data;
    }

    /**
     * 积分商品列表
     * @desc积分商品列表
     * @return array
     */
    function integralgoodsGoodsList()
    {
        $page = $this->page;
        $data = $this->model->integralgoods($page);
        return $data;
    }
    /**
     * 商品评价
     * @desc 商品评价展示
     */
    public function userjudgeCommentListApi()
    {
        $goods_id = intval($this->goods_id);
        $user_id = intval($this->user_id);
        $page = intval($this->pages);
        $comment = new \App\Model\Comment();
        $data = $comment->getGoodsComment($goods_id, $page);

        return $data;
    }

    /**
     * 随机购
     * @desc 随机购
     */
    public function casualPurchaseApi()
    {

        $goods_data = $this->model->getGoodsCasualInfo();
        return $goods_data;
    }

    /**
     * @return mixed限时秒杀
     * @throws \PhalApi\Exception\BadRequestException
     */
    public function getGoodSpikeApi()
    {
        $goods_id = $this->goods_id;
        $token = $this->token;
        $this->checkLogin();
        $spike_data = $this->model->getGoodSpikeShop($goods_id);
        return $spike_data;
    }

    /**
     * 拼团商品购买
     * @desc 拼团商品购买
     */
    public function getGoodsPintuanApi()
    {
        $goods_id = $this->goods_id;
        $order_id = $this->order_id;
        $token = $this->token;
        $this->checkLogin();

        $pt_data = $this->model->getGoodsPintuan($goods_id, $order_id);
        return $pt_data;
    }

    /**
     * 拼团商品列表
     * @desc 拼团商品列表
     */
    public function pintuanListApi()
    {
        $goods_id = $this->goods_id;
        $page = $this->page;

        $data = $this->model->getPtGoodsOrder($goods_id, "list", $page);

        return $data;
    }

    public function vipPriceApi($user_id, $goods_id)
    {
        $data = $this->model->vipPriceModel($user_id, $goods_id);
    }

    /**
     * @return array
     * 首页拼团跳转的页面，取得所有拼团的商品
     */
    public function pintuanGoodsListApi()
    {
        $page = $this->page;

        $data = $this->model->getPtGoodsList($page);

        return $data;
    }


    /**
     * @return array
     * 首页新品跳转的页面，取得所有新品
     */
    public function spikeGoodsListApi()
    {
        $page = $this->page;

        $data = $this->model->getSpikeGoodsList($page);

        return $data;
    }

    /**
     * @return array
     * 人气推荐商品
     */
    public function hotGoodsListApi()
    {
        $page = $this->page;

        $data = $this->model->gethotGoodsList($page);

        return $data;
    }

    public function miaoshaGoodsListApi()
    {
        $page = $this->page;

        $data = $this->model->getMiaoShaGoodsList($page);

        return $data;
    }
}
