<?php
class Unknown extends Browser{
	
	function parse(){
		$this->replacePreg(
			'/background: -webkit-gradient\(linear, left top, ([a-z ]+), from\(([a-z0-9#]+)\), to\(([a-z0-9#]+)\)\);/e',
			'""'
		);
	}
}
?>