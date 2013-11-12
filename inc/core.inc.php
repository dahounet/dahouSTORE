<?php
//创建订单

//订单日志
function recordOrderlog($oid,$action,$logremark){
	$db_log=db_connect();
	$logremark==null?$logremark='null':$logremark="'$logremark'";
	$result = $db_log->query ( "INSERT INTO `store_orders_log`(`oid`,`action`,`ip`,`time`,`remark`) VALUES('$oid','$action','{$_SERVER ['REMOTE_ADDR']}','".time()."',".$logremark.")" );
	//if(!result) echo '<br>'."INSERT INTO `store_orders_log`(`oid`,`action`,`ip`,`time`,`remark`) VALUES('$oid','$action','{$_SERVER ['REMOTE_ADDR']}','".time()."',".$logremark.")".'<br>';
}

//新订单数值校验（新订单创建时使用）：校验每种商品的数量、总数量、单价以及单件商品的优惠、订单优惠、总优惠、应支付、收银、找零的金额互相之间是否相符
function checkOrderValue($count,$amount,$damount,$dorder,$totaldiscount,$actualamount,$receipts,$change,$list){
	$plist=json_decode($list);
	//print_r($plist);
	
	$rcount=0;	//商品总数量
	$ramount=0;	//商品总金额
	$rdamount=0;	//商品总优惠额
	$rtotaldiscount=0;	//总优惠额（商品总优惠额+订单优惠额）
	$ractualamount=0;	//应支付金额
	$rreceipts=0;	//新订单默认的收银金额
	$rchange=0;	//新订单默认的找零金额
	
	for($i=0;$i<count($plist);$i++){
		$productInfo=getProductInfo($plist[$i]->pid);
		$rcount+=$plist[$i]->number;
		if($productInfo['ptype']==1){	//如果是虚拟类商品，则以list中的单价为准
			$price=$plist[$i]->price;
		}else{	//否则以商品列表中查到的价格为准
			$price=$productInfo['price'];
		}
		$ramount+=$price*$plist[$i]->number;
		$rdamount+=$plist[$i]->damount*$plist[$i]->number;
	}
	$rtotaldiscount=$rdamount+$dorder;
	$ractualamount=$ramount-$rdamount-$dorder;
	
	//echo "$count | $rcount,$amount | $ramount,$damount | $rdamount,$rtotaldiscount | $totaldiscount,$ractualamount | $actualamount,$rreceipts | $receipts,$rchange | $change";
	if($count==$rcount&&$amount==$ramount&&$damount==$rdamount&&$rtotaldiscount==$totaldiscount&&$ractualamount==$actualamount&&$rreceipts==$receipts&&$rchange==$change){	//新添加的订单的收银金额、找零金额应均为0
		//echo "相符 =$count $rcount=,=$amount $ramount=,=$damount $rdamount=<br>";
		recordOrderlog(0,'新订单的所有数值（价格、数量）数据互相校验通过','比对结果：'."{$rcount},{$ramount},{$rdamount},{$rtotaldiscount},{$ractualamount},{$rreceipts},{$rchange} | {$count},{$amount},{$damount},{$totaldiscount},{$actualamount},{$receipts},{$change}");
		return true;
	}else{
		recordOrderlog(0,'新订单的订单所有数值（价格、数量）数据互相校验未通过','比对结果：'."{$rcount},{$ramount},{$rdamount},{$rtotaldiscount},{$ractualamount},{$rreceipts},{$rchange} | {$count},{$amount},{$damount},{$totaldiscount},{$actualamount},{$receipts},{$change}");
		//echo "不相符 =$count $rcount=,=$amount $ramount=,=$damount $rdamount=<br>";
		return false;
	}
}

