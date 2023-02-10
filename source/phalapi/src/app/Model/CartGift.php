<?php
namespace App\Model;

use Phalapi\Model\NotORMModel as NotORM;

class CartGift extends NotORM{
    protected function getTableName($id) {
        return 'cart';
    }
    public function insertGift($data){
        $this->getORM()->insert($data);
    }
    public function whereGoodsAndUserId($userId,$goodsId){
        return $this->getORM()->select('*')->where('extension_code','package_buy')->where('user_id',$userId)->where('goods_id',$goodsId)->fetchOne();
    }
    public function updateCart($recId,$data){
        $this->getORM()->where('rec_id',$recId)->update($data);
    }
}