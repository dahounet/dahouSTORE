<?php
include_once 'inc/conn.php';
include_once 'inc/common.inc.php';
include_once 'inc/core.inc.php';

$html=<<< EOT
<style>
.czpanel-wrap{
	width:100%;
	height:500px;
	font-family:"Microsoft Yahei UI";
	font-size:14px;
	position:absolute;
	top:0;
	z-index:99999;
}
.czpanel-bg{
	position:absolute;
	width:100%;
	height:100%;
	left:0;
	top:0;
	background-color:#FFF;
	opacity:.8;
	z-index:500;
}
.tmalliframe-wrap{
	position:absolute;
	top:0;
	left:0;
}
.tmalliframe{
	width:598px;
	border:1px solid #000;
	box-shadow:#666 1px 1px 5px;
}
.CZXXpanel-title{
	height:30px;
	position:relative;
}
.CZXXpanel-title h1{
	height:29px;
	line-height:28px;
	border-bottom:1px dashed #CCC;
	text-align:center;
	font-weight:bold;
	background-color:#F4F4F4;
}
.CZXXpanel-cancel{
	height:30px;
	position:absolute;
	right:0;
	top:0;
	line-height:28px;
}
.CZXXpanel-cancel a{
	font-size:12px;
	color:#F30;
	margin-right:8px;
	text-decoration:none;
	padding:0 5px;
}
.CZXXpanel-cancel a:hover{
	background-color:#F30;
	color:#FFF;
	font-weight:bold;
}
.CZXXpanel-cancel a:active{
	background-color:#C00;
	color:#FFF;
}
.tmalliframe-page{
	width:100%;
	height:300px;
	background-color:#FFF;
}
.tmalliframe iframe{
	width:620px;
	height:320px;
	margin-top:20px;
	margin-left:-22px;
}
.czpanel{
	left:40%;
	position:absolute;
	width:450px;
	z-index:100000;
	box-shadow:#666 1px 1px 5px;
}
.czpanel-main{
	border:1px solid #333;
	background:#FFF;
}
.czpanel-list-wrap{
}
.czpanel-list{
	padding:32px 15px;
}
.czpanel-list li{
	width:420px;
	line-height:30px;
	overflow:hidden;
}
.list-l{
	float:left;
	width:130px;
	text-align:right;
}
.list-r{
	width:290px;
	float:left;
}
.list-r .cz-number{
	font-size:35px;
	font-weight:bold;
	line-height:35px;
	width:220px;
	font-family:Arial;
}
.list-r .cz-parvalue{
	color:#F50;
	font-size:35px;
	font-family:Georgia;
	margin-right:5px;
}
.list-number{
	font-size:32px;
	font-weight:bold;
	line-height:32px;
	width:220px;
	font-family:Arial;
}
.num-zone{
	margin-left:130px;
	color:#5b5b5b;
}
.cz-receipt{
	padding-right:3px;
	font-family:Georgia;
	font-size:22px;
	width:100px;
	margin-right:3px;
}
.confirm-btn{
	padding-top:20px;
}
.confirm-btn button{
	width:160px;
	height:40px;
	margin-left:130px;
}

