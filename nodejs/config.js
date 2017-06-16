var config = {
	WORKPATH : "/tmp/test/",     
	BASE_CRC : "mytest-preleasetool-qq717981419",  //与config.php中一致
	PRO : {
		'cs1' : {
			name : '测试1',
			from : '/home/test/',
			to : '/home/release/test/'
		},
		'cs2' : {
			name : '测试2',
			from : '/home/test2/',
			to : '/home/release/test2/'
		}
	}
	
};
module.exports = config;