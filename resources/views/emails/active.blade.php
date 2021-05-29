<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>{!! $data['subject'] !!}</title>
 <style>
  .account {
   font-size: 18px;
   font-weight: 600;
   margin: 10px;
  }
  .account div {
   margin: 14px 0;
  }
  #activation,
  #activationEng {
   color: #3c46bf;
   text-decoration: underline;
  }
 </style>
</head>
<body>
<div class="account">
 <div>你好！</div>
 <div>欢迎使用Flash Growth渠道代理商一站式平台：</div>
 <div>账号激活请点击：<span id="activation" data-href="{!! $data['content'] !!}">激活</span></div>
 <div>请尽快点击链接，完成账号激活验证，点击后即可激活，可用该账号登录渠道代理商一站式平台进行系统相关操作</div>
 <div>Flash Growth--渠道一站式平台</div>
 <div>若该邮件与你无关，请勿点击！</div>
 <div>此邮件地址无法直接回复。如果您需要了解更多信息，请前往 Flash Growth 渠道官网</div>
 <div>Hello there！</div>
 <div>Welcome to the platform for Flash Growth：</div>
 <div>Account Activation verificatin please click： <span id="activationEng" data-href="{!! $data['content'] !!}">Acvication</span></div>
 <div>Please click the link as soon as possible to complete the account registration verification.</div>
</div>
<script>
 window.onload = function () {
  const activation = document.getElementById('activation')
  const activationEng = document.getElementById('activationEng')
  // console.log(activation.getAttribute('data-href'));
  activation.addEventListener('click', function(e){
   const url = activation.getAttribute('data-href')
   ajaxPost(url)
  }, false)
  activationEng.addEventListener('click', function(e){
   const url = activationEng.getAttribute('data-href')
   ajaxPost(url)
   return false
  }, false)
 }

 function ajaxObject() {
  var xmlHttp;
  try {
   // Firefox, Opera 8.0+, Safari
   xmlHttp = new XMLHttpRequest();
  }
  catch (e) {
   // Internet Explorer
   try {
    xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
   } catch (e) {
    try {
     xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (e) {
     alert("您的浏览器不支持AJAX！");
     return false;
    }
   }
  }
  return xmlHttp;
 }

 // ajax post请求：
 function ajaxPost (url) {
  var ajax = ajaxObject();
  ajax.open( "get" , url , true );
  console.log(url);
  ajax.setRequestHeader( "Content-Type" , "application/json;charset=utf-8" );
  ajax.onreadystatechange = function () {
   if( ajax.readyState == 4 ) {
    if( ajax.status == 200 ) {
     console.log('aaa');
     // console.log(ajax.response);
     var response = JSON.parse(ajax.response)
     console.log(response)
     if (response.code === 0) {
      alert("激活成功");
     }
    }
   }
  }
  ajax.send( null );
 }
</script>
</body>
</html>
