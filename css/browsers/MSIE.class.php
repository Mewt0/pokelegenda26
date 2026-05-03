<?php
class MSIE extends Browser{
	
	function parse(){
		$this->replacePreg('/opacity: ([0-9\.]+)/e','"filter:progid:DXImageTransform.Microsoft.Alpha(opacity=".round(\\1*100).")"');
		$this->replacePreg(
			'/background: -webkit-gradient\(linear, left top, ([a-z ]+), from\(([a-z0-9#]+)\), to\(([a-z0-9#]+)\)\)/e',
			'"filter: progid:DXImageTransform.Microsoft.gradient(gradientType=".("\\1"=="left bottom"?0:1).", startColorstr=\\2, endColorstr=\\3)"'
		);
	}
}
?>