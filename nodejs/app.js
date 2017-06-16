/*
	根据git更新列表，更新生成环境代码
*/

var http = require('http');
var path = require('path');
var url = require('url');
var util = require('util');
var qs = require('querystring');
var exec = require('child_process').exec;
var fs = require('fs');
var config = require('./config');
var crypto = require('crypto');


http.createServer(function(request, response) {
	var pathname = url.parse(request.url).pathname;
	console.log("["+now().toString()+"]","------------------------------->");
	console.log("request url :", pathname);
	//var getQuery = url.parse(request.url).query;
    //var getData = qs.parse(getQuery);
	//console.log('post params :',getData);
	if(request.method.toUpperCase() === 'POST') {
		  var headers = request.headers;
		  var method = request.method;
		  //var body = [];
		  var postData = "";
		  request.on('error', function(err) {
				console.error(err);
		  }).on('data', function(chunk) {
				//body.push(chunk);
				postData += chunk;
		  }).on('end', function() {
				//body = Buffer.concat(body).toString();
				postData = qs.parse(postData);
				console.log('post',postData);
				//console.log('post content:',postData.content);
				response.writeHead(200, {'Content-Type': 'text/html;charset=UTF-8'});
				router(pathname, postData,function(responseBody){
					console.log("send respose over!!!");
					response.end(JSON.stringify(responseBody));
				});
				
		  });
	}else{
		response.statusCode = 404;
		response.end();
	}
}).listen(8181); // Activates this server, listening on port 8080.
console.log("server is running on port 8181!");

function now(formatStr, fdate){
	var fTime, fStr = 'ymdhis';
	if (!formatStr)
	formatStr= "y-m-d h:i:s";
	if (fdate)
	fTime = new Date(fdate);
	else
	fTime = new Date();
	var formatArr = [
		fTime.getFullYear().toString(),
		(fTime.getMonth()+1)<10?("0"+(fTime.getMonth()+1).toString()):(fTime.getMonth()+1).toString(),
		fTime.getDate()<10?("0"+fTime.getDate().toString()):fTime.getDate().toString(),
		fTime.getHours()<10?("0"+fTime.getHours().toString()):fTime.getHours().toString(),
		fTime.getMinutes()<10?("0"+fTime.getMinutes().toString()):fTime.getMinutes().toString(),
		fTime.getSeconds()<10?("0"+fTime.getSeconds().toString()):fTime.getSeconds().toString() 
	]
	for (var i=0; i<formatArr.length; i++)
	{
		formatStr = formatStr.replace(fStr.charAt(i), formatArr[i]);
	}
	return formatStr;
}
function makeCrc(str){
	var src = config.BASE_CRC+str;
	var key = crypto.createHash('md5').update(src).digest('hex');
	console.log("nodejs makeCrc result :", key);
	return key;
}

function checkCrc(crc, postData){
	var curTime = Date.now();
	var diffmicroSec = (curTime - postData['token']);
	if(diffmicroSec > 60*1000){
		return false;
	}
	if(crc != postData['crc']){
		return false;
	}
	return true;
}

//路由中转
function router(pathname, postData, fn){
	if(pathname === '/gettoken'){
		getToken(postData, fn);                 //备份目录检测
	}else{
		//首先验证
		var crc = makeCrc(postData['token']+"#"+postData['taskid']);
		var chkRet = checkCrc(crc, postData);
		if(!chkRet){
			fn({
				code : -1,
				msg  : '非法请求或请求超时'
			});
			return;
		}
		if(pathname === '/checkbakpath'){
			checkBakpath(postData, fn);                 //备份目录检测
		}else if(pathname === '/checkfilefromexist'){
			checkFilefromExist(postData, fn);           //来源文件是否存在检测
		}else if(pathname === '/checkfiletoexist'){
			checkFiletoExist(postData, fn);             //目的文件是否存在
		}else if(pathname === '/execbakup'){
			execBakup(postData, fn);                    //执行备份
		}else if(pathname === '/checkfilebakexist'){
			checkFilebakExist(postData, fn);             //备份文件是否存在
		}else if(pathname === '/execupdate'){
			execUpdate(postData, fn);
		}else if(pathname === '/execgoback'){
			execGoback(postData, fn);
		}
	}
}

function getToken(postData, fn){
	var tokenT = Date.now();
	console.log("token :",tokenT);
	fn({
		code   : 1,
		token  : tokenT
	});
}

//执行备份目录检测和创建
function checkBakpath(postData, fn){
	var fPath = {
		bakDir  : config.WORKPATH+postData['taskid']+"/bak"
	};
	if(!fs.existsSync(fPath.bakDir)) {
		exec("mkdir  -p "+fPath.bakDir, function(err, stdout , stderr ) {
			if (!fs.existsSync(fPath.bakDir)) {
				fn({
					code : -1,
					msg  : '备份目录创建失败'
				});
			}else{
				fn({
					code : 1,
					msg  : '备份目录创建成功'
				});
			}
		});
	}else{
		fn({
			code : 1,
			msg  : '备份目录已创建'
		});
	}
}

