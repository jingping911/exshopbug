<?php

namespace App\Lib;

use PhalApi\Exception\BadRequestException;

class Auth
{
    static function check_user_address($user_id, $address_id)
    {
        $this->_t($string);
        $di = \PhalApi\DI()->notorm->user_address;
        $user_address = $di->where('user_id', $user_id)->where('address_id', $address_id)->fetchOne();
        if (!$user_address) {
            throw new BadRequestException('非法请求', 44);
        }
        return true;
    }

    static function check_user()
    {
        //        $di = \PhalApi\DI()->notorm->users;
        //        $user = $di->where('user_id',$user_id)->fetchOne();
        //        if(!$user){
        //            throw new BadRequestException('非法请求', 44);
        //        }
        // todo  判断用户是否登陆等
        return true;
    }
}
