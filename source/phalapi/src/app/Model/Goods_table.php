<?php
namespace App\Model;

use Phalapi\Model\NotORMModel as NotORM;

class Goods_table extends NotORM{
    //指定表名
    protected function getTableName($id) {
        return 'goods';  
    }
    public function whereGoodsId($goodsId){
        return $this->getORM()->select('*')->where('goods_id',$goodsId)->fetchOne();
    }
}