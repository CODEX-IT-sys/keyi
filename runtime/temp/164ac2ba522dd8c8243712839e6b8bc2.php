<?php /*a:2:{s:64:"D:\phpstudy_pro\WWW\keyi\application\admin\view\index\index.html";i:1634533382;s:26:"./layout/top_and_left.html";i:1634533382;}*/ ?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1 , user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="Cache-Control" content="no-siteapp"/>
        <meta name="apple-mobile-web-app-capable" content="yes">
        <title>CODEX</title>
        <link rel="shortcut icon" href="/static/images/logo-icon.ico">
        <link href="/static/css/animate.min.css" rel="stylesheet" type="text/css">
        <link href="/static/iconfont/iconfont.css" rel="stylesheet" type="text/css">
        <link href="/static/layui/css/layui.css" rel="stylesheet" type="text/css">
        <link href="/static/css/main.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="hnBox">
            <div class="layui-layout layui-layout-admin hn_admin">

                <div class="layui-header hn_header">
                    <ul class="layui-nav layui-layout-left">
                        <li class="layui-nav-item">
                            <a href="javascript:;" class="folded_btn">
                                <i class="iconfont icon-zhedie"></i>
                            </a>
                        </li>
                    </ul>
                    <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">

                        <!--language_switch-->
                        <li class="layui-nav-item language_switch" style="margin-right: 10px">
                            <a href="javascript:;">
                                <i class="iconfont icon-yuyan"></i><?php echo session('language') == '中文'? 'English' : '中文'; ?>
                            </a>
                        </li>
                        <!--msg-->
                        <li class="layui-nav-item notice_tip" style="margin-right: 10px">
                            <a href="javascript:void(0);">
                                <?php if($msg_s==1): ?>
                                <span class="layui-badge-dot"></span>
                                <?php endif; ?>
                                <i class="layui-icon layui-icon-notice"></i>
                            </a>
                        </li>

                        <li class="layui-nav-item avator" style="width: 200px;" lay-unselect>
                            <a href="javascript:void(0);" class="avator_lead">
                                <img src="/static/images/avatar.png" class="layui-nav-img"><?php echo session('administrator.name'); ?>
                            </a>
                            <dl class="layui-nav-child" style="width: 200px; height: 300px;">
                                <dd>
                                    <img src="/static/images/avatar.png" class="layui-nav-img" style="margin:0 auto; width: 120px; height: 120px;">
                                </dd>

                                <dd style="margin-top: 40px; text-align:center;">
                                    <a href="<?php echo url('login/admin/logout'); ?>" style="font-size:18px;color: red;"><?php echo session('language') == '中文'? '退出登录' : 'Login Out'; ?></a>
                                </dd>

                                <?php if(session('administrator.id') == 1): ?>
                                <dd style="margin-top: 10px; text-align:center;">
                                    <a id="edpwd" style="font-size:18px;color: red;"><?php echo session('language') == '中文'? '修改密码' : 'EditPassword'; ?></a>
                                </dd>
                                <?php endif; ?>

                            </dl>
                        </li>

                    </ul>
                </div>

                <!--左侧菜单 menu-->
                <div class="layui-side layui-side-menu hn_menu">
                    <div class="layui-side-scroll">
                        <div class="layui-logo" lay-href="#">
                            <a href="#">
                                <img src="/static/images/codex_logo.png" class="logo_show">
                            </a>
                        </div>

                        <ul class="layui-nav layui-nav-tree" lay-filter="test" id="menu">
                            

    <li class="layui-nav-item">
        <a href="javascript:;" lay-href="<?php echo url('workbench/index'); ?>">
            <i class="iconfont icon-shichangxuanpinjichanpinguanli"></i>
            <cite><?php echo session('language') == '中文'? '我的工作台' : 'Workbench'; ?></cite>
        </a>
    </li>

<?php if(is_array($menu) || $menu instanceof \think\Collection || $menu instanceof \think\Paginator): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>

    <li class="layui-nav-item">
        <a href="javascript:;">
            <i class="iconfont <?php echo htmlentities($v['icon']); ?>"></i>
            <cite><?php echo session('language') == '中文'? $v['cn_name'] : $v['en_name']; ?></cite>
        </a>

        <?php if(is_array($v['z']) || $v['z'] instanceof \think\Collection || $v['z'] instanceof \think\Paginator): $i = 0; $__LIST__ = $v['z'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
		<dl class="layui-nav-child">
		    <dd>
			<?php if($vo['cn_name']=="表格管理"): ?>
				<a href="http://localhost/phpMyAdmin4.8.5/" target="_blank">
				  <?php echo session('language') == '中文'? '表格管理' : 'Table Management'; ?>
				</a>
			<?php else: ?>
				<a href="javascript:;" lay-href="<?php echo url($vo['url']); ?>"><?php echo session('language') == '中文'? $vo['cn_name'] : $vo['en_name']; ?></a>
			<?php endif; ?>
			</dd>
		</dl>
        <?php endforeach; endif; else: echo "" ;endif; ?>

    </li>

<?php endforeach; endif; else: echo "" ;endif; ?>


                        </ul>
                    </div>
                </div>

                <!--页面主体 body-->
                <div class="layui-body hn_main">
                    <div class="mainWrap">
                        <iframe src="

<?php echo url('admin/index/welcome'); ?>

" id="main" name="main" class="layadmin-iframe" frameborder="0"></iframe>
                    </div>
                </div>

            </div>
        </div>

        <script type="text/javascript" src="/static/js/jquery-1.8.3.min.js"></script>
        <script type="text/javascript" src="/static/layui/layui.js"></script>
        <script type="text/javascript" src="/static/js/main.js"></script>

        <script>

        layui.use(['form'], function() {
            var form = layui.form;
            var layer = top.layer;

            // 语言切换
            $('.language_switch').click(function () {

                var language = $.trim($('.language_switch').text());

                var h_url = parent.document.getElementById("main").contentWindow.location.href;
                //console.log(h_url);

                if(language === 'English'){
                    $('.language_switch').text('中文');
                }else{
                    $('.language_switch').text('English');
                }

                $.ajax({
                    type:'post',
                    url:"<?php echo url('index/language'); ?>",
                    //url:"<?php echo url('index/index'); ?>",
                    data:{language: language, if_url:h_url},
                    success: function (res) {

                        /*var _body = window.parent;
                        var _iframe=_body.document.getElementById('main');
                        //iframe 重载
                        _iframe.contentWindow.location.reload(true);*/

                        // 框架重新加载
                        window.location.reload(true);
                    }
                });
            });

            // 消息提醒 列表
            $('.notice_tip').click(function () {

                parent.layer.open({ //在父窗口打开
                    type: 2,
                    title: '消息',
                    maxmin: true,
                    shadeClose: true, //点击遮罩关闭层
                    area : ['900px' , '700px'],
                    content: "<?php echo url('index/msg'); ?>",
                    btn: ['确认', '取消']
                    ,end: function () {
                        // 框架重新加载
                        window.location.reload(true);
                    }
                });
            });

            $('#edpwd').click(function () {

                var id = "<?php echo session('administrator.id'); ?>";

                // 修改密码
                parent.layer.open({
                    type: 2,
                    title: '修改密码',
                    maxmin: true,
                    area: ['600px', '500px'],
                    content: "<?php echo url('admin/editPassword'); ?>" + '?id='+ id
                });
            })

        })

        </script>

    </body>
</html>





