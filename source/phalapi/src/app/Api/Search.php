<?php

namespace App\Api;

use App\Lib\Goods as GoodsLib;
use App\Model\Goods as GoodsModel;
use Phalapi\Api;

/**
 * 商品搜索
 * @package APP\Api
 */
class Search extends Api
{

    public function __construct()
    {
        $this->goods = new GoodsLib();
        $this->model = new GoodsModel();
    }

    public function getRules()
    {
        return array(
            "searchHelperApi" => array(
                "keyword" => array("name" => "keyword", "require" => false, "min" => 0, "max" => 100, "desc" => "搜索商品的名称"),
                'order' => array('name' => 'order', 'require' => false, 'min' => 0, 'max' => 30, 'desc' => '排序方式'),
                'page' => array('name' => 'page', 'require' => false, 'min' => 0, 'max' => 100, 'desc' => '分页'),
                'num' => array('name' => 'num', 'require' => false, 'min' => 0, 'max' => 30, 'desc' => '销量排序'),
                'user_id' => array('name' => 'user_id', 'require' => false, 'min' => 0, 'max' => 30, 'desc' => '用户id'),
            ),
        );
    }
    /**
     * 搜索商品
     * @desc 搜索商品
     * @return array
     */
    public function searchHelperApi()
    {
        $words = $this->keyword;
        $order = $this->order;
        $page = intval($this->page);
        $num = intval($this->num);
        $user_id = intval($this->user_id);
//        var_dump($words);die;
        if (!filter_character($words)&&$words){
            return array("msg" => "只能输入数字字母中文");
        }
        if($words === ''){
            return array("msg" => "搜索框为空不可以搜索");
        }
        $data = $this->goods->searchGoodsList($words, $order, $page, $num, $user_id);
        //搜索引擎数据
        $this->model->searchKeyWordsModel($words);
        if ($data == "false") {
            return array("msg" => "未搜索到该关键字商品");
        } else {
            return $data;
        }
    }

    /**
     * 热搜关键词
     * @desc 热搜关键词
     */
    public function searchIndexActionApi()
    {

        $hot = $this->model->hotSearch();
        return array('hotKeywordList' => $hot);
    }
}
