//初始化
var currentProduct=new Object();
var isChargeOrder=null;

$(document).ready(function(){
	
	$("#buyproduct-main-list").empty();
	
	$.getJSON("order.php?ac=getallproducts", function(json){
		//productsSelect
		var c='<option value="-1" selected="selected">[未选择]</option>';
		if(json.state==200){
			for(var ptype=1;ptype<=3;ptype++){
				var opt=new Array('','<optgroup label="虚拟充值">','<optgroup label="服务">','<optgroup label="实物">');
				c+=opt[ptype];
				for(var i=0;i<json.productsinfo.length;i++){
					if(json.productsinfo[i].ptype==ptype){
						c+='<option value="'+json.productsinfo[i].pid+'" data-ptype="'+json.productsinfo[i].ptype+'">'+json.productsinfo[i].name+' -- [ '+json.productsinfo[i].price+' 元 ]'+' - '+json.productsinfo[i].pid+'</option>';
					}
				}
				c+='</optgroup>';
			}
			$("#productsSelect").html(c);
		}
	})
	
	//获取POST所需数据
	function getPostValue(type){
		var data=new Object();
		data['type']=$("#addorderinfo select[name='type']").val();
		data['cname']=$("#addorderinfo input[name='cname']").val();
		data['amount']=$("#addorderinfo input[name='amount']").val();
		data['damount']=$("#addorderinfo input[name='damount']").val();
		data['dorder']=$("#addorderinfo input[name='dorder']").val();
		data['totaldiscount']=$("#addorderinfo input[name='totaldiscount']").val();
		data['actualamount']=$("#addorderinfo input[name='actualamount']").val();
		data['remark']=$("#addorderinfo textarea[name='remark']").val();
		data['plist']=$("#addorderinfo input[name='plist']").val();
		if(type==1){	//普通订单、提交订单
			data['count']=$("#addorderinfo input[name='count']").val();
		}else if(type==2){	//普通订单、确认订单
			//
		}else if(type==3){	//虚拟充值类订单、提交订单
			data['chargeInfo']=$("#addorderinfo input[name='chargeInfo']").val();
		}else if(type==4){	//虚拟充值类订单、确认充值状态
			//
		}else if(type==5){	//虚拟充值类订单、确认订单
			//
		}
		return data;
	}
	$("#order-submit").click(function(e) {
		if($("#addorderinfo input[name='count']").val()>0){	//如果订单商品数量大于0
			if(isChargeOrder==true){	//如果是虚拟充值类订单
				if($("#chargeinfo-btn").attr("data-state")==0){	//如果虚拟充值类订单的补充信息已经确认
					$.post("/order.php?ac=create&type=charge",getPostValue(3), function(json){
						if(json.state==200){
							alert('添加虚拟充值类订单成功');
							$("#controlpanel").html('<div class="opinfo-wrap"><div class="opinfo"><div class="opinfo-title">添加新虚拟充值订单成功！</div><div class="opinfo-info">订单号：<a href="order_show.php?oid='+json.newoid+'">'+json.newoid+'</a></div><div class="opinfo-op"><a href="order_show.php?oid='+json.newoid+'" class="opinfo-op-link">[查看订单详情]</a></div></div></div>');
						}else{
							alert('添加虚拟充值类订单失败');
						}
					},'json');
				}else{
					alert('请先确认虚拟类充值缴费商品补充信息');
				}
			}else{
				//执行普通订单提交操作
				$.post("/order.php?ac=create&type=normal",getPostValue(1), function(json){
					if(json.state==200){
						alert('添加订单成功');
							$("#controlpanel").html('<div class="opinfo-wrap"><div class="opinfo"><div class="opinfo-title">添加新订单成功！</div><div class="opinfo-info">订单号：<a href="order_show.php?oid='+json.newoid+'">'+json.newoid+'</a></div><div class="opinfo-op"><a href="order_show.php?oid='+json.newoid+'" class="opinfo-op-link">[查看订单详情]</a></div></div></div>');
					}else{
						alert('添加订单失败');
					}
				},'json');
			}
		}else{
			alert('订单商品数量为0，请添加了商品后再提交');
		}
	});
});

