<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;



class Ranks extends NotORM
{
    protected  function getTableName($id)
    {
        return 'user_rank';
    }

    protected function getTableKey($table)
    {
        return 'rank_id';
    }


    public function get_user_rank($user_rank)
    {
        $rank_data =  $this->getORM()->where(array('rank_id' => $user_rank))->fetchOne();

        return $rank_data;
    }
}
