<?php
namespace App\Lib;

use App\Model\Ad as AdModel;

class Ad
{
    public function __construct(){
        $this->ad = new AdModel();
    }

    public function getBannerList(){
        $data = $this->ad->getBanner();
        return $data;
    }
}