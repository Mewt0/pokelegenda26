<?php
abstract class Browser{
	private $clear = false;
	private $content;
	
	function __construct($file,$cache){
		$this->content = file_get_contents($file);
		$this->parse();
		if($this->clear) $this->replaceStr(array(' ',"\r","\n\r","\n","\t"),'');
		file_put_contents($cache, $this->content);
	}
	
	abstract function parse();
	
	function replaceStr($that,$to){
		$this->content = str_replace($that,$to,$this->content);
	}
	
	function replacePreg($that,$to){
		$this->content = preg_replace($that,$to,$this->content);
	}
	
	function __toString(){
		return $this->content;
	}
}
?>