//校验收银部分的金额是否正确（用于确认订单时使用）
function checkMoney($amount,$damount,$dorder,$totaldiscount,$actualamount,$receipts,$change,$oid){
	if(!($damount*1000+$dorder*1000==$totaldiscount*1000&&$amount*1000-$totaldiscount*1000==$actualamount*1000&&$receipts*1000>=$actualamount*1000&&$change*1000==$receipts*1000-$actualamount*1000)){
		$a=$damount*1000+$dorder*1000==$totaldiscount*1000;
		$b=$amount*1000-$totaldiscount*1000==$actualamount*1000;
		$c=$receipts*1000>=$actualamount*1000;
		$d=($change*1000==$receipts*1000-$actualamount*1000);
		recordOrderlog(0,'订单号:'.$oid.' 收银部分金额校验未通过',"{$damount}+{$dorder}=={$totaldiscount}[{$a}]，{$amount}-{$totaldiscount}=={$actualamount}[{$b}]，{$receipts}>={$actualamount}[{$c}]，{$change}=={$receipts}-{$actualamount}[{$d}] —— 值：{$amount},{$damount},{$dorder},{$totaldiscount},{$actualamount},{$receipts},{$change}");
																//16.00,	0.00,		5.00,		5.00,			11.00,			12.05,		1.05
		return false;
	}else{
		recordOrderlog(0,'订单号:'.$oid.' 收银部分金额校验通过',"{$amount},{$damount},{$dorder},{$totaldiscount},{$actualamount},{$receipts},{$change}");
		return true;
	}
}

//创建新订单
function createOrder($type,$cname,$count,$amount,$damount,$dorder,$totaldiscount,$actualamount,$receipts,$change,$remark,$ip,$chargeorder,$plist){
	//订单来路类型，客户名，商品总数，商品总价，商品总折扣，订单折扣，总折扣，应支付，收银，找零，订单备注，IP，是否虚拟充值订单，商品JSON数据
	$db=db_connect();
	
	if(!is_numeric($type)||!in_array($type,array(0,1,2,3))){
		recordOrderlog(0,'欲创建的新订单的订单来源类别的值不正确',null);
	}
	

	$remark=trim($remark);
	//null值文本化
	$cname==null?$cname='null':$cname="'".mysql_real_escape_string($cname)."'";
	$remark==null?$remark='null':$remark="'".mysql_real_escape_string($remark)."'";
	$chargeorder==null?$chargeorder='null':$chargeorder="'$chargeorder'";
	
	if(!checkOrderValue($count,$amount,$damount,$dorder,$totaldiscount,$actualamount,$receipts,$change,$plist)){
		return false;
	}
	
	
	//创建订单
	$result = $db->query ( "INSERT INTO `store_orders`(`type`,`cname`,`count`,`amount`,`damount`,`dorder`,`totaldiscount`,`actualamount`,`receipts`,`change`,`remark`,`ip`,`ctime`,`chargeorder`) VALUES('$type',".$cname.",'$count','$amount','$damount','$dorder','$totaldiscount','$actualamount','$receipts','$change',".$remark.",'$ip','".time()."',".$chargeorder.")" );
	if ($result) {
		$oid=$db->insert_id;
		recordOrderlog($oid,$chargeorder=='null'?'创建新订单 成功':'创建新虚拟充值订单 成功',null);
		if(inventoryOut($plist,$oid)){
			recordOrderlog($oid,$chargeorder=='null'?'创建新订单-出库 成功':'创建新虚拟充值订单-出库 成功',null);
			changeOrderState($oid,11);	//订单状态改为：商品已出库
			return $oid;
		}else{	//出库失败
			recordOrderlog($oid,$chargeorder=='null'?'创建新订单-出库 失败':'创建新虚拟充值订单-出库 失败','此订单将作废（订单状态更改为55）');
			changeOrderState($oid,55);	//订单状态改为：订单作废
			return false;
		}
	} else {
		recordOrderlog(0,$chargeorder=='null'?'创建新订单 失败 [异常-数据库insert操作失败]':'创建新虚拟充值订单 失败 [异常-数据库insert操作失败]',null);
		return FALSE;
	}
}

