<?
	include("../db.php");
	$arrProject = include("../config.php");
	
	$dtStart = $_GET["dtStart"];
	$dtEnd = $_GET["dtEnd"];
	$rows = $_POST["rows"];   //每次请求多少行
	$page = $_POST["page"];   //第几页

	$arrRows = array();
	$start = ($page - 1)*$rows;
	
	$dbMgr = new dbMgr();
	$arrWhere = array();
	if($dtStart != ''){
		$arrWhere[] = " dtDate >= '".$dtStart." 00:00:00' ";
	}
	if($dtEnd != ''){
		$arrWhere[] = " dtDate <= '".$dtEnd." 23:59:59' ";
	}
	$arrResult = $dbMgr->getTaskList($arrWhere, "", $start, $rows);
	$intTotal = $dbMgr->getTaskCount($arrWhere);
	for($i=0;$i<count($arrResult);$i++){
		$arrFl = $dbMgr->getFileList($arrResult[$i]['iNo']);
		$arrFileList = array();
		$arrFileListINos = array();
		for($j=0;$j<count($arrFl);$j++){
			$arrFileList[] = $arrFl[$j]['strFilePath'];
			$arrFileListINos[] = $arrFl[$j]['iNo'];
		}
		$arrRows[] = array(
			"id" => $arrResult[$i]['iNo'],
			"cell"=>array(
					$arrResult[$i]['iNo'], //id
					$arrResult[$i]['intUpdateStatus']."@".$arrResult[$i]['intBakStatus'],  //操作
					$arrProject[$arrResult[$i]['intProjectNo']]['name'],  //项目
					$arrResult[$i]['strName'],   //名称
					$arrResult[$i]['dtDate'],
					$arrResult[$i]['dtUpdateDate'],
					$arrResult[$i]['dtBakDate'],    //备份日期
					$arrResult[$i]['strUser'],      //负责人
					implode("\n",$arrFileList),
					implode(",",$arrFileListINos),
					$arrResult[$i]['intUpdateStatus'],
					$arrResult[$i]['intUpdateStatus'],
					$arrResult[$i]['intBakStatus'],
					$arrResult[$i]['intBakStatus']
			)
		);
	}
	$data = array(
		"total" => ceil($intTotal/$rows),
		"page" =>$page,
		"records" => $intTotal,
		"rows" => $arrRows
	);
	echo json_encode($data);
?>