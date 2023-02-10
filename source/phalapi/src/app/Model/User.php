<?php

namespace  App\Model;

use PhalApi\Model\NotORMModel as NotORM;
use PhalApi\Model\Vcode as Vcode;
use PhalApi\Model\Rank as RankModel;
use App\Model\Order as OrderModel;


class User extends NotORM
{
    protected  function getTableName($id)
    {
        return 'users';
    }

    protected function getTableKey($table)
    {
        return 'user_id';
    }


    public function update_password($username, $password)
    {
        $user_data =  $this->getORM()->where(array('mobile_phone' => $username))->fetchOne();
        if ($user_data['ec_salt']) {
            $user_password = md5(md5($password) . $user_data['ec_salt']);
        } else {
            $user_password = md5($password);
        }
        $data['password'] = $user_password;


        $sql = "update ecs_users set password='" . $data['password'] . "' where user_id='" . $user_data['user_id'] . "'";
        $res = $this->getORM()->queryRows($sql);
        return $res;
    }

    public function sms_login($username, $password)
    {


        $user_data =  $this->getORM()->where(array('mobile_phone' => $username))->fetchOne();

        if (!$user_data) {
            return false;
        }

        if ($user_data['ec_salt']) {
            $user_password = md5(md5($password) . $user_data['ec_salt']);
        } else {
            $user_password = md5($password);
        }
        // var_dump($user_password);
        // var_dump($user_data['password']);

        if ($user_password != $user_data['password']) {
            return false;
        }

        return $user_data;
    }
    public function default_image()
    {
        $sql = "select * from ecs_app_config where k = 'default_image'";
        $data = $this->getORM()->queryRow($sql);
        return $data['val'];
    }

    public function GetRealyAddr($params)
    {

        $url = 'https://apis.map.qq.com/ws/geocoder/v1/?location=' . $params . '&key=K5EBZ-BS464-V3TUC-DKP4U-FSNG2-SHFA5&get_poi=0';
        $address = file_get_contents($url);
        echo $address;
        die;
        //        return $address;

    }


    public function email_login($username, $password)
    {

        $user_data =  $this->getORM()->where(array('email' => $username))->fetchOne();
        if (!$user_data) {
            return false;
        }

        if ($user_data['ec_salt']) {
            $user_password = md5(md5($password) . $user_data['ec_salt']);
        } else {
            $user_password = md5($password);
        }
        if ($user_password != $user_data['password']) {
            return false;
        }

        return $user_data;
    }

    public function username_login($username, $password)
    {
        $user_data =  $this->getORM()->where(array('user_name' => $username))->fetchOne();
        if (!$user_data) {
            return false;
        }

        if ($user_data['ec_salt']) {
            $user_password = md5(md5($password) . $user_data['ec_salt']);
        } else {
            $user_password = md5($password);
        }
        if ($user_password != $user_data['password']) {
            return false;
        }

        return $user_data;
    }

    public function mobile_register($user_name, $password, $platform, $parent_id, $recode)
    {
        $sql = "SELECT value FROM ecs_shop_config WHERE code = 'register_points'";
        $res = $this->getORM()->queryRow($sql);
//        if (!isset($email) || $email == '') {
//            $email = $user_name . '@mail';
//        }
        if ($recode) {
            $user_data =  $this->getORM()->where(array('recode' => $recode))->fetchOne();

            if ($user_data) {
                $parent_id = $user_data['user_id'];
            } else {
                $error['is_register'] = false;
                $error['msg'] = '邀请码不存在';
                return $error;
            }
        }
        $rcode = rand_code();
        if ($parent_id == '') {
            //$sql = "insert into ecs_users (email,user_name,password,platform,alias,msn,qq,office_phone,home_phone,mobile_phone,credit_line,reg_time,recode,pay_points,rank_points) values ('" . $email . "','" . $user_name . "','" . md5($password) . "','" . $platform . "','','','','','','" . $user_name . "','0','" . time() . "','" . $rcode . "','" . $res['value'] . "','" . $res['value'] . "')";
            $sql = "insert into ecs_users (user_name,password,platform,alias,msn,qq,office_phone,home_phone,mobile_phone,credit_line,reg_time,recode,pay_points,rank_points) values ('" . $user_name . "','" . md5($password) . "','" . $platform . "','','','','','','" . $user_name . "','0','" . time() . "','" . $rcode . "','" . $res['value'] . "','" . $res['value'] . "')";

        } else {
            //$sql = "insert into ecs_users (email,user_name,password,platform,alias,msn,qq,office_phone,home_phone,mobile_phone,credit_line,reg_time,parent_id,recode,pay_points,rank_points) values ('" . $email . "','" . $user_name . "','" . md5($password) . "','" . $platform . "','','','','','','" . $user_name . "','0','" . time() . "',$parent_id,'" . $rcode . "','" . $res['value'] . "','" . $res['value'] . "')";
            $sql = "insert into ecs_users (user_name,password,platform,alias,msn,qq,office_phone,home_phone,mobile_phone,credit_line,reg_time,parent_id,recode,pay_points,rank_points) values ('" . $user_name . "','" . md5($password) . "','" . $platform . "','','','','','','" . $user_name . "','0','" . time() . "',$parent_id,'" . $rcode . "','" . $res['value'] . "','" . $res['value'] . "')";
        }
        $orm = $this->getORM();
        $orm->query($sql, '');
        $user_id = $orm->insert_id();
        if ($parent_id != '') {
            $sql = "select user_name from ecs_users where user_id = $parent_id";
            $res = $this->getORM()->queryAll($sql);
            $sql = "insert into ecs_user_recommend (user_id,user_name,parent_id,recommend) values ($user_id,$user_name,$parent_id,'{$res[0]['user_name']}')";
            $this->getORM()->query($sql);
        }
        $user_data =  $this->getORM()->where(array('user_id' => $user_id))->fetchOne();

        return $user_data;
    }



