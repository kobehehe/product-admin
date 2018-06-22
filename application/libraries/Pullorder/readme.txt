php demo 代码
可以实现授权，保存session，拉订单


注意：需要安装以下证书，否则会报错
https://blog.csdn.net/sanbingyutuoniao123/article/details/71124655
下载一个ca-bundle.crt：
https://github.com/bagder/ca-bundle/blob/e9175fec5d0c4d42de24ed6d84a06d504d5e5a09/ca-bundle.crt
在php.ini加入“curl.cainfo="真实路径/ca-bundle.crt"” ，重启web服务器



测试方法：
远程连接电脑：205.209.185.18:6666  用户名：administrator 密码：haonanji123
在远程电脑的chrome 浏览器输入demo代码网址，就可以授权etsy了。
授权好之后，在其它电脑访问都可以拉订单了。