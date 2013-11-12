<?php

//事务项
class itemWork {
	private $arr_info;
	private function is_no_repeats_log($wid,$type){	//完成事务专用，用于防止重复的日志
		global $db;
		$result = $db->query ( 'SELECT * FROM `work_log` WHERE `wid`='.$wid.' ORDER BY `completedate` DESC LIMIT 1' );
		if($result->num_rows>=1){
			$row=$result->fetch_assoc ();
			switch ($type){
				case 1:
					if(date ( 'Y-m-d' ) == substr ( $row['completedate'], 0, 10 )){
						return FALSE;
					}else{
						return TRUE;
					}
					break;
				case 2:
					return FALSE;
					break;
				case 3:
					if(time () - strtotime ( $row ['completedate'] ) < $this->arr_info['intervaltime']){
						return FALSE;
					}else{
						return TRUE;
					}
					break;
				default:;
			}
		}else{
			return TRUE;
		}
	}
	private function update_work_status($wid, $chgto) { //更新指定事务项的状态
		global $db;
		$result = $db->query ( 'UPDATE `work_list` SET `status`=\'' . $chgto . '\' WHERE `wid`=' . $wid );
		if ($result&&$db->affected_rows == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	function __construct($wid=null) {
		global $db;
		if($wid){	//如果有参
			//return array('wId'=>$item['wId'],'title'=>$item['title'],'type'=>$item['type'],'addTime'=>$item['addTime'],'modTime'=>$item['modTime'],'details'=>$item['details'],'startDate'=>$item['startDate'],'stopdate'=>$item['stopdate'],'intervalTime'=>$item['intervalTime'],'status'=>$item['status']);
			$result = $db->query ( 'SELECT * FROM `work_list` WHERE `wid`=' . $wid );
			if ($result->num_rows > 0) {
				foreach ( $result->fetch_assoc () as $key => $value ) {
					$this->arr_info [$key] = $value;
				}
			}
		}else{	//如果无参
			//echo '无参创建';
			$this->arr_info=NULL;
		}
	}
	
	//无参创建对象专用方法开始----
	function create($title,$type,$describe,$startdate,$ctype,$data,$needkey,$stopdate=NULL,$intervaltime=NULL,$status=0){	//创建新事务
		global $db;
		if($describe!=NULL){
			$describe="'".$describe."'";
		}else{
			$describe='NULL';
		}
		$result = $db->query ('INSERT INTO `work_list`(`title`,`type`,`describe`,`startdate`,`status`,`needkey`) VALUES('."'$title','$type',$describe,'$startdate','$status','$needkey')");
		$result2 = $db->query ('INSERT INTO `work_content`(`wid`,`ctype`,`data`) VALUES('."$db->insert_id,'$ctype','$data')");
		if($result&&$result2){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function delete($wid){	//删除已有事务
		global $db;
		$result = $db->query ("UPDATE `work_list` SET `status`='300' WHERE `wid`=".$wid);
		if($result&&$db->affected_rows==1){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	//无参创建对象专用方法结束----
	
	function complete($key=null) { //完成事务
		global $db;
		if($this->is_no_repeats_log($this->arr_info ['wid'],$this->arr_info ['type'])){
			if($this->arr_info ['needkey']){	//如果需要验证KEY
				if(!($key==$_SESSION['workKey_'.$this->arr_info['wid']]&&$key!=null)){
					return 'incorrect_key';
				}
			
				$result = $db->query ( "INSERT INTO `work_log`(`wid`,`ip`) VALUES(" . $this->arr_info ['wid'] . ",'" . $_SERVER ['REMOTE_ADDR'] . "')" );
				if ($result) {
					if($this->arr_info['type']==2){	//如果当前事务是一次性任务，则直接修改状态为自动停止
						$this->update_work_status($this->arr_info ['wid'], 100);
					}
					unset($_SESSION['workKey_'.$this->arr_info['wid']]);
					return 'succeed';
				} else {
					return FALSE;
				}
				
			}else{
				$result = $db->query ( "INSERT INTO `work_log`(`wid`,`ip`) VALUES(" . $this->arr_info ['wid'] . ",'" . $_SERVER ['REMOTE_ADDR'] . "')" );
				if ($result) {
					if($this->arr_info['type']==2){	//如果当前事务是一次性任务，则直接修改状态为自动停止
						$this->update_work_status($this->arr_info ['wid'], 100);
					}
					return 'succeed';
				} else {
					return FALSE;
				}
				
			}
			
			/*
			
			if(!$this->arr_info ['needkey']){	//如果不需要验证KEY
				$result = $db->query ( "INSERT INTO `work_log`(`wid`,`ip`) VALUES(" . $this->arr_info ['wid'] . ",'" . $_SERVER ['REMOTE_ADDR'] . "')" );
				if ($result) {
					if($this->arr_info['type']==2){	//如果当前事务是一次性任务，则直接修改状态为自动停止
						$this->update_work_status($this->arr_info ['wid'], 100);
					}
					return TRUE;
				} else {
					return FALSE;
				}
			}else{	//如果需要验证KEY
				if($key==$_SESSION['workKey_'.$this->arr_info['wid']]&&$key!=null){
					return TRUE;
				} else {
					return FALSE;
				}
			}*/
		}else{
			return 'finished';
		}
	}
	function modify($arr,$wid=null) { //修改事务，有参、无参均可
		global $db;
		if($wid==null){
			$wid=$this->arr_info ['wid'];
		}
		$allow_modify_array1=array('title','describe','startdate','stopdate','intervaltime','status');
		$allow_modify_array2=array('ctype','data');
		print_r($allow_modify_array1);
		print_r($arr);
		$sql1 = '';
		for($i=0;$i<count($allow_modify_array1);$i++){
			$k=$allow_modify_array1[$i];
			if(array_key_exists($k,$arr)){
				$v=$arr[$k];
				if($v!=NULL){
					$v="'".$v."'";
				}else{
					$v='NULL';
				}
				empty ( $sql1 ) ? 1 : $sql1 .= ',';
				$sql1.="`$k`".'='.$v;
			}
		}
		$sql2 = '';
		for($i=0;$i<count($allow_modify_array2);$i++){
			$k=$allow_modify_array2[$i];
			if(array_key_exists($k,$arr)){
				$v=$arr[$k];
				if($v!=NULL){
					$v="'".$v."'";
				}else{
					$v='NULL';
				}
				empty ( $sql2 ) ? 1 : $sql2 .= ',';
				$sql2.="`$k`".'='.$v;
				}
		}
	
		if(!empty($sql1)){
			$result1 = $db->query ( 'UPDATE `work_list` SET ' . $sql1 . ',`modtime`='."'".date ( 'Y-m-d H:i:s' )."'".' WHERE `wid`=' . $wid );
		}
		if(!empty($sql2)){
			$result2 = $db->query ( 'UPDATE `work_content` SET ' . $sql2 . ' WHERE `wid`=' . $wid );
		}
		if(!empty($sql1)&&!empty($sql2)){
			//echo "【1不空，2不空】01";
			if ($result1&&$result2) {
				return TRUE;
			} else {
				return FALSE;
			}
		}elseif(empty($sql1)&&!empty($sql2)){
			//echo "【1空，2不空】02";
			if ($result2) {
				return TRUE;
			} else {
				return FALSE;
			}
		}elseif(!empty($sql1)&&empty($sql2)){
			//echo "【1不空，2空】03";
			if ($result1) {
				return TRUE;
			} else {
				return FALSE;
			}
		}elseif(empty($sql1)&&empty($sql2)){
			//echo "【1空，2空】04";
			return FALSE;
		}
	}
	function get_info() { //获得事务项信息
		return $this->arr_info;
	}
	function get_log(){	//获得事务的完成情况日志
		global $db;
		$arr=array();
		$i=0;
		$result = $db->query ( 'SELECT * FROM `work_log` WHERE `wid`='.$this->arr_info['wid'] );
		if ($result->num_rows > 0) {
			for($i = 0; $i < $result->num_rows; $i ++) {
				$row = $result->fetch_array ();
				$arr[$i]['logid']=$row['logid'];
				$arr[$i]['wid']=$row['wid'];
				$arr[$i]['completedate']=$row['completedate'];
				$arr[$i]['ip']=$row['ip'];
			}
			return $arr;
		}else{
			return FALSE;
		}
	}
	function get_content(){	//获得事务项内容，为前台提供数据以执行此事务
		global $db;
		$result = $db->query ( 'SELECT * FROM `work_content` WHERE `wid`='.$this->arr_info['wid'] );
		if($result->num_rows>=1){
			$row=$result->fetch_assoc ();
			return $row;
		}else{
			return FALSE;
		}
	}
	function generate_complete_key(){	//生成任务完成时验证的KEY
		$v=array('1','2','3','4','5','6','7','8','9','0','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$result=null;
		for($i=1;$i<=10;$i++){
			$r=$v[array_rand($v, 1)];
			if(rand(0,1)){
				$r=strtolower($r);
			}
			$result.=$r;
		}
		$_SESSION['workKey_'.$this->arr_info['wid']]=$result;
		return $result;
	}
}

//今日所有状态正常事务列表
class worksListToday {
	private $wait_do_num = 0; //今日当前待办事务数目
	private $list_arr;
	
	private function is_exist($arr, $wid) { //检查已完成事务列表中是否存在指定的wid的数组
		$count = count ( $arr );
		for($i = 0; $i < $count; $i ++) {
			if ($arr [$i] ['wid'] == $wid) {
				return $arr [$i];
			}
		}
	}
	
	private function update_work_status($wid, $chgto) { //更新指定事务项的状态
		global $db;
		$result = $db->query ( 'UPDATE `work_list` SET `status`=\'' . $chgto . '\' WHERE `wid`=' . $wid );
		if ($result&&$db->affected_rows == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	private function is_compelete_now($row, $last_complete_arr) { //检查一项事务当前是否已完成
		switch ($row ['type']) { //根据事务类型的不同进行相应判断
			case 1 : //每日事务
				//$result=db_connect()->query("SELECT * FROM `work_log` WHERE `wId`=".$row['wId']." AND DATE(`completedate`)=CURRENT_DATE");
				$rt = $this->is_exist ( $last_complete_arr, $row ['wid'] );
				if ($rt) { //如果经检查数组中存在
					if (date ( 'Y-m-d' ) == substr ( $rt ['completedate'], 0, 10 )) {
						return TRUE; //当日已完成
					} else { //当日未完成
						$this->wait_do_num += 1;
						return FALSE;
					}
				} else { //当日未完成，且从未完成过
					$this->wait_do_num += 1;
					return FALSE;
				}
				break;
			case 2 : //一次性事务
				//$result=db_connect()->query("SELECT * FROM `work_log` WHERE `wId`=".$row['wId']);
				$rt = $this->is_exist ( $last_complete_arr, $row ['wid'] );
				if ($rt) { //如果经检查数组中存在
					return TRUE; //完成了
				} else {
					//未完成，且从未完成过
					$this->wait_do_num += 1;
					return FALSE;
				}
				break;
			case 3 : //周期性事务
				//$result=db_connect()->query("SELECT * FROM `work_log` WHERE `wId`=".$row['wId']." AND CURRENT_TIMESTAMP-`completedate`<=".$row['intervalTime']);
				$rt = $this->is_exist ( $last_complete_arr, $row ['wid'] );
				if ($rt) { //如果经检查数组中存在
					if (time () - strtotime ( $rt ['completedate'] ) <= $row ['intervaltime']) {
						return TRUE; //当日已完成
					} else { //当日未完成
						$this->wait_do_num += 1;
						return FALSE;
					}
				} else { //当日未完成，且从未完成过
					$this->wait_do_num += 1;
					return FALSE;
				}
				break;
			default :
				;
		}
	}
	
	function __construct() {
		global $db;
		$last_complete_arr = array ();
		$result = $db->query ( "SELECT * FROM `work_list` WHERE (CURRENT_TIMESTAMP>=`startdate` OR `startdate` IS NULL) AND `status`='0'" );
		if ($result) {
			$result2 = $db->query ( "SELECT MAX(`logid`) AS `logid`,`wid`,MAX(`completedate`) AS `completedate`,`ip` FROM `work_log` GROUP BY `wid`" );
			for($i = 0; $i < $result2->num_rows; $i ++) {
				$row2 = $result2->fetch_assoc ();
				$last_complete_arr [$i] = $row2;	//获得事务完成日志中的每个事务的最新的一条日志的数组
			}
			for($i = 0; $i < $result->num_rows; $i ++) {
				$row = $result->fetch_assoc ();
				if ($row ['stopdate'] != NULL && time () > strtotime ( $row ['stopdate'] ) && $row ['status'] == 0) { //判断一项事务当前是否已过期
					$this->update_work_status ( $row ['wid'], 110 );	//已过期，执行清理，设置为过期停止状态
				}else{	//尚未过期
					$this->list_arr [$i] ['wid'] = $row ['wid'];
					$this->list_arr [$i] ['title'] = $row ['title'];
					$this->list_arr [$i] ['finished'] = $this->is_compelete_now ( $row, $last_complete_arr );	//判断一项事务当前是否已经完成
					$this->list_arr [$i] ['deleteable'] = $row ['deleteable'];
				}
			}
		}
	}
	function get_list() {
		return $this->list_arr;
	}
	function get_sum() {
		return $this->work_sum;
	}
	function get_wait_do_num() {
		return $this->wait_do_num;
	}
}

?>