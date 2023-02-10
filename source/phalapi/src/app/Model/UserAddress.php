<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;


class UserAddress extends NotORM
{
    protected  function getTableName($id)
    {
        return 'user_address';
    }

    protected function getTableKey($table)
    {
        return 'address_id';
    }

    function getAddressList($user_id)
    {
        $rows = $this->getORM()->where('user_id', $user_id)->fetchAll();
        foreach ($rows as $key => $item) {
            $sql = "select * from ecs_region where region_id='" . $item['province'] . "'";
            $tmp = $this->getORM()->queryRow($sql);
            $atmp['province_name'] = $tmp['region_name'];

            $sql = "select * from ecs_region where region_id='" . $item['city'] . "'";
            $tmp = $this->getORM()->queryRow($sql);
            $atmp['city_name'] = $tmp['region_name'];

            $sql = "select * from ecs_region where region_id='" . $item['district'] . "'";
            $tmp = $this->getORM()->queryRow($sql);
            $atmp['district_name'] = $tmp['region_name'];
            $rows[$key]['address_list'] = $atmp['province_name'] . ' ' . $atmp['city_name'] . ' ' . $atmp['district_name'];


            if ($item['is_default'] == 'true') {
                $rows[$key]['is_default'] = true;
            } else {
                $rows[$key]['is_default'] = false;
            }
        }

        return $rows;
    }

    function update_address_detail($address_id, $user_id, $consignee, $email, $mobile, $address, $country, $province, $city, $district, $mobile_addr_id_list, $checked)
    {
        if ($checked == 'true') {
            $is_default = 'true';
        } else {
            $is_default = 'false';
        }

        /**
            判断取消其他默认
         **/
        if ($is_default == 'true') {
            $sql = "select * from ecs_user_address where user_id = '" . $user_id . "' and is_default='true'";
            $tmp = $this->getORM()->queryRow($sql);
            if ($tmp) {
                $sql = "update ecs_user_address set is_default='false' where user_id = '" . $user_id . "'";
                $this->getORM()->queryRow($sql);
            }
        }


        $sql = "select * from ecs_region where region_name = '" . $province . "'";
        $tmp = $this->getORM()->queryRow($sql);
        $province_id = $tmp['region_id'];

        $sql = "select * from ecs_region where region_name = '" . $city . "'";
        $tmp = $this->getORM()->queryRow($sql);
        $city_id = $tmp['region_id'];

        $sql = "select * from ecs_region where region_name = '" . $district . "'";
        $tmp = $this->getORM()->queryRow($sql);
        $district_id = $tmp['region_id'];

        $sql = "update ecs_user_address set 
            consignee='" . $consignee . "',
            mobile='" . $mobile . "',
            email='" . $email . "',
            province='" . $province_id . "',
            city='" . $city_id . "',
            district='" . $district_id . "',
            country='1',
            address='" . $address . "',
            mobile_addr_id_list='" . $mobile_addr_id_list . "',
            is_default='" . $is_default . "'
            where  address_id='" . $address_id . "' and  user_id = '" . $user_id . "'";

        $data = $this->getORM()->queryRow($sql);

        return true;
    }

    function checkAddress($user_id)
    {
        $sql = "select count(*) as all_count from ecs_user_address where user_id = '" . $user_id . "'";
        $tmp = $this->getORM()->queryRow($sql);
        if ($tmp['all_count'] > 10) {
            return false;
        }
        return true;
    }

    function add_address_detail($consignee, $email, $user_id, $mobile, $address, $country, $province, $city, $district, $mobile_addr_id_list, $checked)
    {
        if ($checked == 'true') {
            $is_default = 'true';
        } else {
            $is_default = 'false';
        }


        /**
            判断取消其他默认
         **/
        if ($is_default == 'true') {
            $sql = "select * from ecs_user_address where user_id = '" . $user_id . "' and is_default='true'";
            $tmp = $this->getORM()->queryRow($sql);
            if ($tmp) {
                $sql = "update ecs_user_address set is_default='false' where user_id = '" . $user_id . "'";
                $this->getORM()->queryRow($sql);
            }
        }
        $sql = "select * from ecs_region where region_name = '" . $province . "'";
        $tmp = $this->getORM()->queryRow($sql);
        $province_id = $tmp['region_id'];

        $sql = "select * from ecs_region where region_name = '" . $city . "'";
        $tmp = $this->getORM()->queryRow($sql);
        $city_id = $tmp['region_id'];

        $sql = "select * from ecs_region where region_name = '" . $district . "'";
        $tmp = $this->getORM()->queryRow($sql);
        $district_id = $tmp['region_id'];

        $sql = "insert into ecs_user_address (consignee,email,user_id,mobile,address,country,province,city,district,mobile_addr_id_list,is_default) values (
            '" . $consignee . "',
            '" . $email . "',
            '" . $user_id . "',
            '" . $mobile . "',
            '" . $address . "',
            '1',
            '" . $province_id . "',
            '" . $city_id . "',
            '" . $district_id . "',
            '" . $mobile_addr_id_list . "',
            '" . $is_default . "'
        )";
        $res = $this->getORM()->queryRows($sql);
        $addr_id = $this->getORM()->insert_id();
        return $addr_id;
    }
}
