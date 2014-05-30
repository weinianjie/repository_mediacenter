<?php

if ($CFG->version >= 2012061800) {
	require_once("lib23.php");
} else {
	require_once("lib20.php");
}

class repository_mediacenter extends repository_mediacenter_abs {
	
	//private $useadmin			= false;

	public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
		parent::__construct($repositoryid, $context, $options);

		$this->host 			= get_config('mediacenter', 'host');//这样获取配置页的配置
		//$this->useadmin 		= get_config('mediacenter', 'useadmin');

		if ($this->host == null || $this->host == '') {
			$this->host = 'http://127.0.0.1';
		} else if (!(strpos($this->host, 'http') === 0)) {//地址修正
			$this->host = 'http://'.$this->host;	
		}

		$this->url = $this->host.'/XmlRpcService.action';
	}

	// 搜索功能
	public function search($search_text = '', $page = 0) {
		global 			$USER, $CFG, $SESSION;
		$this->search_keyword = $search_text;
		$ret 			= array();
		$list			= array();
        $ret['nologin'] 	= true;
        $ret['norefresh'] 	= true;
		$ret['dynload'] 	= true;
        $ret['page'] 		= (int)$page;
		if($ret['page'] < 1){
			$ret['page'] = 1;
		}
		$pageSize			= 12;

		// 用户id没有统一的情况下，先使用username
		$xml_request  = 	'<?xml version="1.0" encoding="UTF-8" ?>';
		$xml_request .= 	'<RequestMsg>';
		$xml_request .= 		'<MsgHead>';
		$xml_request .= 			'<MsgCode>10108</MsgCode>';
		$xml_request .= 		'</MsgHead>';
		$xml_request .= 		'<MsgBody>';

		//if(!empty($this->useadmin)){
		//	$xml_request .= 			'<UserName>admin</UserName>';
		//}else{
			$xml_request .= 			'<UserName>'.($USER->username).'</UserName>';
		//}

		$xml_request .= 			'<Keyword>'.$search_text.'</Keyword>';
		$xml_request .= 			'<BeginDate></BeginDate>';
		$xml_request .= 			'<EndDate></EndDate>';
		$xml_request .= 			'<PageSize>'.$pageSize.'</PageSize>';
		$xml_request .= 			'<PageNum>'.$ret['page'].'</PageNum>';
		$xml_request .= 		'</MsgBody>';		
		$xml_request .= 	'</RequestMsg>';

		try {
			$xml_response		= $this->do_post_request($this->url, $xml_request);
			$xml_object			= simplexml_load_string($xml_response);

			if($xml_object->MsgHead->ReturnCode == 1) {
				$list				= $this->fetchResult($xml_object);
				$ret['list'] 		= $list;
				$ret['pages'] 		= (int)$xml_object->MsgBody->Page->LastPage;
			}else {
				echo $xml_object->MsgBody->FaultString;
				exit;
			}
		}catch(Exception $e) {
			echo $e->getMessage();
			exit;
		}

        return $ret;
	}
    
  //配置页呈现效果
	public static function type_config_form_real($mform) {
    	$mform->addElement('text', 'host', get_string('host', 'repository_mediacenter'));
		$mform->setType('host', PARAM_RAW_TRIMMED);

		$strrequired = get_string('required');
		$mform->addRule('host', $strrequired, 'required', null, 'client');

    	//$mform->addElement('checkbox', 'useadmin', get_string('useadmin', 'repository_mediacenter'));
		//$mform->setDefault('useadmin', 0);
	}

	// 结合配置页使用，否则配置页的配置无法保存 
	public static function get_type_option_names() {
        //return array('host', 'useadmin', 'pluginname');
        return array('host', 'pluginname');
	}
  
	public function get_listing($path = '', $page = '') {
		return $this->search(null, $page);
	}  

	public function global_search() {
		return false;
	}

	public function supported_filetypes() {
		return '*';
	}

	public function supported_returntypes() {
		//return FILE_INTERNAL;
		return FILE_EXTERNAL;
	}

	//send post request
	private function do_post_request($url, $data, $optional_headers = null) {
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
}