.autochoose-wrap{
	/*display:none;*/
	padding:15px;
	background-color:#fafafa;
	border-bottom:1px dashed #666;
}
.autochoose{
	overflow:hidden;
	width:424px;
	padding:12px 0;
}
.autochoose-caption{
	font-size:14px;
	width:20px;
	float:left;
	margin-left:80px;
	line-height:1em;
}
.autochoose-result{
	float:right;
	width:318px;
	height:96px;
	overflow:hidden;
}
.autochoose-result li{
	float:left;
	height:96px;
	width:96px;
	margin-right:10px;
	cursor:pointer;
	position:relative;
}
.autochoose-result li a{
	display:block;
	width:100%;
	height:100%;
	position:absolute;
	left:0;
	top:0;
}
.autochoose-result li:hover{
	background-color:#D0E8FF;
}
.autochoose-result li h2{
	height:30px;
	line-height:30px;
	background-color:#DDD;
	text-align:center;
	margin:1px;
}
.autochoose-result li .price{
	height:59px;
	background-color:#F0F0F0;
	margin:0 1px 1px 1px;
	line-height:59px;
	text-align:center;
	color:#F50;
	font-weight:bold;
	font-size:18px;
	font-family:Georgia;
}
.price-forecast{
	font-size:12px;
	line-height:14px;
	display:block;
	text-align:center;
	position:absolute;
	width:90px;
	text-align:center;
	margin-left:1px;
	margin-top:8px;
	font-weight:normal;
	color:#666;
}
.price-exampleinfo{
	position:absolute;
	margin-top:44px;
	margin-left:1px;
	height:16px;
	width:90px;
	line-height:16px;
	font-size:12px;
	font-family:arial;
	color:#999;
	overflow:hidden;
}
.price-exampleinfo-value{
	float:left;
	margin-left:3px;
}
.price-exampleinfo-diff{
	float:right;
}
.price-exampleinfo-diff span{
	padding:0 2px;
	color:#FFF;
	background-color:#999;
}
.price-exampleinfo-diff span.ok{
	background-color:#3C3;
}
.price-exampleinfo-diff span.greater{
	background-color:#F03;
}
.autochoose-result li .process{
	height:59px;
	background-color:#F0F0F0;
	margin:0 1px 1px 1px;
	line-height:59px;
	text-align:center;
	color:#333;
}
.autochoose-result li .error{
	height:59px;
	background-color:#FF6868;
	margin:0 1px 1px 1px;
	line-height:59px;
	text-align:center;
	color:#FFF;
}
.result-wrap{
	border:2px solid transparent;
}
.autochoose-selected .result-wrap{
	border:2px solid #090;
}
.autochoose-retry{
	margin-left:106px;
}
.autochoose-retry button{
	line-height:30px;
	width:202px;
	margin-top:6px;
}

