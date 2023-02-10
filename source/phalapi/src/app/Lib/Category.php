<?php
namespace App\Lib;

use App\Model\Category as CategoryModel;

class Category
{
    public function __construct(){
        $this->category = new CategoryModel();
    }

    public function getIndexTopList(){
        $data = $this->category->getIndexTop();
        return $data;
    }

    public function getNewCategoryList(){
        return $this->category->getNewCategory();
    }
    
    public function getCategoryList($id)
    {
        return $this->category->getCategory($id);
    }
}