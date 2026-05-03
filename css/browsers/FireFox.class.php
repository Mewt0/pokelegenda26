<?php
class FireFox extends Browser{
	
	function parse(){
		$this->replaceStr('border-radius',	'-moz-border-radius');
		$this->replaceStr('box-shadow',		'-moz-box-shadow');
		$this->replacePreg(
			'/background: -webkit-gradient\(linear, left top, ([a-z ]+), from\(([a-z0-9#]+)\), to\(([a-z0-9#]+)\)\)/e',
			'"background: -moz-linear-gradient(".("\\1"=="left bottom"?"top":"left").", \\2, \\3)"'
		);
	}
}
?>