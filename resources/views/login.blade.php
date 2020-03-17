<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=yes" />
    <link rel="stylesheet" type="text/css" href="/asset/login/css/normalize.css" />
    <link rel="stylesheet" type="text/css" href="/asset/login/css/demo.css" />
    <!--必要样式-->
    <link rel="stylesheet" type="text/css" href="/asset/login/css/component.css" />
    <link rel="stylesheet" type="text/css" href="/asset/layui/css/layui.css" />
    <script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="/asset/layui/layui.js"></script>
</head>
<body>
<div class="container demo-1">
    <div class="content">
        <div id="large-header" class="large-header">
            <canvas id="demo-canvas"></canvas>
            <div class="logo_box">
                <h3>登录 woann-chat</h3>
                <form action="#" name="f" method="post">
                    <input type="password" style="position:absolute;top:-999px"/>
                    <div class="input_outer">
                        <span class="u_user"></span>
                        <input name="username" class="text" style="color: #FFFFFF !important" type="text" placeholder="请输入账户" value="">
                    </div>
                    <div class="input_outer">
                        <span class="us_uer"></span>
                        <input name="password" class="text" style="color: #FFFFFF !important; position:absolute; z-index:100;"value="" type="password" placeholder="请输入密码">
                    </div>
                    <div class="mb2"><a id = "sub" lay-filter="sub" class="act-but submit" href="javascript:;" style="color: #FFFFFF">登录</a></div>
                </form>
                <p style="text-align: center;">还没有账号？立即<a style="color: #cccccc;" href="javascript:;" onclick="register()"> 注册 </a></p>
            </div>
        </div>
    </div>
</div><!-- /container -->
<script src="/asset/login/js/jquery-1.8.min.js"></script>
<script src="/asset/login/js/jquery.cookie.js"></script>
<script src="/asset/login/js/TweenLite.min.js"></script>
<script src="/asset/login/js/EasePack.min.js"></script>
<script src="/asset/login/js/rAF.js"></script>
<script src="/asset/login/js/demo-1.js"></script>
</body>
<script>
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?6b614cdee352cbb9f55e05ad81084c3a";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
<script>
    function register() {
        layer.open({
            type: 2,
            title: '注册',
            shadeClose: true,
            shade: 0.8,
            area: ['40%', '70%'],
            content: '/register' //iframe的url
        });
    }
    //加载弹出层组件
    layui.use('layer',function(){

        var layer = layui.layer;

        //登录的点击事件
        $("#sub").on("click",function(){
            login();
        })

        $("body").keydown(function(){
            if(event.keyCode == "13"){
                login();
            }
        })

        //登录函数
        function login(){
            var username = $(" input[ name='username' ] ").val();
            var password = $(" input[ name='password' ] ").val();
            $.ajax({
                url:"/api/auth/login",
                data:{"username":username,"password":password},
                type:"post",
                dataType:"json",
                success:function(res){
                    if(res.code == 200){
                        layer.msg(res.message);
                        $.cookie("token", res.token);
                        setTimeout(function(){
                            window.location = "/";
                        },1500);
                    }else{
                        layer.msg(res.message,function(){});
                    }
                }
            })
        }
    })
</script>
</html>
