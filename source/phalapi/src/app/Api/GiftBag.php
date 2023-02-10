<?php
namespace App\api;

use PhalApi\Api;
use App\Lib\GiftBag as GiftBagLib;
/*
 *超值礼包详情页面接口
 */
class GiftBag extends Api {
	private $doMain;
	public function __construct(){
		$this->doMain=new GiftBagLib();
	}
	public function getRules() {
		return array(
			'getData' => array('actId' => array('name' => 'actId')),
			'addCart' => array(
				'actId'=>array('name'=>'actId'),
				'num'=>array('name'=>'num'),
				'userId'=>array('name'=>'user_id'),
				'isBuy'=>array('name'=>'isBuy'),
			),
		);
	}
	//返回所有数据,键名为表名，键值为该表的数据
	public function getData() {
		$data['goods_activity'] = $this->doMain->getGiftBag($this->actId);
		$data['goods'] = $this->doMain->getGoods($this->actId);
		return $data;
	}
	public function addCart(){
		$this->doMain->addCart($this->actId,$this->num,$this->userId,$this->isBuy);
	}
}