.orderinfo-wrap{
	padding:15px;
	background-color:#fafafa;
}
.orderinfo{
	width:418px;
	line-height:30px;
}
.orderinfo-list{
	width:100%;
	overflow:hidden;
}
.orderinfo-list-l{
	float:left;
	width:32%;
	text-align:right;
}
.orderinfo-list-r{
	float:left;
	width:68%;
	color:#5b5b5b;
}
.orderinfo-number{
	font-size:32px;
	color:#333;
	font-family:arial;
}
.orderinfo-list-me{
	color:#F50;
	padding-right:3px;
	font-family:Georgia;
	font-size:26px;
}
.orderinfo-orderstate01{
	color:#06F;
}
.orderinfo-orderstate02{
	color:#090;
}
.orderinfo-autocapture{
	font-size:12px;
	background-color:#06F;
	color:#FFF;
	padding:0 5px;
	margin-left:5px;
}
.orderinfo-manualset{
	padding:0 4px;
	font-size:12px;
	margin-left:3px;
}
.checkout-cap{
	font-size:14px;
	font-weight:bold;
	background-color:#CCC;
	line-height:22px;
	text-align:center;
	margin:12px 0 5px;
}
.checkout{
	width:418px;
}
.checkout li{
	width:100%;
	overflow:hidden;
}
.checkout .checkout-l{
	width:32%;
	float:left;
	text-align:right;
}
.checkout .checkout-r{
	width:68%;
	float:left;
	color:#5b5b5b;
}
.checkout .checkout-r input{
	padding-right:3px;
	font-family:Georgia;
	font-size:22px;
	width:100px;
	margin-right:3px;
}
.checkout-needpay span{
	color:#F50;
	padding-right:3px;
	font-family:Georgia;
	font-size:26px;
}
.checkout-change span{
	color:#F50;
	padding-right:3px;
	font-family:Georgia;
	font-size:26px;
}
.checkout-orderremark textarea{
	width:220px;
	height:40px;
}
.orderctrl{
	margin:20px 0 25px;
	overflow:hidden;
}
.orderctrl-confirm{
	float:left;
	margin-left:22%;
}
.orderctrl-confirm button{
	width:160px;
	height:40px;
}
.orderctrl-print{
	float:left;
	margin-left:30px;
}
.orderctrl-print button{
	margin-left:22%;
	width:120px;
	height:40px;
}
</style>
	<div class="czpanel-wrap" style="display:none;" id="dahouSTORE__czpanel">
		<div class="czpanel">
			<div class="czpanel-main">
				<div class="CZXXpanel-title"><h1>充值信息面板</h1><div class="CZXXpanel-cancel"><a href="javascript:;" id="giveupCZ">放弃此次充值</a></div></div>
				<div class="autochoose-wrap">
					<div class="autochoose">
						<h1 class="autochoose-caption">选择充值渠道</h1>
						<ul class="autochoose-result">
							<li data-qd="1">
								<div class="result-wrap">
									<h2>1: TMALL</h2>
									<span class="price-forecast">预计为</span>
									<div class="price-exampleinfo">
										<div class="price-exampleinfo-value"><span>---</span></div>
										<div class="price-exampleinfo-diff"><span>---</span></div>
									</div>
									<div class="price">---</div>
								</div>
								<a href="javascript:;"></a>
							</li>
							<li class="autochoose-selected" data-qd="2">
								<div class="result-wrap">
									<h2>2: CHONG</h2>
									<div class="price-exampleinfo">
										<div class="price-exampleinfo-value"><span>---</span></div>
										<div class="price-exampleinfo-diff"><span>---</span></div>
									</div>
									<div class="price">---</div>
								</div>
								<a href="javascript:;"></a>
							</li>
						</ul>
					</div>
				</div>
				<div class="tmalliframe-wrap" style="display:none; padding-top:160px; margin-left:-70px;">
					<div class="tmalliframe">
						<div class="CZXXpanel-title"><h1>渠道1：TMALL - 充值面板</h1><div class="CZXXpanel-cancel"><a href="javascript:;">更换充值渠道</a></div></div>
						<div class="tmalliframe-page"><iframe src="http://tcc.taobao.com/cc/mobile_charge.htm?css=wt_one_menu" frameborder="0" scrolling="auto" id="tmalliframe"></iframe></div>
					</div>
				</div>
				<div class="czpanel-list-wrap">
					<ul class="czpanel-list">
						<li>
							<div class="list-l">充值号码：</div>
							<div class="list-r"><b class="cz-number">---</b></div>
							<div class="num-zone">---</div>
						</li>
						<li>
							<div class="list-l">充值面值：</div>
							<div class="list-r"><b class="cz-parvalue">---</b>元</div>
						</li>
						<li>
							<div class="list-l">收银：</div>
							<div class="list-r"><input type="text" class="cz-receipt" />元</div>
						</li>
						<li>
							<div class="confirm-btn"><button>确定充值</button></div>
						</li>
					</ul>
				</div>
				<div class="orderinfo-wrap">
					<div class="orderinfo">
						<ul class="orderinfo-list">
							<li>
								<div class="orderinfo-list-l">充值号码：</div>
								<div class="orderinfo-list-r"><b class="orderinfo-number">139-8672-8679</b></div>
							</li>
							<li>
								<div class="orderinfo-list-l">&nbsp;</div>
								<div class="orderinfo-list-r">湖北移动</div>
							</li>
							<li>
								<div class="orderinfo-list-l">&nbsp;</div>
								<div class="orderinfo-list-r">中国移动话费充值</div>
							</li>
							<li>
								<div class="orderinfo-list-l">充值面值：</div>
								<div class="orderinfo-list-r"><span class="orderinfo-list-me">50</span>元</div>
							</li>
							<li>
								<div class="orderinfo-list-l">订单号：</div>
								<div class="orderinfo-list-r"><a href="#">10001</a></div>
							</li>
							<li>
								<div class="orderinfo-list-l">订单时间：</div>
								<div class="orderinfo-list-r">2013-10-31 12:22:19</div>
							</li>
							<li>
								<div class="orderinfo-list-l">充值状态：</div>
								<div class="orderinfo-list-r"><span class="orderinfo-orderstate01">尚未充值</span><span class="orderinfo-autocapture">自动监测中...[5]</span><!--<span class="orderinfo-orderstate02">已完成充值</span>--><button class="orderinfo-manualset">手动设为已充值</button></div>
							</li>
						</ul>
						<div class="checkout-wrap">
							<form name="checkout" method="post">
							<h1 class="checkout-cap">结账信息</h1>
							<ul class="checkout">
								<li class="checkout-discount">
									<div class="checkout-l">优惠金额：</div>
									<div class="checkout-r"><input type="text" value="0.00" />元</div>
								</li>
								<li class="checkout-needpay">
									<div class="checkout-l">应支付：</div>
									<div class="checkout-r"><span>0.00</span>元</div>
								</li>
								<li class="checkout-receipts">
									<div class="checkout-l">收银：</div>
									<div class="checkout-r"><input type="text" value="0.00" />元</div>
								</li>
								<li class="checkout-change">
									<div class="checkout-l">找零：</div>
									<div class="checkout-r"><span>0.00</span>元</div>
								</li>
								<li class="checkout-orderremark">
									<div class="checkout-l">备注：</div>
									<div class="checkout-r"><textarea></textarea></div>
								</li>
							</ul>
							</form>
						</div>
						<div class="orderctrl">
							<div class="orderctrl-confirm"><button id="test">确认订单信息</button></div>
							<div class="orderctrl-print"><button>打印订单</button></div>
						</div>
					</div>
			</div>
			</div>
		</div>
		<div class="czpanel-bg"></div>
	</div>
