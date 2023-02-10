<?php

namespace App\Lib;

use App\Model\Article as ArticleModel;



class Article
{
    protected $article_model;
    public function __construct()
    {
        $this->article_model = new ArticleModel();
    }

    public function getRecommendArticleList($page)
    {
        return $this->article_model->getRecommendArticleList($page);
    }

    public function getArticleDetail($article_id)
    {
        return $this->article_model->getArticleDetail($article_id);
    }

    public function getTopArticleList()
    {
        return $this->article_model->getTopArticle();
    }

    public function getTopicArticleList($page)
    {
        return $this->article_model->getTopicArticle($page);
    }

    public function getTopicDetailList($id)
    {
        return $this->article_model->getTopicDetail($id);
    }

    public function getArticleGoodsList($id)
    {
        return $this->article_model-> getArticleGoodsListModel($id);
    }
}
