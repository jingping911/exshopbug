<?php
namespace App\Model;

use Phalapi\Model\NotORMModel as NotORM;

class Goods_activity extends NotORM{
    public function whereActId($actId){
        return $this->getORM()->select('*')->where('act_id',$actId)->fetchOne();
    }
}