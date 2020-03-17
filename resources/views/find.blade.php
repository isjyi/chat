<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>查找</title>
    <link rel="stylesheet" href="/asset/layuiv2/css/layui.css" media="all">
</head>
<body>
<div class="layui-row">
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">
            <li @if($type == 'user'  || $type == '')  class="layui-this" @endif>找人</li>
            <li @if($type == 'group')  class="layui-this" @endif>找群</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item @if($type == 'user' || $type == '')  layui-show @endif">
                <div>
                    <input style="float: left;width: 90%;" type="text" id="user-wd" required lay-verify="required"
                           placeholder="请输入ID/昵称" autocomplete="off" class="layui-input"
                           @if($type == 'user') value="{{ $wd }}" @endif>
                    <button onclick="findUser()" style="float: right;width: 10%" class="layui-btn">
                        <i class="layui-icon">&#xe615;</i> 查找
                    </button>
                </div>
                <div class="layui-row">
                    @foreach($user_list as $k=>$v)
                        <div class="layui-col-md4" style="border-bottom: 1px solid #f6f6f6">
                            <div class="layui-card">
                                <div class="layui-card-header">{{ $v->nickname }}({{ $v->id }})</div>
                                <div class="layui-card-body">
                                    <img style="width: 75px;height: 75px;object-fit: cover;" src="{{ $v->avatar }}"
                                         alt="">
                                    <button onclick="addFriend({{ $v->id }},'{{ $v->nickname }}','{{ $v->avatar }}')"
                                            style="float: right" class="layui-btn layui-btn-normal layui-btn-sm">
                                        <i class="layui-icon">&#xe654;</i> 添加
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="layui-tab-item @if($type == 'group')  layui-show @endif">
                <div>
                    <input style="float: left;width: 80%;" type="text" id="group-wd" required lay-verify="required"
                           placeholder="请输入群Id/群名称" autocomplete="off" class="layui-input"
                           @if($type == 'group') value="{{ $wd }}" @endif>
                    <button onclick="createGroup()" style="float: right;width: 10%" class="layui-btn layui-btn-warm">
                        <i class="layui-icon">&#xe654;</i> 创建群
                    </button>
                    <button onclick="findGroup()" style="float: left;width: 10%;margin-left: 0" class="layui-btn">
                        <i class="layui-icon">&#xe615;</i> 查找群
                    </button>
                </div>
                @foreach($group_list as $k=>$v)
                    <div class="layui-col-md4" style="border-bottom: 1px solid #f6f6f6">
                        <div class="layui-card">
                            <div class="layui-card-header">{{ $v->groupname }}({{ $v->id }})</div>
                            <div class="layui-card-body">
                                <img style="width: 75px;height: 75px;object-fit: cover;" src="{{ $v->avatar }}" alt="">
                                <button onclick="joinGroup({{ $v->id }})" style="float: right"
                                        class="layui-btn layui-btn-normal layui-btn-sm">
                                    <i class="layui-icon">&#xe654;</i> 加入
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="/asset/login/js/jquery.cookie.js"></script>
<script src="/asset/layui/layui.js"></script>
<script>
    var _hmt = _hmt || [];
    (function () {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?6b614cdee352cbb9f55e05ad81084c3a";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
<script>
    var layer;
    layui.use('layer', function () {
        layer = layui.layer;
    });
    layui.use('element', function () {
        var element = layui.element;
    });

    function findUser() {
        wd = $('#user-wd').val();
        window.location.href = "/find?type=user&wd=" + wd + "&token=" + $.cookie('token')
    }

    function findGroup() {
        wd = $('#group-wd').val();
        window.location.href = "/find?type=group&wd=" + wd + "&token=" + $.cookie('token')
    }

    function addFriend(id, nickname, avatar) {
        layui.use('layim', function (layim) {
            layim.add({
                type: 'friend' //friend：申请加好友、group：申请加群
                , username: nickname //好友昵称，若申请加群，参数为：groupname
                , avatar: avatar //头像
                , submit: function (group, remark, index) { //一般在此执行Ajax和WS，以通知对方
                    $.ajax({
                        url: "/api/friend",
                        type: "post",
                        data: {user_id: id, remark: remark, friend_group_id: group},
                        dataType: "json",
                        success: function (res) {
                            if (res.code == 200) {
                                layer.msg(res.msg)
                                layer.close(index); //关闭该面板
                            } else {
                                layer.msg(res.msg, function () {
                                })
                            }
                        },
                        error: function () {
                            layer.msg("网络繁忙", function () {
                            });
                        }
                    })
                }
            });
        });
    }

    function joinGroup(id) {
        $.ajax({
            url: "/api/join/group/" + id,
            type: "post",
            data: {},
            dataType: "json",
            success: function (res) {
                if (res.code == 200) {
                    layer.msg(res.msg)
                    parent.layui.layim.addList(res.data)
                } else {
                    layer.msg(res.msg, function () {
                    })
                }
            },
            error: function () {
                layer.msg("网络繁忙", function () {
                });
            }
        })
    }

    function createGroup() {
        layer.open({
            type: 2,
            title: '创建群',
            shadeClose: true,
            shade: 0.8,
            area: ['40%', '70%'],
            content: '/create/group' //iframe的url
        });
    }
</script>
</body>
</html>
