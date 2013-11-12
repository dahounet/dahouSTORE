//初始化
var currentProduct=new Object();
var isChargeOrder=null;

$(document).ready(function(){
	
	$("#order-receipts").unbind().click(function(e) {
		$(this).val($("#order-actualamount").text());
		function checkValue(target){
			if(target.val()==''){
				target.val($("#order-actualamount").text());
			}
			if(target.val()<0){
				//console.log('小了');
				target.val($("#order-actualamount").text());
			}
			var pointPos=target.val().indexOf('.');
			if(pointPos>=0){	//如果含有小数点
				if(target.val().length-1-pointPos>2){	//如果小数点后面的字符数超过2个
					var a=target.val().substr(0,pointPos);	//截取整数部分
					var b=target.val().substr(pointPos+1,2);	//截取小数部分
					target.val(a+'.'+b);
				}
				if(pointPos==0&&target.val().length==1){	//如果小数点在开头,并且内容长度等于1
					//说明可能正在输入小数，不执行操作
				}
				if(pointPos==0&&target.val().length>1){	//如果小数点在开头,并且内容长度大于1
					target.val('0'+target.val());
				}
			}else{	//如果没有小数点
				target.val(target.val()*1);
			}
		}
		_this=$(this);
		if(_this.attr('data-focus')!='yes'){
			_this.select().attr('data-focus','yes');
			var t=setInterval(function(){
					if(_this.attr('data-focus')=='yes'){
						checkValue(_this);
						//console.log('焦点在，继续循环');
						handleOrderInfo();
					}else{
						checkValue(_this);
						//console.log('焦点已不在，退出');
						handleOrderInfo();
						clearInterval(t)}
					}
				,10);
		}
	}).blur(function(e) {
		//console.log('移除焦点');
		var pointPos=$(this).val().indexOf('.');
		if($(this).val().length==pointPos+1){
			$(this).val($(this).val()+'0');
		}
		$(this).attr('data-focus','no');
	});;
	
	$("#chargeinfo-setState").click(function(e) {	//设为已充值状态按钮
		//chargeState
		if($("#order-type").val()==0){	//如果当前订单是虚拟充值订单
			if($("#chargeState").attr("data-chargestate")==0){	//如果订单充值状态为未充值
				$.post("/order.php?ac=setchargecompleted",{oid:$("#addorderinfo input[name='oid']").val()}, function(json){
					if(json.state==200){
						alert('设为已充值状态成功');
						$("#chargeState").attr("data-chargestate",1).text('已充值');
						$("#chargeinfo-setState").attr("disabled","");
					}else{
						alert('设为已充值状态失败');
					}
				},'json');
			}else{
				alert('无需设置');
			}
		}else{
			alert('不是虚拟充值订单');
		}
	});
	
	//获取POST所需数据
	function getPostValue(){
		var data=new Object();
		data['oid']=$("#addorderinfo input[name='oid']").val();
		data['dorder']=$("#addorderinfo input[name='dorder']").val();
		data['totaldiscount']=$("#addorderinfo input[name='totaldiscount']").val();
		data['actualamount']=$("#addorderinfo input[name='actualamount']").val();
		data['remark']=$("#addorderinfo textarea[name='remark']").val();
		data['receipts']=$("#addorderinfo input[name='receipts']").val();
		data['change']=$("#addorderinfo input[name='change']").val();
		
		return data;
	}
	$("#order-submit02").click(function(e) {
		if($("#order-type").val()==0){	//如果当前订单是虚拟充值订单
			if($("#chargeState").attr("data-chargestate")!=1){	//如果充值尚未完成
				alert('请先确认充值状态！');
				return;
			}
		}
		
		if($("#addorderinfo input[name='receipts']").val()*1>=$("#addorderinfo input[name='actualamount']").val()*1&&$("#addorderinfo input[name='change']").val()*1>=0){	//如果收银大于等于应支付并且找零大于等于0
				$.post("/order.php?ac=confirm",getPostValue(), function(json){
					if(json.state==200){
							alert('确认订单信息成功');
							window.location.reload();
					}else{
							alert('确认订单信息失败');
					}
				},'json');
		}else{
			alert('收银与找零金额不正确！');
		}
	});
});

$("#addorderinfo").submit( function(e){
  return false;
});

function handleOrderInfo(){	//处理订单的信息数据
		
	var o6=$("#order-actualamount").text();	//应支付
	console.log('应支付：'+o6);
	var o9=$("#order-receipts").val();	//收银*
	console.log('收银：'+o9);
	var o10=(o9*1000-o6*1000)/1000;	
	console.log('找零：'+o10);
	//找零*
	
	//计算与整理数据完毕，开始设置表单值
	$("#addorderinfo input[name='receipts']").val(o9);	//设置收银
	$("#addorderinfo input[name='change']").val(o10);	//设置找零
	
	showOrderInfo();	//在页面上显示订单信息
	function showOrderInfo(){	//在页面上显示订单信息
		$("#order-change").text(o10);	//显示找零
	}
}