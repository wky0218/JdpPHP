<!DOCTYPE html>
<html lang="en">
  	<head>
	    <meta charset="utf-8">
	    <title>提示信息</title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta name="description" content="">
	    <meta name="author" content="">
	
  	</head>
<style>
*::after, *::before {
    box-sizing: border-box;
}
html {
    font-size: 10px;
}
h1{font-size: 20px;color:#666666;}
a{color:#23B7E5;}
body {
    background: #f0f2f4;
    color: #333;
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
    font-size: 14px;
    line-height: 1.42857;
}

.wrapper {
    min-height: 100%;
    overflow: hidden;
    padding-bottom: 45px;
    padding-top: 54px;
    position: relative;
}
.wrapper.no-navigation {
    padding-top: 0;
}
.error-wrapper .error-inner {
	background: #ffffff;
    margin: 50px auto;
    text-align: center;
    background-clip: padding-box;
    background-color: #fff;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 6px;
    box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
    margin-left: auto;
    margin-right: auto;
    outline: 0 none;
    padding: 20px;
    position: relative;
    width: 370px;	
	min-height:100px;
	
}
.error-con p {
    text-align: left;
    margin-left: 50px;
	margin-top: 30px;
	margin-bottom: 30px;
}
@media (max-width: 481px) {
.error-wrapper .error-inner {
    margin-top: 30px;
    padding-left: 15px;
    padding-right: 15px;
    width: 85%;
}
}
.error-con{
border: 1px solid  #E6E6E1;
   position: relative;
}

.m-top-md {
	background-color: #E6E6E1;
    margin-top: 10px;
}
.msg{
font-size:18px;
color:#666666;
}
.toast-item-image {
    width:32px;
    height: 32px;
    position: absolute;
    top: 50%;
    margin-top: -16px;
    left: 10px;
}

.toast-item-image-success {
    background-image:url(data:image/png;base64,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);
}


.toast-item-image-error {
    background-image:url(data:image/png;base64,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);
}

</style>		
  	<body class="overflow-hidden light-background">
		<div class="wrapper no-navigation preload">
			<div class="error-wrapper">
				<div class="error-inner">
					<div class="error-con">				
						<?php if($jumpdata['type']):?>
						<div class="toast-item-image toast-item-image-success"></div>
						<p><span class="msg"><?php echo($jumpdata['msg']); ?></span></p>
						<?php else:?>
						<div class="toast-item-image toast-item-image-error"></div>
						<p><span class="msg"><?php echo($jumpdata['msg']); ?></span></p>
						<?php endif;?>
						<p class="detail"></p>						

					</div>
					
					<div class="m-top-md">
						页面自动<a id="href" href="<?php echo($jumpdata['jumpurl']); ?>" class="btn btn-default btn-lg text-upper"> 跳 转 </a>等待时间： <b id="wait"><?php echo($jumpdata['wait']); ?></b>
					</div>					
				</div><!-- ./error-inner -->
			</div><!-- ./error-wrapper -->
		</div><!-- /wrapper -->

		<a href="" id="scroll-to-top" class="hidden-print"><i class="icon-chevron-up"></i></a>

	<script type="text/javascript">
		
	(function(){
	var wait = document.getElementById('wait'),href = document.getElementById('href').href;
	totaltime=parseInt(wait.innerHTML);
	var interval = setInterval(function(){
		var time = --totaltime;
			wait.innerHTML=""+time;
		if(time === 0) {
			location.href = href;
			clearInterval(interval);
		};
	}, 1000);
	})();

	</script>
		
  	</body>
</html>
