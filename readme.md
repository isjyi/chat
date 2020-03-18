> `woann-chat`是一个基于LaravelS和Layim编写的聊天系统。

项目地址：[https://github.com/isjyi/chat](https://github.com/isjyi/chat)

演示地址：[http://im.aishyu.com](http://im.aishyu.com) （测试账号同[安装](https://github.com/isjyi/chat#%E5%AE%89%E8%A3%85)中的测试账号同步）

| 依赖 | 说明 |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/zh/install.php) | `>= 7.3` `推荐7.2` |
| [Swoole](https://www.swoole.com/) | `>= 4.2.9` `从2.0.12开始不再支持PHP5` `推荐4.2.9+` |
| [LaravelS](https://github.com/hhxsv5/laravel-s) | `>=3.6. LaravelS是一个将swoole和laravel框架结合起来的胶水工具` |

## 声明
* 此项目是基于LaravelS作为服务端，所以在此之前，你要熟悉swoole、laravel、还有将他们完美结合的`LaravelS`[https://github.com/hhxsv5/laravel-s](https://github.com/hhxsv5/laravel-s)
* 前端部分是采用layui,在此郑重说明，layui中的im部分`layim`并不开源，仅供交流学习，请勿将此项目中的layim用作商业用途。
* 此项目是基于https://github.com/woann/chat完整重构

## 功能列表
* 登录 | 没什么好说的...
* 注册 | 注册过程中为用户分配了一个默认分组，并将用户添加到所有人都在的一个群（10001）
* 查找-添加好友 | 可以根据用户名、昵称、id来查找，不输入内容则查找所有用户，点击发起好友申请
* 查找-加入群 | 可根据群昵称、群id查找群聊，点击加入
* 创建群 | 创建一个群聊
* 消息盒子 | 用来接受好友请求和同意或拒绝好友请求的系统消息
* 个性签名 | 并没有什么卵用的功能
* 一对一聊天 | 可发送文字、表情、图片、文件、代码等
* 群聊 | 新成员加入群聊时，如果此刻你正开启着该群对话框，将收到新人入群通知
* 查看群成员
* 临时会话 | 在群成员中，点击群成员头像即可发起临时会话
* 历史记录 | 聊天面板只显示20条记录，更多记录点击`聊天记录`查看
* 离线消息 | 对方不在线的时候，向对方发起好友请求或者消息，将在对方上线后第一时间推送
* 换肤 | 这个是layim自带的东西。。
* ...

## 安装
* 执行安装命令`git clone https://github.com/isjyi/chat`将项目克隆到本地
* cp .env.example .env
* 修改`.env`文件，按照你的数据库账号密码进行配置
* composer install
* php artisan key:generate
* php artisan jwt:secret
* php artisan migrate --seed
* 运行laravelS `php bin/laravels start`
* 此时访问`127.0.0.1:9501`即可进入登录页面
* 测试账号 `admin` 密码`123456`，当然你也可以自行注册。

## 配合nginx使用
* nginx配置文件
```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
upstream laravels {
    # 通过 IP:Port 连接
    server 127.0.0.1:9501 weight=5 max_fails=3 fail_timeout=30s;
    # 通过 UnixSocket Stream 连接，小诀窍：将socket文件放在/dev/shm目录下，可获得更好的性能
    #server unix:/xxxpath/laravel-s-test/storage/laravels.sock weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.1.1:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.1.2:5200 backup;
    keepalive 16;
}
server {
    listen 80;
    # 别忘了绑Host哟
    server_name xxx.com;#在这里配置域名
    root /xxx/woann-chat/public;#在这里配置文件目录
    access_log /yyypath/log/nginx/$server_name.access.log;
    autoindex off;
    index index.html index.htm;
    # Nginx处理静态资源(建议开启gzip)，LaravelS处理动态资源。
    location / {
        try_files $uri @laravels;
    }
    # 当请求PHP文件时直接响应404，防止暴露public/*.php
    #location ~* \.php$ {
    #    return 404;
    #}
    # Http和WebSocket共存，Nginx通过location区分
    # !!! WebSocket连接时路径为/ws
    # Javascript: var ws = new WebSocket("ws://xxx.com/ws");
    location =/ws {
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout：如果60秒内被代理的服务器没有响应数据给Nginx，那么Nginx会关闭当前连接；同时，Swoole的心跳设置也会影响连接的关闭
        # proxy_read_timeout 60s;
        proxy_http_version 1.1;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header Server-Protocol $server_protocol;
        proxy_set_header Server-Name $server_name;
        proxy_set_header Server-Addr $server_addr;
        proxy_set_header Server-Port $server_port;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_pass http://laravels;
    }
    location @laravels {
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout 60s;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header Server-Protocol $server_protocol;
        proxy_set_header Server-Name $server_name;
        proxy_set_header Server-Addr $server_addr;
        proxy_set_header Server-Port $server_port;
        proxy_pass http://laravels;
    }
}
```
* 将`resources/view/index.blade.php`文件中简历websocket中的
```javascript
socket = new WebSocket('ws://127.0.0.1:9501?sessionid={{ $sessionid }}');
```
替换成
```javascript
socket = new WebSocket('ws://xxx.com/ws?sessionid={{ $sessionid }}');
```

## 待完成
* 前端关于jwt axios的拦截
* ...

