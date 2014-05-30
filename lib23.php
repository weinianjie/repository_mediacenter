<?php

require_once($CFG->dirroot . '/repository/lib.php');
//require_once($CFG->libdir . "/sheep.php");

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
					'source'            =>  $this->changeUrl2Moodle((string)($f->VodUrl))//文件源
				);  
			}
		}
		return $list;
	}

    public static function type_config_form($mform) {
		parent::type_config_form($mform);
		repository_mediacenter::type_config_form_real($mform);
    }

    // 把获取到的地址改成通过moodle方法的代理地址
    private function changeUrl2Moodle($url) {
        if($url != null) {
			$arr = explode('?', $url);
			$arr2 = explode('repository', $_SERVER['PHP_SELF']);//娘的，处理可能的上下文，虽然PHP里面没有上下文的概念
            $url = 'http://'.$_SERVER['HTTP_HOST'].$arr2[0].'blocks/mediacenter_lbcontrol/proxy_vod.php?'.$arr[1];
        }
        return $url;
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
		$html .= '<table><tr>';
		$html .= '<td><input style="width:160px;" type="text" name="s" value="" /></td>';
		//$html .= '<td><input style="width:30px;" type="radio" name="searchtype" value="vod" /></td><td style="width:50px;">'.get_string('vod', 'repository_mediacenter').'</td>';
		//$html .= '<td><input style="width:30px;" type="radio" name="searchtype" value="live" /></td><td style="width:50px;">'.get_string('live', 'repository_mediacenter').'</td>';
		$html .= '<td><input style="width:30px;" type="radio" name="searchtype" value="vod" /></td><td>VOD</td>';
		$html .= '<td><input style="width:30px;" type="radio" name="searchtype" value="live" /></td><td>LIVE</td>';
		$html .= '<td><input style="width:60px; padding:0; margin:-5px 0 0 20px; background:none;" type="submit" value="Submit"/></td>';
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
