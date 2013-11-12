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
<title>出库列表</title>
<link href="style/common.css" rel="stylesheet" type="text/css">
</head>

<body>
<div class="main">
	<div class="main-top">大猴STORE 管理系统 V0.1</div>
	<div class="main-left-wrap">
		<div class="main-left">
			<h1 class="nav-top"><a href="#">管理系统首页</a></h1>
			<dl class="nav-list">
				<?php echo generateLeftList(1,1) ?>
			</dl>
		</div>
	</div>
	<div class="main-right">
		<div class="tabview">
			<ul class="tabpanel">
				<li class="main-right-tab-curr"><a href="javascript:void();">出库列表</a></li>
			</ul>
		</div>
		<div class="logslist-wrap">
			<div class="logslist">
				<table class="logslist-tb">
					<thead>
						<tr>
							<th style="width:7%;">流水号</th>
							<th style="width:12%;">所属订单</th>
							<th style="width:10%;">商品编号</th>
							<th style="width:10%;">售出时进价</th>
							<th style="width:10%;">售价</th>
							<th style="width:8%;">每件优惠</th>
							<th style="width:8%;">数量</th>
							<th style="width:16%;">备注</th>
							<th style="width:14%;">出库时间</th>
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
				
				$result1=$db->query("SELECT * FROM `store_inventory_out` ORDER BY  `sid` DESC");
				$result2=$db->query("SELECT * FROM `store_inventory_out` ORDER BY  `sid` DESC LIMIT $start, {$num}");
				//echo "SELECT * FROM `store_orders_log` ORDER BY  `logid` DESC LIMIT $start, {$num}";
				
				$total_num_rows=$result1->num_rows;
				$num_rows=$result2->num_rows;
				
				if($num_rows>=1){
					for($i=0;$i<$result2->num_rows;$i++){
						$row=$result2->fetch_assoc ();
						echo '
						<tr>
							<td>'.$row['sid'].'</td>
							<td><a class="logslist-ordernumber" href="order_show.php?oid='.$row['oid'].'">'.$row['oid'].'</a></td>
							<td>'.$row['pid'].'</td>
							<td>'.$row['pprice'].'</td>
							<td>'.$row['price'].'</td>
							<td>'.$row['damount'].'</td>
							<td>'.$row['number'].'</td>
							<td>'.$row['remark'].'</td>
							<td>'.date("Y-m-d H:i:s",$row['time']).'</td>
						</tr>';
					}
				}else{
					echo '
						<tr class="logslist-empty">
							<td colspan="9"><span>无数据</span></td>
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