$("#addorderinfo").submit( function(e){
  return false;
});
$("#productsSelect").change(function(e) {
	if($(this).val()!='-1'){
		$("#product-addbtn").removeAttr("disabled");
		$.getJSON('http://dahoustore.com/order.php?ac=getproductinfo&pid='+$(this).val(), function(json){
			if(json.state==200){
				$("#product-num").removeAttr("disabled");
				$("#product-discount").removeAttr("disabled");
				$("#product-addbtn").removeAttr("disabled");
				
				$("#product-unit1").text(json.productinfo.unit);
				$("#product-unit2").text(json.productinfo.unit);
				
				currentProduct.name=json.productinfo.name;
				currentProduct.price=json.productinfo.price;
				currentProduct.ptype=json.productinfo.ptype;
				currentProduct.unit=json.productinfo.unit;
				currentProduct.pid=json.productinfo.pid;
				
				if(json.productinfo.ptype==1){	//如果当前选择的商品是虚拟充值类商品，则
					$("#product-num").attr("disabled","");
					$("#product-discount").attr("disabled","");
				}else{
					$("#product-num").removeAttr("disabled");
					$("#product-discount").removeAttr("disabled");
				}
			}else{
				alert('获取商品信息出错');
			}
		})
	}else{
		$("#product-addbtn").attr("disabled","");
		currentProduct.name='';
		currentProduct.price='';
		currentProduct.ptype='';
		currentProduct.unit='';
		currentProduct.pid='';
	}
});
function removeListItem(t){
	var target=$(t).parent().parent();
	
	target.remove();
	
	var num=$("#buyproduct-main-list li").length;
	if(num==0){	
		$("#buyproduct-none").show();
		$("#buyproduct-main").hide();
		$("#buyproduct-main-list").empty();
		$("#buyproducts-add-notice").siblings().show();
		$("#productsSelect option").removeAttr("disabled");
	}
	if(target.attr("data-ptype")=='1'){
		isChargeOrder=false;
		$("#chargeInfo").hide();
	}
	
	$("#order-dorder").val('0');
	handleOrderInfo();
}
function handleOrderInfo(){	//处理订单的信息数据
	var pItemNum=$("#buyproduct-main-list li").length;	//商品列表条数
	var o1=0	//商品总数
	var o2=0	//商品总价
	var o3=0	//商品总折扣
	var o4=parseFloat($("#order-dorder").val())	//订单折扣
	var o5=0	//总折扣
	var o6=0	//应支付
	var o7=''	//虚拟充值类订单JSON信息**，为''时说明不是此类订单
	var o8=''	//购买的商品列表JSON数据
		
	var o9=0	//收银*
	var o10=0	//找零*
	
	for(var i=0;i<pItemNum;i++){
		pItem=$("#buyproduct-main-list li:eq("+i+")");
		
		o1+=parseInt(pItem.attr("data-sumnum"));
		o2+=((parseFloat(pItem.attr("data-price"))*1000)*parseInt(pItem.attr("data-sumnum")))/1000;
		o3+=(parseInt(pItem.attr("data-sumnum"))*(parseFloat(pItem.attr("data-discount"))*1000))/1000;
		
		//生成商品列表JSON信息
		if(o8==''){
			o8='[{"pid":'+pItem.attr("data-pid")+',"damount":'+pItem.attr("data-discount")+',"number":'+pItem.attr("data-sumnum")+',"remark":null}';
		}else{
			o8+=',{"pid":'+pItem.attr("data-pid")+',"damount":'+pItem.attr("data-discount")+',"number":'+pItem.attr("data-sumnum")+',"remark":null}';
		}
		
		//处理虚拟充值类订单的面值问题
		if(pItem.attr("data-ptype")=='1'){	//如果此项为虚拟充值类商品
			o2=parseFloat($("#charge-parvalue").val());
		}
		//生成虚拟充值类订单JSON信息
		if(pItem.attr("data-ptype")=='1'){	//如果此项为虚拟充值类商品
			if(o7==''){
				o7='{"parvalue":'+$("#charge-parvalue").val()+',"mypaid":'+$("#charge-mypaid").val()+',"account":"'+$("#charge-account").val()+'","data":"'+$("#charge-data").val()+'","myremark":"'+$("#charge-myremark").val()+'"}';
			}else{
				o7+=',{"parvalue":'+$("#charge-parvalue").val()+',"mypaid":'+$("#charge-mypaid").val()+',"account":"'+$("#charge-account").val()+'","data":"'+$("#charge-data").val()+'","myremark":"'+$("#charge-myremark").val()+'"}';
			}
		}
	}
	if(o8!='')	o8+=']';
	
	o5=(o3*1000+o4*1000)/1000;
	o6=(parseFloat(o2)*1000-parseFloat(o5)*1000)/1000;
	
	//计算与整理数据完毕，开始设置表单值
	$("#addorderinfo input[name='count']").val(o1);	//设置商品总数
	$("#addorderinfo input[name='amount']").val(o2);	//设置商品总价
	$("#addorderinfo input[name='damount']").val(o3);	//设置商品总折扣
	$("#addorderinfo input[name='dorder']").val(o4);	//设置订单折扣
	$("#addorderinfo input[name='totaldiscount']").val(o5);	//设置总折扣
	$("#addorderinfo input[name='actualamount']").val(o6);	//设置应支付
	if(pItem.attr("data-ptype")=='1'){$("#addorderinfo input[name='chargeInfo']").val(o7);}//设置虚拟充值类订单JSON信息数据
	$("#addorderinfo input[name='plist']").val(o8);	//设置购买的商品列表JSON数据
	
	showOrderInfo();	//在页面上显示订单信息
	function showOrderInfo(){	//在页面上显示订单信息
		$("#order-count").text($("#addorderinfo input[name='count']").val());	//显示商品总数
		$("#order-amount").text($("#addorderinfo input[name='amount']").val());	//显示商品总价
		$("#order-dmount").text($("#addorderinfo input[name='damount']").val());	//显示商品总折扣
		$("#order-dorder").text($("#addorderinfo input[name='dorder']").val());	//显示订单折扣
		$("#order-totaldiscount").text($("#addorderinfo input[name='totaldiscount']").val());	//显示总折扣
		$("#order-actualamount").text($("#addorderinfo input[name='actualamount']").val());	//显示应支付
	}
}

