<?
	include("../db.php");
	$arrProject = include("../config.php");
	
	//print_r($_POST);

	$backstatus = $_POST['backstatus'];
	$id = $_POST['id'];
	$action = $_POST['action'];
	
	$dbMgr = new dbMgr();
	
	if($action == 'set'){
		$sql = "update tblGitReleaseTool_Task  set intUpdateStatus='".$backstatus."',dtUpdateDate=now() where iNo = '".$id."'";
		$dbMgr->getConn()->query($sql);
		echo "ok";
		die();
	}
	if($action == 'get'){
		$arrMap = array(
			'0' => array('code'=>-1,'msg'=>'该文件未更新'),
			'1' => array('code'=>1, 'msg'=>'更新成功'),
			'2' => array('code'=>-1,'msg'=>'更新出错'),
			'3' => array('code'=>1,'msg'=>'无需更新')
		);
		$sql = "select iNo,strFilePath,intUpdateStatus from tblGitReleaseTool_FileList where intTaskPK = '".$id."' order by iNo";
		$arrData = $dbMgr->getConn()->select($sql);
		$ret = array();
		for($i=0;$i<count($arrData);$i++){
			$ret[] = array(
				'code' => $arrMap[$arrData[$i]['intUpdateStatus']]['code'],
				'msg'  => $arrMap[$arrData[$i]['intUpdateStatus']]['msg'],
				'filePath' => $arrData[$i]['strFilePath']
			);
		}

		echo json_encode(array('code'=>1,'data'=>$ret));
		die();
	}

	
?>