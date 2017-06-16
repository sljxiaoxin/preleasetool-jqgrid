<?

$CONFIG_PRO = array(
	'cs1' => array(
		'name'       => '测试1',                     //项目显示名称
		'NODEJSURL'   => 'http://localhost:8181'       //在哪台服务器就指向到哪，根据nodejs的部署来配置
	),
	"cs2" => array(
		'name'       => '测试2',
		'NODEJSURL'   => 'http://192.168.1.2:8181'
		
	)
	//####每增加一个项目都需要增加一个服务器端nodejs的配置
);

/*
NODEJSURL 可以必须至少POST传3个参数，
http://localhost:8181/checkbakpath?taskid=&prono=&filepath=文件列表过滤后的路径
*/
function requireNodejs($url, $params){
	//$params.= "&test=aaa bbb ccc =%/";
	global $CONFIG_PRO;
	$tokenUrl = $CONFIG_PRO[$params['prono']]['NODEJSURL']."/gettoken";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $tokenUrl);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	$data = curl_exec($ch);
	curl_close($ch);
	$arrToken = json_decode($data, true);
	if($arrToken == null || empty($arrToken)){
		return array(
			'code' => '-1',
			'msg' => 'token获取失败'
		);
	}
	$token = $arrToken['token'];
	$strParams = array();
	$strParams[] = "token=".$token;
	$crc = makeCrc($token."#".$params['taskid']);
	$strParams[] = "crc=".$crc;
	foreach($params as $k=>$v){
		$strParams[] = $k."=".$v;
	}
	$strParams = implode("&",$strParams);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $strParams);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	$data = curl_exec($ch);
	curl_close($ch);
	//echo $data;
	$result = json_decode($data, true);
	return $result;
}

function makeCrc($str){
	$src = "mytest-preleasetool-qq717981419".$str;
	return md5($src);
}

//项目地址
return $CONFIG_PRO;
?>