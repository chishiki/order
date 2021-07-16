<?php

final class OrderViewController {

	private $loc;
	private $input;
	private $modules;
	private $errors;
	private $orders;

	public function __construct($loc = array(), $input = array(), $modules = array(), $errors = array(), $orders = array()) {

		$this->loc = $loc;
		$this->input = $input;
		$this->modules = $modules;
		$this->errors = $errors;
		$this->orders = $orders;

	}

	public function getView() {

		if ($this->loc[0] == 'order') {

			$view = new OrderView();
			return $view->orderTest();

		}

		if (isset($v)) {
			return $v->getView();
		} else {
			$url = '/' . Lang::prefix();
			header("Location: $url" );
		}

	}

}

?>