<?
	/*
	备份还原
	*/
	include("../db.php");
	$arrProject = include("../config.php");

	$fileINo = $_POST['fileINo'];    //tblGitReleaseTool_FileList表的iNo
	$filePath = trim($_POST['filePath']);
	$action = $_POST['action'];  //check或update
	$dbMgr = new dbMgr();
	$sql = "select TT.*,FL.strFilePath,FL.intBakStatus AS intFlBakStatus from tblGitReleaseTool_Task as TT inner join tblGitReleaseTool_FileList as FL";
	$sql .= " on FL.iNo = '".$fileINo."' and FL.intTaskPK = TT.iNo where 1";
	$arrData = $dbMgr->getConn()->select($sql);
	if(empty($arrData)){
		echo json_encode(array(
			'code' => '-1',
			'msg'=>'数据库未记载该记录',
			'filePath' => $filePath
		));
		die();
	}
	
	//路径过滤 M bussinessdiy/xxx  去掉M
	$filePathT = preg_replace('/(\s)+/i',";",$filePath);
	$debug = "@".$filePathT."@";
	$arrContent = explode(";", $filePathT);
	if(count($arrContent) ==1){
		$filePathT = $arrContent[0];
	}elseif(count($arrContent) >=2){
		$filePathT = $arrContent[1];
	}else{
		$filePathT = "";
	}
	if($filePathT == ""){
		echo json_encode(array(
			'code' => '-1',
			'msg'=>'错误的路径',
			'filePath' => $filePath,
			'debug' => $debug
		));
		die();
	}
	//根据任务id获取备份路径
	//$taskId = $arrData[0]['iNo'];
	//$backupPath = WORKPATH."/".$taskId."/bak/";
	$taskId = $arrData[0]['iNo'];
	$projectNo = $arrData[0]['intProjectNo'];
	$baseUrl = $arrProject[$projectNo]["NODEJSURL"];
	//$params = "prono=".$projectNo."&taskid=".$taskId."&path=".$filePathT;
	$params = array(
		'prono' => $projectNo,
		'taskid' => $taskId,
		'path' => $filePathT
	);



	if($action == 'check'){
		//1、判断备份文件是否存在
		//2、判断是否FileList表中状态是否正确
		if($arrData[0]['intBakStatus'] == '0'){
			echo json_encode(array(
				'code' => '-1',
				'msg'=>'该项任务备份状态错误',
				'filePath' => $filePath
			));
			die();
		}
		if($arrData[0]['intFlBakStatus'] == '0'){
			echo json_encode(array(
				'code' => '-1',
				'msg'=>'该文件备份状态错误',
				'filePath' => $filePath
			));
			die();
		}
		//如果不是无需备份的文件，需要检测目录和备份是否存在
		if($arrData[0]['intFlBakStatus'] == '1'){
			$url = $baseUrl."/checkfilebakexist";
			$arrResut = requireNodejs($url, $params);
			if($arrResut == null || empty($arrResut)){
				echo json_encode(array(
					'code' => '-1',
					'msg'=>'node执行备份文件检测失败',
					'filePath' => $filePath
				));
				die();
			}elseif($arrResut['code'] == '-1'){
				echo json_encode(array(
					'code' => '-1',
					'msg'=>$arrResut['msg'],
					'filePath' => $filePath
				));
				die();
			}
		}
		echo json_encode(array(
			'code' => '1',
			'msg'  => "检测通过",
			'filePath' => $filePath
		));
	}elseif($action == 'goback'){
		//执行恢复还原
		if($arrData[0]['intFlBakStatus'] == '2'){
			//新文件，无需备份的那种
			echo json_encode(array(
				'code' => '1',
				'msg'=>'生成环境以前无该文件，无需还原',
				'filePath' => $filePath
			));
			die();
		}
		//2、前面检测都通过，通过nodejs执行更新文件，因权限问题不能使用php，php是apache权限有些文件不能盖
		//nodejs内部会先检测备份是否存在
		$url = $baseUrl."/execgoback";
		$arrResut = requireNodejs($url, $params);
		if($arrResut == null || empty($arrResut)){
			echo json_encode(array(
				'code' => '-1',
				'msg'=>'node执行还原失败',
				'filePath' => $filePath
			));
			die();
		}elseif($arrResut['code'] == '-1'){
			echo json_encode(array(
				'code' => '-1',
				'msg'=>$arrResut['msg'],
				'filePath' => $filePath
			));
			die();
		}
		$sql = "update tblGitReleaseTool_FileList set intUpdateStatus=0 where iNo = '".$fileINo."'";
		$dbMgr->getConn()->query($sql);
		echo json_encode(array(
			'code' => $arrResut['code'],
			'msg'  => $arrResut['msg'],
			'filePath' => $filePath
		));
		die();

	}
	die();
	
?>