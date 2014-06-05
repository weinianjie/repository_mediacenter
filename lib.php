<?php

if ($CFG->version >= 2012061800) {
	require_once("lib23.php");
} else {
	require_once("lib20.php");
}
require_once('mclib.php');

class repository_mediacenter extends repository_mediacenter_abs {
	
	//private $useadmin			= false;

	public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
		parent::__construct($repositoryid, $context, $options);
	}

	// 搜索功能
	public function search($search_text = '', $page = 0) {
		global 			$USER, $CFG, $SESSION;

		$this->search_keyword = $search_text;
		$this->search_type 	= optional_param('searchtype', 'vod', PARAM_TEXT);
		$msgcode = ($this->search_type == 'vod'? 10108 : 10110);// 10108课件、10110教室

		$ret 			= array();
		$list			= array();
        $ret['nologin'] 	= true;
        $ret['norefresh'] 	= true;
		$ret['dynload'] 	= true;
        $ret['page'] 		= (int)$page;
		if($ret['page'] < 1){
			$ret['page'] = 1;
		}

		$params = array(
			'UserName'=>($USER->username),
			'Keyword'=>$search_text,
			'BeginDate'=>'',
			'EndDate'=>'',
			'PageSize'=>12,
			'PageNum'=>$ret['page']
		);

		$result = mediacenter_request($msgcode, $params);
		if($result == null) {
			exit;
		}

		$list				= $this->fetchResult($result);
		$ret['list'] 		= $list;
		$ret['pages'] 		= (int)$result->Page->LastPage;

        return $ret;
	}
    
  //配置页呈现效果
	public static function type_config_form_real($mform) {
    	$mform->addElement('text', 'host', get_string('host', 'repository_mediacenter'));
		$mform->setType('host', PARAM_RAW_TRIMMED);

		$strrequired = get_string('required');
		$mform->addRule('host', $strrequired, 'required', null, 'client');
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

}
