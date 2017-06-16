<?
	//print_r($_POST);
	include("../db.php");
	$arrProject = include("../config.php");
	$project = $_POST["project"];
	$title = trim($_POST["title"]);
	$list = trim($_POST["list"]);
	$user = trim($_POST["user"]);
	$oper = $_POST["oper"];
	$id = $_POST["id"];
	
	if($project == "" || $title == "" || $list == "" || $user==""){
		die("信息输入不完整，新增失败！");
	}
	
	$dbMgr = new dbMgr();

	if($oper == 'edit'){
		//先修改task表
		$arrDataTask = array(
			'intProjectNo' => $project,
			'strName' => $title,
			'strUser' => $user
		);
		$dbMgr->updateTask($id, $arrDataTask);
		//删除原有filelist表
		$dbMgr->deleteFileList($id);
		//添加新的filelist表
		$strList = preg_replace('/((\s)*(\n)+(\s)*)/i',",",$list);
		$arrList = explode(",", $strList);
		$arrDataFile = array();
		for($i=0;$i<count($arrList);$i++){
			$strPath = trim($arrList[$i]);
			if($strPath == '')continue;
			$arrDataFile[] = array(
				'intTaskPK' => $id,
				'strFilePath' => $strPath
			);
		}
		$dbMgr->addFileList($arrDataFile);

	}else{
		//先添加task表
		$arrDataTask = array(
			'intProjectNo' => $project,
			'strName' => $title,
			'dtDate' => date("Y-m-d H:i:s"),
			'strUser' => $user
		);
		$iNo = $dbMgr->addTask($arrDataTask);
		//再添加list表
		$strList = preg_replace('/((\s)*(\n)+(\s)*)/i',",",$list);
		$arrList = explode(",", $strList);
		$arrDataFile = array();
		for($i=0;$i<count($arrList);$i++){
			$strPath = trim($arrList[$i]);
			if($strPath == '')continue;
			$arrDataFile[] = array(
				'intTaskPK' => $iNo,
				'strFilePath' => $strPath
			);
		}
		$dbMgr->addFileList($arrDataFile);
		
	}
	$sql = "update tblGitReleaseTool_Task  set intUpdateStatus=0,intBakStatus='0' where iNo = '".$id."'";
	$dbMgr->getConn()->query($sql);
	echo "保存成功！";
?>