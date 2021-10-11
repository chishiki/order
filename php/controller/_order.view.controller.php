<?php

final class OrderViewController implements ViewControllerInterface {

	private $loc;
	private $input;
	private $modules;
	private $errors;
	private $messages;

	public function __construct($loc = array(), $input = array(), $modules = array(), $errors = array(), $messages = array()) {

		$this->loc = $loc;
		$this->input = $input;
		$this->modules = $modules;
		$this->errors = $errors;
		$this->messages = $messages;

	}

	public function getView() {

		$loc = $this->loc;
		$input = $this->input;
		$modules = $this->modules;
		$errors = $this->errors;
		$messages = $this->messages;

		if ($loc[0] == 'order') {

			switch ($loc[1]) {

				case 'admin':
					$v = new OrderAdminViewController($loc, $input, $modules, $errors, $messages);
					break;

				case 'test':
					$v = new OrderTestViewController($loc, $input, $modules, $errors, $messages);
					break;

				default:
					$v = new OrderMainViewController($loc, $input, $modules, $errors, $messages);

			}

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