<?php
/**
 * 统一访问入口
 */

//error_reporting(0);
error_reporting(E_ALL^E_NOTICE^E_WARNING);


require_once dirname(__FILE__) . '/init.php';

$pai = new \PhalApi\PhalApi();
$pai->response()->output();

