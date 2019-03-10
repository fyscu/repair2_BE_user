<!doctype html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="//www.fyscu.com/images/ico/favicon.ico">
<title>飞扬统一认证系统</title>
<link rel="stylesheet" type="text/css" href="static/fonts/SourceHanSansCN-Light.css">
<link rel="stylesheet" type="text/css" href="static/css/styles.css">
</head>
<body>

<div class="htmleaf-container">
	<div class="wrapper">
		<div class="container">
		    <div id="login-panel">
    			<h1 id="login-title">登录<?php echo($appname); ?></h1>
    			<form class="form" id="login-form">
    			    <input type="hidden" name="appid" value="<?php echo($appid); ?>">
    				<input type="text" name="username" placeholder="用户名">
    				<input type="password" name="password" placeholder="密码">
    				<button type="submit" id="login-button">登录</button>
    				<p class="form-more"><a href="#" id="login-reg">注册</a> &middot; <a href="#" id="login-forgot">忘记密码</a></p>
    			</form>
		    </div>
		    <div id="register-panel" style="display: none;">
    			<form class="form" id="reg-form">
    			    <input type="hidden" name="appid" value="<?php echo($appid); ?>">
    			    <input type="hidden" name="type" id="reg-type">
    				<input type="text" name="username" id="reg-cell" placeholder="手机号">
    				<span>
        				<input type="text" name="otpcode" placeholder="验证码" class="input-s">
        				<button id="reg-getotp" class="btn-s">获取</button>
    				</span>
    				<input type="password" id="reg-psw" name="password" placeholder="密码">
    				<input type="password" id="reg-psw-chk" name="password-check" placeholder="确认密码">
    				<button type="submit" id="reg-button">注册</button>
    				<p class="form-more"><a href="#" id="reg-back">&lt; 返回登录</a></p>
    			</form>
		    </div>
		    <div id="info-panel" style="display: none;">
    			<h1 id="info-title">还有一步</h1>
    			<form class="form" id="info-form">
    				<input type="text" name="name" placeholder="姓名">
    				<input type="email" name="email" placeholder="邮箱(可选)">
    				<button type="submit" id="info-button">完成</button>
    			</form>
		    </div>

		</div>
		<ul class="bg-bubbles">
			<li></li><li></li><li></li><li></li><li></li>
			<li></li><li></li><li></li><li></li><li></li>
		</ul>
	</div>
</div>

<script src="static/js/jquery-2.1.1.min.js" type="text/javascript"></script>
<script src="static/js/gt.js" type="text/javascript"></script>
<script>
var errorCode = {
    login: {
        '400': '用户名或密码错误',
        '401': '令牌不正确',
        '404': '令牌不存在',
        '408': '令牌已过期',
        '444': '信息不完整',
        '500': '服务器错误'
    },
    register: {
        '400': '手机号码不正确',
        '401': '校验不成功',
        '403': '短信请求被拒绝',
        '409': '注册不成功, 可能原因为：\r1.您已注册 \r2.服务器错误',
        '444': '信息不完整',
        '500': '短信验证服务错误',
        '502': '短信服务不可用'
    }
}
$.ajax({
    url: "/api/captcha?" + (new Date()).getTime(),
    type: "get",
    dataType: "json",
    success: function (data) {
        initGeetest({
            gt: data.gt,
            challenge: data.challenge,
            new_captcha: data.new_captcha,
            product: "bind",
            offline: !data.success
        }, geetesthandler);
    }
});
var smstimeout = 150;
var smsinterval = null;
var callbackurl = null;
var checksms = function (){
    if (--smstimeout === 0) {
        clearInterval(smsinterval);
        $('#reg-getotp').prop("disabled", false);
        $('#reg-getotp').text("获取");
        return;
    }
    $('#reg-getotp').text("(" + smstimeout + "s)");
}
var geetesthandler = function (captchaObj) {
    captchaObj.onReady(function () {
        $("#wait").hide();
    }).onSuccess(function () {
        var result = captchaObj.getValidate();
        if (!result) {
            return alert('请完成验证');
        }
        $.ajax({
            url: 'api/smsotp',
            type: 'POST',
            dataType: 'json',
            data: {
                username: $('#reg-cell').val(),
                geetest_challenge: result.geetest_challenge,
                geetest_validate: result.geetest_validate,
                geetest_seccode: result.geetest_seccode
            },
            success: function (data) {
                if (data.code === 200) {
                    $('#reg-getotp').prop("disabled", true);
                    smstimeout = 150;
                    smsinterval = setInterval("checksms()", 1000);
                } else {
                    setTimeout(function () {
                        alert(errorCode.register[data.code]);
                        captchaObj.reset();
                    }, 1500);
                }
            }
        });
    });
    $('#reg-getotp').click(function(){
        event.preventDefault();
        captchaObj.verify();
    });
};

$('#login-reg').click(function(){
    $('#reg-type').val(1);
    $('#reg-button').text("注册");
    $('#login-panel').fadeOut(1);
    $('#register-panel').fadeIn(200);
});
$('#login-forgot').click(function(){
    $('#reg-type').val(0);
    $('#reg-button').text("修改密码");
    $('#login-panel').fadeOut(1);
    $('#register-panel').fadeIn(200);
});
$('#reg-back').click(function(){
    $('#register-panel').fadeOut(1);
    $('#login-panel').fadeIn(200);
});
$('#login-button').click(function (event) {
	event.preventDefault();
	$.ajax({
		url: 'api/login',
		type: 'post',
		data: $("#login-form").serialize(),
		success: function (data) {
			if (data.code == 200) {
				callbackurl = data.callback;
				if (data.profile == 100) {
                    $('#login-panel').fadeOut(1);
                    $('#info-panel').fadeIn(200);
                    $('.wrapper').removeClass('form-success');
				} else {
				    location.href = callbackurl;
				}
			} else {
				$('form').show();
				$('.wrapper').removeClass('form-success');
				alert(errorCode.login[data.code]);
				$('#login-title').text('请重试');
			}
		}
	});
	$('#login-panel form').hide();
	$('#login-title').text("欢迎");
	$('.wrapper').addClass('form-success');
});
$('#reg-button').click(function (event) {
	event.preventDefault();
	if ($("#reg-psw").val()!=$("#reg-psw-chk").val()){
	    alert("两次密码不同，请重试");
	    return;
	}
	$.ajax({
		url: 'api/register',
		type: 'post',
		data: $("#reg-form").serialize(),
		success: function (data) {
			if (data.code == 200) {
			    $("#reg-form")[0].reset();
			    if ($('#reg-type').val() == 1){
                    $('#register-panel').fadeOut(1);
                    $('#info-panel').fadeIn(200);
				    callbackurl = data.login.callback;
			    } else {
			        alert("找回密码成功");
                    $('#register-panel').fadeOut(1);
                    $('#login-panel').fadeIn(200);
			    }
			} else {
				alert(errorCode.register[data.code]);
			}
		}
	});
});
$('#info-button').click(function (event) {
	event.preventDefault();
	$.ajax({
		url: 'api/updateinfo',
		type: 'post',
		data: $("#info-form").serialize(),
		success: function (data) {
			if (data.code == 200) {
                location.href = callbackurl;
			} else {
				alert(errorCode.register[data.code]);
			}
		}
	});
});

</script>
</body>
</html>