//创建虚拟类新订单
function createChargeOrder($type,$cname,$amount,$damount,$dorder,$totaldiscount,$actualamount,$receipts,$change,$remark,$ip,$chargeInfo,$plist){
	//订单来路类型，客户名，商品总价，商品总折扣，订单折扣，总折扣，应支付，收银，找零，订单备注，IP，虚拟充值订单补充信息，商品JSON数据
	$db=db_connect();
	//null值文本化
	
	$chargeInfoObject=json_decode($chargeInfo);
	$chargeInfoObject->myremark=trim($chargeInfoObject->myremark);
	
	$chargeInfoObject->data==null?$chargeInfoObject->data='null':$chargeInfoObject->data="'".$chargeInfoObject->data."'";
	$chargeInfoObject->myremark==null?$chargeInfoObject->myremark='null':$chargeInfoObject->myremark="'".$chargeInfoObject->myremark."'";
	
	$chargeInfoObject->account=$chargeInfoObject->account;
	
	$plist_temp=json_decode($plist);
	$plist_temp[0]->pprice=$chargeInfoObject->mypaid;
	$plist_temp[0]->price=$chargeInfoObject->parvalue;
	$plist=json_encode($plist_temp);
	
	//创建虚拟类订单补充信息
	$sql="INSERT INTO `store_orders_charge`(`parvalue`,`mypaid`,`account`,`data`,`myremark`) VALUES (".$chargeInfoObject->parvalue.','.$chargeInfoObject->mypaid.',"'.$chargeInfoObject->account.'",'.$chargeInfoObject->data.','.$chargeInfoObject->myremark.")";
	$result = $db->query ( $sql );
	if ($result) {
		recordOrderlog(0,'创建新虚拟类订单-创建虚拟类充值订单 补充信息 成功','CID:'.$db->insert_id);
		$cid=$db->insert_id;
	} else {
		recordOrderlog(0,'创建新虚拟类订单-创建虚拟类充值订单 补充信息 失败 [异常-数据库insert操作失败]','sql串:'.$sql);
		return FALSE;
	}

	$return=createOrder($type,$cname,1,$amount,$damount,$dorder,$totaldiscount,$actualamount,$receipts,$change,$remark,$ip,$cid,$plist);
	if ($return==false){
		recordOrderlog(0,'创建新虚拟类订单 失败','创建虚拟类充值订单 补充信息成功后，创建订单时失败（请查阅邻近的日志以确定原因）');
		$result = $db->query("DELETE FROM `store_orders_charge` WHERE `cid`={$cid}");
		if ($result) {
			recordOrderlog(0,'创建新虚拟类订单失败后，删除多余的虚拟类充值订单 补充信息 成功',"cid:{$cid}");
		} else {
			recordOrderlog(0,'创建新虚拟类订单失败后，删除多余的虚拟类充值订单 补充信息 失败',"cid:{$cid}");
		}
		return FALSE;
	}
	return $return;
}

//更改订单状态
function changeOrderState($oid,$newState){
	$allowState=array('0'=>array('11','55'),'11'=>array('12','51'),'55'=>null,'12'=>null,'51'=>null);	//允许的订单状态更改次序
	$result = db_connect()->query ( "SELECT `state`,`ctime`,`chargeorder` FROM `store_orders` WHERE `oid`='{$oid}'" );
	if($result->num_rows>=1){
		$row=$result->fetch_assoc ();
		if($allowState[$row['state']]!=null&&in_array($newState,$allowState[$row['state']])){
			if($newState=='51'&&time()-$row['ctime']<=60*60*24){	//如果欲改为的新状态是51（超过24小时未确认的订单），判断订单是否超过24小时未确认，如果没有超过24小时，则
				recordOrderlog($oid,'更改订单状态 失败',"未超过24小时不能关闭订单 原状态：{$row['state']}，欲改为的新状态：$newState");
				return FALSE;
			}
			if($newState=='12'&&$row['chargeorder']!=null){	//如果欲改为的新状态是12（订单信息已确认），并且是虚拟充值类订单，则判断充值操作是否已完成
				recordOrderlog($oid,'此订单是虚拟充值类订单，开始判断充值操作是否已完成',"只有充值操作已经完成才可更改订单状态");
				$result = db_connect()->query ( "SELECT `completed` FROM `store_orders_charge` WHERE `cid`='{$row['chargeorder']}'" );
				if($result->num_rows>=1){
					$row2=$result->fetch_assoc ();
					if($row2['completed']==1){	//如果已充值完成
						recordOrderlog($oid,'允许更改订单状态',"虚拟充值类订单充值操作已经完成，原状态：{$row['state']}，欲改为的新状态：$newState");
					}else{
						recordOrderlog($oid,'更改订单状态 失败',"虚拟充值类订单充值操作未完成，原状态：{$row['state']}，欲改为的新状态：$newState");
						return false;
					}
				}else{
					recordOrderlog($oid,'判断充值操作是否已完成 失败 [异常-数据库select操作出错]',null);
					return false;
				}
			}
			$sql="UPDATE `store_orders` SET `state`='{$newState}',`modtime`=".time()." WHERE `oid`='{$oid}'";
			$db=db_connect();
			$result = $db->query ( $sql );
			if ($result&&$db->affected_rows == 1) {
				recordOrderlog($oid,'更改订单状态 成功',"原状态：{$row['state']}，新状态：$newState");
				return true;
			}else{
				recordOrderlog($oid,'更改订单状态 失败 [异常-数据库update操作失败]',"原状态：{$row['state']}，欲改为的新状态：$newState");
				return false;
			}
		}else{
			recordOrderlog($oid,'更改订单状态 失败',"原状态：{$row['state']} 不允许更改为新状态：$newState");
		}
	}else{
		recordOrderlog($oid,'更改订单状态 失败-获取订单的当前状态失败 [异常-数据库select操作失败]',"欲改为的新状态：$newState");
		return FALSE;
	}
}

