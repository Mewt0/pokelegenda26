<?php
class ChatController
{ 
 private        $mothod;
  
	function __construct($getmetod)
  {
		$this->mothod = trim(htmlspecialchars($getmetod));
		if($this->mothod == "index")
    {
            
		}
    elseif($this->mothod == "add")
    {

		}
		else
		{
    
    }    

	}
 
  public function actionIndex() 
  {
  
  }
  public function actionAdd() 
  {
 
 
  }
}

?>