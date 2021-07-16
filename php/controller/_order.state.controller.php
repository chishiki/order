<?php

final class OrderController {

	private $loc;
	private $input;
	private $modules;
	private $errors;
	private $orders;

	public function __construct($loc, $input, $modules) {

		$this->loc = $loc;
		$this->input = $input;
		$this->modules = $modules;
		$this->errors = array();
		$this->orders =  array();

	}

	public function setState() {

		$input = $this->input;

		if ($this->loc[0] == 'order') {

			if (!Auth::isLoggedIn()) {

				$loginURL = '/' . Lang::prefix() . 'login/';
				header("Location: $loginURL");

			}

		}

		if (isset($controller)) {
			$controller->setState();
			$this->errors = $controller->getErrors();
			$this->orders = $controller->getOrders();
		}

	}

	public function getErrors() {
		return $this->errors;
	}

	public function getOrders() {
		return $this->orders;
	}

}

?>