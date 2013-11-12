<?php
header('Content-Type:text/html; charset=utf-8');

include_once 'inc/conn.php';
include_once 'inc/common.inc.php';
include_once 'inc/core.inc.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>添加新订单</title>
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
				<?php echo generateLeftList(0,0) ?>
			</dl>
		</div>
	</div>
	<div class="main-right">
		<div class="tabview">
			<ul class="tabpanel">
				<li class="main-right-tab-curr"><a href="#">添加新订单</a></li>
			</ul>
		</div>
		<div class="controlpanel" id="controlpanel">
			<form action="order.php?ac=create&type=charge" method="post" id="addorderinfo">
				<div class="formpanel">
					<dl class="formpanel-itemlist">
						<dt><em class="required">*</em>订单来源：</dt>
						<dd>
							<select name="type">
								<option value="1" selected="selected">店面</option>
								<option value="2">网络</option>
								<option value="3">电话</option>
								<option value="0">其他</option>
							</select>
						</dd>
						<dt>客户姓名：</dt>
						<dd>
							<input type="text" name="cname"/>
						</dd>
						<dt><em class="required">*</em>商品详情：</dt>
						<dd>
							<div class="buyproducts-wrap">
								<div class="buyproducts">
									<div class="buyproducts-head"> <span class="buyproducts-head-1">商品名称</span> <span class="buyproducts-head-2">单价</span> <span class="buyproducts-head-3">数量</span> <span class="buyproducts-head-4">每件优惠</span> <span class="buyproducts-head-5">操作</span> </div>
									<div class="buyproducts-empty" id="buyproduct-none">尚未添加商品</div>
									<div class="buyproducts-main" id="buyproduct-main">
										<ul class="buyproducts-main-list" id="buyproduct-main-list">
											<li>
												<div class="buyproducts-main-item buyproducts-main-1">手机充电器</div>
												<div class="buyproducts-main-item buyproducts-main-2">15.00</div>
												<div class="buyproducts-main-item buyproducts-main-3">
													<input value="1" />
													<span class="buyproducts-main-unit">件</span></div>
												<div class="buyproducts-main-item buyproducts-main-4">
													<input value="0" />
													元</div>
												<div class="buyproducts-main-item buyproducts-main-5"><a href="#">删除</a></div>
											</li>
											<li>
												<div class="buyproducts-main-item buyproducts-main-1">手机充电器</div>
												<div class="buyproducts-main-item buyproducts-main-2">15.00</div>
												<div class="buyproducts-main-item buyproducts-main-3">
													<input value="1" />
													<span class="buyproducts-main-unit">件</span></div>
												<div class="buyproducts-main-item buyproducts-main-4">
													<input value="0" />
													元</div>
												<div class="buyproducts-main-item buyproducts-main-5"><a href="#">删除</a></div>
											</li>
										</ul>
									</div>
									<div class="buyproducts-add">
										<div class="buyproducts-add-item">
											<select class="buyproducts-add-o1" id="productsSelect">
												<option value="-1" selected="selected">[未选择]</option>
											</select>
										</div>
										<div class="buyproducts-add-item"><input value="1" class="buyproducts-add-o2" id="product-num" disabled="" /><span class="buyproducts-add-item-unit" id="product-unit1">件</span></div>
										<div class="buyproducts-add-item">每<span id="product-unit2">件</span>优惠：<input value="0" class="buyproducts-add-o3" id="product-discount" disabled="" />元</div>
										<div class="buyproducts-add-btn"><button id="product-addbtn" disabled="">添加</button></div>
										<p class="buyproducts-add-notice" id="buyproducts-add-notice">注意：每个订单有且仅能有1件虚拟充值类商品，不允许与非虚拟充值类商品在一个订单。</p>
									</div>
								</div>
							</div>
							<div class="chargeinfo-wrap" id="chargeInfo">
								<div class="chargeinfo">
									<h1><b>虚拟类充值缴费商品补充信息</b></h1>
									<ul class="chargeinfo-list">
										<li>
											<div class="chargeinfo-list-item-n"><em class="required">*</em>手机号码/帐号：</div>
											<div class="chargeinfo-list-item-r">
												<input type="text" id="charge-account" />
											</div>
										</li>
										<li>
											<div class="chargeinfo-list-item-n"><em class="required">*</em>面值：</div>
											<div class="chargeinfo-list-item-r">
												<select id="charge-parvalue">
													<option value="0" selected="selected">[未选择]</option>
													<option value="1">1元</option>
													<option value="10">10元</option>
													<option value="20">20元</option>
													<option value="30">30元</option>
													<option value="50">50元</option>
													<option value="100">100元</option>
													<option value="200">200元</option>
													<option value="300">300元</option>
													<option value="500">500元</option>
												</select>
											</div>
											<div class="chargeinfo-list-item-n"><em class="required">*</em>MY进价：</div>
											<div class="chargeinfo-list-item-r">
												<input class="chargeinfo-list-myprice" type="text" id="charge-mypaid" value="0" />
												元</div>
										</li>
										<li>
											<div class="chargeinfo-list-item-n">相关信息：</div>
											<div class="chargeinfo-list-item-r">
												<textarea class="chargeinfo-list-data" id="charge-data"></textarea>
											</div>
										</li>
										<li>
											<div class="chargeinfo-list-item-n">MY备注：</div>
											<div class="chargeinfo-list-item-r">
												<textarea class="chargeinfo-list-myremark" id="charge-myremark"></textarea>
											</div>
										</li>
									</ul>
									<div class="chargeinfo-btn"><button id="chargeinfo-btn" data-state="1">确认充值信息</button></div>
								</div>
							</div>
						</dd>
						<dt>订单备注：</dt>
						<dd>
							<textarea class="formpanel-remark" name="remark"></textarea>
						</dd>
						<dt></dt>
						<dd></dd>
						<dt><b>总件数：</b></dt>
						<dd><span class="n-sum" id="order-count">0</span>件</dd>
						<dt>总金额：</dt>
						<dd><span class="n-amount" id="order-amount">0.00</span>元</dd>
						<dt>总优惠金额：</dt>
						<dd><span class="n-amount" id="order-totaldiscount">0.00</span>元=商品优惠<span class="n-amount" id="order-dmount">0.00</span>元+订单优惠<span class="n-amount">
							<input value="0.00" id="order-dorder" />
							</span>元</dd>
						<dt><b>应支付：</b></dt>
						<dd><span class="n-amount n-big" id="order-actualamount">0.00</span>元</dd>
					</dl>
					<p class="formpanel-btn">
						<input type="submit" value="添加订单" id="order-submit" />
					</p>
				</div>
				<div style="display:none;">
				商品总数<input type="text" value="0" name="count" /><!--商品总数-->

				商品总价<input type="text" value="0" name="amount" /><!--商品总价-->

				订单内所有商品优惠金额总和<input type="text" value="0" name="damount" /><!--订单内所有商品优惠金额总和-->

				订单优惠金额<input value="0" name="dorder" id="dorder" /><!--订单优惠金额-->

				订单总优惠金额<input type="text" value="0" name="totaldiscount" /><!--订单总优惠金额-->

				应支付<input type="text" value="0" name="actualamount" /><!--应支付-->

				充值类订单信息<input type="text" value="" name="chargeInfo" /><!--充值类订单信息-->

				购买的商品列表<input type="text" value="" name="plist" /><!--购买的商品列表-->
				</div>

			</form>
		</div>
	</div>
</div>
<script src="js/order.js"></script>
</body>
</html>