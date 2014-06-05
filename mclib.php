<?php
	/*
	* mclib.php
	*/
	require_once(dirname(__FILE__) . '/../../config.php');

	// 请求的公共方法
	function mediacenter_request($code, $params) {

		$rt = null;		

		if(empty($code)) {
			return $rt;
		}
		
		$xml_request  =     '<?xml version="1.0" encoding="UTF-8" ?>';
		$xml_request .=     '<RequestMsg>';
		$xml_request .=         '<MsgHead>';
		$xml_request .=             '<MsgCode>'.$code.'</MsgCode>';
		$xml_request .=         '</MsgHead>';
		$xml_request .=         '<MsgBody>';
		$xml_request .=         params2xml($params);
		$xml_request .=         '</MsgBody>';
		$xml_request .=     '</RequestMsg>';

		try {
			$xml_response       = do_post_request(get_mc_host().'/XmlRpcService.action', $xml_request);
			$xml_object         = simplexml_load_string($xml_response);
			
			if($xml_object->MsgHead->ReturnCode == 1) {
				$rt  = $xml_object->MsgBody;
			}else{
				echo $xml_object->MsgBody->FaultString;
			}
		}catch(Exception $e) {
			echo $e->getMessage();
		}

		return $rt;
	}

	// 兼容的获取context
	function get_context($params = null) {
		global $CFG;

		if ($CFG->version >= 2011120500) {// 记录在该版本的version.php里
			return context_system::instance();//Moodle 2.2 and greater
		}else{
			return get_system_context();//Moodle 2.0 and 2.1
		}
	}

	// 获取并修正媒体中心地址
	function get_mc_host() {
		$mc_host  = get_config('mediacenter', 'host');
		if ($mc_host == null || $mc_host == '') {
			$mc_host = 'http://127.0.0.1';
		} else if (!(strpos($mc_host, 'http') === 0)) {//地址修正
			$mc_host = 'http://'.$mc_host;
		}
		return $mc_host;
	}

	// 参数转换为xml
	function params2xml($params, $_key = null) {
		$rt = '';
		if(empty($params)) {
			return '';
		}
		if(!is_array($params)) {// 使得参数可以接收字符串
			return $params;
		}
		
		foreach($params as $key => $val) {
			$__key = (is_numeric($key) && $_key != null)? substr($_key, 0, strlen($_key) - 1) : $key;

			$rt .= '<'.$__key.'>';
			if(!is_array($val)) {
				$rt .= $val;
			}else{
				$rt .= params2xml($val, $key);
			}
			$rt .= '</'.$__key.'>';
		}
		return $rt;
	}

    // 把获取到的地址改成通过moodle方法的代理地址
	function changeUrl2Moodle($url, $type='vod') {
        if($url != null) {
            $arr = explode('?', $url);
			$arr2 = explode('repository', $_SERVER['PHP_SELF']);//娘的，处理可能的上下文，虽然PHP里面没有上下文的概念
            $qstr = str_replace('&preview=1', '', $arr[1]);
            $url = 'http://'.$_SERVER['HTTP_HOST'].$arr2[0].'repository/mediacenter/proxy_'.$type.'.php?'.$qstr;
        }
        return $url;
    }

	// 普通时间转时间戳
	function toTimestamp($strtime){	
		$array = explode("-",$strtime);
		$year = $array[0];
		$month = $array[1];
		
		$array = explode(":",$array[2]);
		$minute = $array[1];
		$second = $array[2];
		
		$array = explode(" ",$array[0]);
		$day = $array[0];
		$hour = $array[1];
		
		$timestamp = mktime($hour,$minute,$second,$month,$day,$year);
		return $timestamp;
	}

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
