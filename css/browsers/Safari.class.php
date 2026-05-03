<?php
class Safari extends Browser{
	
	function parse(){
		$this->replaceStr('border-radius',	'-webkit-border-radius');
		$this->replaceStr('box-shadow',		'-webkit-box-shadow');
	}
}
?>