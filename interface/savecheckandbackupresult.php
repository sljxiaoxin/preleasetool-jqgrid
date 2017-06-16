<?
	/*
	$arrStatus = array(
		"未备份","已备份"
	);
	*/
	include("../db.php");
	$arrProject = include("../config.php");
	
	//print_r($_POST);

	$backstatus = $_POST['backstatus'];
	$id = $_POST['id'];

	$dbMgr = new dbMgr();
	$sql = "update tblGitReleaseTool_Task  set intBakStatus='".$backstatus."',dtBakDate=now() where iNo = '".$id."'";
	$dbMgr->getConn()->query($sql);
	echo "ok";
	
?>