<?php

final class OrderAdminViewController implements ViewControllerInterface {

	private $loc;
	private $input;
	private $modules;
	private $errors;
	private $messages;

	public function __construct($loc, $input, $modules, $errors, $messages) {

		$this->loc = $loc;
		$this->input = $input;
		$this->modules = $modules;
		$this->errors = $errors;
		$this->messages =  $messages;

	}
	
	public function getView() {

		$loc = $this->loc;
		$input = $this->input;

		if ($loc[0] == 'order' && $loc[1] == 'admin') {

			$view = new OrderView($this->loc, $this->input, $this->modules, $this->errors, $this->messages);

			if ($this->loc[2] == 'create') { return $view->orderForm('create'); }

			if ($this->loc[2] == 'update' && is_numeric($this->loc[3])) { return $view->orderForm('update', $this->loc[3]); }

			if ($this->loc[2] == 'confirm-delete' && is_numeric($this->loc[3])) { return $view->orderConfirmDelete($this->loc[3]); }

			$arg = new OrderListArguments();
			if (isset($_SESSION['order']['admin']['search'])) {
				foreach ($_SESSION['order']['admin']['search'] AS $key => $value) {
					if (property_exists($arg, $key)) {
						$arg->$key = $value;
					}
				}
			}

			return $view->orderSearchForm($arg) . $view->orderList($arg);

		}

	}

}

?>