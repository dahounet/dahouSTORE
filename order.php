<?php
header('Content-Type:text/html; charset=utf-8');

include_once 'inc/conn.php';
include_once 'inc/common.inc.php';
include_once 'inc/core.inc.php';

if($_GET['ac']=='create'){	//如果是创建订单
	if($_GET['type']=='charge'){	//如果是虚拟充值类订单
		$r=createChargeOrder($_POST['type'],$_POST['cname']
				,$_POST['amount'],$_POST['damount']
				,$_POST['dorder'],$_POST['totaldiscount']
				,$_POST['actualamount']
				,0,0,$_POST['remark']
				,$_SERVER ['REMOTE_ADDR']
				,$_POST['chargeInfo']
				,$_POST['plist']);
		if($r){	//创建虚拟充值订单成功
			echo json_encode(array('newoid'=>$r,'state'=>200,'msg'=>'创建虚拟充值类订单成功'));
		}else{
			recordStorelog('创建虚拟充值类订单订单 失败',null);
			echo json_encode(array('state'=>400,'msg'=>'创建虚拟充值类订单失败'));
		}
	}elseif($_GET['type']=='normal'){	//如果是普通订单
		$r=createOrder($_POST['type'],$_POST['cname']
				,$_POST['count'],$_POST['amount']
				,$_POST['damount'],$_POST['dorder'],$_POST['totaldiscount']
				,$_POST['actualamount']
				,0,0,$_POST['remark']
				,$_SERVER ['REMOTE_ADDR']
				,null
				,$_POST['plist']);
		if($r){	//创建订单成功
			echo json_encode(array('state'=>200,'newoid'=>$r,'msg'=>'创建订单成功'));
		}else{
			recordStorelog('创建普通订单 失败',null);
			echo json_encode(array('state'=>400,'msg'=>'创建普通订单失败'));
		}
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
}elseif($_GET['ac']=='setchargecompleted'){	//虚拟充值订单充值状态改为已充值
	if(is_numeric($_POST['oid'])){
		$r=chargeOrderSetToCompleted($_POST['oid']);
		if($r==true){
			echo json_encode(array('state'=>200,'msg'=>'设为已充值状态成功'));
		}else{
			recordStorelog('虚拟充值订单充值状态改为已充值 失败',"oid:{$_POST['oid']}");
			echo json_encode(array('state'=>400,'msg'=>'设为已充值状态失败'));
		}
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
}elseif($_GET['ac']=='modify'){	//修改订单信息（订单优惠金额、总优惠金额、应支付金额、收银金额、找零金额、订单备注）仅限状态为11的订单
	$r=modOrderInfo($_POST['oid'],$_POST['dorder'],$_POST['totaldiscount'],$_POST['actualamount'],$_POST['receipts'],$_POST['change'],$_POST['remark']);
	if($r!=false){
		echo json_encode(array('state'=>200,'oid'=>"{$_POST['oid']}",'msg'=>'订单信息修改成功'));
	}else{
			recordStorelog('订单信息修改 失败',"oid:{$_POST['oid']}");
			echo json_encode(array('state'=>400,'msg'=>'订单信息修改失败'));
	}
}elseif($_GET['ac']=='confirm'){	//确认订单信息
	if(is_numeric($_POST['oid'])){
		$r=confirmOrder($_POST['oid'],$_POST['dorder'],$_POST['totaldiscount'],$_POST['actualamount'],$_POST['receipts'],$_POST['change'],$_POST['remark']);
		if($r==true){
			echo json_encode(array('state'=>200,'msg'=>'确认订单信息成功'));
		}else{
			recordStorelog('确认订单信息 失败',"oid:{$_POST['oid']}");
			echo json_encode(array('state'=>400,'msg'=>'确认订单信息失败'));
		}
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
}elseif($_GET['ac']=='getorderinfo'){	//获取指定订单的信息
	if(is_numeric($_GET['oid'])){
		$info=getOrderInfo($_GET['oid']);
		if($info!=false){
			echo json_encode(array('state'=>200,'orderinfo'=>$info));
		}else{
			recordStorelog('获取指定订单的信息 失败',"oid:{$_GET['oid']}");
			echo json_encode(array('state'=>400,'msg'=>'获取指定订单的信息失败'));
		}
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
}elseif($_GET['ac']=='getproductslist'){	//获取指定订单号的商品列表信息
	if(is_numeric($_GET['oid'])){
		$list=getOrderProductsList($_GET['oid']);
		if($list!=false){
			echo json_encode(array('state'=>200,'oid'=>$_GET['oid'],'productslist'=>$list));
		}else{
			recordStorelog('获取指定订单号的商品列表信息 失败',"oid:{$_GET['oid']}");
			echo json_encode(array('state'=>400,'msg'=>'获取指定订单号的商品列表信息失败'));
		}
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
}elseif($_GET['ac']=='getproductinfo'){	//获取指定商品的信息
	if(is_numeric($_GET['pid'])){
		$info=getProductInfo($_GET['pid']);
		if($info!=false){
			echo json_encode(array('state'=>200,'productinfo'=>$info));
		}else{
			recordStorelog('获取指定商品的信息 失败',"pid:{$_GET['pid']}");
			echo json_encode(array('state'=>400,'msg'=>'获取指定商品的信息失败'));
		}
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
}elseif($_GET['ac']=='getallproducts'){	//获取所有/指定类别的商品信息
	if(isset($_GET['ptype'])&&is_numeric($_GET['ptype'])){
		$info=getAllProductsInfo($_GET['ptype']);
		if($info!=false){
			echo json_encode(array('state'=>200,'productsinfo'=>$info));
		}else{
			recordStorelog('获取指定类别的商品信息 失败',"ptype:{$_GET['ptype']}");
			echo json_encode(array('state'=>400,'msg'=>'获取指定类别的商品信息失败'));
		}
	}elseif(!isset($_GET['ptype'])){
		$info=getAllProductsInfo();
		if($info!=false){
			echo json_encode(array('state'=>200,'productsinfo'=>$info));
		}else{
			recordStorelog('获取所有类别的商品信息 失败',null);
			echo json_encode(array('state'=>400,'msg'=>'获取所有类别的商品信息失败'));
		}
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
}elseif($_GET['ac']=='getinverntoryinfo'){	//获取指定商品的库存信息
	if(is_numeric($_GET['pid'])){
		$r=getInverntoryInfo($_GET['pid']);
		if($r!=false){
			echo json_encode(array('state'=>200,'inverntoryinfo'=>$r));
		}else{
			recordStorelog('获取指定商品的库存信息 失败',"pid:{$_GET['pid']}");
			echo json_encode(array('state'=>400,'msg'=>'获取指定商品的库存信息失败'));
		}
	}else{
		echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
	}
}else{
	echo json_encode(array('state'=>500,'msg'=>'请求不正确'));
}






/*
if($_GET['ac']=='add'){
	$c=array('parvalue'=>50.00,'mypaid'=>49.25,'account'=>"13986728679",'data'=>"湖北移动",'myremark'=>"测试中...");	//来自于充值页面上采集的充值信息
	//订单来路类型，客户名，商品总价，商品总折扣，订单折扣，总折扣，应支付，收银，找零，订单备注，IP，虚拟充值订单补充信息，商品JSON数据
	echo createChargeOrder(0,'大猴'	//订单来路类型，客户名
			,50,0	//商品总价，商品总折扣
			,0,0	//订单折扣，总折扣
			,50,0,0	//应支付，收银，找零
			,null	//订单备注
			,'192.168.1.1'	//IP
			,json_encode($c)	//虚拟充值订单补充信息
			,'[{"pid":1003,"damount":0,"number":1,"remark":"中国移动","pprice":49.25,"price":50}]');	//商品JSON数据
}

if($_GET['ac']=='add2'){
	//订单来路类型，客户名，商品总数，商品总价，商品总折扣，订单折扣，总折扣，应支付，收银，找零，订单备注，IP，是否虚拟充值订单，商品JSON数据
	echo createOrder(0,'测试姓名'	//订单来路类型，客户名
			,2,16	//商品总数，商品总价
			,0,5,5	//商品总折扣，订单折扣，总折扣
			,11,0,0	//应支付，收银，找零
			,'备注一下'	//订单备注
			,'192.168.1.100'	//IP
			,null	//是否虚拟充值订单
			,'[{"pid":1005,"damount":0.00,"number":1,"remark":null},{"pid":1005,"damount":0.00,"number":1,"remark":null}]');	//商品JSON数据
}*/


//print_r( getOrderProductList(16));
//checkOrderValue(4,8+12*2+50,2,0,0+2,80,0,0,'[{"pid":1005,"damount":1.00,"number":1,"remark":null},{"pid":1006,"damount":0.50,"number":2,"remark":null},{"pid":1003,"damount":0.00,"number":1,"remark":"中国移动","pprice":49.25,"price":50}]');

//checkMoney(50,5,7,12,38,100,62,'4559');

//print_r(getOrderInfo(28));
//print_r(getOrderProductList(28));

/*
$r=confirmOrder(52,5,5,11,100,89,'备注TEST');	//订单号、订单优惠金额、总优惠金额、实际应支付金额、收银金额、找零金额、订单备注
if($r){
	echo '确认成功';
}else{
	echo '确认失败';
}*/
//chargeOrderSetToCompleted(43);

//changeOrderState(27,51);
//inventoryOut('[{"pid":1005,"damount":0.00,"number":1,"remark":null},{"pid":1006,"damount":0.00,"number":2,"remark":null}]',900008);

//print_r(getProductInfo(1006));
/*
$result = $db->multi_query ("INSERT INTO `store_orders_log`(`oid`,`action`,`ip`,`time`,`remark`) VALUES('99145','创建新订单','192.168.1.100','1382969887',null);INSERT INTO `store_orders_log`(`oid`,`action`,`ip`,`time`,`remark`) VALUES('99345','创建新订单','192.168.1.100','1382969887',null);INSERT INTO `store_orders_log`(`oid`,`action`,`ip`,`time`,`remark`) VALUES('99245','创建新订单','192.168.1.100','1382969887',null);");
if ($result) {
	echo '成功';
} else {
	echo '失败！！';
}*/
/*
if(inventoryOut('[{"pid":1003,"damount":0.00,"number":1,"remark":"中国移动","pprice":49.25,"price":50}]',999988)){
	echo '出库成功！！';
}else{
	echo '出库失败';
}*/
/*$successed=array();
array_push($successed, array('pid'=>111,'number'=>45666));	//加入锁定成功数组
print_r($successed);*/

/*
for($i=0;$i<600;$i++){
	$result = db_connect()->query ( "INSERT INTO `store_inventory_log`(`action`,`ip`,`time`,`remark`) VALUES('测试{$i}','{$_SERVER ['REMOTE_ADDR']}','".time()."','呵呵')" );
	inventoryOut('[{"pid":1005,"damount":0.00,"number":1,"remark":null},{"pid":1006,"damount":0.00,"number":2,"remark":null}]',900008);
	if($result){
		echo $i." 成功<br>";
	}else{
		echo $i." 失败！<br>";
	}
}*/

//confirmOrder(48,0,0,50,100,50,'测试备注');
/*$timer->end();
$timer->display();*/
?>