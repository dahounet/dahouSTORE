<?php
class RunTime//页面执行时间类
{
    private $starttime;//页面开始执行时间
    private $stoptime;//页面结束执行时间
    private $spendtime;//页面执行花费时间
    function getmicrotime()//获取返回当前微秒数的浮点数
    {
        list($usec,$sec)=explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
    function start()//页面开始执行函数，返回开始页面执行的时间
    {
        $this->starttime=$this->getmicrotime();
    }
    function end()//显示页面执行的时间
    {
        $this->stoptime=$this->getmicrotime();
        $this->spendtime=$this->stoptime-$this->starttime;
        //return round($this->spendtime,10);
    }
    function display()
    {
        //$this->end();
        echo "<p>运行时间：".round($this->spendtime,10)."秒</p>";
    }
}

$timer=new RunTime();
$timer->start();

date_default_timezone_set('Asia/Shanghai');
session_start();

//获取指定订单号的商品列表信息
function getOrderProductsList($oid){
	$sqls="SELECT * FROM `store_inventory_out` WHERE `oid`={$oid}";
	$result = db_connect()->query ($sqls);
	if($result->num_rows>=1){
		for($i=0;$i<$result->num_rows;$i++){
			$row[$i]=$result->fetch_assoc ();
		}
		recordOrderlog($oid,"获取订单:{$oid} 的商品列表信息 成功",null);
		return $row;
	}else{
		recordOrderlog($oid,"获取订单:{$oid} 的商品列表信息 失败 [异常-数据库select操作失败]",'sql串:'.$sqls);
		return FALSE;
	}
}

//记录打印订单情况和日志
function recordPrintOrder($oid,$data){
	$db=db_connect();
	$sql="UPDATE `store_orders` SET `printcount`=`printcount`+1 WHERE `oid`={$oid}";
	$r=$db->query($sql);
	if($r&&$db->affected_rows == 1){
		recordOrderlog($oid,"订单打印次数增加 1 次 成功",null);
		$sql="INSERT INTO `store_print_log`(`oid`,`data`,`time`,`ip`) VALUES ({$oid},\"".htmlspecialchars($data)."\",".time().",'{$_SERVER ['REMOTE_ADDR']}') ";
		//echo $sql;
		$r=$db->query($sql);
		if($r){
			return true;
		}else{
			return false;
		}
	}else{
		recordOrderlog($oid,"订单打印次数增加 1 次 失败","sql串:{$sql}");
		return false;
	}
}

function generateLeftList($currentclassindex,$thislinkindex){
	$list=array();
	$list[0]=array('classname'=>'订单','links'=>array(0=>array('title'=>'添加新订单','url'=>'order_add.php'),1=>array('title'=>'订单列表','url'=>'orderslist.php'),2=>array('title'=>'订单操作日志','url'=>'order_log.php')));
	$list[1]=array('classname'=>'库存','links'=>array(0=>array('title'=>'库存状态','url'=>'inventory_info.php'),1=>array('title'=>'出库列表','url'=>'inventory_out_list.php'),2=>array('title'=>'库存操作日志','url'=>'inventory_log.php')));
	$list[2]=array('classname'=>'商品','links'=>array(0=>array('title'=>'添加新商品','url'=>'javascript:();'),1=>array('title'=>'商品列表','url'=>'javascript:();'),2=>array('title'=>'商品操作日志','url'=>'javascript:();')));
	$list[3]=array('classname'=>'打印','links'=>array(0=>array('title'=>'小票打印操作日志','url'=>'print_log.php')));
	$list[4]=array('classname'=>'全局日志','links'=>array(0=>array('title'=>'全局日志','url'=>'store_log.php')));
	
	$html='';
	for ($i=0;$i<count($list);$i++){
		$html.='
				<dt>'.$list[$i]['classname'].'</dt>';
		for($i2=0;$i2<count($list[$i]['links']);$i2++){
			$html.='
				<dd><a href="'.$list[$i]['links'][$i2]['url'].'"';
			if($currentclassindex==$i&&$thislinkindex==$i2){
				$html.=' class="curr";';
			}
			$html.='>'.$list[$i]['links'][$i2]['title'].'</a></dd>';
		}
	}
	return $html;
}

function generatePageBtn($url,$text,$sum,$currpage=1,$num=10,$btnnum=5){	//生成翻页按钮及页码	页面的URL信息，信息总数量，当前页码，每页的数量，最多显示的页码按钮的数量
	if(!is_numeric($sum)){
		$sum=0;
	}
	$totalPage=ceil($sum/$num);	//总页数
	
	$currpage>$totalPage?$currpage=$totalPage:1;
	
	//echo "{$sum}/{$num}={$totalPage}";
	$lnum=floor(($btnnum-1)/2);	//当前页码的左边的页码按钮数量
	$rnum=$btnnum-1-$lnum;	//当前页码的右边的页码按钮数量
	
	$start=$currpage-$lnum;	//当前页码的左边的第一个页码按钮的页码
	$stop=$currpage+$rnum;	//当前页码的右边的最后一个页码按钮的页码

	if($start<1){
		//echo "$start|$stop|";
		$stop+=abs($start)+1;
		$start=1;
		//echo "$start|$stop|";
	}
	if($stop>$totalPage){
		//echo "$start|";
		$n=$stop-$totalPage;
		$start-=$n;
		$stop=$totalPage;
		//echo "$start|";
		$start<1?$start=1:1;
	}
	
	if(stripos($url,'?')===false){
		$url=$url.'?page=';
	}else{
		$url=$url.'&page=';
	}
	
	$html='
							<div class="pagination-page">';
	if($currpage!=1&&$currpage>0){
		$html.='
								<div class="page-wrap page-up"><a href="'.$url.($currpage-1).'" class="page-up-btn">上一页</a></div>';
	}
	
	if($start>=2){
		$html.='
								<div class="page-wrap">
									<a href="'.$url.'1" class="page">1</a>
								</div>';
	}
	if($start>=3){
		$html.='
								<div class="page-wrap">
									<a href="'.$url.'2" class="page">2</a>
								</div>';
	}
	if($start>=4){
		$html.='
								<span class="page-split">...</span>';
	}
	
	for($i=$start;$i<=$stop;$i++){
		if($currpage==$i){
			$html.='
								<div class="page-wrap">
									<span class="page-curr">'.$currpage.'</span>
								</div>';
		}else{
			$html.='
								<div class="page-wrap">
									<a href="'.$url.$i.'" class="page">'.$i.'</a>
								</div>';
		}
	}
	
	if($totalPage-$stop>1){
		$html.='
								<span class="page-split">...</span>';
	}
	
	if($totalPage!=$stop){
		$html.='
								<div class="page-wrap">
									<a href="'.$url.$totalPage.'" class="page">'.$totalPage.'</a>
								</div>';
	}
	
	if($currpage!=$totalPage){
		$html.='
								<div class="page-wrap page-down"><a href="'.$url.($currpage+1).'" class="page-down-btn">下一页</a></div>';
	}
	
	$html.='
							</div>
							<div class="pagination-count">共'.$totalPage.'页，'.$sum.'条'.$text.'</div>
						</div>';
	
	//echo "<p>总页数：{$totalPage}，左数量：{$lnum}，右数量：{$rnum}，开始页码：{$start}，结束页码：{$stop}</p>";
	return $html;
}
?>