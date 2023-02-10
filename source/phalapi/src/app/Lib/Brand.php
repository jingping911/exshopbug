<?php
namespace App\Lib;

use App\Model\Brand as BrandModel;

class Brand
{
    public function __construct()
    {
        $this->brand = new BrandModel;
    }

    public function getIndexBrandList()
    {
        $data = $this->brand->getIndexBrand();
        return $data;
    }
}