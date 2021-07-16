<?php

final class OrderView {

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

	public function orderTest() {

		$body = 'ORDER TEST';
		$header = Lang::getLang('orderTest');
		$card = new CardView('order_test',array('container'),'',array('col-12'),$header,$body);
		return $card->card();

	}

}

?>