    public function get_rank_discount($user_id)
    {
        $sql = "select discount,user_rank from ecs_users eu left join ecs_user_rank eur on eu.user_rank = eur.rank_id where eu.user_id = '" . $user_id . "'";
        $member_data = $this->getORM()->queryRow($sql);
        if ($member_data['member_data'] == '') {
            $discount  = 100;
        } else {
            $discount  = $member_data['discount'];
        }
        $discount = $discount / 100;
        $return = array(
            'discount' => $discount,
            'user_rank' => $member_data['user_rank']
        );
        return $return;
    }


    public function getAddressList($user_id)
    {
        $sql = "select * from ecs_user_address where user_id ={$user_id}";
        $data = $this->getORM()->queryAll($sql);
        return $data;
    }

    public function check_username($user_name)
    {
        $sql = "select * from ecs_users where user_name='" . $user_name . "' or mobile_phone = '" . $user_name . "'";
        $data = $this->getORM()->queryRow($sql);
        return $data;
    }
    public function memberinfoSave($user_id, $birthdaytime, $sex)
    {

        $sql = "update ecs_users set birthday ='" . $birthdaytime . "' , sex = '" . $sex . "' where user_id ='" . $user_id . "'";
        $this->getORM()->queryRow($sql);

        return true;
    }

    public function get_UserFeedback($user_id, $conent, $title)
    {
        $up_sql = "update ecs_users set chartcode ='' where user_id = ".$user_id;
        $this->getORM()->queryRows($up_sql);

        $sql = "select * from  ecs_users where user_id ='" . $user_id . "'";
        $user_data = $this->getORM()->queryRow($sql);
        $sql = "insert  into ecs_feedback (parent_id, user_id, user_name, user_email, msg_title, msg_type, msg_status,  msg_content, msg_time) values (0,'" . $user_id . "','" . $user_data['user_name'] . "','" . $user_data['email'] . "','" . $title . "','1','0','" . $conent . "','" . time() . "')";
        $this->getORM()->queryRow($sql);
        return true;
    }

    public function get_user($user_id)
    {
        $user_data =  $this->getORM()->where(array('user_id' => $user_id))->fetchOne();
        $user_data['user_name'] = urldecode($user_data['user_name']);
        return $user_data;
    }

    public function get_user_info($user_id)
    {

        $user_data =  $this->getORM()->where(array('user_id' => $user_id))->fetchOne();
        if (!$user_data) {
            return false;
        }

        $sql = "select * from ecs_user_rank where rank_id = '" . $user_data['user_rank'] . "'";
        $user_rank = $this->getORM()->queryRow($sql);

        $thistime = time();
        $sql = "select count(bonus_id) as sum from ecs_user_bonus eub left join ecs_bonus_type ebt on eub.bonus_type_id = ebt.type_id where user_id = '" . $user_id . "' and order_id = '0' and use_end_date > '" . $thistime . "' ";
        $bonus = $this->getORM()->queryRow($sql);

        // 获得可用红包的数量
        //        $thistime = time();
        //        $sql = "select count(bonus_id) as num from ecs_user_bonus eub left join ecs_bonus_type ebt on eub.bonus_type_id = ebt.type_id where user_id = '".$user_id."' and order_id = '0'  and use_end_date > '".$thistime."' and use_start_date <= '".$thistime."'  ";
        //        $bonus_data = $this->getORM()->queryRow($sql);

        $return = array(
            'advance' => $user_data['user_money'],
            'user_money' => $user_data['user_money'],
            'frozen_money' => $user_data['frozen_money'],
            'email' => $user_data['email'],
            'point' => $user_data['pay_points'],
            'couponNum' => $bonus['sum'],
            'user_rank' => $user_rank['rank_name'],
            'mobile' => $user_data['mobile_phone'],
        );

        return $return;
    }


