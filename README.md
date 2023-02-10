## EcShop v4.1.5 file upload vulnerability

### 1.Affected version

----

EcShop v4.1.5

### 2.**Firmware download address**

https://www.ecshop.com/download

or this project

### 3.**Vulnerability details**

![image-20230210211607769](https://github.com/jingping911/exshopbug/blob/main/1.png)

The file upload vulnerability in the ecshop management background allows the webshell to be uploaded to elevate permissions.

**Filtering is not strict in file upload, which leads to bypassing the restriction of. php**

![image-20230210211607769](https://github.com/jingping911/exshopbug/blob/main/7.jpg)


### 4.**Vulnerability details**

1.First log in to the background, and then use burp to grab the cookie

![image-20230210212025434](https://github.com/jingping911/exshopbug/blob/main/2.png)

2.After getting the cookie, import or open and paste the burp request package in the attachment into burp (it is better to import the data package, pasting may cause data package errors), and then replace the cookie value with the cookie value just obtained

![image-20230210212025434](https://github.com/jingping911/exshopbug/blob/main/3.png)

3.Then send the constructed request packet, return 200, and generate the corresponding file locally

![image-20230210212025434](https://github.com/jingping911/exshopbug/blob/main/4.png)

4.Use behinder to connect to the trojan file. The trojan file is/themes/hhhh/123.php in the root directory, and the password is **a**, and successfully connect to webshell

*/ECShop_ V4.1.5/source/ecshop/themes/hhhh/123.phP*


![image-20230210212025434](https://github.com/jingping911/exshopbug/blob/main/5.png)

## 5.author

Wangjingping 
 
