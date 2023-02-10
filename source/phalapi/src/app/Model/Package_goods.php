<?php
namespace App\Model;

use Phalapi\Model\NotORMModel as NotORM;

class Package_goods extends NotORM{
    public function wherePackageId($actId){
        return $this->getORM()->select('*')->where('package_id',$actId)->fetchAll();
    }
}