    function log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER)
    {
        /* 插入帐户变动记录 */
        $account_log = array(
            'user_id'       => $user_id,
            'user_money'    => $user_money,
            'frozen_money'  => $frozen_money,
            'rank_points'   => $rank_points,
            'pay_points'    => $pay_points,
            'change_time'   => gmtime(),
            'change_desc'   => $change_desc,
            'change_type'   => $change_type
        );
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $account_log, 'INSERT');

        /* 更新用户信息 */
        $sql = "UPDATE " . $GLOBALS['ecs']->table('users') .
            " SET user_money = user_money + ('$user_money')," .
            " frozen_money = frozen_money + ('$frozen_money')," .
            " rank_points = rank_points + ('$rank_points')," .
            " pay_points = pay_points + ('$pay_points')" .
            " WHERE user_id = '$user_id' LIMIT 1";
        $GLOBALS['db']->query($sql);
    }


    public function getUserRank($user_id)
    {
        // 获取当前会员的等级
        $sql = "select user_rank from ecs_users where user_id = '" . $user_id . "'";
        $rank = $this->getORM()->queryRow($sql);

        // 获取当前的会员等级信息
        $rank_sql = "select rank_name,discount from ecs_user_rank where rank_id = '" . $rank['user_rank'] . "'";
        $rank_msg = $this->getORM()->queryRow($rank_sql);


        if (empty($rank_msg)) {
            //$rank_msg['rank_name'] = '非特殊用户组';
            $rank_msg['rank_name'] = '普通会员';
            $rank_msg['discount'] = 100;
        }

        // 当前等级的优惠
        $discount = round($rank_msg['discount'] / 100, 2) * 10;

        return array(
            'rank_name' => $rank_msg['rank_name'],
            'discount' => $discount,
        );
    }

    //查询其它页logo
    public function logo_other()
    {
        $sql = "select value from ecs_shop_config where code = 'shop_other'";
        $result = $this->getORM()->queryRow($sql);
        return empty($result['value']) ? "https://imgt1.oss-cn-shanghai.aliyuncs.com/ecAllRes/images/logo.png" : goods_img_url($result['value']);
    }

    public function kefu_tel()
    {
        $sql = "select val from ecs_app_config where k = 'kefu_tel'";
        $result = $this->getORM()->queryRow($sql);
        return $result;
    }

    public function getDeposit($user_id)
    {
        // 我的钱包显示当前余额
        $sql = "SELECT user_money FROM ecs_users WHERE user_id = {$user_id}";
        $data = $this->getORM()->queryRows($sql);
        return $data[0]['user_money'];
    }

    public function getCollectListAction($page, $user_id)
    {
        // 我的收藏
        $page_size = getConfigPageSize();

        $offset = ($page - 1) * $page_size;
        $sql = "SELECT a.goods_id AS id,b.goods_name AS name,b.shop_price AS retail_price,b.goods_id,b.goods_thumb AS list_pic_url
                FROM ecs_collect_goods AS a
                LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                WHERE a.user_id = {$user_id} LIMIT $offset,$page_size";
        //        var_dump($sql);die;
        $data = $this->getORM()->queryRows($sql);
        foreach ($data as $k => $v) {
            $data[$k]['list_pic_url'] = goods_img_url($data[$k]['list_pic_url']);
        }
        $sql = "SELECT count(a.goods_id) as sum FROM ecs_collect_goods AS a
                LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                WHERE a.user_id = {$user_id}";
        $sum = $this->getORM()->queryRow($sql);
        $return['pagetotal'] = $this->pageTotal($sql, $page_size);
        $return['data'] = $data;

        return $return;
    }

    public function getAdvanceListAction($user_id, $page)
    {
        // 余额变动记录
        $return = array();
        $page_size = pagesize();
        $offset = ($page - 1) * $page_size;

        $sql = "select * from ecs_account_log where user_id = $user_id and (user_money != 0 or frozen_money != 0) order by log_id desc limit $offset,$page_size";
        //        $sql = "SELECT * FROM ecs_account_log WHERE user_id = {$user_id} AND rank_points = 0 AND pay_points = 0 ORDER BY log_id DESC LIMIT {$offset},{$page_size} ";
        $data = $this->getORM()->queryRows($sql);

        foreach ($data as $k => $v) {
            $data[$k]['do_money'] = $data[$k]['user_money'];
            $data[$k]['mtime'] = date("Y-m-d H:i:s", $data[$k]['change_time']);
            $data[$k]['message'] = $data[$k]['change_desc'];
        }
        $sql = "SELECT COUNT(*) AS num FROM ecs_account_log WHERE user_id = {$user_id} AND rank_points = 0 AND pay_points = 0";
        $total = $this->getORM()->queryRow($sql);
        $return['data'] = $data;
        $return['count'] = $total['num'];
        //        $return['pagetotal'] = $this->pageTotal($sql,$page_size);
        $return['pagetotal'] = ceil($total['num'] / $page_size);

        return $return;
    }

    public function getPointList($user_id, $page)
    {
        // 积分变动记录
        $return = array();
        $page_size = pagesize();
        $offset = ($page - 1) * $page_size;

        $sql = "SELECT * FROM ecs_account_log WHERE user_id = {$user_id} AND user_money = '0' AND frozen_money = '0' ORDER BY log_id DESC LIMIT {$offset},{$page_size} ";
        $data = $this->getORM()->queryRows($sql);
        foreach ($data as $k => $v) {
            $data[$k]['change_point'] = $data[$k]['pay_points'];
            $data[$k]['addtime'] = date("Y-m-d H:i:s", $data[$k]['change_time']);
            $data[$k]['reason'] = $data[$k]['change_desc'];
        }
        $sql = "SELECT COUNT(*) as sum FROM ecs_account_log WHERE user_id = {$user_id} AND user_money = '0' AND frozen_money = '0'";
        $sum = $this->getORM()->queryRow($sql);
        $return['data'] = $data;
        $return['a'] = $sum['sum'];
        $return['pagetotal'] = $this->pageTotal($sql, $page_size);

        return $return;
    }
    public  function GetPromoteApi($user_id)
    {

        $data = \PhalApi\DI()->config->get('app');
        $return['url'] = $data['host_url'] . "h5/apiPam/register/main?parent_id=" . $user_id;
        $return['code_url'] = 'https://wenhairu.com/static/api/qr/?size=100&text=' . urlencode($return['url']);
        return  $return;
    }

    public  function  SendBonus($user_id, $bonus_id)
    {

        $sql = "SELECT * FROM ecs_bonus_type WHERE type_id = '" . $bonus_id . "'";
        $bonus_type = $this->getORM()->queryRow($sql);
        $sql = "select count(user_id) as count from   ecs_user_bonus where user_id='" . $user_id . "' and bonus_type_id='" . $bonus_id . "'";
        $count = $this->getORM()->queryRow($sql);
        if ($count['count'] >= 1) {
            return array("res" => false, "msg" => "你已领取该红包");
        }
        if (empty($bonus_type)) {
            return array("res" => false, "msg" => "领取失败");
        }
        if ($bonus_type['send_start_date'] > time()) {
            return array("res" => false, "msg" => "该红包还未开始领取");
        }
        if ($bonus_type['send_end_date'] < time()) {
            return array("res" => false, "msg" => "该红包领取结束");
        }
        /* 向会员红包表录入数据 */
        $sql = "INSERT INTO ecs_user_bonus (bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) VALUES ('$bonus_id', 0, '$user_id', 0, 0,1)";
        $da =  $this->getORM()->query($sql);
        return array("res" => true, "msg" => "领取成功");
    }
    function pageTotal($sql, $page_size)
    {
        /**
         * 返回总页数
         * 页数别名 num
         */
        $page_total = $this->getORM()->queryRows($sql);
        return ceil($page_total[0]['sum'] / $page_size);
    }

    public function vcodeLogin($mobile)
    {
        // 短信验证码登录
        $user_data =  $this->getORM()->where(array('mobile_phone' => $mobile))->fetchOne();

        if (!$user_data) {
            return false;
        }
        return $user_data;
    }

    public function getAviBonusList($user_id, $page, $status)
    {
        $thistime = time();

        if ($status == 'showinvalid') {
            $sql = "select * from ecs_user_bonus eub left join ecs_bonus_type ebt on eub.bonus_type_id = ebt.type_id where user_id = '" . $user_id . "' and (order_id > '0' OR use_end_date < '" . $thistime . "') ";
            $data = $this->getORM()->queryRows($sql);
            foreach ($data as $key => $item) {
                $data[$key]['use_end_date'] = date('Y-m-d', $item['use_end_date']);
                $data[$key]['use_start_date'] = date('Y-m-d', $item['use_start_date']);
            }
        } else {
            $sql = "select * from ecs_user_bonus eub left join ecs_bonus_type ebt on eub.bonus_type_id = ebt.type_id where user_id = '" . $user_id . "' and order_id = '0' and use_end_date > '" . $thistime . "' and use_start_date <'" . $thistime . "'";
            $data = $this->getORM()->queryRows($sql);
            foreach ($data as $key => $item) {
                $data[$key]['use_end_date'] = date('Y-m-d', $item['use_end_date']);
                $data[$key]['use_start_date'] = date('Y-m-d', $item['use_start_date']);
            }
        }
        $sql = "select count(bonus_id) as sum from ecs_user_bonus eub left join ecs_bonus_type ebt on eub.bonus_type_id = ebt.type_id where user_id = '" . $user_id . "' and order_id = '0' and use_end_date > '" . $thistime . "' and use_start_date <'" . $thistime . "'";
        $bonus_Available = $this->getORM()->queryRow($sql);

        $sql = "select count(bonus_id) as sum from ecs_user_bonus eub left join ecs_bonus_type ebt on eub.bonus_type_id = ebt.type_id where user_id = '" . $user_id . "' and order_id > '0' OR use_end_date < '" . $thistime . "' ";

        $bonus_Invalid = $this->getORM()->queryRow($sql);
        $return['bonus'] = $data;
        $return['showinvalid'] = $bonus_Invalid['sum']; //失效数量
        $return['showvalid'] = $bonus_Available['sum']; //可用数量
        return $return;
    }

    public function checkMobile($user_id, $mobile)
    {
        // 检测修改手机号时id与手机号是否照应

        $sql = "SELECT * FROM ecs_users WHERE user_id = " . $user_id . " AND mobile_phone = " . $mobile . " ";
        $res = $this->getORM()->queryRow($sql);
        if (empty($res) || $res == '') {
            return false;
        }
        return true;
    }

    public function securityphoneUpdateBefore($mobile, $vcode)
    {
        // 更改手机号第一步  验证身份
        $check_time = time() - 3600;

        $sql = "SELECT * FROM ecs_vcode WHERE mobile = " . $mobile . " AND vcode = " . $vcode . " AND add_time >'" . $check_time . "'";
        $res = $this->getORM()->queryRow($sql);
        if (empty($res) || $res == '') {
            return false;
        }

        return true;
    }

    public function securityphoneUpdateAfter($mobile, $vcode, $user_id)
    {
        // 更改手机号第二步 更换手机号
        $check_time = time() - 3600;

        $sql = "SELECT * FROM ecs_vcode WHERE mobile = " . $mobile . " AND vcode = " . $vcode . " AND add_time >'" . $check_time . "'";
        $res = $this->getORM()->queryRow($sql);
        if (empty($res) || $res == '') {
            return false;
        }

        $sql = "UPDATE ecs_users SET `user_name` = '" . $mobile . "',mobile_phone = " . $mobile . " WHERE user_id = " . $user_id . "";  // 修改手机号的同时修改用户名
        $this->getORM()->queryRow($sql);
        return true;
    }

    public function securitypwdUpdate($mobile, $vcode, $user_id, $password)
    {
        // 修改登录密码
        $check_time = time() - 3600;

        $sql = "SELECT * FROM ecs_vcode WHERE mobile = " . $mobile . " AND vcode = " . $vcode . " AND add_time >'" . $check_time . "'";
        $res = $this->getORM()->queryRow($sql);
        if (empty($res) || $res == '') {
            return false;
        }

        $sql = "SELECT ec_salt FROM ecs_users WHERE user_id = " . $user_id . "";
        $user_data = $this->getORM()->queryRow($sql);

        if (empty($user_data['ec_salt']) || $user_data['ec_salt'] == '') {
            $new_password = md5($password);
        } else {
            $new_password = md5(md5($password) . $user_data['ec_salt']);
        }

        $sql = "update ecs_users set password='" . $new_password . "' where user_id='" . $user_id . "'";
        $this->getORM()->queryRow($sql);

        return true;
    }

    public function checkWechatReg($unionid, $platform, $openid)
    {
        if (isset($unionid) && !empty($unionid)) {
            $sql = "SELECT * FROM ecs_users WHERE unionid = '$unionid'";
        } else {
            // 检测该微信是否注册
            if ($platform == 'MP-WEIXIN') {   // 微信小程序登录
                $sql = "select * from ecs_users where openid_mp = '" . $openid . "'";
            }
            if ($platform == 'APP-PLUS') {
                $sql = "select * from ecs_users where openid = '" . $openid . "'";
            }
            if ($platform == 'H5') {
                $sql = "select * from ecs_users where openid_h5 = '" . $openid . "'";
            }
        }

        $res = $this->getORM()->queryRow($sql);

        if ($res) {
            return array("res" => true, "msg" => "该微信已注册");
        }
        return array("res" => false, "msg" => "该微信尚未注册");
    }

    public function  wxLogin()
    {
        $hselect = "select * from ecs_shop_config where code = 'hfive'";
        $hquery =  $this->getORM()->queryRow($hselect);
        $hresult = unserialize($hquery['value']);

        return  $hresult;
    }
    public function wechatLogin($unionid, $platform = '', $openid)
    {
        if (isset($unionid) && !empty($unionid)) {
            $user_data =  $this->getORM()->where(array('unionid' => $unionid))->fetchOne();
        } else {
            // 通过openid来获取用户的信息
            if ($platform == 'MP-WEIXIN') {
                $user_data = $this->getORM()->where(array("openid_mp" => $openid))->fetchOne();
            }
            if ($platform == 'APP-PLUS') {
                $user_data =  $this->getORM()->where(array('openid' => $openid))->fetchOne();
            }
            if ($platform == 'H5') {
                $user_data =  $this->getORM()->where(array('openid_h5' => $openid))->fetchOne();
            }
        }

        $user_data['user_name'] = urldecode($user_data['user_name']);
        if (!$user_data) {
            return false;
        }
        return $user_data;
    }

    public function nextBingdingMobileModel($openid, $platform, $unionid)
    {
        //查询是否已经注册，user_name
        if (!empty($unionid) && isset($unionid)) {
            $data =  $this->wechatLogin($unionid, $platform, $openid);
        } else {
            $data =  $this->wechatLogin($openid, $platform, $openid);
        }

        if ($data['user_name'] == '' || empty($data['user_name'])) {
            //该微信号属否已经注册
            if (isset($unionid) && !empty($unionid)) {
                $get_wechat = $this->checkWechatReg($unionid, $platform, $openid);
            } else {
                $get_wechat = $this->checkWechatReg($openid, $platform, $openid);
            }
            if ($get_wechat['res'] == false) {
                if (isset($unionid) && !empty($unionid)) {
                    $email = $unionid . '@mail';
                    $user_name = $unionid;
                } else {
                    $email = $openid . '@mail';
                    $user_name = $openid;
                }
                if ($platform == 'MP-WEIXIN') {
                    $data = array(
                        'email' => $email,
                        'user_name' => $user_name,
                        'password' => md5(rand(100000, 999999)),
                        'mobile_phone' => '',
                        'reg_time' => time(),
                        'alias' => '',
                        'msn' => '',
                        'qq' => '',
                        'office_phone' => '',
                        'home_phone' => '',
                        'credit_line' => '0',
                        'openid_mp' => $openid,
                        'unionid' => $unionid
                    );
                    $openid_s = 'openid_mp';
                } else {
                    $data = array(
                        'email' => $email,
                        'user_name' => $user_name,
                        'password' => md5(rand(100000, 999999)),
                        'mobile_phone' => '',
                        'reg_time' => time(),
                        'alias' => '',
                        'msn' => '',
                        'qq' => '',
                        'office_phone' => '',
                        'home_phone' => '',
                        'credit_line' => '0',
                        'openid' => $openid,
                        'unionid' => $unionid
                    );
                    $openid_s = 'openid';
                }
                $orm = $this->getORM();
                $sql = "insert into ecs_users (email,user_name,password,mobile_phone,alias,msn,qq,office_phone,home_phone,credit_line,reg_time," . $openid_s . ",unionid) values ('" . $data['email'] . "','" . $data['user_name'] . "','" . md5(rand(100000, 999999)) . "','" . $data['mobile_phone'] . "','','','','','','0','" . time() . "','$openid','$unionid')";
                $orm->query($sql, '');
                $user_id = $orm->insert_id();
                return $user_id;
            }
        } else {
            if (isset($unionid) && !empty($unionid)) {
                $sql = "SELECT user_id FROM ecs_users WHERE unionid = '$unionid' ";
            } else {
                if ($platform == 'MP-WEIXIN') {
                    $openid_s = 'openid_mp';
                } else {
                    $openid_s = 'openid';
                }
                $sql = "SELECT user_id FROM ecs_users WHERE " . $openid_s . " = '$openid' ";
            }

            $user_id = $this->getORM()->queryRow($sql);
            return $user_id;
        }
    }

    public function getData($data)
    {
        $sql = "SELECT * FROM ecs_users WHERE user_id = '" . $data['user_id'] . "' ";
        $user = $this->getORM()->queryRow($sql);
        return $user;
    }

    public function bangdingMobileModel($user_id, $mobile, $verCode, $platform)
    {
        $email = $mobile . '@mail';
        $sql = "UPDATE ecs_users SET mobile_phone = '$mobile',user_name = '$mobile',email = '$email' WHERE user_id = '$user_id'";
        return $this->getORM()->queryRow($sql);
    }

    public function getSettingModel($user_id)
    {
        $sql = "SELECT mobile_phone FROM ecs_users WHERE user_id = '$user_id'";
        return $this->getORM()->queryRow($sql);
    }

    public function bindWechat($mobile, $vcode, $openid, $type, $platform, $unionid)
    {
        // 绑定或注册微信
        $check_time = time() - 3600;

        $sql = "SELECT * FROM ecs_vcode WHERE mobile = " . $mobile . " AND vcode = " . $vcode . " AND add_time >'" . $check_time . "'";
        $res = $this->getORM()->queryRow($sql);
        if (empty($res) || $res == '') {   // 验证不通过
            return false;
        }
        if ($type == "bind") {
            // 绑定微信
            if ($platform == 'MP-WEIXIN') {
                $sql = "update ecs_users set openid_mp = '" . $openid . "',unionid = '" . $unionid . "' where mobile_phone = '" . $mobile . "'";
            } else {
                $sql = "update ecs_users set openid = '" . $openid . "',unionid = '" . $unionid . "' where mobile_phone = '" . $mobile . "'";
            }

            $this->getORM()->queryRow($sql);  // 进行绑定
            return true;
        } elseif ($type == 'reg') {
            // 进行注册操作
            if (!isset($email) || $email == '') {
                $email = $mobile . '@mail';
            }
            if ($platform == 'MP-WEIXIN') {
                $data = array(
                    'email' => $email,
                    'user_name' => $mobile,
                    'password' => md5(rand(100000, 999999)),
                    'mobile_phone' => $mobile,
                    'reg_time' => time(),
                    'alias' => '',
                    'msn' => '',
                    'qq' => '',
                    'office_phone' => '',
                    'home_phone' => '',
                    'credit_line' => '0',
                    'openid_mp' => $openid,
                    'unionid' => $unionid
                );
                $openid_s = 'openid_mp';
            } else {
                $data = array(
                    'email' => $email,
                    'user_name' => $mobile,
                    'password' => md5(rand(100000, 999999)),
                    'mobile_phone' => $mobile,
                    'reg_time' => time(),
                    'alias' => '',
                    'msn' => '',
                    'qq' => '',
                    'office_phone' => '',
                    'home_phone' => '',
                    'credit_line' => '0',
                    'openid' => $openid,
                    'unionid' => $unionid
                );
                $openid_s = 'openid';
            }


            $orm = $this->getORM();
            $sql = "insert into ecs_users (email,user_name,password,mobile_phone,alias,msn,qq,office_phone,home_phone,credit_line,reg_time," . $openid_s . ",unionid) values ('" . $data['email'] . "','" . $data['user_name'] . "','" . md5(rand(100000, 999999)) . "','" . $data['mobile_phone'] . "','','','','','','0','" . time() . "','$openid','$unionid')";
            $orm->query($sql, '');
            //            $orm->insert($data);
            $user_id = $orm->insert_id();
            $user_data =  $this->getORM()->where(array('user_id' => $user_id))->fetchOne();
            return $user_data;
        }
    }

    public function deleteCoupon($user_id, $coupon_id)
    {
        $sql = "delete from ecs_user_bonus where bonus_id = '$coupon_id' and user_id = '$user_id'";
        $k = $this->getORM()->query($sql,'');
        return $k;
    }

    public function bindWXH5($nickname, $openid, $randpwd, $unionid)
    {
        $password = md5(rand(100000, 999999));
        $nickname = urlencode($nickname);
        $rcode = rand_code();
        $sql = "insert into ecs_users (user_name,password,reg_time,openid_h5,alias,msn,qq,office_phone,home_phone,credit_line,mobile_phone,recode,unionid) values('$nickname','$password','$randpwd','$openid','','','','','','0','','" . $rcode . "','" . $unionid . "')";
        $status = $this->getORM()->query($sql);

        return $status;
    }

    public function bindWxApp($nickname, $openid, $randpwd, $unionid)
    {
        $password = md5(rand(100000, 999999));
        $nickname = urlencode($nickname);
        $rcode = rand_code();
        $sql = "insert into ecs_users (user_name,password,reg_time,openid,alias,msn,qq,office_phone,home_phone,credit_line,mobile_phone,recode,unionid) values('$nickname','$password','$randpwd','$openid','','','','','','0','','" . $rcode . "','" . $unionid . "')";
        $status = $this->getORM()->query($sql);

        return $status;
    }

    /**
     * emoji_decode
     *
     * @param mixed $nicknamei 微信昵称
     * @access public
     * @return 转码之后的emoji表情
     */
    public function emoji_decode($nickname)
    {
        if (!$nickname) {
            return '';
        }
        $nickname_json =  '{"nickname":"' . $nickname . '"}';
        $arr = json_decode($nickname_json, true);
        $nickname = $arr['nickname'];

        return $nickname;
    }

    /**
     * emoji_encode
     *
     * @param mixed $nickname 微信昵称
     * @access public
     * @return 编码之后 的emoji表情
     */
    public function emoji_encode($nickname)
    {
        if (!$nickname) {
            return '';
        }
        $nickname = json_encode($nickname);
        $nickname = preg_replace("#(\\\u(e|d)[0-9a-f]{3})#ie", "addslashes('\\1')", $nickname);
        $nickname = json_decode($nickname);

        return $nickname;
    }


    public function get_user_visit($goods_id, $user_id, $platform)
    {
        $sql = "select * from ecs_user_visit_log where user_id = $user_id and goods_id = $goods_id";
        $res = $this->getORM()->queryAll($sql);
        if ($res) {
            $sql = "update ecs_user_visit_log set hitCounts = hitCounts+1 where user_id = $user_id and goods_id = $goods_id";
        } else {
            $sql = "insert into ecs_user_visit_log (user_id,goods_id,hitCounts,addTime,platform) values ($user_id,$goods_id,1,NOW(),'" . "$platform')";
        }
        $this->getORM()->queryAll($sql);
        $num = 0;

        $sql = "select * from ecs_user_visit_log where goods_id = $goods_id";
        $data = $this->getORM()->queryAll($sql);
        foreach ($data as $row) {
            $num += $row['hitCounts'];
        }

        return $num;
    }

    public function activeBonus($user_id, $bonus_id)
    {
        /* 查询红包序列号是否已经存在 */
        $sql = "SELECT bonus_id, bonus_sn, user_id, bonus_type_id FROM ecs_user_bonus where bonus_sn = $bonus_id";
        $row = $this->getORM()->queryRow($sql);
        if ($row) {
            if ($row['user_id'] == 0) {
                //红包没有被使用
                $sql = "SELECT send_end_date, use_end_date " .
                    " FROM ecs_bonus_type" .
                    " WHERE type_id = '" . $row['bonus_type_id'] . "'";

                $bonus_time = $this->getORM()->queryRow($sql);

                $now = time();
                if ($now > $bonus_time['use_end_date']) {
                    //使用超时
                    return false;
                }

                $sql = "UPDATE ecs_user_bonus SET user_id = '$user_id' " .
                    "WHERE bonus_id = '$row[bonus_id]'";
                $result = $this->getORM()->query($sql, '');
                if ($result) {
                    //更新成功
                    return true;
                } else {
                    //更新失败
                    return false;
                }
            } else {
                //已经被别人占用
                return false;
            }
        } else {
            //不存在这个红包
            return false;
        }
    }


