<?php

	require_once(dirname(__FILE__) . '/../../config.php');
	require_once($CFG->dirroot.'/my/lib.php');
	
	// 校验登录
	require_login();

	// 检测系统'URL'模块是否存在 
	$dbman = $DB->get_manager();
	if(!$dbman->table_exists('url')) {
		echo 'URL module is not installed'.'<br/>';
		exit;
	}

	//{mdl_user}--{mdl_user_enrolments}--{mdl_enrol}--{mdl_course}--{mdl_url}
	//course(访客可访问性，暂时忽略)

	// 检查用户的课程里是否含有该URL（用户+资源）
	$user_id = $DB->get_field('user', 'id', array('username'=>$USER->username), IGNORE_MISSING);
	$cur_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$sql = 'SELECT 1 FROM {url} a
			LEFT JOIN {enrol} b ON a.course = b.courseid
			LEFT JOIN {user_enrolments} c ON b.id = c.enrolid
			LEFT JOIN {user} d ON c.userid = d.id
			WHERE a.externalurl = :url
			AND d.id = :userid';
	$sql_ps = array(
				'url'       => $cur_url,
				'userid'    => $user_id
			);
	if (!$DB->record_exists_sql($sql, $sql_ps)){
		echo 'Have no auths in moodle'.'<br/>';
		exit;
	}		

	// 获取并修正媒体中心地址
	$mc_host  = get_config('mediacenter', 'host');
	if ($mc_host == null || $mc_host == '') {
		$mc_host = 'http://127.0.0.1';
	} else if (!(strpos($mc_host, 'http') === 0)) {//地址修正
		$mc_host = 'http://'.$mc_host;
	}

	// 请求媒体中心返回动态播放令牌
	$xml_request  =     '<?xml version="1.0" encoding="UTF-8" ?>';
	$xml_request .=     '<RequestMsg>';
	$xml_request .=         '<MsgHead>';
	$xml_request .=             '<MsgCode>10116</MsgCode>';
	$xml_request .=         '</MsgHead>';
	$xml_request .=         '<MsgBody>';
	$xml_request .=         '</MsgBody>';
	$xml_request .=     '</RequestMsg>';

	$sign = '';
	try {
		$xml_response       = do_post_request($mc_host.'/XmlRpcService.action', $xml_request);
		$xml_object         = simplexml_load_string($xml_response);
		$sign               = $xml_object->MsgBody->Sign;
	}catch(Exception $e) {
		echo $e->getMessage().'<br/>';
		exit;
	}

	// 获取并跳转到媒体中心播放
	$param_str = $_SERVER["QUERY_STRING"];
	$mcplay_url = $mc_host.'/backstage/Vod.action?'.$param_str.'&sign='.$sign;
	//echo $mcplay_url
	Header( 'HTTP/1.1 301 Moved Permanently' ) ;
	Header( 'Location: '.$mcplay_url );

	// 发送POST请求的方法
	function do_post_request($url, $data, $optional_headers = null) {
		$params = array('http' => array(
				  'method' => 'POST',
				  'content' => $data
		));
		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			throw new Exception("Problem with $url");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url");
		}
		return $response;
	}

?>
