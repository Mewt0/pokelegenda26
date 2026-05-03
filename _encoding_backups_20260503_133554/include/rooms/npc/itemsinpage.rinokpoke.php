<?php
class Itemsinpage{

	private      $inpage = 10;
	private        $page = 0;
	private  $totalpages = 0;
	private   $linkcount = 20;
	private $inpagestart = 10;
	private   $inpageend = 25;
	private   $inpageinc = 5;
	protected  $res = Array();
	
	function __construct($total){
		$_GET['page'] = $this->page = max(0,isset($_GET['page'])?(int)$_GET['page']:0);
		$_GET['count'] = $this->inpage = 20;
		$this->totalpages = ceil($total/$this->inpage);
		// РЎРѓР С”Р С•Р В»РЎРЉР С”Р С• РЎРѓРЎР‚Р В°Р В·РЎС“ РЎРѓРЎРѓРЎвЂ№Р В»Р С”Р С• Р Р…Р В° РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ  Р Р†РЎвЂ№Р Р†Р ВµР Т‘Р С‘РЎвЂљРЎРЉРЎРѓРЎРЏ
		$this->linkcount= 11; // Р Т‘Р С•Р В»Р В¶Р Р…Р С• Р В±РЎвЂ№РЎвЂљРЎРЉ Р Р…Р ВµРЎвЂЎР ВµРЎвЂљР Р…РЎвЂ№Р С!
		$pagelistdiv2=(int)(($this->linkcount-1)/2);
		$res['Page']=$this->page+1;
		$where = '';
		$res['Count'] = array();
		if($this->page<=$pagelistdiv2){
			$c = min($this->totalpages,$this->linkcount);
			for($i=0;$i<$c;$i++) $res['Count'][] = array($i+1,$i);
			if($this->totalpages>$this->linkcount) $res['Count'][] = array('...',$c);
		}else{
			$c = $pagelistdiv2 + min($this->totalpages-$this->page, $pagelistdiv2);
			if($this->page>$pagelistdiv2) $res['Count'][] = array('...',$this->page-$pagelistdiv2-1);
			for($i=0;$i<$c;$i++) $res['Count'][] = array($i+$this->page-$pagelistdiv2+1,$i+$this->page-$pagelistdiv2);
			if($this->totalpages>$this->page+$pagelistdiv2) $res['Count'][] = array('...',$this->page+$pagelistdiv2+1);
		}
		$res['InPage'] = array();
		for($i=$this->inpagestart;$i<=$this->inpageend;$i+=$this->inpageinc) $res['InPage'][] = $i;
		$res['Start'] = $this->inpage*$this->page;
		$res['Limit'] = $this->inpage;
		$this->res = $res;
	}
	function get($key){
		return $this->res[$key];
	}
	function SmartyArr(){
		$arr = array();
		$arr['Page'] = $this->res['Page'];
		$arr['Count'] = $this->res['Count'];
		$arr['InPage'] = $this->res['InPage'];
		return $arr;
	}
}
?>