//获取图形验证码的随机数
    public function getActiveBonus($user_id)
    {
        $number = rand(1000,9999);
        $sql = "update ecs_users set chartcode ='" . $number . "' where user_id = ".$user_id;
        $this->getORM()->queryRows($sql);
        return $number;
    }

    //判断图形验证码是否正确
    public function Judge_figure($user_id,$chart_code)
    {
        $sql = "select chartcode from ecs_users where user_id =" . $user_id ;
        $data = $this->getORM()->queryRow($sql);
        $chartcode = $data['chartcode'];
//        var_dump($chart_code);
//        var_dump($chartcode == $chart_code);die;
        if ($chartcode == $chart_code){
            return false;
        }else{
            return true;
        }
    }

    //获取用户推广信息
    public function getPromoteNum($user_id, $page)
    {

        $page_size = pagesize();
        $offset = ($page - 1) * $page_size;

        $sql = "select user_id,parent_id,user_name,user_pic,reg_time from ecs_users where parent_id ='" . $user_id . "' order by reg_time desc limit $offset,$page_size";

        $level_2 = $this->getORM()->queryRows($sql);
        $num =  count($level_2);
        foreach ($level_2 as $key2 => $row2) {
            $data['level_2'][$key2]['reg_time'] = date('Y-m-d', $row2['reg_time']);
            $data['level_2'][$key2]['user_name'] = $row2['user_name'];
            $data['level_2'][$key2]['level'] = "二级代理";
            $data['level_2'][$key2]['user_id'] = $row2['user_id'];
        }
        $data['num'] = $num;
        return $data;
    }

    public function LowerOrderList($user_id, $page, $parent_id)
    {
        $page_size = pagesize();
        $page = ($page - 1) * $page_size;
        $OrderModel = new OrderModel();
        $sql = "SELECT order_sn AS order_id,user_id , order_status,pay_status,shipping_status ,order_id AS id,money_paid
                    FROM ecs_order_info WHERE user_id = {$user_id}  order by add_time desc limit {$page},{$page_size} ";
        $data = $this->getORM()->queryRows($sql);


        foreach ($data as $k => $v) {
            $sql = "SELECT a.goods_name AS name,a.goods_id,a.extension_code,a.goods_number AS number,a.goods_price AS price,a.goods_attr AS spec,b.goods_thumb AS list_pic_url,a.act_id
                        FROM ecs_order_goods AS a
                        LEFT JOIN ecs_goods AS b ON a.goods_id = b.goods_id
                        WHERE order_id = " . $v['id'] . "";

            $goods_data = $this->getORM()->queryRows($sql);

            $sql  = "select * from  ecs_account_log where  order_sn='" . $v['order_id'] . "' and user_id ='" . $parent_id . "'";
            $account_data  =  $this->getORM()->queryRow($sql);
            $data[$k]['promote_money']  = $account_data['user_money'];
            $data[$k]['promote_integral']  = $account_data['rank_points'];

            foreach ($goods_data as $a => $d) {
                if ($d['act_id'] != 0) {
                    $sql = "select * from  ecs_goods_activity where act_id ='" . $d['act_id'] . "'";
                    $order_goods = $this->getORM()->queryRow($sql);
                    $goods_data[$a]['list_pic_url'] = goods_img_url($order_goods['package_image']);
                } else {
                    $goods_data[$a]['list_pic_url'] = goods_img_url($goods_data[$a]['list_pic_url']);
                }
            }
            // pay_detail
            $total_fee = $OrderModel->pay_detail($data[$k]['order_id'], $user_id);

            $data[$k]['total_amount'] = $total_fee['total_fee']; // 计算总金额

            $data[$k]['goods_list'] = $goods_data;
            if (($data[$k]['order_status'] == '0' || $data[$k]['order_status'] == '1') && $data[$k]['shipping_status'] == '0' && $data[$k]['pay_status'] == '0') {
                $data[$k]['type'] = 'unpaid';

                $data[$k]['type_status'] = '等待付款';
            }
            if ($data[$k]['order_status'] == '1' && $data[$k]['shipping_status'] == '0' && $data[$k]['pay_status'] == '2') {
                $data[$k]['type'] = 'back';
                $data[$k]['type_status'] = '等待发货';
            }
            if ($data[$k]['order_status'] == '1' && $data[$k]['shipping_status'] == '3' && $data[$k]['pay_status'] == '2') {
                $data[$k]['type'] = 'unreceived';
                $data[$k]['type_status'] = '配货中';
            }
            if ($data[$k]['order_status'] == '5' && $data[$k]['shipping_status'] == '5' && $data[$k]['pay_status'] == '2') {
                $data[$k]['type'] = 'unreceived';
                $data[$k]['type_status'] = '发货中';
            }
            if ($data[$k]['order_status'] == '5' && $data[$k]['shipping_status'] == '1' && $data[$k]['pay_status'] == '2') {
                $data[$k]['type'] = 'unreceived';
                $data[$k]['type_status'] = '已发货';
            }
            if ($data[$k]['order_status'] == '5' && $data[$k]['shipping_status'] == '2' && $data[$k]['pay_status'] == '2') {
                $data[$k]['type'] = 'received';
                $data[$k]['type_status'] = '已收货';
            }
            if ($data[$k]['order_status'] == '4' && $data[$k]['shipping_status'] == '2' && $data[$k]['pay_status'] == '2') {
                $data[$k]['type'] = 'cancelled';
                $data[$k]['type_status'] = '退换货';
            }
            if ($data[$k]['order_status'] == '2' && $data[$k]['shipping_status'] == '0' && $data[$k]['pay_status'] == '0') {
                $data[$k]['type'] = 'cancelled';
                $data[$k]['type_status'] = '订单取消';
            }
            if ($data[$k]['order_status'] == '6' && $data[$k]['shipping_status'] == '4' && $data[$k]['pay_status'] == '2') {
                $data[$k]['type'] = 'unreceived';
                $data[$k]['type_status'] = '部分已发货';
            }

            $data[$k]['add_time'] = date('Y-m-d H:i:s', $data[$k]['add_time']); // 计算总金额
        }

        $sql = "SELECT COUNT(*) AS num FROM ecs_order_info WHERE user_id = {$user_id}";
        $page_total = $this->getORM()->queryRows($sql);
        $return['pagetotal'] = ceil($page_total[0]['num'] / $page_size);
        $return['lowerorder'] = $data;
        return $return;
    }

    // 获取用户账户信息
    public function getUserAccount($user_id)
    {
        $sql = "select user_money from ecs_users where user_id = $user_id";
        $account = $this->getORM()->queryRow($sql);

        return array("res" => true, "account" => $account);
    }

    // 申请提现
    public function applyWithdrawal($user_id, $card, $withdrawal, $platform, $bank_account, $bank_addr)
    {
        if (empty($card)) {
            return array("res" => false, "msg" => "请填写卡号！");
        }
        if (empty($withdrawal)) {
            return array("res" => false, "msg" => "请输入金额！");
        }
        if (empty($bank_account)) {
            return array("res" => false, "msg" => "请输入开户行！");
        }
        if (empty($bank_addr)) {
            return array("res" => false, "msg" => "请输入开户行地址！");
        }

        //将提现记录写入到表中
        /*process_type: 0为帐户冲值 1从帐户提款 2购买商品 3 取消订单*/
        $process_type  = 1;
        $user_note  = '';
        $payment  = '银行转账';
        $sql = 'INSERT INTO ecs_user_account (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment, is_paid,bank_cart,bank_account,bank_addr)' .
            " VALUES ('$user_id', '', '$withdrawal', '" . time() . "', 0, '', '$user_note', '$process_type', '$payment', 0,'" . $card . "','" . $bank_account . "','" . $bank_addr . "')";
        $this->getORM()->queryRows($sql);
        $sql = "update ecs_users set user_money = user_money - '" . $withdrawal . "' where user_id = $user_id";
        $this->getORM()->queryRows($sql);
        $sql = "insert into ecs_account_log(user_id,user_money,frozen_money,rank_points,pay_points,change_time,change_desc,change_type) values ($user_id,'" . $withdrawal . "',0,0,0,'" . time() . "','提现扣除',1)";
        $this->getORM()->queryRows($sql);
        return array("res" => true, "msg" => "发起提现请求成功！");
    }


    public function getQq()
    {
        $sql = "select value from ecs_shop_config where code = 'qq'";
        $qq = $this->getORM()->queryRow($sql);
        return $qq['value'];
    }

    public function my_subordinates()
    {
        $sql ="SELECT value from ecs_shop_config WHERE code = 'my_subordinates'";
        $data = $this->getORM()->queryRow($sql);
        return $data['value'];
    }

    /**
     * 判断苹果账户是否存在
     * @param string $openid
     */
    public function appleOpenidExist($openid = '')
    {
        return $this->getORM()->where(array('apple_openid'=>$openid))->fetchRow();
    }

    /**
     * 苹果账号注册
     * @param string $openid
     */
    public function appleReg($openid = '')
    {
        $password = md5(rand(100000,999999));
        $nickname = 'apple_'. rand(10000,99999);
        $regTime = time();
        $rcode = rand_code();
        $sql = "insert into ecs_users (user_name,password,reg_time,apple_openid,alias,msn,qq,office_phone,home_phone,credit_line,mobile_phone,recode) values('$nickname','$password','$regTime','$openid','','','','','','0','','".$rcode."')";
        $status = $this->getORM()->query($sql);

        return $status;
    }

    /**
     * 是否开启apple登录
     * @return int
     */
    public function apple_login()
    {
        $open = 0;
        $is_exit_sql = "select * from ecs_shop_config where code = 'apple_login'";
        $exit = $this->getORM()->queryRow($is_exit_sql);
        if($exit) {
            $open = $exit['value'];
        }
        return $open;
    }

//public function appzhanghaozhuxiao($user_name,$password,$confirmPassword)
//{
//    //1、查出用户名//4.查出用户密码
//    $sql = "select * from ecs_users where user_name = '$user_name'";
//    $data = $this->getORM()->queryRow($sql);
//    //2.验证用户是否存在
//    if (empty($data['user_name'])){
//        return false;
//    }
//    //3.验证两次密码是否一致
//    if ($password != $confirmPassword){
//        return false;
//    }
//    //如果该用户存在验证密码
//    //123123    567567
//    if ($data['ec_salt']) {
//        $password = md5(md5($password) . $data['ec_salt']);
//    } else {
//        $password = md5($password);
//    }
//    if ($data['password'] == $password){
//        return true;
//    }
//    return false;
//
//
//}








}