//获取所有/指定类别的商品信息
function getAllProductsInfo($ptype=null){
	if($ptype!=null){
		$sql='SELECT * FROM `store_products` WHERE `ptype`='.$ptype;
	}else{
		$sql='SELECT * FROM `store_products`';
	}
	
	if($ptype==null||$ptype==1||$ptype==2||$ptype==3){
		$db=db_connect();
		$result = $db->query ( $sql );
		if($result->num_rows>=1){
			for($i=0;$i<$result->num_rows;$i++){
				$row[$i]=$result->fetch_assoc ();
			}
			return $row;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
//获取商品的信息
function getProductInfo($pid){
	$db=db_connect();
	$result = $db->query ( 'SELECT * FROM `store_products` WHERE `pid`='.$pid );
	if($result->num_rows>=1){
		$row=$result->fetch_assoc ();
		return $row;
	}else{
		return FALSE;
	}
}
//增加库存
function inventoryPlus($pid,$plusNum){
	if($plusNum<=0) return false;
	$db=db_connect();
	$result=$db->query ( 'UPDATE `store_inventory` SET `number`=`number`+'.$plusNum.' WHERE `pid`=' . $pid );
	if ($result&&$db->affected_rows == 1) {
		recordInventorylog("商品编号:{$pid}，库存增加 {$plusNum} 成功",null);
		return true;
	}else{
		recordInventorylog("商品编号:{$pid}，库存增加 {$plusNum} 失败 [异常-数据库update操作失败]",null);
		return FALSE;
	}
}
//减少库存
function inventoryMinus($pid,$minusNum){
	if($minusNum<=0) return false;
	$db=db_connect();
	$result=$db->query ( 'UPDATE `store_inventory` SET `number`=`number`-'.$minusNum.' WHERE `pid`=' . $pid );
	if ($result&&$db->affected_rows == 1) {
		recordInventorylog("商品编号:{$pid}，库存减少 {$minusNum} 成功",null);
		return true;
	}else{
		recordInventorylog("商品编号:{$pid}，库存减少 {$minusNum} 失败 [异常-数据库update操作失败]","可能的原因：库存不足");
		return FALSE;
	}
}
//获取指定商品的库存信息
function getInverntoryInfo($pid){
	$db=db_connect();
	$result = $db->query ( 'SELECT `number`,`type`,`remark` FROM `store_inventory` WHERE `pid`='.$pid );
	if($result->num_rows>=1){
		$row=$result->fetch_assoc ();
		return $row;
	}else{
		return FALSE;
	}
}
//库存变动日志
function recordInventorylog($action,$logremark){
	$db_log=db_connect();
	$logremark==null?$logremark='null':$logremark="'$logremark'";
	$result = $db_log->query ( "INSERT INTO `store_inventory_log`(`action`,`ip`,`time`,`remark`) VALUES('$action','{$_SERVER ['REMOTE_ADDR']}','".time()."',".$logremark.")" );
	//if($result) ; else echo '['."INSERT INTO `store_inventory_log`(`action`,`ip`,`time`,`remark`) VALUES('$action','{$_SERVER ['REMOTE_ADDR']}','".time()."',".$logremark.")".']库存变动日志添加失败...';
}
//商品出库
function inventoryOut($list,$oid){
	$db=db_connect();
	$plist=json_decode($list);
	//print_r($plist);
	
	//预先减少库存（从库存中预先锁定所有订单内商品的数量对应的库存数）
	$successed=array();
	for($i=0;$i<count($plist);$i++){
		//echo "=循环$i=";
		$productInfo=getProductInfo($plist[$i]->pid);
		//echo "[".$productInfo['ptype']."]";
		/*$plist[$i]->pid;
		$plist[$i]->damount;
		$plist[$i]->number;
		$plist[$i]->remark;*/
		if($productInfo['ptype']==1||$productInfo['ptype']==2||inventoryMinus($plist[$i]->pid,$plist[$i]->number)){	//如果发现当前商品是虚拟充值类商品，或者是服务类商品，或者成功锁定库存
			array_push($successed, array('pid'=>$plist[$i]->pid,'number'=>$plist[$i]->number,'ptype'=>$productInfo['ptype']));	//加入锁定成功数组
			
			//如果当前商品是服务或实物商品，则获取对应的进价和售价
			$productInfo['ptype']>=2?$plist[$i]->pprice=$productInfo['pprice']:1;
			$productInfo['ptype']>=2?$plist[$i]->price=$productInfo['price']:1;
		}else{	//发现未能成功锁定库存
			//echo '发现未能成功锁定库存！！';
			recordInventorylog("订单号:{$oid}，商品编号:{$plist[$i]->pid}未能成功锁定库存","欲锁定的数量：{$plist[$i]->number}，商品类型：{$productInfo['ptype']}");
			break;
		}
	}
	
	//echo '=|'.count($plist).'=|'.count($successed);
	if(count($plist)==count($successed)){	//锁定库存成功
		recordInventorylog("订单号:{$oid}，全部商品已成功锁定库存",null);
		//echo '成功';
		//将商品信息放入出库表
		$sqls='';
		for($i=0;$i<count($plist);$i++){
			$plist[$i]->remark=trim($plist[$i]->remark);
			
			$plist[$i]->remark==null?$plist[$i]->remark='null':$plist[$i]->remark="'".mysql_real_escape_string($plist[$i]->remark)."'";
			$sqls.="INSERT INTO `store_inventory_out`(`oid`,`pid`,`pprice`,`price`,`damount`,`number`,`remark`,`time`) VALUES ($oid,".$plist[$i]->pid.",".$plist[$i]->pprice.",".$plist[$i]->price.",".$plist[$i]->damount.",".$plist[$i]->number.",".$plist[$i]->remark.",".time().");";
		}
		//echo "【$sqls】";
		$result = $db->multi_query ($sqls);
		if ($result) {
			recordInventorylog("订单号:{$oid}，全部商品出库 成功",null);
			return true;
		} else {
			recordInventorylog("订单号:{$oid}，全部商品出库 失败 [异常-将商品信息放入出库表 数据库insert操作失败]",'sql串:'.$sqls);
			return FALSE;
		}
	}else{	//锁定库存失败
		recordInventorylog("订单号:{$oid}，锁定库存失败，开始恢复全部被锁定的库存",null);
		//echo '失败！！';
		//print_r($successed);
		for($i=0;$i<count($successed);$i++){	//恢复被锁定的库存
			//echo "恢复！！-".$successed[$i]['pid'].",".$successed[$i]['number'].",i=".$i."---<br>";
			if($successed[$i]['ptype']==3){	//如果是实物类商品，则执行增加库存操作
				inventoryPlus($successed[$i]['pid'],$successed[$i]['number']);
			}
		}
		recordInventorylog("订单号:{$oid}，已恢复全部被锁定的库存",null);
		return false;
	}
	
	
	//print_r($plist);
}

//全局日志
function recordStorelog($action,$logremark){
	$db_log=db_connect();
	$logremark==null?$logremark='null':$logremark="'$logremark'";
	$result = $db_log->query ( "INSERT INTO `store_log`(`action`,`ip`,`time`,`remark`) VALUES('$action','{$_SERVER ['REMOTE_ADDR']}','".time()."',".$logremark.")" );
}

//获取指定订单号的订单信息
function getOrderInfo($oid){
	$sqls="SELECT * FROM `store_orders` WHERE `oid`={$oid}";
	$result = db_connect()->query ($sqls);
	if($result->num_rows>=1){
		$row=$result->fetch_assoc ();
		if($row['chargeorder']!=null){	//如果是虚拟充值类订单，则还要获取 补充信息
			recordOrderlog($oid,"获取订单:{$oid} 的订单信息 成功",'虚拟充值类订单，需继续获取补充信息');
			$chargeInfo=getOrder_chargeInfo($oid,$row['chargeorder']);
			if(chargeInfo){
				$row['chargeorder']=$chargeInfo;
				return $row;
			}else{
				return false;
			}
		}else{
			recordOrderlog($oid,"获取订单:{$oid} 的订单信息 成功",null);
			return $row;
		}
	}else{
		recordOrderlog($oid,"获取订单:{$oid} 的订单信息 失败 [异常-数据库select操作失败]",'sql串:'.$sqls);
		return FALSE;
	}
}
//获取指定虚拟充值类订单的订单补充信息
function getOrder_chargeInfo($oid,$cid){
	$sqls="SELECT * FROM `store_orders_charge` WHERE `cid`={$cid}";
	$result = db_connect()->query ($sqls);
	if($result->num_rows>=1){
		$row=$result->fetch_assoc ();
		recordOrderlog($oid,"获取订单:{$oid} 的订单补充信息(cid:{$cid}) 成功",null);
		return $row;
	}else{
		recordOrderlog($oid,"获取订单:{$oid} 的订单补充信息(cid:{$cid}) 失败 [异常-数据库select操作失败]",'sql串:'.$sqls);
		return FALSE;
	}
}

//修改订单信息
function modOrderInfo($oid,$dorder,$totaldiscount,$actualamount,$receipts,$change,$remark){	//订单号、订单优惠金额、总优惠金额、实际应支付金额、收银金额、找零金额、订单备注
	$orderInfo=getOrderInfo($oid);
	$remark=trim($remark);
	$remark==''?$remark='null':$remark="'".mysql_real_escape_string($remark)."'";
	if($orderInfo['state']&&checkMoney($orderInfo['amount'],$orderInfo['damount'],$dorder,$totaldiscount,$actualamount,$receipts,$change,$oid)){	//如果订单状态为11，且各个数值校验通过
		$sql="UPDATE `store_orders` SET `dorder`='{$dorder}',`totaldiscount`='{$totaldiscount}',`actualamount`='{$actualamount}',`receipts`='{$receipts}',`change`='{$change}',`remark`={$remark},`modtime`=".time()." WHERE `oid`={$orderInfo['oid']}";
		//echo "<p>大家好：$sql</p>";
		$db=db_connect();
		$result=$db->query($sql);
		if ($result&&$db->affected_rows == 1){
			recordOrderlog($oid,"修改订单信息 成功",null);
			return true;
		}else{
			recordOrderlog($oid,"修改订单信息 失败 [异常-数据库update操作失败]",'sql串:'.$sql);
			return false;
		}
	}else{
		recordOrderlog($oid,"修改订单信息 失败","可能的原因：1.订单的当前状态({$orderInfo['state']})不允许修改（只有11状态才允许修改）、2. 数值校验未通过");
		return false;
	}
}

//（已出库的）虚拟充值类订单充值状态改为已充值
function chargeOrderSetToCompleted($oid){
	$orderInfo=getOrderInfo($oid);
	if($orderInfo['chargeorder']!=null&&$orderInfo['state']==11){	//如果订单为虚拟充值类订单，且订单状态为11（已出库状态）
		if($orderInfo['chargeorder']['completed']==0){	//如果该虚拟充值订单当前充值状态为未充值状态，则改为已充值状态
			$sql="UPDATE `store_orders_charge` SET `completed`=1 WHERE `cid`={$orderInfo['chargeorder']['cid']}";
			//echo $sql;
			$db=db_connect();
			$r=$db->query($sql);
			if($r&&$db->affected_rows == 1){
				recordOrderlog($oid,"虚拟充值类订单充值状态改为已充值 成功",null);
				return true;
			}else{
				recordOrderlog($oid,"修改订单信息 失败 [异常-数据库update操作失败]","sql串:{$sql}");
			}
		}else{
			recordOrderlog($oid,"虚拟充值类订单充值状态改为已充值 失败","当前充值状态已经为已充值状态");
		}
	}else{
		recordOrderlog($oid,"虚拟充值类订单充值状态改为已充值 失败","当前订单须是虚拟充值类订单且订单状态（{$orderInfo['state']}）必须为11（已出库状态）");
	}
	return false;
}

//确认订单信息
function confirmOrder($oid,$dorder,$totaldiscount,$actualamount,$receipts,$change,$remark){		//订单号、订单优惠金额、总优惠金额、实际应支付金额、收银金额、找零金额、订单备注
	if(modOrderInfo($oid,$dorder,$totaldiscount,$actualamount,$receipts,$change,$remark)&&changeOrderState($oid,12)){
		recordOrderlog($oid,"确认订单信息 成功",null);
		return true;
	}else{
		recordOrderlog($oid,"确认订单信息 失败",null);
		return false;
	}
}
?>