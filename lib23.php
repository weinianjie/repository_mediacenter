<?php

require_once($CFG->dirroot . '/repository/lib.php');

class repository_mediacenter_abs extends repository {

    protected $host       		= null;
    protected $url        		= null;

    protected $search_keyword  	= '';

	protected function fetchResult($xml_object) {

		$list = array();

		if ($xml_object != null && $xml_object->MsgHead->ReturnCode == '1') {
			foreach($xml_object->MsgBody->Files->File as $f) {
				$thumbnail = trim((string)($f->PreviewUrl));
				if($thumbnail == '') {
					$thumbnail = '../repository/mediacenter/source/video2.png';
				}else{
					$thumbnail = ($this->host).$thumbnail;
				}
				$list[] = array(
					'title'             =>  (string)($f->FileName),//另存为的名称
					'shorttitle'        =>  (string)($f->FileCName),//选择列表中的标题
					'thumbnail'         =>  $thumbnail,//图片
					'thumbnail_width'   =>  143,
					'thumbnail_height'  =>  98,
					'size'              =>  (int)($f->Size),//文件大小
					'date'              =>  $this->toTimestamp($f->UTS),//日期  
					'author'            =>  (string)($f->Author),//作者
					//'icon'                =>  '',//找不到预览图的替代图标
					'source'            =>  (string)($f->VodUrl)//文件源
				);  
			}
		}
		return $list;
	}

    public static function type_config_form($mform) {
		parent::type_config_form($mform);
		repository_mediacenter::type_config_form_real($mform);
    }

	private function toTimestamp($strtime){	
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

	public function print_search() {
	 
		$html = '';
		// label search name
	    //$param = array('for' => 'label_search_name');
		//$title = get_string('search_name', 'myrepo_search_name');
		//$html .= html_writer::tag('label', $title, $param);
		//$html .= html_writer::empty_tag('br');
		 
	
		// text field search name
		$attributes['type'] = 'text';
		$attributes['name'] = 's';
		$attributes['value'] = $this->search_keyword;
		//$attributes['title'] = $title;
		$html .= html_writer::empty_tag('input', $attributes);
	 
		return $html;
	}
}
