# think-jwt

基于[lcobucci/jwt](https://packagist.org/packages/lcobucci/jwt)封装的一个jwt工具包,在前后端分离时
它非常有用。

由 [x852/think-jwt](https://gitee.com/x852/think-jwt) 改造

# 安装

~~~
composer require okcoder/think-jwt
~~~

# 配置

安装完毕后会自动生成配置文件config/jwt.php

```php
<?php
return [
    //注销token缓存key
    'delete_key' => 'delete_token',
    //时区
    'timezone' => 'Asia/Shanghai',
    //编号
    'jti' => '4f1g23a12aa',
    //签名密钥
    'sign' => 'a4693602cbb7a',
    //签发人
    'iss' => 'http://example.cn',
    //接收人
    'aud' => [
        'http://example.com',
        'http://example.org',
        'http://example.top',
    ],
    //主题
    'sub' => '100',
    //有效期(默认两个小时)  单位:秒
    'exp' => 3600 * 2
];
```

# 使用

think-jwt的使用方式非常简单,因为它不管你是如何传递token参数的，你可以选择Header、Cookie、Param，那都是你的自由 think-jwt只纯粹的提供3个静态方法`create()`,`parse()`
,`logout()`,分别是生成，解析，和注销。

## create

> 示例：通过用户id生成token

```php
$token = \okcoder\think\jwt\Jwt::create(100);
```

得到如下结果:

~~~
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvYXBpLnh4eC5jb20iLCJhdWQiOiJodHRwczpcL1wvd3d3Lnh4eC5jb20iLCJqdGkiOiIzZjJnNTdhOTJhYSIsImlhdCI6MTY1MTg1MTQ2MywibmJmIjoxNjUxODUxNDYyLCJleHAiOjE2NTE4NTg2NjMsIl90aGlua0p3dCI6IntcImlkXCI6MTAwLFwibmFtZVwiOlwiSmFja1wifSJ9.yVjHKxtZii3YfSwGMfFX_PIuBM5co-xpALx7p-Ld2_A
~~~

## parse

用于解析token，直接返回create设置的数据

### 异常

|异常类|状态码|说明|
|----|----|----|
|JwtException| -1 | JWT异常错误基类 |
|TokenLogoutOffException| 10000 | token已经被注销 |
|TokenParseFailException| 10001 | token解码失败 |
|IssuerVerifyFailException| 10002 | 签发人验证失败 |
|AudienceVerifyFailException| 10003 | 接收人验证失败 |
|TokenExpiredException| 10004 | token已过期 |
|JtiVerifyFailException| 10005 | 编号验证失败 |
|SubjectVerifyFailException| 10006 | 主题验证失败 |
|SignKeyVerifyFailException| 10007 | 签名密钥验证失败 |

> 示例

```php
//生成token
$token = \okcoder\think\jwt\Jwt::create(100);
//解析token  extract函数可以把数组分别作为变量解析出来
try {
    $data = \okcoder\think\jwt\Jwt::parse($token);
 }catch (\okcoder\think\jwt\exceptions\JwtException){
    
 }
// 100
```

## logout

jwt的token一经签发是它是无法被注销的，所以只能通过服务端来进行判断(结果到这里又变成有状态的了),这里 是通过把要注销的token存储到缓存中，所以配置文件`jwt.php`中它有个`delete_key`
配置就是用来实现注销功能的，默认 缓存的key是`delete_token`,如果和你的业务发生冲突，你可以自行更改。 这里的的缓存用的是tp6框架自带 的缓存`cache`方法

```php
\okcoder\think\jwt\Jwt::logout("eyJ0eXAiOiJKV1QiLCJhb...");
```

当注销成功后，再使用上面的`parse`方法就会抛出异常`TokenLogoutOffException`，提示你该token已经被注销
