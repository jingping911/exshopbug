<?php

/* 公共函数库 */
//error_reporting(0);

function getConfigPageSize()
{
    return 6;
}

function getEcHttpHost()
{
    //$di->config = new FileConfig(API_ROOT . '/config');
    $url = \PhalApi\DI()->config->get('app');
    return $url['host_url'];
}

function getUrl()
{
    //$di->config = new FileConfig(API_ROOT . '/config');
    $url = \PhalApi\DI()->config->get('app');
    return $url['host_url'];
}

function pagesize()
{
    //$di->config = new FileConfig(API_ROOT . '/config');
    $data = \PhalApi\DI()->config->get('app');
    if ($data['page_size'] == '') {
        $data['page_size'] = 12;
    }
    return $data['page_size'];
}
//推荐码
function rand_code()
{
    $rand = "123567890qwertyuioplkjhgfdsazxcvbnmQWERTYUIOPLKJHGFDSAZXCVBNM";
    mt_srand(10000000 * (float)microtime());
    $referrer_code = '';
    for ($i = 0, $str = '', $lc = strlen($rand) - 1; $i < 6; $i++) {
        $referrer_code .= $rand[mt_rand(0, $lc)];
    }
    return $referrer_code;
}


function default_category_img()
{
    // 默认分类图片
    $data = \PhalApi\DI()->config->get('app');
    return $data['default_category_img'];
}

function default_category_goodsImage()
{
    //默认商品图片
    $data = \PhalApi\DI()->config->get('app');
    return $data['default_category_goodsImage'];
}

function default_category_banner()
{
    // 默认分类banner图
    $data = \PhalApi\DI()->config->get('app');
    return $data['default_category_banner'];
}

function default_article_img()
{
    // 默认文章展示图
    $data = \PhalApi\DI()->config->get('app');
    return $data['default_article_img'];
}

/**
 * 检测是否是URL
 */
function is_url($v)
{
    $pattern = "#(http|https)://(.*\.)?.*\..*#i";
    if (preg_match($pattern, $v)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 返回商品图片链接
 */
function goods_img_url($url)
{
    if (empty($url)) {
        $default = new App\Model\User;
        $image =  $default->default_image();
        return $image;
    }
    $data = \PhalApi\DI()->config->get('app');
    $pattern = "#(http|https)://(.*\.)?.*\..*#i";
    if (preg_match($pattern, $url)) { // 是外链直接返回
        return $url;
    } else { // 不是外链则加上相对应的地址
        $url = $data['host_url'] . $url;
        return $url;
    }
}

function alipay_h5_url()
{
    // 支付宝H5支付地址
    $url = \PhalApi\DI()->config->get('app');
    return $url['alipay_h5_url'];
}

function filter_character($data){
    $pattern = "/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u";
    if(preg_match($pattern, $data)) {
        return true;
    }else {
        return false;
    }
}
/**
 * 支付配置 -- 支付宝配置
 */

function alipay_config()
{
    $config = \PhalApi\DI()->config->get('app');
    // $return = [];

    // $return['gatewayUrl'] = $config['gateway_url'];
    // $return['appid'] = $config['alipay_appid'];
    // $return['rsaPrivateKey'] = $config['rsa_private_key'];
    // $return['alipayrsaPublicKey'] = $config['alipayrsa_public_key'];
    // $return['notifyUrl'] = $config['alipay_notify_url'];

    // return $return;
    return array(
        'gatewayUrl' => $config['gateway_url'],
        'appid' => $config['alipay_appid'],
        'rsaPrivateKey' => $config['rsa_private_key'],
        'alipayrsaPublicKey' => $config['alipayrsa_public_key'],
        'notifyUrl' => $config['alipay_notify_url'],
    );
}

/**
 * 支付配置 -- 微信配置
 */

function wxpay_config()
{
    $config = \PhalApi\DI()->config->get('app');
    return array(
        "appid" => $config['wxpay_appid'],
        "appid_app" => $config['wxpay_appid_app'],
        "appid_mp" => $config['wxpay_appid_mp'],
        "mchid" => $config['mchid'],
        "notify_url" => $config['wxpay_notify_url'],
        "key" => $config['key'],
        "appsecret" => $config['appsecret'],
        "redirect_url" => $config['redirect_url'],
    );
}

/** 
 * 替换fckedit中的图片 添加域名 
 * @param  string $content 要替换的内容 
 * @param  string $strUrl 内容中图片要加的域名 
 * @return string  
 * @eg  
 */
function replacePicUrl($content = null, $strUrl = null)
{
    if ($strUrl) {
        //提取图片路径的src的正则表达式 并把结果存入$matches中    
        preg_match_all("/<img(.*)src=\"([^\"]+)\"[^>]+>/isU", $content, $matches);
        $img = "";
        if (!empty($matches)) {
            //注意，上面的正则表达式说明src的值是放在数组的第三个中    
            $img = $matches[2];
        } else {
            $img = "";
        }
        if (!empty($img)) {
            $patterns = array();
            $replacements = array();
            foreach ($img as $imgItem) {
                $final_imgUrl = goods_img_url($imgItem);
                $replacements[] = $final_imgUrl;
                $img_new = "/" . preg_replace("/\//i", "\/", $imgItem) . "/";
                $img_new = preg_replace("/\(/i", "\(", $img_new);
                $img_new = preg_replace("/\)/i", "\)", $img_new);
                $patterns[] = $img_new;
            }

            //让数组按照key来排序    
            ksort($patterns);
            ksort($replacements);

            //替换内容    
            $vote_content = preg_replace($patterns, $replacements, $content);

            return $vote_content;
        } else {
            return $content;
        }
    } else {
        return $content;
    }
}
