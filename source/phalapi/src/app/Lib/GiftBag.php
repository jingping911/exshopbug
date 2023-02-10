<?php
namespace App\Lib;

use App\Model\CartGift;
use App\Model\Goods_activity;
use App\Model\Goods_table;
use App\Model\Package_goods;

class GiftBag {
	private $goodsActivity;
	private $goods;
	private $packageGoods;
	private $cart;
	public function __construct() {
		$this->goodsActivity = new Goods_activity();
		$this->goods = new Goods_table();
		$this->packageGoods = new Package_goods();
		$this->cart = new CartGift();
	}
	public function getGiftBag($actId) {
		$libao = $this->whereActId($actId);
		$libao['packagePrice'] = $this->giftPrice($libao['ext_info']);
		$goods = $this->whereGoodsId($actId);
		return $libao;
	}
	public function addCart($actId, $num, $userId, $isBuy) {
		$data = $this->whereActId($actId);
		$gift['goods_id'] = $actId;
		$gift['goods_number'] = $num;
		$gift['user_id'] = $userId;
		$gift['goods_name'] = $data['act_name'];
		$gift['goods_price'] = $this->giftPrice($data['ext_info']);
		$gift['goods_attr'] = '';
		$gift['extension_code'] = 'package_buy';
		$cartData = $this->cart->whereGoodsAndUserId($userId, $actId);
		if ($cartData) {
			if ($isBuy) {
				$giftBuy['goods_price'] = $this->giftPrice($data['ext_info']);
				$giftBuy['goods_number'] = $num;
				$giftBuy['is_checked'] = 'true';
				$this->cart->updateCart($cartData['rec_id'], $giftBuy);
			} else {
				$giftBuy['goods_price'] = $this->giftPrice($data['ext_info']);
				$giftBuy['goods_number'] = $num + $cartData['goods_number'];
				$this->cart->updateCart($cartData['rec_id'], $giftBuy);
			}
		} else {
			if ($isBuy) {
				$gift['is_checked'] = 'true';
				$this->cart->insertGift($gift);
			} else {
				$this->cart->insertGift($gift);
			}
		}
	}
	public function getGoods($actId) {
		return $this->whereGoodsId($actId);
	}
	protected function giftPrice($extInfo) {
		$ext = unserialize($extInfo);
		return $ext['package_price'];
	}
	protected function whereActId($actId) {
		return $this->goodsActivity->whereActId($actId);
	}
	protected function wherePackageId($actId) {
		return $this->packageGoods->wherePackageId($actId);
	}
	protected function whereGoodsId($actId) {
		$packageGoods = $this->wherePackageId($actId);
		foreach ($packageGoods as $key => $value) {
			$goods[$key] = $this->goods->whereGoodsId($value['goods_id']);
			$goods[$key]['goods_thumb'] = goods_img_url($goods[$key]['goods_thumb']);
		}
		return $goods;
	}

}