//来源文件是否存在检测
function checkFilefromExist(postData, fn){
	var fPath = {
		from  : config.PRO[postData['prono']].from+postData['path']
	};
	if(!fs.existsSync(fPath.from)) {
		fn({
			code : -1,
			msg  : '来源文件不存在'
		});
	}else{
		fn({
			code : 1,
			msg  : '来源文件存在'
		});
	}
}
//目的文件是否存在检测
function checkFiletoExist(postData, fn){
	var fPath = {
		to  : config.PRO[postData['prono']].to+postData['path']
	};
	if(!fs.existsSync(fPath.to)) {
		fn({
			code : -1,
			msg  : '目的文件不存在'
		});
	}else{
		fn({
			code : 1,
			msg  : '目的文件存在'
		});
	}
}
//执行备份
function execBakup(postData, fn){
	var fPath = {
		bakDir  : path.dirname(config.WORKPATH+postData['taskid']+"/bak/"+postData['path']),
		bakFilePath : config.WORKPATH+postData['taskid']+"/bak/"+postData['path'],
		to  : config.PRO[postData['prono']].to+postData['path']
	};
	//先检测备份目录是否存在，不存在则创建
	if(!fs.existsSync(fPath.bakDir)) {
		exec("mkdir  -p "+fPath.bakDir, function(err, stdout , stderr ) {
			if (!fs.existsSync(fPath.bakDir)) {
				fn({
					code : -1,
					msg  : '备份目录创建失败'
				});
			}else{
				execBakup_step2(fPath, postData, fn);
			}
		});
	}else{
		execBakup_step2(fPath, postData, fn);
	}
}
function execBakup_step2(fPath, postData, fn){
	//先删掉之前可能已有的备份
	if(fs.existsSync(fPath.bakFilePath)){
		fs.unlinkSync(fPath.bakFilePath);
	}
	//复制目的路径的文件到备份目录
	var readStream = fs.createReadStream(fPath.to);
	var writeStream = fs.createWriteStream(fPath.bakFilePath);
	readStream.pipe(writeStream);
	readStream.on('end', function () {
		if(!fs.existsSync(fPath.bakFilePath)) {
			fn({
				code : -1,
				msg  : '备份失败'
			});
		}else{
			fn({
				code : 1,
				msg  : '备份成功'
			});
		}
	});
	readStream.on('error', function () {
		fn({
			code : -1,
			msg  : '备份失败'
		});
	});
}

//检测备份文件是否存在
function checkFilebakExist(postData, fn){
	var fPath = {
		bakFilePath  : config.WORKPATH+postData['taskid']+"/bak/"+postData['path']
	};
	if(!fs.existsSync(fPath.bakFilePath)) {
		fn({
			code : -1,
			msg  : '备份文件不存在'
		});
	}else{
		fn({
			code : 1,
			msg  : '备份文件存在'
		});
	}
}


function execUpdate(postData, fn){
	/*
	console.log("__dirname:",__dirname);
	var toolPath = path.resolve(__dirname, 'tool.php');
	var cmd = "php "+toolPath;
	*/
	var fPath = {
		from  : config.PRO[postData['prono']].from+postData['path'],
		toDir  : path.dirname(config.PRO[postData['prono']].to+postData['path']),
		to  : config.PRO[postData['prono']].to+postData['path']
	};
	//1、首先判断目的路径是否存在
	if (!fs.existsSync(fPath.toDir)) {
		//如果目的路径不存在
		exec("mkdir  -p "+fPath.toDir, function(err, stdout , stderr ) {
			if (!fs.existsSync(fPath.toDir)) {
				fn({
					code : -1,
					msg  : '目的路径创建失败'
				});
				return false;
			}
			doCopyFiles(fPath.from, fPath.to, fn);
		});
	}else{
		doCopyFiles(fPath.from, fPath.to, fn);
	}
}

//执行备份还原
function execGoback(postData, fn){
	var fPath = {
		bakFilePath  : config.WORKPATH+postData['taskid']+"/bak/"+postData['path'],
		toDir  : path.dirname(config.PRO[postData['prono']].to+postData['path']),
		to  : config.PRO[postData['prono']].to+postData['path']
	};
	if (!fs.existsSync(fPath.bakFilePath)) {
		fn({
			code : -1,
			msg  : '备份文件不存在'
		});
		return false;
	}
	doCopyFiles(fPath.bakFilePath, fPath.to, fn);

}

function doCopyFiles(from, to, fn){
	var readStream = fs.createReadStream(from);
	var writeStream = fs.createWriteStream(to);
	readStream.pipe(writeStream);
	readStream.on('end', function () {
		fn({
			code : 1,
			msg  : '更新成功'
		});
	});
	readStream.on('error', function () {
		fn({
			code : -1,
			msg  : 'copy更新失败'
		});
	});
}

