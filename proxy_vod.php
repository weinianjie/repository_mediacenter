<?php

	require_once(dirname(__FILE__) . '/../../config.php');
	require_once('mclib.php');
	require_once($CFG->dirroot.'/my/lib.php');
	
	// 校验登录
	require_login();

	// 检测系统'URL'模块是否存在 
	$dbman = $DB->get_manager();

	// 没有Moodle管理员权限 
	if(!has_capability('moodle/site:config', context_system::instance()) 
	&& !has_capability('block/lbcontrol:addinstance', context_system::instance())) {
		
		// 对该课件不具有管理权限(由于目前moodle的课件只能从url模块里点播，所以暂时不加校验)
		if(1==1) {
			//{mdl_user}--{mdl_user_enrolments}--{mdl_enrol}--{mdl_course}--{mdl_url}
			//course(访客可访问性，暂时忽略)

			// 检查用户的课程里是否含有该URL（用户+资源）

			$user_id = $DB->get_field('user', 'id', array('username'=>$USER->username), IGNORE_MISSING);

			// 从URL表里查
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

				// 从LINK表里查
				if(!$dbman->table_exists('link')) {
					echo 'Have no auths in moodle'.'<br/>';
					exit;
				}
				$cur_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				$sql = 'SELECT 1 FROM {link} a
						LEFT JOIN {enrol} b ON a.course = b.courseid
						LEFT JOIN {user_enrolments} c ON b.id = c.enrolid
						LEFT JOIN {user} d ON c.userid = d.id
						WHERE a.externalurl like :url
						AND d.id = :userid
						AND (a.timeopen = 0 OR (a.timeopen < :now1 AND a.timeclose > :now2))';// 传入now实现跨数据库类型
				$sql_ps = array(
							'url'       => $cur_url,//先不考虑附加系统参数的情况
							'userid'    => $user_id,
							'now1'		=> time(),
							'now2'		=> time()
						);
				if (!$DB->record_exists_sql($sql, $sql_ps)){
					//echo $cur_url.'<br/>';
					echo 'Have no auths in moodle'.'<br/>';
					exit;
				}

			}
		}
		
	}

	$file_id = $_GET['a'];
	$params =  array(
		'Type'=>1, 
		'UserName'=>($USER->username), 
		'ClientIp'=>$_SERVER['REMOTE_ADDR'], 
		'Content'=>$file_id
	);
	$result = mediacenter_request(10401, $params);

	if($result == null) {
		exit;
	}

	$sign               = $result->Sign;

	// 获取并跳转到媒体中心播放
	$param_str = $_SERVER["QUERY_STRING"];
	$mcplay_url = get_mc_host().'/backstage/Vod.action?'.$param_str.'&sign='.$sign;
	//echo $mcplay_url
	//Header( 'HTTP/1.1 301 Moved Permanently' ) ;
	//Header( 'Location: '.$mcplay_url );
	echo '<html><head></head><body style="margin:0px; padding:0px;">';
	echo '<iframe marginwidth="0" marginheight="0" frameborder="0" scrolling="no" style="width:100%; height:100%; margin:0px; padding:0px;" src="'.$mcplay_url.'"></iframe>';
	echo '</body></html>';

?>
