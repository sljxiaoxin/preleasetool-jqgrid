<?

	//环境配置等检测和初始化
	include("db.php");
	$arrProject = include("config.php");
	
	$dbMgr = new dbMgr();
	///////////////////////////////////////
	$strProject = array();
	foreach($arrProject as $key =>$val){
		$strProject[] = $key.":".$val["name"];
	}
	
	$strListUrl = "interface/getlist.php";
	if($_GET["dtStart"] != "" || $_GET["dtEnd"] != ""){
		$strListUrl = "interface/getlist.php?dtStart=".$_GET["dtStart"]."&dtEnd=".$_GET["dtEnd"];
	}
?>
<!DOCTYPE html>
<html lang="cn">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<!-- jqGrid组件基础样式包-必要 -->
		<link rel="stylesheet" href="css/ui.jqgrid.css" />
		
		<!-- jqGrid主题包-非必要 --> 
		<!-- 在jqgrid/css/css这个目录下还有其他的主题包，可以尝试更换看效果 -->
		<link rel="stylesheet" href="css/jquery-ui.min.css" /> 

		<!-- jquery插件包-必要 -->
		<!-- 这个是所有jquery插件的基础，首先第一个引入 -->
		<script type="text/javascript" src="js/jquery-1.7.1.js"></script>
		<script type="text/javascript" src="js/jquery-ui.min.js"></script>
		
		<!-- jqGrid插件包-必要 -->
		<script type="text/javascript" src="js/jquery.jqGrid.src.js"></script>
		
		<!-- jqGrid插件的多语言包-非必要 -->
		<!-- 在jqgrid/js/i18n下还有其他的多语言包，可以尝试更换看效果 -->
		<script type="text/javascript" src="js/grid.locale-cn.js"></script>

		<script type="text/javascript" src="js/scrollable.js"></script>
		<title>GIT 程序自动部署工具</title>
		
		<!-- 本页面初始化用到的js包，创建jqGrid的代码就在里面 -->
		<!-- <script type="text/javascript" src="index.js"></script> -->
		<style>
		/*用于jqgrid展示textarea类型，里面有换行信息的处理，避免撑开过高*/
		tr.jqgrow>td.textInDiv>div {
			max-height: 40px;
			overflow-x: hidden;
			overflow-y: hidden;
			padding:5px 1px;
		}
		/*用于dialog中的引导页*/
		#wizard {
			border:5px solid #789;
			font-size:12px;
			height:600px;
			margin:1px auto;
			width:770px;
			overflow:hidden;
			position:relative;
			-moz-border-radius:5px;
			-webkit-border-radius:5px;
		}
		#wizard .items{width:20000px; clear:both; position:absolute;}
		#wizard .right{float:right;}
		#wizard #status{height:35px;background:#123;padding-left:25px !important;}
		#status li{float:left;color:#fff;padding:10px 30px;}
		#status li.active{background-color:#369;font-weight:normal;}
		.input{width:240px; height:18px; margin:10px auto; line-height:20px; border:1px solid #d3d3d3; padding:2px}
		.page{padding:20px 30px;width:700px;float:left;}
		.page h3{height:42px; font-size:16px; border-bottom:1px dotted #ccc; margin-bottom:20px; padding-bottom:5px}
		.page h3 em{font-size:12px; font-weight:500; font-style:normal}
		.page p{line-height:24px;}
		.page p label{font-size:14px; display:block;}
		.btn_nav{height:36px; line-height:36px; margin:20px auto;}
		.prev,.next{width:100px; height:32px; line-height:32px; background:url(btn_bg.gif) repeat-x bottom; border:1px solid #d3d3d3; cursor:pointer}

		@charset "utf-8";
		/* CSS Document */
		html,body,div,span,h1,h2,h3,h4,h5,h6,p,pre,a,code,em,img,small,strong,sub,sup,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label{margin:0;padding:0;border:0;outline:0;font-size:100%;vertical-align:baseline;background:transparent}
		a{color:#007bc4/*#424242*/; text-decoration:none;outline: none;}
		a:hover{text-decoration:underline}
		a:focus {outline:none; -moz-outline:none;}
		ol,ul{list-style:none}
		table{border-collapse:collapse;border-spacing:0}
		body{
			height:100%; 
			font:14px/18px "Microsoft Yahei", Tahoma, Helvetica, Arial, Verdana, "\5b8b\4f53", sans-serif; 
			color:#51555C; 
		}
		img{border:none}


		#main{
			width:980px;
			min-height:600px;
			margin:5px auto 0 auto;
			border:1px solid #d3d3d3;
			background:#fff; 
			-moz-border-radius:5px;
			-khtml-border-radius: 5px;
			-webkit-border-radius: 5px; 
			border-radius:5px;
		}
		h2.top_title{
			margin:4px 20px; 
			padding-top:5px; 
			padding-left:20px; 
			padding-bottom:10px; 
			border-bottom:1px solid #d3d3d3; 
			font-size:18px; 
			color:#a84c10;
		}
		
		</style>
	</head>
	<body>
	<div id='container' style='width:100%;'>
		<div style='width:1100px;margin:15px auto;'>
			起始年月：<input type="text" id="dtpicker_start" style='width:100px' value='<?=$_GET["dtStart"]?>'>
		~ 截止年月：<input type="text" id="dtpicker_end" style='width:100px' value='<?=$_GET["dtEnd"]?>'>
			<input type="submit" id="btnSelect" value="查询" />
			<input type="button" id="btnAdd" value="新增" /><br>
			<table id="list2"></table> 
			<div id="pager2"></div>
		</div>
		<div id="dialog" title="Basic dialog">
			<div id="wizard">
				<ul id="status">
					<li class="active"><strong>1.</strong><span id='scroll_activeHolderOne'>核对</span></li>
					<li><strong>2.</strong><span id='scroll_activeHolderTwo'>备份</span></li>
				</ul>

				<div class="items">
					<div class="page">
					   <h3 id='scroll_h3One'>核对<br/><em>请仔细再次确认更新列表文件信息</em></h3>
					   <div id='info-check' readonly style="border:1px solid LightGray;width:100%;height:400px;white-space:nowrap;overflow:scroll;">
					   
					   </div >
					   <div class="btn_nav">
						  <span id='info-check-tip'></span>
						  <input type="button" class="next right" value="下一步&raquo;" />
					   </div>
					</div>
					<div class="page">
					   <h3 id='scroll_h3Two'>检测和备份<br/><em id='info-tip' style=''>进度：<div id="progressbar"></div></em></h3>
					   <div id='info-do' readonly style="border:1px solid LightGray;width:100%;height:400px;white-space:nowrap;overflow:scroll;">
					   </div >
					   <div class="btn_nav">
						  <span id='info-do-tip'></span>
					   </div>
					</div>
				</div>
			</div>
		</div>
	</div>
		<script>
		///*
		if(typeof console == 'undefined'){
			console = {
				log : function(){
					
				}
			};
		}
		//*/
		var scrollHelper = {
			//
			isRunning : false,   
			activeIndex : null,  //0:备份；1：更新；2：恢复 三种类型操作
			listContent : null,
			listINos    : null,
			rowData     : null,
			ajaxBackResult : null,   //数组，存放每次执行返回结果
			isDialogCanClose : function(){
				//TODO 判断当前是否处于工作状体，不能随意关闭窗口
				return !this.isRunning;
			},
			doTask : function(url, index, obj){
				var self = this;
				$.post(url,obj,function(data,status){
					try{
						var ret = $.parseJSON(data);
						self.ajaxBackResult[index] = ret;
					}catch(e){
						self.ajaxBackResult[index] = {code:-1,msg:'未知错误',filePath:obj.filePath};
					}
				}).error(function(){
					self.ajaxBackResult[index] = {code:-1,msg:'未知错误',filePath:obj.filePath};
				});
			},
			setActive : function(index){
				this.activeIndex = index;
			},
			setRowDatas : function(rowDatas){
				this.rowData = rowDatas;
				var lc = rowDatas["list"].replace(/<div>|<\/div>/gi,'');
				lc = lc.replace(/\n/gi,',');
				this.listContent = lc.split(",");
				this.listINos = rowDatas["listINos"].split(',');
				console.log("setRowDatas rowDatas:", rowDatas);
			},
			init : function(){
				this.viewInit();   //三种view的初始化
			},
			reset : function(){
				this.rowData = null;
				$("#info-check").html("");
				$("#info-do").html("");
				$("#info-do-tip").html("");
				$("#info-check-tip").html("");
				$(".next.right").attr("disabled",false);
			},
			appendTextarea : function(id,obj){
				var color = "blue";
				var type = "";
				if(obj.code <0){
					color = "red";
					type = "error";//isError = true;
				}else if(obj.code == 0){
					color = "Orange";
					type = "warn";//isWarn = true;
				}
				$("#"+id).append("<div style='color:"+color+"'>"+obj.msg+"&nbsp;&nbsp;"+obj.filePath+"</div>");
				return type;
			},
			viewInit : function(){
				if(this.activeIndex == 0){
					this.view0Init();
				}else if(this.activeIndex == 1){
					this.view1Init();
				}else if(this.activeIndex == 2){
					this.view2Init();
				}
			},
			view0Init : function(){
				var title = "【备份】"+this.rowData['project']+"-"+this.rowData['title'];
				$("#dialog").dialog("option",{title:title})
				$("#scroll_activeHolderOne").html("核对");
				$("#scroll_activeHolderTwo").html("备份");
				$("#scroll_h3One").html("核对<br/><em>请仔细再次确认更新列表文件信息</em>");
				$("#scroll_h3Two").html("备份<br/><em id='info-tip' style=''>进度：<div id='progressbar'></div></em>");
				$("#info-check").html(this.listContent.join("<br/>"));
			},
			view1Init : function(){
				var title = "【更新】"+this.rowData['project']+"-"+this.rowData['title'];
				$("#dialog").dialog("option",{title:title})
				$("#scroll_activeHolderOne").html("更新前核对");
				$("#scroll_activeHolderTwo").html("更新");
				$("#scroll_h3One").html("更新前核对<br/><em>请仔细再次确认更新列表文件信息</em>");
				$("#scroll_h3Two").html("更新<br/><em id='info-tip' style=''>进度：<div id='progressbar'></div></em>");
				$("#info-check").html(this.listContent.join("<br/>"));
			},
			view2Init : function(){
				var title = "【查看|恢复】"+this.rowData['project']+"-"+this.rowData['title'];
				$("#dialog").dialog("option",{title:title})
				$("#scroll_activeHolderOne").html("上次更新结果");
				$("#scroll_activeHolderTwo").html("恢复");
				$("#scroll_h3One").html("上次更新结果<br/><em>注意：全部错误的情况下无需恢复！</em>");
				$("#scroll_h3Two").html("恢复<br/><em id='info-tip' style=''>进度：<div id='progressbar'></div></em>");
				$(".next.right").attr("disabled",true);
				var self = this;
				$.post("interface/updateresult.php",{
						action : 'get',
						id:this.rowData['id']
				},function(data,status){
					//TODO
					console.log($.parseJSON(data));
					try{
						var ret = $.parseJSON(data);
						var arrRet = ret.data;
						for(var i=0;i<arrRet.length;i++){
							self.appendTextarea('info-check', arrRet[i]);
						}
					}catch(e){
						$("#info-check-tip").html("<span style='color:red'>获取更新结果列表失败，请检测网络或重试！</span>");
					}
					$(".next.right").attr("disabled",false);
					
				}).error(function(){
					$("#info-check-tip").html("<span style='color:red'>获取更新结果列表失败，请检测网络或重试！</span>");
				});						

			},
			btnClick : function(index){
				if(index == 0){
					this.reset();
					return true;
				}else if(index == 1){
					if(!window.confirm("确认执行下一步？")){
						return false;
					}
					if(this.activeIndex == 0){
						this.doBakup();
					}else if(this.activeIndex == 1){
						this.doUpdate('check');
					}else if(this.activeIndex == 2){
						this.doGoback('check');
					}
					return true;
				}
			},
			doBakup  : function(){
				this.isRunning = true;
				//执行备份动作
				this.ajaxBackResult = [];
				for(var i=0;i<this.listINos.length;i++){
					//逐条检测加备份
					this.doTask("interface/checkandbackup.php", i, {
						fileINo : this.listINos[i],
						filePath : this.listContent[i]
					});
				}
				this.bakupTimer();
			},
			bakupTimer : function(){
				var isError = false;
				var isWarn = false;
				var i = 0;
				var self = this;
				var timer = setInterval(function(){
					if(typeof self.ajaxBackResult[i] != 'undefined'){
						//插入一行到textarea
						var strResult = self.appendTextarea('info-do', self.ajaxBackResult[i]);
						if(strResult == "error"){
							isError = true;
						}
						if(strResult == "warn"){
							isWarn = true;
						}
						i = i+1;
						$( "#progressbar" ).progressbar({
						  value: i/self.listINos.length*100
						});
					}
					if(self.ajaxBackResult.length == self.listINos.length && i==(self.listINos.length)){
						//完成了，清除timer
						window.clearInterval(timer);
						$("#info-tip").html("<font color='blue'>正在保存结果，请不要关闭或刷新浏览器。。。</font>");
						var backstatus = 1;
						if(isError){
							backstatus = 0;
						 }
						 if(backstatus == 0){
							$("#info-tip").html("<font color='blue'>完成！</font>");
							self.isRunning = false;
						 }else{
							 // 保存备份结果
							 $.post("interface/savecheckandbackupresult.php",{
									backstatus:backstatus,
									id:self.rowData['id']
							 },function(data,status){
								self.isRunning = false;
								$("#info-tip").html("<font color='blue'>完成！</font>");
							 });							
						 }
						 if(isError){
							$("#info-do-tip").html("<span style='color:red'>发生错误，请核对后重做！</span>");
						 }else if(isWarn){
							$("#info-do-tip").html("<span style='color:Orange'>请检查橙色警告信息，无误可以进行更新了！</span>");
						 }else{
							$("#info-do-tip").html("<span style='color:blue'>检查和备份通过，可以进行更新了！</span>");
						 }
					}
				}, 200);
			},
			doUpdate : function(type){
				this.isRunning = true;
				this.ajaxBackResult = [];
				for(var i=0;i<this.listINos.length;i++){
					this.doTask("interface/execupdate.php", i, {
						action : type,
						fileINo : this.listINos[i],
						filePath : this.listContent[i]
					});
				}
				this.updateTimer(type);
			},
			updateTimer : function(type){
				var isError = false;
				var isWarn = false;
				var i = 0;
				var self = this;
				var timer = setInterval(function(){
					if(typeof self.ajaxBackResult[i] != 'undefined'){
						//插入结果提醒到div
						var strResult = self.appendTextarea('info-do', self.ajaxBackResult[i]);
						if(strResult == "error"){
							isError = true;
						}
						if(strResult == "warn"){
							isWarn = true;
						}
						i = i+1;
						$( "#progressbar" ).progressbar({
						  value: i/self.listINos.length*100
						});
					}
					if(self.ajaxBackResult.length == self.listINos.length && i==(self.listINos.length)){
						//完成了，清除timer
						window.clearInterval(timer);
						$("#info-tip").html("<font color='blue'>正在处理中，请不要关闭或刷新浏览器。。。</font>");
						if(type == 'check'){
							//执行完备份检测，走到这个入口
							if(isError){
								$("#info-tip").html("<font color='blue'>完成！</font>");
								$("#info-do-tip").html("<span style='color:red'>更新检测发生错误，请核对后重做！</span>");
								self.isRunning = false;
							}else{
								$("#info-do").append("<div style='color:black'>---------------------------------------------开始执行更新---------------------------------------------</div>");
								self.doUpdate('update');
							}
						}
						if(type == 'update'){
							//执行完更新动作进到这里
							var backstatus = '0';
							if(isError){
								backstatus = '2';    //更新出错
							}else{
								backstatus = '1'; 
							}
							//TODO 记录更新结果
							 $.post("interface/updateresult.php",{
									action : 'set',
									backstatus: backstatus,
									id: self.rowData['id']
							 },function(data,status){
								$("#info-tip").html("<font color='blue'>完成！</font>");
								if(isError){
									$("#info-do-tip").html("<span style='color:red'>更新时发生错误，请尽快处理！</span>");
								}else{
									$("#info-do-tip").html("<span style='color:blue'>更新成功！</span>");
								}
								self.isRunning = false;
							 });							
						}
					}
				}, 200);
			},
			//恢复还原
			doGoback : function(type){
				this.isRunning = true;
				this.ajaxBackResult = [];
				for(var i=0;i<this.listINos.length;i++){
					this.doTask("interface/execgoback.php", i, {
						action : type,
						fileINo : this.listINos[i],
						filePath : this.listContent[i]
					});
				}
				this.gobackTimer(type);
			},
			gobackTimer : function(type){
				var isError = false;
				var isWarn = false;
				var i = 0;
				var self = this;
				var timer = setInterval(function(){
					if(typeof self.ajaxBackResult[i] != 'undefined'){
						//插入结果提醒到div
						var strResult = self.appendTextarea('info-do', self.ajaxBackResult[i]);
						if(strResult == "error"){
							isError = true;
						}
						if(strResult == "warn"){
							isWarn = true;
						}
						i = i+1;
						$( "#progressbar" ).progressbar({
						  value: i/self.listINos.length*100
						});
					}
					if(self.ajaxBackResult.length == self.listINos.length && i==(self.listINos.length)){
						//完成了，清除timer
						window.clearInterval(timer);
						$("#info-tip").html("<font color='blue'>正在处理中，请不要关闭或刷新浏览器。。。</font>");
						if(type == 'check'){
							//执行完备份检测，走到这个入口
							if(isError){
								$("#info-tip").html("<font color='blue'>完成！</font>");
								$("#info-do-tip").html("<span style='color:red'>恢复检测发生错误，请核对后重做！</span>");
								self.isRunning = false;
							}else{
								$("#info-do").append("<div style='color:black'>---------------------------------------------开始执行恢复---------------------------------------------</div>");
								self.doGoback('goback');
							}
						}
						if(type == 'goback'){
							
							if(isError){
								$("#info-tip").html("<font color='blue'>完成！</font>");
								$("#info-do-tip").html("<span style='color:red'>还原失败，请检查或重做！</span>");
								self.isRunning = false;
							}else{
								//记录还原结果
								 $.post("interface/updateresult.php",{
										action : 'set',
										backstatus: 3,
										id: self.rowData['id']
								 },function(data,status){
									$("#info-tip").html("<font color='blue'>完成！</font>");
									$("#info-do-tip").html("<span style='color:blue'>还原成功！</span>");
									self.isRunning = false;
								 });
							}
							
						}
					}
				}, 200);
			}
			
		};

		$(function(){
			$( "#dtpicker_start" ).datepicker({ dateFormat: "yy-mm-dd"});
			$( "#dtpicker_end" ).datepicker({ dateFormat: "yy-mm-dd"});
			$("#btnSelect").button().click(function(){
				location.href = "index.php?dtStart="+$("#dtpicker_start").val()+"&dtEnd="+$("#dtpicker_end").val();
			});
			//页面加载完成之后执行
			pageInit();
			$( "#dialog" ).dialog({
			  autoOpen: false,
			  width : '800px',
		      modal: true,
			  beforeClose : function(event, ui ){
				  return scrollHelper.isDialogCanClose();
			  },
			  close: function( event, ui ) {
				  $("#wizard").scrollable().begin();
				  $("#list2").trigger("reloadGrid");
			  },
			  open: function( event, ui ) {
				 scrollHelper.init();
			  }
			});
			$("#wizard").scrollable({
				keyboard: false,
				//initialIndex : 0,
				onSeek: function(event,i){
					$("#status li").removeClass("active").eq(i).addClass("active");
				},
				onBeforeSeek:function(event,i){
					return scrollHelper.btnClick(i);
				}
			});
		});

		function addCellAttr(rowId, val, rawObject, cm, rdata) {  
			return "style='overflow: visible;'";
		}
		function actionDo(type,rowid){
			//alert(type+"#"+rowid);
			var rowDatas = $("#list2").jqGrid('getRowData', rowid);
			//console.log("rowDatas",rowDatas);
			if(type == 'delete'){
				if(window.confirm("确定删除吗？")){
					
				}
			}else if(type == 'edit'){
				//点击修改按钮
				jQuery("#list2").jqGrid('editGridRow', rowid, {
				  width:750,
				  height : 470,
				  beforeShowForm:function(frm){
					//console.log("frm:",frm.find('#project'));
					//弹出窗口显示前事件
					var project = frm.find('#project');
					var txt = project.find("option:selected").text();
					project.hide();
					project.after("<span>"+txt+"</span>");
				  },
				  afterComplete:function(xhr){
					alert(xhr.responseText);
					//location.href = location.href;
				  },
				  closeAfterEdit: true,     //成功后关闭此窗口
				  reloadAfterSubmit : true,
				  viewPagerButtons:false
				});
			}else if(type == 'bak'){
				//点击备份按钮
				scrollHelper.setActive(0);
				scrollHelper.setRowDatas(rowDatas);
				$( "#dialog" ).dialog( "open" );
			}else if(type == 'update'){
				//点击更新按钮
				scrollHelper.setActive(1);
				scrollHelper.setRowDatas(rowDatas);
				$( "#dialog" ).dialog( "open" );
			}else if(type == 'goback'){
				//点击恢复
				scrollHelper.setActive(2);
				scrollHelper.setRowDatas(rowDatas);
				$( "#dialog" ).dialog( "open" );
			}
		}
		function formatOptions(cellValue, options, rawObject){
			console.log("cellValue",cellValue);
			console.log("options",options);
			var arrStatus = cellValue.split("@");
			var intStatus_Update = arrStatus[0];
			var intStatus_Bak = arrStatus[1];
			var arrButtons = [];
			if(intStatus_Update == '0' || intStatus_Update == '3'){
				arrButtons.push('<input type="button" value="修改" onclick="actionDo(\'edit\',\'' + options.rowId + '\')">');
			}
			if(intStatus_Update == '0' && intStatus_Bak == '0'){
				arrButtons.push('<input type="button" value="备份" onclick="actionDo(\'bak\',\'' + options.rowId + '\')">');
			}
			if((intStatus_Update == '0' || intStatus_Update == '2' || intStatus_Update == '3') && intStatus_Bak == '1'){
				arrButtons.push('<input type="button" value="更新" onclick="actionDo(\'update\',\'' + options.rowId + '\')">');
			}
			if((intStatus_Update == '1' || intStatus_Update == '2') && intStatus_Bak == '1'){
				arrButtons.push('<input type="button" value="查看 | 恢复" onclick="actionDo(\'goback\',\'' + options.rowId + '\')">');
			}
			var detail = arrButtons.join("&nbsp;&nbsp;");
			return detail;
			
		}
	
		
		function pageInit(){
			//创建jqGrid组件
			jQuery("#list2").jqGrid(
					{
						//url : 'data/JSONData.json',//组件创建完成之后请求数据的url
						url : '<?=$strListUrl?>',//组件创建完成之后请求数据的url
						datatype : "json",//请求数据返回的类型。可选json,xml,txt
						colNames : [ 'id','操作',
									'所属项目','功能名称', 
									'填写日期','更新日期', 
									'备份日期','负责人',
									'更新列表', '更新列表iNos',
									'更新状态', '更新状态原始值',
									'备份状态', '备份状态原始值'],//jqGrid的列显示名字
						colModel : [ //jqGrid每一列的配置信息。包括名字，索引，宽度,对齐方式.....
									 {name : 'id',index : 'id',width : 60,height:25,editable : false,hidden:true},

									 {name: 'operations',index: 'operations',width: 170, sortable: false,align:'center',cellattr: addCellAttr,formatter:formatOptions},
									 
									 {name : 'project',index : 'project',width : 100,editable : true,edittype : "select",editoptions :	{value : "<?=implode(';', $strProject)?>"},align:'center'},
									 
									 {name : 'title',index : 'title',width : 200,editable : true,editoptions : {size : 50},align:'center'},
									 
									 
									 {name : 'date',index : 'date',width : 150,editable : false},

									 {name : 'dtUpdate',index : 'dtUpdate',width : 150,editable : false},

									 {name : 'dtBak',index : 'dtBak',width : 150,editable : false},
									 
									 {name : 'user',index : 'user',width : 80,editable : true,editoptions : {size : 10}},
									 
									 {name : 'list',index : 'list',width : 150,height:25,hidden:false,editable : true,edittype : "textarea",classes: "textInDiv",formatter: function (v) {return '<div>' + v + '</div>';},editoptions : {dataInit : function (elem) {
										 //因为列表页直接显示文件列表会把grid撑高，所以在formatter里增加了div通过样式控制高度，点击修改的时候需要把div去掉
										 var val = $(elem).val();
										 val = val.replace(/<div>|<\/div>/gi,'');
										 $(elem).val(val);
									 },rows : "15",cols : "80"}},
									 {name : 'listINos',index : 'listINos',width : 60,height:25,editable : false,hidden:true},
									 {name : 'status',index : 'status',width : 100,formatter:function(cellValue, options, rawObject){
											var arrStatus = ["未开始","<font color='blue'>更新成功</font>","<font color='red'>更新出错</font>","已恢复"];
											return arrStatus[cellValue];
										},align:'center'},
									 
									 {name : 'statusOrg',index : 'statusOrg',hidden:true},
									 
									 {name : 'backstatus',index : 'backstatus',width : 80,formatter:function(v){
										var arr = ['未备份','<font color="blue">已备份</font>'];
										return arr[v];
									 },align:'center'},
									 
									 {name : 'backstatusOrg',index : 'backstatusOrg',hidden:true},
									 
								   ],
						rowNum : 10,//一页显示多少条
						rowList : [ 10, 20, 30 ],//可供用户选择一页显示多少条
						pager : '#pager2',//表格页脚的占位符(一般是div)的id
						sortname : 'id',//初始化的时候排序的字段
						sortorder : "desc",//排序方式,可选desc,asc
						mtype : "post",//向后台请求数据的ajax的类型。可选post,get
						viewrecords : true,
						editurl : "interface/add.php",
						width : 1100,
						height : 500,
						caption : "记录表",//表格的标题名字
						loadComplete : function(data){
							
						}
					});
			/*创建jqGrid的操作按钮容器*/
			/*可以控制界面上增删改查的按钮是否显示*/
			//jQuery("#list2").jqGrid('navGrid', '#pager2', {edit : false,add : true,del : false});
			 $("#btnAdd").button().click(function() {
				jQuery("#list2").jqGrid('editGridRow', "new", {
				  width:750,
				  height : 470,
				  beforeShowForm:function(frm){
					//console.log("frm:",frm.find('#project'));
				  },
				  afterComplete:function(xhr){
					alert(xhr.responseText);
					//location.href = location.href;
				  },
				  //成功后关闭此窗口
				  closeAfterAdd: true,
				  reloadAfterSubmit : true
				});
			 });

		}
		</script>
	</body>
</html>