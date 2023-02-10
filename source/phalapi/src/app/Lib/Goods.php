<?php
namespace App\Lib;

use App\Model\Goods as GoodsModel;

class Goods
{
    public function __construct()
    {
        $this->goods = new GoodsModel();
    }

    public function searchGoodsList($words, $order,$page,$num)
    {
        $data = $this->goods->searchGoods($words, $order,$page,$num,'');
        return $data;
    }

    public function getNewGoodsList($user_id)
    {
        $data = $this->goods->getNewGoods($user_id);
        return $data;
    }

    //秒杀商品
    public function getSpikeGoodList(){
        $data = $this->goods->getSpikeGoods();
        return $data;
    }
    //超值礼包
    public function SuperPackageApi(){
        $data = $this->goods->indexSuperPackageApi();
        return $data;
    }
    //积分兑换
    public function integralgoodsGoodsList(){
        $data = $this->goods->indexintegralgoods();
        return $data;
    }

    //拼团商品
    public function getPinTuanGoodsList($user_id){
        $data = $this->goods->getPinTuanGoods($user_id);
        return $data;
    }

    public function getHotGoodsList($user_id)
    {
        $data = $this->goods->getHotGoods($user_id);
        return $data;
    }

    public function getappdownload()
    {
        $data = $this->goods->getappdownload();
        return $data;
    }

    public function getGoodsSkuList($goods_id, $user_id)
    {
        // 返回商品数据配置
        $goods_attr = $this->goods->checkGoodsAttr($goods_id);
        $goods_attr_array = explode("|", $goods_attr[0]['goods_attr']);

        if (count($goods_attr_array) > 1) { // 组合属性
            $goods_sku = array(); // 存放属性
            $goods_sku_array = array(); // 存放合并数据
            $goods_return = array(); // 最后返回的数据

            if (count($goods_attr_array) > 1) {

                $pz = array(); // 不同参数的配置信息
                $return = array(); // 需要返回的数据

                foreach ($goods_attr as $k => $v) {
                    $goods_attr_array = explode("|", $goods_attr[$k]['goods_attr']); // 分离商品属性的id

                    foreach ($goods_attr_array as $i => $j) { // 获得当前的属性的信息
                        $data = $this->goods->getGoodsSku($goods_id, $j, 'y', $goods_attr[$k]['product_id']);

                        foreach ($data as $j => $v) {
                            if ($data[$k]['attr_type'] != 2) { // 去除可多选
                                $pz[$data[$j]['goodsCode']]['goodsCode'] = $data[$j]['goodsCode'];
                                $pz[$data[$j]['goodsCode']]['goodsId'] = $data[$j]['goodsId'];
                                $pz[$data[$j]['goodsCode']]['price'] = $data[$j]['price'];
                                $pz[$data[$j]['goodsCode']]['product_id'] = $data[$j]['product_id'];
                                $pz[$data[$j]['goodsCode']]['skuNameIds'] = $data[$j]['skuNameIds'];
                                $pz[$data[$j]['goodsCode']]['skuNames'] = $data[$j]['skuNames'];
                                $pz[$data[$j]['goodsCode']]['skuValIds'] = $data[$j]['skuValIds'];
                                $pz[$data[$j]['goodsCode']]['skuVals'] = $data[$j]['skuVals'];
                                $pz[$data[$j]['goodsCode']]['stockNum'] = $data[$j]['stockNum'];
                                $pz[$data[$j]['goodsCode']]['attr_price'] = $data[$j]['attr_price'];
                            }

                        }

                        foreach ($pz as $m => $n) {
                            $return[] = $pz[$m];
                        }
                        $goods_sku = $return;

                    }

                    // 将数据进行处理
                    for ($i = 0; $i < count($goods_sku); $i++) {

                        if ($goods_sku[$i]['product_id'] == $goods_attr[$k]['product_id']) {

                            if ($k == 0) {
                                $goods_sku_array[$goods_sku[$i]['goodsCode']]['is_default'] = 'true';
                            } else {
                                $goods_sku_array[$goods_sku[$i]['goodsCode']]['is_default'] = 'false';
                            }
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['goodsCode'] = $goods_sku[$i]['goodsCode'];
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['goodsId'] = $goods_sku[$i]['goodsId'];
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['price'] = $this->price($goods_sku, $goods_attr[$k]['product_id'], $goods_id, $user_id);
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['product_id'] = $goods_sku[$i]['product_id'];
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['skuNameIds'] = $this->skuNameIds($goods_sku, $goods_attr[$k]['product_id']);
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['skuNames'] = $this->skuNames($goods_sku, $goods_attr[$k]['product_id']);
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['skuValIds'] = $this->skuValIds($goods_sku, $goods_attr[$k]['product_id']);
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['skuVals'] = $this->skuVals($goods_sku, $goods_attr[$k]['product_id']);
                            $goods_sku_array[$goods_sku[$i]['goodsCode']]['stockNum'] = $goods_sku[$i]['stockNum'];

                        }

                    }

                }
                foreach ($goods_sku_array as $a => $b) {
                    $goods_return[] = $goods_sku_array[$a];
                }
                return $goods_return;
            }
        } else {
            // 配置只有一种的情况
            $data = $this->goods->getGoodsSku($goods_id);
            $pz = array(); // 不同参数的配置信息
            $return = array(); // 需要返回的数据

            foreach ($data as $k => $v) {
                if ($data[$k]['attr_type'] != 2) { // 去除可多选
                    if ($data[$k]['skuNameIds'] == '') {
                        $data[$k]['skuNameIds'] = 'null';
                    } else {
                        $data[$k]['skuNameIds'] = "[" . $data[$k]['skuNameIds'] . "]";
                    }
                    if ($data[$k]['skuValIds'] == '') {
                        $data[$k]['skuValIds'] = 'null';
                    } else {
                        $data[$k]['skuValIds'] = "[" . $data[$k]['skuValIds'] . "]";
                    }
                    if ($data[$k]['skuVals'] == '') {
                        $data[$k]['skuVals'] = 'null';
                    } else {
                        $data[$k]['skuVals'] = "[" . $data[$k]['skuVals'] . "]";
                    }
                    $pz[$data[$k]['goodsCode']]['goodsCode'] = $data[$k]['goodsCode'];
                    $pz[$data[$k]['goodsCode']]['goodsId'] = $data[$k]['goodsId'];
                    $pz[$data[$k]['goodsCode']]['price'] = $this->OneSkuPrice($data[$k]['attr_price'], $goods_id, $user_id);
                    $pz[$data[$k]['goodsCode']]['product_id'] = $data[$k]['product_id'];
                    $pz[$data[$k]['goodsCode']]['skuNameIds'] = $data[$k]['skuNameIds'];
                    $pz[$data[$k]['goodsCode']]['skuNames'] = "[" . $data[$k]['skuNames'] . "]";
                    $pz[$data[$k]['goodsCode']]['skuValIds'] = $data[$k]['skuValIds'];
                    $pz[$data[$k]['goodsCode']]['skuVals'] = $data[$k]['skuVals'];
                    $pz[$data[$k]['goodsCode']]['stockNum'] = $data[$k]['stockNum'];
                }

            }

            foreach ($pz as $k => $v) {
                $return[] = $pz[$k];
            }
            return $return;
        }
    }