EOT;
$html2=<<<EOT
<style>
.dahouSTORE_czxx-wrap{
	font-size:12px;
	font-family:Verdana;
	color:#333;
	line-height:30px;
	width:900px;
	position:absolute;
	top:20px;
	left:0;
	z-index:99999;
}
.dahouSTORE_czxx{
	height:30px;
	border:1px solid #09F;
	background-color:#D2E9FF;
	background-color:rgba(210,233,255,0.9);
	box-shadow:#666 0 2px 5px;
}
.dahouSTORE_czxx h1{
	font-size:12px;
	float:left;
	padding:0;
	margin:0;
	margin:0 8px;
}
.dahouSTORE_czxx ul{
	font-size:12px;
	float:left;
	padding:0;
	margin:0;
	float:left;
}
.dahouSTORE_czxx ul li{
	padding:0;
	margin:0;
	list-style:none;
	float:left;
	margin-right:5px;
	padding:0 3px;
}
.dahouSTORE_czxx ul li.fail{
	background-color:#FF8686;
}
.dahouSTORE_czxx ul li.ok{
	background-color:#E4F8C3;
}
.dahouSTORE_czxx-l{
	font-weight:bold;
	padding-right:3px;
}
.dahouSTORE_czxx-r{
	color:#202020;
}
</style>
		
<div class="dahouSTORE_czxx-wrap">
	<div class="dahouSTORE_czxx">
		<h1>充值状态追踪</h1>
		<ul>
			<li><span class="dahouSTORE_czxx-l">当前状态:</span><span class="dahouSTORE_czxx-r">---</span></li>
			<li><span class="dahouSTORE_czxx-l">号码:</span><span class="dahouSTORE_czxx-r">---</span></li>
			<li><span class="dahouSTORE_czxx-l">面值:</span><span class="dahouSTORE_czxx-r">---</span></li>
			<li><span class="dahouSTORE_czxx-l">价格:</span><span class="dahouSTORE_czxx-r">---</span></li>
			<li><span class="dahouSTORE_czxx-l">ISP:</span><span class="dahouSTORE_czxx-r">---</span></li>
			<li><span class="dahouSTORE_czxx-l">订单号:</span><span class="dahouSTORE_czxx-r">---</span></li>
			<li><span class="dahouSTORE_czxx-l">渠道:</span><span class="dahouSTORE_czxx-r">---</span></li>
			<li><span class="dahouSTORE_czxx-l">备注:</span><span class="dahouSTORE_czxx-r">---</span></li>
		</ul>
	</div>
