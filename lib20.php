<?php

require_once($CFG->dirroot . '/repository/lib.php');
require_once('mclib.php');

class repository_mediacenter_abs extends repository {

    protected $search_keyword  	= '';
    protected $search_type		= 'vod';

	protected function fetchResult($result) {

		$list = array();

		if($this->search_type == 'vod') {// 点播解析
			foreach($result->Files->File as $f) {
				$list[] = array(
					'title'             =>  (string)($f->FileName),//另存为的名称
					'shorttitle'        =>  (string)($f->FileCName),//选择列表中的标题
					//'thumbnail'         =>  ($CFG->wwwroot).'/repository/mediacenter/pix/video.png',//图片
					'thumbnail'         =>  '../repository/mediacenter/pix/video.png',//图片
					//'thumbnail'         =>  ($this->host).((string)($f->PreviewUrl)),//图片
					'thumbnail_width'   =>  143,
					'thumbnail_height'  =>  98,
					//'thumbnail_width'   =>  320,
					//'thumbnail_height'  =>  240,
					'size'              =>  (int)($f->Size),//文件大小
					//'date'              =>  (int)($f->UTS),//日期  
					'author'            =>  (string)($f->Author),//作者
					//'icon'                =>  '',//找不到预览图的替代图标
					'source'            =>  changeUrl2Moodle((string)($f->VodUrl), 'vod')//文件源
				);  
			}
		}else {// 直播解析
			foreach($result->RoomLives->RoomLive as $f) {
				$list[] = array(
					'title'             =>  (string)($f->ClassRoomName),//另存为的名称
					'shorttitle'        =>  (string)($f->ClassRoomName),//选择列表中的标题
					//'thumbnail'         =>  ($CFG->wwwroot).'/repository/mediacenter/pix/room.png',//图片
					'thumbnail'         =>  '../repository/mediacenter/pix/room.png',//图片
					//'thumbnail'         =>  ($this->host).((string)($f->PreviewUrl)),//图片
					'thumbnail_width'   =>  143,
					'thumbnail_height'  =>  98,
					//'thumbnail_width'   =>  320,
					//'thumbnail_height'  =>  240,
					'size'              =>  0,//文件大小
					//'date'              =>  (int)($f->UTS),//日期  
					'author'            =>  'system',//作者
					//'icon'                =>  '',//找不到预览图的替代图标
					'source'            =>  changeUrl2Moodle2((string)($f->LiveUrls->LiveUrl[0]), 'live')//文件源
				);  
			}
		}
		return $list;
	}

    public function type_config_form($mform, $classname = 'repository') {
		parent::type_config_form($mform);
		repository_mediacenter::type_config_form_real($mform);
    }

	public function print_search() {
		$html = '';
		$html .= '<table><tr>';
		$html .= '<td><input style="width:160px;" type="text" name="s" value="" /></td>';
		//$html .= '<td><input style="width:30px;" type="radio" name="searchtype" value="vod" /></td><td style="width:50px;">'.get_string('vod', 'repository_mediacenter').'</td>';
		//$html .= '<td><input style="width:30px;" type="radio" name="searchtype" value="live" /></td><td style="width:50px;">'.get_string('live', 'repository_mediacenter').'</td>';
		$html .= '<td><input style="width:30px;" type="radio" name="searchtype" value="vod" /></td><td>VOD</td>';
		$html .= '<td><input style="width:30px;" type="radio" name="searchtype" value="live" /></td><td>LIVE</td>';
// 20版本自带提交按钮
		
		$html .= '</tr></table>';
		
		/*$html .= '<script type="text/javascript">';
		$html .= 	'function radio_click(obj){';
		$html .=		'alert(obj.value);';
		$html .= 	'}';
		$html .= '</script>';
*/
	 
		return $html;
	}
}
 
