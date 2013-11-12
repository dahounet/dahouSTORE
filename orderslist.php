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
<title>订单列表</title>
<link href="style/common.css" rel="stylesheet" type="text/css">
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
				<li class="main-right-tab-curr"><a href="#">全部订单</a></li>
				<li><a href="#">虚拟类订单</a></li>
				<li><a href="#">服务类订单</a></li>
				<li><a href="#">实物订单</a></li>
				<li><a href="#">已关闭订单</a></li>
			</ul>
		</div>
		<div class="logslist-wrap">
			<div class="logslist">
				<table class="orderslist-tb">
					<thead>
						<tr>
							<th>订单号</th>
							<th style="width:8%;">订单来源</th>
							<th style="width:8%;">客户姓名</th>
							<th style="width:6%;">总件数</th>
							<th style="width:8%;">消费金额</th>
							<th style="width:12%;">状态</th>
							<th style="width:9%;">创建时间</th>
							<th style="width:9%;">上次修改</th>
							<th style="width:8%;">已打印次数</th>
							<th style="width:18%;">操作</th>
						</tr>
					</thead>
					<tbody>
				<?php
				$num=20;	//每页显示的数量
				if(isset($_GET['page'])&&is_numeric($_GET['page'])&&$_GET['page']>0){
					$currentpage=floor($_GET['page']);
					$start=($currentpage-1)*$num;	//需要显示的数据库开始行的序号
				}else{
					$currentpage=1;
					$start=0;
				}

				$db=db_connect();
				
				$result1=$db->query("SELECT * FROM `store_orders` ORDER BY  `oid` DESC");
				$result2=$db->query("SELECT * FROM `store_orders` ORDER BY  `oid` DESC LIMIT $start, {$num}");
				//echo "SELECT * FROM `store_orders_log` ORDER BY  `logid` DESC LIMIT $start, {$num}";
				
				$total_num_rows=$result1->num_rows;
				$num_rows=$result2->num_rows;
				
				$ofromtype=array(0=>'其他',1=>'店面',2=>'网络',3=>'电话');
				$otype=array(0=>'<i class="orderslist-chargemark">虚拟</i>',1=>'<i class="orderslist-servicemark">服务</i>',2=>'');
				$ostate=array(0=>'已创建',11=>'已出库',12=>'已确认 已完成',51=>'已作废',55=>'已关闭');
				
				if($num_rows>=1){
					for($i=0;$i<$result2->num_rows;$i++){
						$row=$result2->fetch_assoc ();
						if($row['chargeorder']!=null&&$row['serviceorder']==null){
							$otypesign=$otype[0];
						}elseif($row['chargeorder']==null&&$row['serviceorder']!=null){
							$otypesign=$otype[1];
						}else{
							$otypesign=$otype[2];
						}
						echo '
						<tr>
							<td><a class="orderslist-ordernumber" href="order_show.php?oid='.$row['oid'].'">'.$row['oid'].$otypesign.'</a></td>
							<td>'.$ofromtype[$row['type']].'</td>
							<td>'.$row['cname'].'</td>
							<td>'.$row['count'].'</td>
							<td>'.$row['actualamount'].'</td>
							<td>'.$ostate[$row['state']].'</td>
							<td>'.date("Y-m-d H:i:s",$row['ctime']).'</td>
							<td>'.date("Y-m-d H:i:s",$row['modtime']).'</td>
							<td><a href="#">'.$row['printcount'].'</a></td>
							<td class="orderslist-tb-op"><a href="order_show.php?oid='.$row['oid'].'">查看详情</a><a href="http://dahouprint.com/orderprint.php?ac=preview&oid='.$row['oid'].'" target="_blank">打印</a></td>
						</tr>';
					}
				}else{
					echo '
						<tr class="logslist-empty">
							<td colspan="6"><span>无数据</span></td>
						</tr>';
				}
				?>
					</tbody>
				</table>
				<div class="pagination-wrap">
					<div class="pagination">
						<div class="pagination-pages">
							<?php echo generatePageBtn(null,'记录',$total_num_rows,$currentpage,$num,5); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>
