<?php

final class OrderPDF {

	private $doc;
	private $fileObject;
	private $fileObjectID;

	public function __construct($loc, $input) {

		if ($loc[0] == 'pdf' && $loc[1] == 'order') {

			// /pdf/order/<orderID>/
			if (is_numeric($loc[3])) {

				$orderID = $loc[3];
				$view = new OrderView($loc, $input);
				$this->doc = $view->orderPrint($orderID);
				$this->fileObject = 'Order';
				$this->fileObjectID = $orderID;

			}

		}

	}

	public function doc() {

		return $this->doc;

	}

	public function getFileObject() {

		return $this->fileObject;

	}

	public function getFileObjectID() {

		return $this->fileObjectID;

	}

}

?>