    public function price($goods_sku, $product_id, $goods_id, $user_id)
    {
        // 计算价格
//        $price = $goods_sku[0]['price'];

//        $price = $this->goods->get_final_price($goods_id,1, $user_id);
        $price = $this->goods->get_price($goods_id, $user_id);
        for ($i = 0; $i < count($goods_sku); $i++) {
            if ($goods_sku[$i]['product_id'] == $product_id) {
                $price += $goods_sku[$i]['attr_price'];
            }
        }
        //return round($price, 2);
		return $price;
    }

    public function OneSkuPrice($attr_price, $goods_id, $user_id)
    {
//        $price = $this->goods->get_final_price($goods_id,1, $user_id);
        $price = $this->goods->get_price($goods_id, $user_id);

        $price = $price + $attr_price;
        //return round($price, 2);
		return $price;
    }

    public function skuNameIds($goods_sku, $product_id)
    {
        // 返回组合后的ID
        $skuNameIds = [];
        for ($i = 0; $i < count($goods_sku); $i++) {
            if ($goods_sku[$i]['product_id'] == $product_id) {
                $skuNameIds[] = $goods_sku[$i]['skuNameIds'];
            }
        }
        // implode
        return "[" . implode(",", $skuNameIds) . "]";
    }

    public function skuNames($goods_sku, $product_id)
    {
        // 返回组合后的名字
        $skuNames = [];
        for ($i = 0; $i < count($goods_sku); $i++) {
            if ($goods_sku[$i]['product_id'] == $product_id) {
                $skuNames[] = $goods_sku[$i]['skuNames'];
            }
        }
        return "[" . implode(",", $skuNames) . "]";
    }

    public function skuValIds($goods_sku, $product_id)
    {
        // 返回组合后的属性ID
        $skuValIds = [];
        for ($i = 0; $i < count($goods_sku); $i++) {
            if ($goods_sku[$i]['product_id'] == $product_id) {
                $skuValIds[] = $goods_sku[$i]['skuValIds'];
            }
        }
        return "[" . implode(",", $skuValIds) . "]";
    }

    public function skuVals($goods_sku, $product_id)
    {
        // 返回组合后的值
        $skuVals = [];
        for ($i = 0; $i < count($goods_sku); $i++) {
            if ($goods_sku[$i]['product_id'] == $product_id) {
                $skuVals[] = $goods_sku[$i]['skuVals'];
            }
        }
        return "[" . implode(",", $skuVals) . "]";
    }
    //版权设置
    public function getappcopyright()
    {
        $data = $this->goods->getappcopyright();
        return $data;
    }
}