$("#product-addbtn").click(function(e) {
	if($("#productsSelect").val()=="-1"){alert('请选择售出的商品');return;}
	
	var sumnum=+$("#product-num").val();	//添加进去的商品数量
	var discount=$("#product-discount").val();	//新添加商品的每件优惠金额
	
	$("#buyproduct-none").hide();
	$("#buyproduct-main").show();
	$("#buyproduct-main-list").append('<li data-pid="'+currentProduct.pid+'" data-ptype="'+currentProduct.ptype+'" data-price="'+currentProduct.price+'" data-sumnum="'+sumnum+'" data-discount="'+discount+'" data-price="'+currentProduct.price+'"><div class="buyproducts-main-item buyproducts-main-1">'+currentProduct.name+'</div><div class="buyproducts-main-item buyproducts-main-2">'+currentProduct.price+'</div><div class="buyproducts-main-item buyproducts-main-3"><input value="'+sumnum+'" disabled="" /><span class="buyproducts-main-unit">'+currentProduct.unit+'</span></div><div class="buyproducts-main-item buyproducts-main-4"><input value="'+discount+'" disabled="" />元</div><div class="buyproducts-main-item buyproducts-main-5"><a href="javascript:;">删除</a></div></li>');
	
	$("#productsSelect option[value='-1']").attr("selected","");
	$("#product-num").val(1).attr("disabled","");
	$("#product-discount").val(0).attr("disabled","");
	$("#product-addbtn").attr("disabled","");
	
	$("#buyproduct-main-list li div a").unbind("click").click(function(e) {
		removeListItem($(this));
	});
	
	$("#order-dorder").unbind().click(function(e) {
		function checkValue(target){
			if(target.val()==''){
				target.val('0');
			}
			if(target.val()<0){
				//console.log('小了');
				target.val('0');
			}
			if(target.val()*1+$("#addorderinfo input[name='damount']").val()*1>$("#addorderinfo input[name='amount']").val()*1){
				//console.log('大了');
				target.val('0');
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
	
	if(currentProduct.ptype==1){
		$("#buyproducts-add-notice").siblings().hide();
		$("#productsSelect option").attr("disabled","");
		$("#chargeInfo").show();
		isChargeOrder=true;
		$("#chargeinfo-btn").unbind().click(function(e) {
			if($("#charge-account").val().length>3&&$("#charge-parvalue").val()!=0&&$("#charge-mypaid").val()*1>0){	//如果必填项均已填写
				var t=$(this).attr("data-state");
				if(t==1){	//如果是状态1，确认充值信息
					$("#charge-account").attr("disabled","");
					$("#charge-parvalue").attr("disabled","");
					$("#charge-mypaid").attr("disabled","");
					$("#charge-data").attr("disabled","");
					$("#charge-myremark").attr("disabled","");
					$(this).attr("data-state",0).text('修改充值信息');
				}else{	//修改充值信息
					$("#charge-account").removeAttr("disabled");
					$("#charge-parvalue").removeAttr("disabled");
					$("#charge-mypaid").removeAttr("disabled");
					$("#charge-data").removeAttr("disabled");
					$("#charge-myremark").removeAttr("disabled");
					$(this).attr("data-state",1).text('确认充值信息');
				}
				handleOrderInfo();
			}else{
				alert('请将 虚拟类充值缴费商品补充信息 的必填项填写完整！');
			}
		});
	}else{
		$("#productsSelect option[data-ptype='1']").attr("disabled","");
	}
	handleOrderInfo();
});