</div>
EOT;
if($_GET['ac']=='gethtml'){
	if($_GET['charset']=='gbk'){
		header('Content-Type:text/html; charset=gbk');
		//echo iconv("utf-8","gbk",$html);
		echo $html;
	}else{
		header('Content-Type:text/html; charset=utf8');
		echo $html;
	}
}
if($_GET['ac']=='gethtml2'){
	if($_GET['charset']=='gbk'){
		header('Content-Type:text/html; charset=gbk');
		//echo iconv("utf-8","gbk",$html);
		echo $html2;
	}else{
		header('Content-Type:text/html; charset=utf8');
		echo $html2;
	}
}
if($_GET['ac']=='trace'){	//追踪充值信息
	
	//recordStorelog('手机话费充值 追踪',"session:".json_encode($_SESSION['traceinfo']));
	
	$t=array();
	$t['state']=0;	//追踪当前状态（0：初始值，1：初始化，2：号码输入与面值选择页面充值按钮已按下、订单已创建，3：正在付款，4：追踪完成）
	$t['oid']=null;	//订单号
	$t['number']=null;	//手机号
	$t['data']=null;	//相关信息
	$t['parvalue']=null;	//充值面值
	$t['QD']=null;	//渠道号（1：天猫；2：淘宝充值平台）
	$t['price']=null;	//我的购买价格
	$t['remark']=null;	//备注信息
	$t['time']=time();	//各个追踪状态的设置时间
	
	$result=array();
	
	if($_GET['tac']=='init'){	//初始化追踪信息，号码输入与面值选择页面，号码已输入、面值已选择，充值渠道尚未确定，充值信息面板的“确定充值”按钮已按下
		unset($_SESSION['traceinfo']);	//清理追踪信息
		
		$t['state']=1;
		$t['number']=$_POST['number'];
		$t['parvalue']=$_POST['parvalue'];
		$t['data']=$_POST['data'];
		$t['remark']=$_POST['remark'];
		$_SESSION['traceinfo']=$t;

		$result['state']='200';
		$result['msg']='初始化追踪信息 成功';
		$result['traceinfo']=$_SESSION['traceinfo'];

		recordStorelog('手机话费充值 初始化追踪信息',"session:".json_encode($_SESSION['traceinfo']));
		echo json_encode($result);
	}elseif($_GET['tac']=='start'){	//充值渠道已选择，充值按钮已按下，订单已创建成功，还未进入付款页面
		if($_SESSION['traceinfo']['state']>=1&&$_SESSION['traceinfo']['state']<=2){
			$t['state']=2;
			$t['number']=$_SESSION['traceinfo']['number'];
			$t['parvalue']=$_SESSION['traceinfo']['parvalue'];
			$t['data']=$_SESSION['traceinfo']['data'];
			$t['remark']=$_SESSION['traceinfo']['remark'];
			$t['oid']=$_POST['oid'];
			$t['price']=$_POST['price'];
			$t['QD']=$_POST['QD'];
			$_SESSION['traceinfo']=$t;
			
			$result['state']='200';
			$result['msg']='充值按钮已按下，即将付款';
			$result['traceinfo']=$_SESSION['traceinfo'];
			
	
			recordStorelog('手机话费充值 进入充值付款页面（充值渠道已选择，充值按钮已按下，订单已创建成功）',"session:".json_encode($_SESSION['traceinfo']));
		}else{
			$result['state']='400';
			$result['msg']='无追踪信息';
		}
		echo json_encode($result);
	}elseif($_GET['tac']=='pay'){	//进入充值付款页面，正在付款中
		if($_SESSION['traceinfo']['state']>=1&&$_SESSION['traceinfo']['state']<=3){
			$t['state']=3;
			$t['number']=$_SESSION['traceinfo']['number'];
			$t['parvalue']=$_SESSION['traceinfo']['parvalue'];
			$t['data']=$_SESSION['traceinfo']['data'];
			$t['remark']=$_SESSION['traceinfo']['remark'];
			$t['oid']=$_SESSION['traceinfo']['oid'];
			$t['price']=$_SESSION['traceinfo']['price'];
			$t['QD']=$_SESSION['traceinfo']['QD'];
			$_SESSION['traceinfo']=$t;
			
			$result['state']='200';
			$result['msg']='充值付款页面';
			$result['traceinfo']=$_SESSION['traceinfo'];
	
			recordStorelog('手机话费充值 进入充值付款页面',"session:".json_encode($_SESSION['traceinfo']));
		}else{
			$result['state']='400';
			$result['msg']='无追踪信息';
		}
		echo json_encode($result);
	}elseif($_GET['tac']=='complete'){	//完成追踪（充值付款成功）
		if($_SESSION['traceinfo']['state']>=1&&$_SESSION['traceinfo']['state']<=3){
			/*$t['state']=4;
			$t['number']=$_SESSION['traceinfo']['number'];
			$t['parvalue']=$_SESSION['traceinfo']['parvalue'];
			$t['data']=$_SESSION['traceinfo']['data'];
			$t['remark']=$_SESSION['traceinfo']['remark'];
			$t['oid']=$_SESSION['traceinfo']['oid'];
			$t['price']=$_SESSION['traceinfo']['price'];
			$t['QD']=$_SESSION['traceinfo']['QD'];*/
	
			$result['state']='200';
			$result['msg']='充值付款页面';
			$result['traceinfo']=$_SESSION['traceinfo'];
			
			chargeOrderSetToCompleted($_SESSION['traceinfo']['oid']);
			
			recordStorelog('手机话费充值 完成追踪（充值付款成功） 清理追踪信息',"session:".json_encode($_SESSION['traceinfo']));
		}else{
			$result['state']='400';
			$result['msg']='无追踪信息';
		}
		echo json_encode($result);
		unset($_SESSION['traceinfo']);	//清理追踪信息
	}elseif($_GET['tac']=='completebymanual'){	//手动设为 成追踪（充值付款成功）
		if($_SESSION['traceinfo']['state']>=1&&$_SESSION['traceinfo']['state']<=3){
			/*$t['state']=4;
			$t['number']=$_SESSION['traceinfo']['number'];
			$t['parvalue']=$_SESSION['traceinfo']['parvalue'];
			$t['data']=$_SESSION['traceinfo']['data'];
			$t['remark']=$_SESSION['traceinfo']['remark'];
			$t['oid']=$_SESSION['traceinfo']['oid'];
			$t['price']=$_SESSION['traceinfo']['price'];
			$t['QD']=$_SESSION['traceinfo']['QD'];*/
	
			$result['state']='200';
			$result['msg']='手动设为完成追踪';
			$result['traceinfo']=$_SESSION['traceinfo'];
			
			chargeOrderSetToCompleted($_SESSION['traceinfo']['oid']);
	
			recordStorelog('手机话费充值 [手动]设为完成追踪（充值付款成功） 清理追踪信息',"session:".json_encode($_SESSION['traceinfo']));
		}else{
			$result['state']='400';
			$result['msg']='无追踪信息';
		}
		echo json_encode($result);
		unset($_SESSION['traceinfo']);	//清理追踪信息
	}elseif($_GET['tac']=='get'){//获取当前追踪信息
		if(isset($_SESSION['traceinfo'])){
			$result['state']='200';
			$result['msg']='获取当前追踪信息';
			$result['traceinfo']=$_SESSION['traceinfo'];
	
			recordStorelog('手机话费充值 获取当前追踪信息',"session:".json_encode($_SESSION['traceinfo']));
		}else{
			$result['state']='400';
			$result['msg']='当前没有追踪信息';
			
			recordStorelog('手机话费充值 获取当前追踪信息-当前没有追踪信息',"session:".json_encode($_SESSION['traceinfo']));
		}
		echo json_encode($result);
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
	
}
//$_SESSION['currentoid']='988911115';
//echo '---------------'.$_SESSION['currentoid'].'------------';
?>