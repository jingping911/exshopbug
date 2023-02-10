<?php

namespace App\Api;

use PhalApi\Api;

/**
 * ECSHOP代码下载
 * @author: dogstar <chanzonghuang@gmail.com> 2014-10-04
 */
class Site extends Api
{
    public function getRules()
    {
        return array(
            'indexMobile' => array(
                'host'  => array('name' => 'host', 'require' => true, 'default' => 'https://ecshop.yunyingbao.net', 'desc' => '主域名'),
                'apihost'  => array('name' => 'apihost', 'require' => true, 'default' => 'https://api.ecshop.yunyingbao.net', 'desc' => 'api主域名'),
            ),
            'indexMp' => array(
                'host'  => array('name' => 'host', 'require' => true, 'default' => 'https://ecshop.yunyingbao.net', 'desc' => '主域名'),
                'apihost'  => array('name' => 'apihost', 'require' => true, 'default' => 'https://api.ecshop.yunyingbao.net', 'desc' => 'api主域名'),
                'appid'  => array('name' => 'appid', 'require' => true, 'default' => 'os1oa4x4oVgPeh9Bc8iUjKLxKBcs', 'desc' => 'appid'),
            ),
            'indexApi' => array(
                'host'  => array('name' => 'host', 'require' => true, 'default' => 'https://ecshop.yunyingbao.net', 'desc' => '主域名'),
            ),
        );
    }
    /**
     * 下载H5代码
     * @desc 下载H5代码
     * @exception 400 非法请求，参数传递错误
     */
    public function indexMobile()
    {
        $url1 = $this->apihost;
        $url2 = $this->host;

        //H5配置
        $root1 = $this->getDir('../../h5/static/js');

        foreach ($root1 as $k => $v) {
            if (strstr($v, 'h5/static/js/index.')) {
                $file_url1 = $v;
            }
        }
        $str1 = file_get_contents($file_url1);

        $str1 = preg_replace("/((http|https):\/\/)?([^\/]+)\/\?service=/", $url1 . '/?service=', $str1);
        $str1 = preg_replace("/((http|https):\/\/)?([^\/]+)\/js\/region.json/", $url2 . '/js/region.json', $str1);
        file_put_contents($file_url1, $str1);

        $zipName = 'h5.zip';
        $path = '../../h5';
        // 如果压缩文件不存在，就创建压缩文件
        if (!is_file($zipName)) {
            $fp = fopen($zipName, 'w');
            fclose($fp);
        }
        $zip = new \ZipArchive();
        // OVERWRITE选项表示每次压缩时都覆盖原有内容，但是如果没有那个压缩文件的话就会报错，所以事先要创建好压缩文件
        // 也可以使用CREATE选项，此选项表示每次压缩时都是追加，不是覆盖，如果事先压缩文件不存在会自动创建
        if ($zip->open($zipName, \ZipArchive::OVERWRITE) === true) {
            $current = '';
            $this->addFileToZip($path, $current, $zip);
            $zip->close();
        } else {
            $return['url'] = $file_url1;
            $return['msg'] = '地址替换成功,打开zip文件失败！';
            return $return;
        }

        echo "<script>window.location.href='http://" . $_SERVER['SERVER_NAME'] . "/h5.zip'</script>";
        exit;
        // $return['url'] = $file_url1;
        // $return['msg'] = '地址替换成功';
        // return $return;
    }

