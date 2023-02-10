<?php

namespace App\Api;

use PhalApi\Api;
use App\Model\Category as CategoryModel;
use App\Model\Order as OrderModel;
use App\Lib\Ad as AdLib;
use App\Lib\Category as CategoryLib;
use App\Lib\Brand as BrandLib;
use App\Lib\Goods as GoodsLib;
use App\Lib\Article as ArticleLib;
use App\Model\ShopConfig as ShopConfigModel;

use App\Model\App as AppModel;



/**
 * 首页接口
 * @package APP\Api
 */
class Index extends Api
{

    public function __construct()
    {
        $this->ad = new AdLib();
        $this->category = new CategoryLib();
        $this->brand = new BrandLib();
        $this->goods = new GoodsLib();
        $this->article = new ArticleLib();
        $this->shop_config = new ShopConfigModel();
        $this->app_config = new AppModel();
        $this->order = new OrderModel();
    }
    public function getRules()
    {
        return array(
            'indexListApi' => array(
                'user_id' => array('name' => 'user_id', 'require' => false, 'min' => 1, 'max' => 50, 'desc' => '会员ID'),
            ),
        );
    }

    /**
     * 首页数据展示接口
     * @desc 首页
     */
    public function indexListApi()
    {
        $user_id =   intval($this->user_id);
        // banner 
        $banner = $this->ad->getBannerList();
        // channel 
        $channel = $this->category->getIndexTopList();
        //brandList
        $brand_list = $this->brand->getIndexBrandList();
        //newGoods
        $new_goods = $this->goods->getNewGoodsList($user_id);

        //pingtuan
        $pingtuan = $this->goods->getPinTuanGoodsList($user_id);

        //miaosha
        $miaosha  = $this->goods->getSpikeGoodList();

        //supergoods
        $supergoods  = $this->goods->SuperPackageApi();

        //intergral
        $integral = $this->goods->integralgoodsGoodsList();

        //hotGoods
        $hot_goods = $this->goods->getHotGoodsList($user_id);

        $appdownload = $this->goods->getappdownload();

        //topicList
        $topic_list = $this->article->getTopArticleList();
        //newCategoryList
        $new_category = $this->category->getNewCategoryList();
        //logo
        $logo = $this->shop_config->getLogo();

        $index_prompt = $this->shop_config->index_prompt();
        //prompt
        $prompt = $this->order->getPrompt();
        //限时秒杀
        $time_spike = $this->goods->getSpikeGoodList();
        //拼团购买
        // 版权设置 copyright
        $copyright = $this->goods->getappcopyright();
        // 直播设置
        $live_setting = $this->shop_config->getLiveSettings();
        return array(
            "banner" => $banner,
            "channel" => $channel,
            "brandList" => $brand_list,
            "newGoods" => $new_goods,
            "hotGoods" => $hot_goods,
            "topicList" => $topic_list,
            "integral" => $integral,
            "downloadUrl" => $appdownload,
            "supergoods" => $supergoods,
            "index_prompt" => $index_prompt,
            "pingtuan" => $pingtuan,
            "miaosha" => $miaosha,
            "newCategoryList" => $new_category,
            "prompt" => $prompt,
            "logo" => empty($logo[0]['val']) ? "https://imgt1.oss-cn-shanghai.aliyuncs.com/ecAllRes/images/logo.png" : goods_img_url($logo[0]['val']),
            "time_spike" => $time_spike,
            "pintuan_shop" => $pintuan_shop,
            "copyright" => $copyright,
            'live_setting' => $live_setting
        );
    }

    /**
     * 首页 引导页
     * @desc 首页
     */
    public function indexGuidePagesApi()
    {
        $guide_data = $this->app_config->getGuidePages();
        return array(
            "guide" => $guide_data,
        );
    }
}
