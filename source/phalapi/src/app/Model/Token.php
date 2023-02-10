<?php

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

class Token extends NotORM
{
    protected function getTableName($id)
    {
        return 'sessions';
    }

    protected function getTableKey($table)
    {
        return 'user_id';
    }

    public function set_sess($user_data)
    {
        $sess_id = md5(json_encode($user_data) . time() . rand(1000, 9999));

        $sql = "select * from ecs_sessions where sesskey = '" . $sess_id . "'";
        $res = $this->getORM()->queryRow($sql);

        // 防止APP意外退出 重新登录时 登录不上去的问题
        if (!$res) {
            $expiry_time = time() + 3600 * 12;

            if (!$user_data['discount']) {
                $user_data['discount'] = 0;
            }

            $data = array(
                'sesskey' => $sess_id,
                'expiry' => $expiry_time,
                'userid' => $user_data['user_id'],
                'user_name' => urlencode($user_data['user_name']),
                'user_rank' => $user_data['user_rank'],
                'discount' => $user_data['discount'],
                'email' => $user_data['email'],
                'ip' => '',
                'data' => 'a:0:{}',
            );
            $orm = $this->getORM();
            $orm->insert($data);
        }

        return $sess_id;
    }

    public function get_sess($sesskey, $user_id)
    {

        $sess_data = $this->getORM()->where(array('sesskey' => $sesskey, 'userid' => $user_id))->fetchOne();

        if (!$sess_data) {
            //session错误
            return false;
        }

        if (time() < $sess_data['expiry']) {
            //超时
            return true;
        }

        return false;
    }
}