    /**
     * 下载小程序代码
     * @desc 下载小程序代码
     * @exception 400 非法请求，参数传递错误
     */
    public function indexMp()
    {
        $url1 = $this->apihost;
        $url2 = $this->host;
        $appid = $this->appid;

        $file_url2 = '../../mp-weixin/project.config.json';
        $str2 = file_get_contents($file_url2);
        $str2 = preg_replace("/\"appid\"\:\s\"[0-9,a-z,A-Z]+\"/", '"appid": "' . $appid . '"', $str2);
        file_put_contents($file_url2, $str2);

        $file_url3 = '../../mp-weixin/common/vendor.js';
        $str3 = file_get_contents($file_url3);
        $str3 = preg_replace("/((http|https):\/\/)?([^\/]+)\/\?service=/", $url1 . '/?service=', $str3);
        $str3 = preg_replace("/((http|https):\/\/)?([^\/]+)\/js\/region.json/", $url2 . '/js/region.json', $str3);
        file_put_contents($file_url3, $str3);

        $zipName = 'mp-weixin.zip';
        $path = '../../mp-weixin';
        // 如果压缩文件不存在，就创建压缩文件
        if (!is_file($zipName)) {
            $fp = fopen($zipName, 'w');
            fclose($fp);
        }
        $zip = new \ZipArchive();
        // OVERWRITE选项表示每次压缩时都覆盖原有内容，但是如果没有那个压缩文件的话就会报错，所以事先要创建好压缩文件
        // 也可以使用CREATE选项，此选项表示每次压缩时都是追加，不是覆盖，如果事先压缩文件不存在会自动创建
        if ($zip->open($zipName, \ZipArchive::OVERWRITE) === true) {
            $current = '';
            $this->addFileToZip($path, $current, $zip);
            $zip->close();
        } else {
            $return['url1'] = $file_url2;
            $return['url2'] = $file_url3;
            $return['msg'] = '地址替换成功,打开zip文件失败！';
            return $return;
        }

        echo "<script>window.location.href='http://" . $_SERVER['SERVER_NAME'] . "/mp-weixin.zip'</script>";
        exit;
        // $return['url1'] = $file_url2;
        // $return['url2'] = $file_url3;
        // $return['msg'] = '地址替换成功';
        // return $return;
    }
    /**
     * phpapi域名配置
     * @desc phpapi域名配置
     * @exception 400 非法请求，参数传递错误
     */
    public function indexApi()
    {
        $url2 = $this->host;
        // phpapi域名配置
        $file_url4 = '../config/app.php';
        $str4 = file_get_contents($file_url4);
        $str4 = preg_replace("/\'host_url\'\=\>\'((http|https):\/\/)?([^\/]+)\/\'/", "'host_url'=>'" . $url2 . "/'", $str4);
        $str4 = preg_replace("/\'host_url_res\'\=\>\'((http|https):\/\/)?([^\/]+)\/\'/", "'host_url_res'=>'" . $url2 . "/'", $str4);
        file_put_contents($file_url4, $str4);
        $return['url'] = $file_url4;
        $return['msg'] = '地址替换成功';
        return $return;
    }
    protected function getDir($path)
    {
        if (!file_exists($path)) {
            return array();
        }
        $files = scandir($path);
        $fileItem = array();
        foreach ($files as $v) {
            $newPath = $path . DIRECTORY_SEPARATOR . $v;
            if (is_dir($newPath) && $v != '.' && $v != '..') {
                $fileItem = array_merge($fileItem, $this->getDir($newPath));
            } else if (is_file($newPath)) {
                $fileItem[] = $newPath;
            }
        }
        return $fileItem;
    }

    protected function addFileToZip($path, $current, $zip)
    {
        // 打开文件夹资源
        $handler = opendir($path);
        // 循环读取文件夹内容
        while (($filename = readdir($handler)) !== false) {
            // 过滤掉Linux系统下的.和..文件夹
            if ($filename != '.' && $filename != '..') {
                // 文件指针当前位置指向的如果是文件夹，就递归压缩
                if (is_dir($path . '/' . $filename)) {
                    $this->addFileToZip($path . '/' . $filename, $filename, $zip);
                } else {
                    // 为了在压缩文件的同时也将文件夹压缩，可以设置第二个参数为文件夹/文件的形式，文件夹不存在自动创建压缩文件夹
                    $zip->addFile($path . '/' . $filename, $current . '/' . $filename);
                }
            }
        }
        @closedir($handler);
    }
}
