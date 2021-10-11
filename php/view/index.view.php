<?php 

final class OrderIndexView {

    private $urlArray;
	private $view;
	
	public function __construct($urlArray) {
		
	    $this->urlArray = $urlArray;
		$this->view = $this->index();

	}

	private function index() {

		$index = '';
	    return $index;
	    
	}
	
	public function getView() {
		
		return $this->view;
		
	}
	
}


?>