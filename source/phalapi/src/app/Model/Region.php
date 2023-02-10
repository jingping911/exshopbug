<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;


class Region extends NotORM
{
    protected  function getTableName($id)
    {
        return 'region';
    }

    protected function getTableKey($table)
    {
        return 'region_id';
    }

    public function get_addr_id_list($addr_list)
    {
        $tmp = explode(',', $addr_list);
        $sql = "select * from ecs_region where region_name = '" . $tmp[0] . "' and region_type='1'";
        $province_data = $this->getORM()->queryRows($sql, $params);
        if (count($province_data) == 0) {
            return false;
        }
        if ($tmp['0'] == '北京市' ||  $tmp['0'] == '天津市' || $tmp['0'] == '上海市' || $tmp['0'] == '重庆市') {
            $sql = "select * from ecs_region where region_name = '" . $tmp[0] . "' and region_type='2'";
            $city_data = $this->getORM()->queryRows($sql, $params);
            if (count($city_data) == 0) {
                return false;
            }
        } else {
            $sql = "select * from ecs_region where region_name = '" . $tmp[1] . "' and region_type='2'";
            $city_data = $this->getORM()->queryRows($sql, $params);
        }
        $sql = "select * from ecs_region where region_name = '" . $tmp[2] . "' and parent_id='" . $city_data[0]['region_id'] . "' and region_type='3'";
        $district_data = $this->getORM()->queryRows($sql, $params);

        $province_data = current($province_data);
        $city_data = current($city_data);
        $district_data = current($district_data);
        $region_list = array(
            'country' => 1,
            'province' => intval($province_data['region_id']),
            'city' => intval($city_data['region_id']),
            'district' => intval($district_data['region_id'])
        );
        return $region_list;
    }
}
