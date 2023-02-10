<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;


class Address extends NotORM
{
    protected  function getTableName($id)
    {
        return 'user_address';
    }

    protected function getTableKey($table)
    {
        return 'address_id';
    }

    public function getUserAddress($user_id)
    {
        $sql = "select * from ecs_user_address where user_id={$user_id} order by address_id desc";
        $addresses = $this->getORM()->queryAll($sql);
        return $addresses;
    }

    public function getUserAddressLast($user_id)
    {
        $sql = "select * from ecs_user_address where user_id={$user_id} and is_default='true' order by address_id desc  limit 0,1";
        $addresses = $this->getORM()->queryAll($sql);
        $address = current($addresses);
        return $address;
    }

    public function getUserDefaultAddress($user_id)
    {
        $sql = "select * from ecs_user_address where user_id={$user_id} and is_default='1'";
        $address =  $this->getORM()->queryRows($sql);
        return $address;
    }

    public function get_address_by_id($user_id, $address_id)
    {
        $sql = "select * from ecs_user_address where user_id='" . $user_id . "' and address_id='" . $address_id . "'";
        $address =  $this->getORM()->queryRow($sql);

        if ($address) {
            $sql = "select * from ecs_region where region_id='" . $address['province'] . "'";
            $tmp = $this->getORM()->queryRow($sql);
            $address['province_name'] = $tmp['region_name'];

            $sql = "select * from ecs_region where region_id='" . $address['city'] . "'";
            $tmp = $this->getORM()->queryRow($sql);
            $address['city_name'] = $tmp['region_name'];

            $sql = "select * from ecs_region where region_id='" . $address['district'] . "'";
            $tmp = $this->getORM()->queryRow($sql);
            $address['district_name'] = $tmp['region_name'];
        }

        return $address;
    }

    public function deleteUserAddress($address_id)
    {
        $result = $this->delete($address_id);
        return $result;
    }

    public function saveAddress($save_data)
    {
        if ($save_data['is_default'] == '1') {
            $this->getORM()->where('user_id', $save_data['user_id'])->update(['is_default' => '0']);
        }
        $data = $this->getORM()->insert_update([], $save_data, []);
        return $data;
    }

    public function addressDelete($user_id, $id)
    {

        $sql = "delete from ecs_user_address where address_id = '$id' and user_id = '$user_id'";
        $this->getORM()->queryRow($sql);
    }
}
