<?php

namespace App\Api;

use PhalApi\Api;
use App\Lib\Article as ArticleLib;

/**
 * 文章相关接口服务
 * @package App\Api
 */
class Article extends Api
{
    protected $domain;

    public function __construct()
    {
        $this->domain = new ArticleLib();
    }

    public function getRules()
    {
        return array(
            "topicListApi" => array(
                "page"  => array("name" => "page", "require" => true, "min" => 1, "desc" => "页码")
            ),
            "topicdetailListApi" => array(
                "id" => array("name" => "id", "require" => true, "min" => 1, "desc" => "文章的ID")
            ),
            "getArticleGoodsApi" => array(
                "id" => array("name" => "id", "require" => true, "min" => 1, "desc" => "文章的ID")
            ),
        );
    }
    /**
     * 文章详情
     * @param int $id
     * @desc 文章详情
     * @return array
     */
    public function articleDetail($id = 1)
    {
        $article = $this->domain->getArticleDetail($id);
        return $article;
    }

    /**
     * 文章列表
     * @desc 文章列表
     * @return array
     */
    public function articleList()
    {
        $_POST['page'] = 1;
        $articles = $this->domain->getRecommendArticleList($_POST['page']);
        return $articles;

        switch ($_POST['page']) {
            case '1':
                $data = file_get_contents('https://jinjiajin.net/vue-app/json/topic/listaction1.json');
                break;
            case '2':
                $data = file_get_contents('https://jinjiajin.net/vue-app/json/topic/listaction2.json');
                break;
            case '3':
                $data = file_get_contents('https://jinjiajin.net/vue-app/json/topic/listaction3.json');
                break;
            case '4':
                $data = file_get_contents('https://jinjiajin.net/vue-app/json/topic/listaction4.json');
                break;
            default:
                # code...
                break;
        }
        $data = json_decode($data, true);
        return $data;
    }

    /**
     * 文章精选
     * @desc 文章精选
     * @return array
     */
    public function topicListApi()
    {
        $page = intval($this->page); // 接收传来的页数
        $data = $this->domain->getTopicArticleList($page);
        return $data;
    }

    /**
     * 文章详情页
     * @desc 文章详情页
     * @return array
     */
    public function topicdetailListApi()
    {
        $id = intval($this->id);
        return $this->domain->getTopicDetailList($id);
    }

    /**
     * 文章关联商品
     * @desc 文章关联的商品
     * @return array
     */
    public function getArticleGoodsApi()
    {
        $id = intval($this->id);
        return $this->domain->getArticleGoodsList($id);
    }
}
