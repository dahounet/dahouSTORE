<?php
header('Content-Type:text/html; charset=utf-8');

include_once 'inc/conn.php';
include_once 'inc/common.inc.php';
include_once 'inc/core.inc.php';

if(is_numeric($_GET['oid'])){
	$info=getOrderInfo($_GET['oid']);	//订单信息
	//print_r($info);
	$list=getOrderProductsList($_GET['oid']);	//订单的商品列表
	//print_r($list);
	//echo count($list);
}else{
	echo '请求不正确';
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>订单详情</title>
<link href="style/common.css" rel="stylesheet" type="text/css">
<script src="js/jquery-1.10.2.min.js"></script>
</head>

<body>
<div class="main">
	<div class="main-top">大猴STORE 管理系统 V0.1</div>
	<div class="main-left-wrap">
		<div class="main-left">
			<h1 class="nav-top"><a href="#">管理系统首页</a></h1>
			<dl class="nav-list">
				<?php echo generateLeftList(0,1) ?>
			</dl>
		</div>
	</div>
	<div class="main-right">
		<div class="tabview">
			<ul class="tabpanel">
				<li class="main-right-tab-curr"><a href="#">订单详情</a></li>
			</ul>
		</div>
		<div class="controlpanel" id="controlpanel">
			<form action="order.php?ac=create&type=charge" method="post" id="addorderinfo">
				<div class="formpanel">
					<dl class="formpanel-itemlist">
						<dt>订单来源：</dt>
						<dd><?php $ofromtype=array(0=>'其他',1=>'店面',2=>'网络',3=>'电话');echo $ofromtype[$info['type']]; ?></dd>
						<dt>订单类型：</dt>
						<dd><?php if($info['chargeorder']!=null&&$info['serviceorder']==null){
							echo '虚拟充值订单';
						}elseif($info['chargeorder']==null&&$info['serviceorder']!=null){
							echo '服务订单';
						}else{
							echo '实物(服务*)订单';
						} ?></dd>
						<dt>订单号：</dt>
						<dd><?php echo $info['oid']; ?></dd>
						<dt>订单状态：</dt>
						<dd><?php $ostate=array(0=>'已创建',11=>'已出库',12=>'已确认 已完成',51=>'已作废',55=>'已关闭');echo $ostate[$info['state']]; ?></dd>
						<dt>创建时间：</dt>
						<dd><?php echo date("Y-m-d H:i:s",$info['ctime']); ?></dd>
						<dt>上次修改：</dt>
						<dd><?php echo date("Y-m-d H:i:s",$info['modtime']);?></dd>
						<dt>打印次数：</dt>
						<dd><?php echo $info['printcount'];?></dd>
						<dt>创建者IP：</dt>
						<dd><?php echo $info['ip'];?></dd>
						<dt>客户姓名：</dt>
						<dd><?php if(strlen($info['cname'])==0){echo '---';}else{echo $info['cname'];} ?></dd>
						<dt>商品详情：</dt>
						<dd>
							<div class="buyproducts-wrap">
								<div class="buyproducts">
									<div class="buyproducts-head"> <span class="buyproducts-head-1">商品名称</span> <span class="buyproducts-head-2">单价</span> <span class="buyproducts-head-3">数量</span> <span class="buyproducts-head-4">每件优惠</span> <span class="buyproducts-head-5">操作</span> </div>
									<div class="buyproducts-main" id="buyproduct-main" style="display:block;">
										<ul class="buyproducts-main-list" id="buyproduct-main-list" style="display:block;">
										<?php
											$listHtml="";
											for($i=0;$i<count($list);$i++){
												$pinfo=getProductInfo($list[$i]['pid']);
												$listHtml.='<li><div class="buyproducts-main-item buyproducts-main-1">'.$pinfo['name'].'</div><div class="buyproducts-main-item buyproducts-main-2">'.$list[$i]['price'].'</div><div class="buyproducts-main-item buyproducts-main-3">'.$list[$i]['number'].'</div><div class="buyproducts-main-item buyproducts-main-4">'.$list[$i]['damount'].'</div><div class="buyproducts-main-item buyproducts-main-5">-</div></li>';
											}
											echo $listHtml;
										?>
										</ul>
									</div>
								</div>
							</div>
							<div class="chargeinfo-wrap" id="chargeInfo"<?php if($info['chargeorder']!=null){echo ' style="display:block;"';}?>>
								<div class="chargeinfo">
									<h1><b>虚拟类充值缴费商品补充信息</b></h1>
									<ul class="chargeinfo-list">
										<li>
											<div class="chargeinfo-list-item-n">手机号码/帐号：</div>
											<div class="chargeinfo-list-item-r"><?php echo $info['chargeorder']['account']; ?></div>
										</li>
										<li>
											<div class="chargeinfo-list-item-n"><em class="required">*</em>面值：</div>
											<div class="chargeinfo-list-item-r"><?php echo $info['chargeorder']['parvalue']; ?>元</div>
											<div class="chargeinfo-list-item-n"><em class="required">*</em>MY进价：</div>
											<div class="chargeinfo-list-item-r"><?php echo $info['chargeorder']['mypaid']; ?>元</div>
										</li>
										<li>
											<div class="chargeinfo-list-item-n">相关信息：</div>
											<div class="chargeinfo-list-item-r"><?php if(strlen($info['chargeorder']['data'])>0){echo $info['chargeorder']['data'];}else{echo '---';} ?></div>
										</li>
										<li>
											<div class="chargeinfo-list-item-n">MY备注：</div>
											<div class="chargeinfo-list-item-r"><?php if(strlen($info['chargeorder']['myremark'])>0){echo $info['chargeorder']['myremark'];}else{echo '---';} ?></div>
										</li>
										<li>
											<div class="chargeinfo-list-item-n">充值状态：</div>
											<div class="chargeinfo-list-item-r" id="chargeState" data-chargestate="<?php echo $info['chargeorder']['completed']; ?>"><?php $stateArr=array('0'=>'尚未充值','1'=>'已充值');echo $stateArr[$info['chargeorder']['completed']]; ?></div>
										</li>
									</ul>
									<?php
									if($info['chargeorder']['completed']==0){
										echo '<div class="chargeinfo-btn"><button id="chargeinfo-setState" data-state="1">设为已充值状态</button></div>';
									}
									?>
								</div>
							</div>
						</dd>
						<dt>订单备注：</dt>
						<dd>
							<textarea class="formpanel-remark" name="remark"<?php if($info['state']!=11){echo ' disabled=""';} ?>><?php echo $info['remark']; ?></textarea>
						</dd>
						<dt></dt>
						<dd></dd>
						<dt><b>总件数：</b></dt>
						<dd><span class="n-sum" id="order-count"><?php echo $info['count']; ?></span>件</dd>
						<dt>总金额：</dt>
						<dd><span class="n-amount" id="order-amount"><?php echo $info['amount']; ?></span>元</dd>
						<dt>总优惠金额：</dt>
						<dd><span class="n-amount" id="order-totaldiscount"><?php echo $info['totaldiscount']; ?></span>元=商品优惠<span class="n-amount" id="order-dmount"><?php echo $info['damount']; ?></span>元+订单优惠<span class="n-amount">
							<input id="order-dorder" disabled="" value="<?php echo $info['dorder']; ?>" />
							</span>元</dd>
						<dt><b>应支付：</b></dt>
						<dd><span class="n-amount n-big" id="order-actualamount"><?php echo $info['actualamount']; ?></span>元</dd>
						<dt><em class="required">*</em><b>收银：</b></dt>
						<dd><span class="n-amount n-big">
							<input value="<?php echo $info['receipts']; ?>" id="order-receipts" <?php if($info['state']!=11){echo 'disabled=""';}?> />
							</span>元</dd>
						<dt><b>找零：</b></dt>
						<dd><span class="n-amount n-big" id="order-change"><?php echo $info['change']; ?></span>元</dd>
					</dl>
					<p class="formpanel-btn">
						<?php if($info['state']==11){echo '<input type="submit" value="确认订单信息" id="order-submit02" />';}else{echo '<a href="http://dahouprint.com/orderprint.php?ac=preview&oid='.$info['oid'].'" target="_blank">打印订单</a>';}?>
					</p>
				</div>
				<div style="display:none;">
				OID<input type="text" value="<?php echo $info['oid']; ?>" name="oid" /><!--oid--><br>
				***订单类型值<input type="text" value="<?php if($info['chargeorder']!=null&&$info['serviceorder']==null){
							echo '0';	//虚拟充值订单
						}elseif($info['chargeorder']==null&&$info['serviceorder']!=null){
							echo '1';	//服务订单
						}else{
							echo '2';	//实物订单
						} ?>" name="otype" id="order-type" /><!--订单类型值--><br>

				订单优惠金额<input value="<?php echo $info['dorder']; ?>" name="dorder" id="dorder" /><!--订单优惠金额--><br>

				订单总优惠金额<input type="text" value="<?php echo $info['totaldiscount']; ?>" name="totaldiscount" /><!--订单总优惠金额--><br>

				应支付<input type="text" value="<?php echo $info['actualamount']; ?>" name="actualamount" /><!--应支付--><br>

				收银<input type="text" value="0" name="receipts" /><!--收银--><br>

				找零<input type="text" value="0" name="change" /><!--找零--><br>
				</div>
			</form>
		</div>
	</div>
</div>
<script src="js/order-show.js"></script>
</body>
</html>