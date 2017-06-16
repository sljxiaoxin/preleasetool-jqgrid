<?
class dbMgr{
	
	private $conn = NULL;
	public function __construct()
	{
		//TODO 连接数据库动作在此补充
		$this->conn = new xxx(xxx);
	}

	public function getConn(){
		return $this->conn;
	}
	
	public function getTaskCount($arrWhere = array()){
		$sql = "select iNo from tblGitReleaseTool_Task where 1 ";
		if(!empty($arrWhere)){
			for($i=0;$i<count($arrWhere);$i++){
				$sql .= " and ".$arrWhere[$i];
			}
		}
		$data = $this->conn->select($sql);
		return count($data);
	}

	public function getTaskList($arrWhere = array(), $order = "", $start="", $limit =""){
		$sql = "select * from tblGitReleaseTool_Task where 1 ";
		if(!empty($arrWhere)){
			for($i=0;$i<count($arrWhere);$i++){
				$sql .= " and ".$arrWhere[$i];
			}
		}
		if($order != ''){
			$sql .= " order by ".$order;
		}else{
			$sql .= " order by iNo desc";
		}
		if($start != ""){
			$sql .= " limit ".$start.",".$limit;
		}
		//echo $sql;
		return $this->conn->select($sql);
	}


	public function addTask($data = array()){
		$arr = $this->insertFormat($data);
		//print_r($arr);
		$sql  = "insert into tblGitReleaseTool_Task(`".implode("`,`", $arr['field'])."`) values ";
		$arrSql = array();
		for($i=0;$i<count($arr['values']);$i++){
			$arrSql[] = "('".implode("','", $arr['values'][$i])."')";
		}
		$sql .= implode(",", $arrSql);
		//echo $sql;
		return $this->conn->insert($sql);
	}

	public function updateTask($pk, $data = array()){
		$sql = "update tblGitReleaseTool_Task set ";
		$arrSql = array();
		foreach($data as $k=>$v){
			$arrSql[] = $k." = '".$v."'";
		}
		$sql .= implode(", ", $arrSql);
		$sql .= "where iNo = '".$pk."'";
		return $this->conn->update($sql);
	}

	public function addFileList($data = array()){
		$arr = $this->insertFormat($data);
		//print_r($arr);
		$sql  = "insert into tblGitReleaseTool_FileList(`".implode("`,`", $arr['field'])."`) values ";
		$arrSql = array();
		for($i=0;$i<count($arr['values']);$i++){
			$arrSql[] = "('".implode("','", $arr['values'][$i])."')";
		}
		$sql .= implode(",", $arrSql);
		//echo $sql;
		return $this->conn->insert($sql);
	}

	public function updateFileList($pk, $data = array()){
		$sql = "update tblGitReleaseTool_FileList set ";
		$arrSql = array();
		foreach($data as $k=>$v){
			$arrSql[] = $k." = '".$v."'";
		}
		$sql .= implode(", ", $arrSql);
		$sql .= "where iNo = '".$pk."'";
		return $this->conn->update($sql);
	}

	public function getFileList($PK){
		$sql = "select * from tblGitReleaseTool_FileList where intTaskPK = '".$PK."' order by iNo ";
		//echo $sql;
		return $this->conn->select($sql);
	}

	public function deleteFileList($PK){
		$sql = "delete from tblGitReleaseTool_FileList where intTaskPK = '".$PK."' ";
		//echo $sql;
		return $this->conn->delete($sql);
	}
	
	//多个二维数组，一个一位数组
	private function insertFormat($data){
		$arrField = array();
		$arrValues = array();
		if (count($data) == count($data, 1)) {
			//是一维数组
			$arrValues[] = array();
			foreach($data as $k => $v){
				$arrField[] = $k;
				$arrValues[0][] = $v;
			}
		} else {
			//二维数组
			$arrDataMap = array();
			for($i=0;$i<count($data);$i++){
				foreach($data[$i] as $k => $v){
					if(!isset($arrDataMap[$k])){
						$arrDataMap[$k] = array();
					}
					$arrDataMap[$k][] = $v;
					
				}
			}
			$i = 0;
			foreach($arrDataMap as $k=>$v){
				$arrField[$i] = $k;
				for($j=0;$j<count($v);$j++){
					if(!isset($arrValues[$j])){
						$arrValues[$j] = array();
					}
					$arrValues[$j][$i] = $v[$j];
				}
				$i+=1;
			}
		}
		return array(
			'field' => 	$arrField,
			'values' => $arrValues
		);
	}



}
?>