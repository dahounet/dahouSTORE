<?php
header('Content-Type:text/html; charset=utf-8');

include_once '../inc/conn.php';
include_once '../inc/common.inc.php';
include_once '../inc/core.inc.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>打印订单</title>
<link href="style/print.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php
//获取订单的打印数据
function getPrintData($oid,$ac){
	$orderInfo=getOrderInfo($oid);
	if($orderInfo['chargeorder']==null){	//如果不是虚拟充值类订单
		if($ac=='preview'){	//如果是打印预览
			return printNormalOrder($orderInfo);
		}else{	//如果是打印
			return printNormalOrder($orderInfo,true);
		}
	}else{	//如果是虚拟充值类订单
		if($ac=='preview'){	//如果是打印预览
			return printChargeOrder($orderInfo);
		}else{	//如果是打印
			return printChargeOrder($orderInfo,true);
		}
	}
}
//打印虚拟充值类订单
function printChargeOrder($d,$a=false){
	$printTime=time();
	$printTime_formatted=date("Y-m-d H:i:s",$printTime);
	$pList=getOrderProductsList($d['oid']);
	$productInfo=getProductInfo($pList[0]['pid']);
	$orderCtime=date("Y-m-d H:i:s",$d['ctime']);
	$html=<<<"EOT"
<div class="main">
	<div class="header">
		<h1>手机话费充值交易凭据</h1>
	</div>
	<div class="content">
		<table class="hf-tb1">
			<tr>
				<th class="th1"></th>
				<th class="th2"></th>
			</tr>
			<tr>
				<td class="td1">充值号码：</td>
				<td class="td2"><b>{$d['chargeorder']['account']}</b></td>
			</tr>
			<tr>
				<td class="td1"></td>
				<td class="td2">{$d['chargeorder']['data']}</td>
			</tr>
			<tr>
				<td class="td1"></td>
				<td class="td2">{$productInfo['name']}</td>
			</tr>
			<tr>
				<td class="td1">充值面值：</td>
				<td class="td2"><b>{$d['chargeorder']['parvalue']}</b>元</td>
			</tr>
			<tr>
				<td class="td1">订单号：</td>
				<td class="td2">{$d['oid']}</td>
			</tr>
			<tr>
				<td class="td1">订单时间：</td>
				<td class="td2">{$orderCtime}</td>
			</tr>
		</table>
		<div class="payinfo">
			<h1><div class="payinfo-title"><span>结账信息</span></div></h1>
			<div class="payinfo-main">
				<div class="payinfo-l">
					<div class="payinfo-l-text">应支付</div>
					<div class="payinfo-l-money"><span>{$d['actualamount']}</span>元</div>
				</div>
				<div class="payinfo-r">
					<div class="payinfo-r-item"><span class="payinfo-r-item-text">优惠：</span><b>{$d['totaldiscount']}</b>元</div>
					<div class="payinfo-r-item"><span class="payinfo-r-item-text">收银：</span><b>{$d['receipts']}</b>元</div>
					<div class="payinfo-r-item"><span class="payinfo-r-item-text">找零：</span><b>{$d['change']}</b>元</div>
				</div>
			</div>
		</div>
	</div>
	<div class="footer">
		<div class="printtime">打印时间：$printTime_formatted</div>
		<div class="storeinfo">
			<div class="storeinfo-address">地址：北京西路443号巷内10米</div>
			<div class="storeinfo-ad"><span>充话费</span><span>下歌下电影</span><span>电脑手机配件</span><span>电脑维护</span>
			</div>
		</div>
	</div>
</div>
EOT;
	if($a){
		recordPrintOrder($d['oid'],$html);
	}
	return $html;
}

//打印非虚拟充值类订单
function printNormalOrder($d){
	return '<div style="margin:auto;width:220px;">打印非虚拟充值类订单-建设中...</div>';
}

if($_GET['ac']=='preview'){	//打印预览
	if(isset($_GET['oid'])){
		$oid=$_GET['oid']*1;
		
		$previewCaption=<<<"EOT"
<div class="preview-caption">订单打印预览</div>
EOT;
		$confirmPrint=<<<"EOT"
<div class="confirmprint">
	<form method="post" action="?ac=print">
		<input type="hidden" value="print" name="ac" />
		<input type="hidden" value="{$oid}" name="oid" />
		<input type="submit" value="确认打印订单" class="confirmbtn" id="confirmbtn" />
		<script>setTimeout(function(){(document.getElementById('confirmbtn')).focus();},1000);</script>
	</form>
</div>
EOT;
		echo $previewCaption.getPrintData($oid,'preview').$confirmPrint;
	}else{
		echo '缺少订单号';
	}
}
if($_GET['ac']=='print'&&$_POST['ac']=='print'&&isset($_POST['oid'])){	//打印
	$oid=$_POST['oid']*1;
	$script=<<<"EOT"
		
<script>
window.print();
		
window.opener=null;
window.open('', '_self', '');
		
setTimeout(function(){window.close();},1500);
</script>
		
EOT;
	echo getPrintData($oid,'print').$script;
}

//echo getPrintData(48,'preview');
?>

</body>
</html>