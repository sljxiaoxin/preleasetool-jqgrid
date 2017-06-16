<?
	/*
	'-1' : 错误
	0    ：警告
	1    ：成功
	*/
	include("../db.php");
	$arrProject = include("../config.php");

	$fileINo = $_POST['fileINo'];    //tblGitReleaseTool_FileList表的iNo
	$filePath = trim($_POST['filePath']);
	$dbMgr = new dbMgr();

	//路径字符串杂波过滤
	$filePathT = preg_replace('/(\s)+/i',";",$filePath);
	$arrContent = explode(";", $filePathT);
	$fileType = "";
	if(count($arrContent) ==1){
		$filePathT = $arrContent[0];
	}elseif(count($arrContent) >=2){
		$fileType = strtolower(trim($arrContent[0]));
		$filePathT = $arrContent[1];
	}else{
		$filePathT = "";
	}
	if($filePathT == ""){
		echo json_encode(array(
			'code' => '-1',
			'msg'=>'错误的路径',
			'filePath' => $filePath
		));
		die();
	}


	
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
	if($arrData[0]['intBakStatus'] != '0'){
		echo json_encode(array(
			'code' => '-1',
			'msg'=>'该文件备份状态错误',
			'filePath' => $filePath
		));
		die();
	}
	$taskId = $arrData[0]['iNo'];
	$projectNo = $arrData[0]['intProjectNo'];
	$baseUrl = $arrProject[$projectNo]["NODEJSURL"];
	//$params = "prono=".$projectNo."&taskid=".$taskId."&path=".$filePathT;
	$params = array(
		'prono' => $projectNo,
		'taskid' => $taskId,
		'path' => $filePathT
	);
	//1、备份目录检测是否存在，不存在则创建
	$url = $baseUrl."/checkbakpath";
	$arrResut = requireNodejs($url, $params);
	if($arrResut == null || empty($arrResut) || $arrResut['code'] == '-1'){
		echo json_encode(array(
			'code' => '-1',
			'msg'=>'node执行备份目录检测失败',
			'filePath' => $filePath
		));
		die();
	}
	//2、来源文件是否存在检测
	if($fileType != 'd'){
		$url = $baseUrl."/checkfilefromexist";   
		$arrResut = requireNodejs($url, $params);
		if($arrResut == null || empty($arrResut) || $arrResut['code'] == '-1'){
			echo json_encode(array(
				'code' => '-1',
				'msg'=>'node来源文件检测失败',
				'filePath' => $filePath
			));
			die();
		}
	}
	//3、生产环境文件检测
	$url = $baseUrl."/checkfiletoexist";   
	$arrResut = requireNodejs($url, $params);
	if($arrResut == null || empty($arrResut)){
		echo json_encode(array(
			'code' => '-1',
			'msg'=>'node目的文件检测失败',
			'filePath' => $filePath
		));
		die();
	}elseif($arrResut['code'] == '-1'){
		$sql = "update tblGitReleaseTool_FileList set intBakStatus=2 where iNo = '".$fileINo."'";
		$dbMgr->getConn()->query($sql);
		echo json_encode(array(
			'code' => '0',
			'msg'=>'生产环境不存在该文件',
			'filePath' => $filePath,
			'debug' => $fileInfo
		));
		die();
	}
	//4、执行备份
	$url = $baseUrl."/execbakup";   
	$arrResut = requireNodejs($url, $params);
	if($arrResut == null || empty($arrResut)){
		echo json_encode(array(
			'code' => '-1',
			'msg'=>'node执行备份失败',
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
	$sql = "update tblGitReleaseTool_FileList set intBakStatus=1 where iNo = '".$fileINo."'";
	$dbMgr->getConn()->query($sql);
	echo json_encode(array(
		'code' => '1',
		'msg'=>'备份成功',
		'filePath' => $filePath,
		'debug' => $fileInfo
	));
?>
