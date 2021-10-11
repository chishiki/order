<?php

/*

CREATE TABLE `order_Order` (
  `orderID` int NOT NULL AUTO_INCREMENT,
  `siteID` int NOT NULL,
  `creator` int NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `deleted` int NOT NULL,
  `orderDate` date NOT NULL,
  `status` varchar(10) NOT NULL,
  `projectName` varchar(100) NOT NULL,
  `customerOrderNo` varchar(30) NOT NULL,
  `customerID` int NOT NULL,
  `customerRepresentative` varchar(30) NOT NULL,
  `customerContact` varchar(50) NOT NULL,
  `orderNotes` text NOT NULL,
  `orderFinal` int NOT NULL,
  `orderDiscountRate` decimal(13,4) NOT NULL,
  `orderDiscount` decimal(13,4) NOT NULL,
  `orderSubtotal` decimal(13,4) NOT NULL,
  `orderTotal` decimal(13,4) NOT NULL,
  PRIMARY KEY (`orderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4

*/

final class Order extends ORM {

	public $orderID;
	public $siteID;
	public $creator;
	public $created;
	public $updated;
	public $deleted;
	public $orderDate;
	public $status;
	public $projectName;
	public $customerOrderNo;
	public $customerID;
	public $customerRepresentative;
	public $customerContact;
	public $orderNotes;
	public $orderFinal;
	public $orderDiscountRate;
	public $orderDiscount;
	public $orderSubtotal;
	public $orderTotal;

	public function __construct($orderID = null) {

		$this->orderID = 0;
		$this->siteID = $_SESSION['siteID'];
		$this->creator = $_SESSION['userID'];
		$this->created = date('Y-m-d H:i:s');
		$this->updated = null;
		$this->deleted = 0;
		$this->orderDate = date('Y-m-d');
		$this->status = '';
		$this->projectName = '';
		$this->customerOrderNo = '';
		$this->customerID = 0;
		$this->customerRepresentative = '';
		$this->customerContact = '';
		$this->orderNotes = '';
		$this->orderFinal = 0;
		$this->orderDiscountRate = 0;
		$this->orderDiscount = 0;
		$this->orderSubtotal = 0;
		$this->orderTotal = 0;

		if ($orderID) {

			$nucleus = Nucleus::getInstance();

			$query = "SELECT * FROM order_Order WHERE orderID = :orderID LIMIT 1";
			$statement = $nucleus->database->prepare($query);
			$statement->execute(array(':orderID' => $orderID));
			if ($row = $statement->fetch()) {
				foreach ($row AS $key => $value) {
					if (isset($this->$key)) {
						$this->$key = $value;
					}
				}
			}
		}

	}

	public function markAsDeleted() {

		$sodl = new OrderDetailList($this->orderID);
		$details = $sodl->details();
		foreach ($details AS $orderDetailID) {
			$sod = new OrderDetail($orderDetailID);
			$sod->markAsDeleted();
		}

		$dt = new DateTime();
		$this->deleted = 1;
		$this->updated = $dt->format('Y-m-d H:i:s');
		$cond = array('orderID' => $this->orderID);
		Order::update($this, $cond, true, false, 'order_');

	}

}

final class OrderList {

	private $orders;

	public function __construct(OrderListArguments $arg) {

		$nucleus = Nucleus::getInstance();

		$whereClause = array();
		$whereClause[] = 'order_Order.siteID = :siteID';
		$whereClause[] = 'order_Order.deleted = 0';

		if (!is_null($arg->orderDateFrom)) { $whereClause[] = 'order_Order.orderDate >= :orderDateFrom'; }
		if (!is_null($arg->orderDateTo)) { $whereClause[] = 'order_Order.orderDate <= :orderDateTo'; }
		if (!is_null($arg->orderID)) { $whereClause[] = 'order_Order.orderID = :orderID'; }
		if (!is_null($arg->status)) { $whereClause[] = 'order_Order.status = :status'; }
		if (!is_null($arg->projectName)) { $whereClause[] = 'order_Order.projectName = :projectName'; }
		if (!is_null($arg->customerOrderNo)) { $whereClause[] = 'order_Order.customerOrderNo = :customerOrderNo'; }
		if (!is_null($arg->customerID)) { $whereClause[] = 'order_Order.customerID = :customerID'; }
		if (!is_null($arg->orderNotes)) { $whereClause[] = 'order_Order.orderNotes LIKE concat(:orderNotes,"%")'; }

		if (!is_numeric($arg->productID)) {

			$query = 'SELECT orderID FROM order_Order WHERE ' . implode(' AND ',$whereClause) . ' ORDER BY order_Order.orderID DESC';

		} else {

			$whereClause[] = 'order_OrderDetail.deleted = 0';
			$whereClause[] = 'order_OrderDetail.orderDetailProductID = :productID';

			$query = '
				SELECT DISTINCT(order_Order.orderID) FROM order_Order RIGHT JOIN order_OrderDetail
				ON order_Order.orderID = order_OrderDetail.orderID
				WHERE ' . implode(' AND ',$whereClause) . ' ORDER BY order_Order.orderDate DESC
			';

		}

		$statement = $nucleus->database->prepare($query);
		$statement->bindParam(':siteID', $_SESSION['siteID'], PDO::PARAM_INT);
		if (!is_null($arg->orderDateFrom)) { $statement->bindParam(':orderDateFrom', $arg->orderDateFrom); }
		if (!is_null($arg->orderDateTo)) { $statement->bindParam(':orderDateTo', $arg->orderDateTo); }
		if (!is_null($arg->orderID)) { $statement->bindParam(':orderID', $arg->orderID, PDO::PARAM_INT); }
		if (!is_null($arg->status)) { $statement->bindParam(':status', $arg->status); }
		if (!is_null($arg->projectName)) { $statement->bindParam(':projectName', $arg->projectName); }
		if (!is_null($arg->customerOrderNo)) { $statement->bindParam(':customerOrderNo', $arg->customerOrderNo); }
		if (!is_null($arg->customerID)) { $statement->bindParam(':customerID', $arg->customerID, PDO::PARAM_INT); }
		if (!is_null($arg->orderNotes)) { $statement->bindParam(':orderNotes', $arg->orderNotes); }
		if ($arg->productID != 0) { $statement->bindParam(':productID', $arg->productID); }

		$statement->execute();

		$this->orders = array();
		while ($row = $statement->fetch()) {
			$this->orders[] = $row['orderID'];
		}

	}

	public function orders() {

		return $this->orders;

	}
}

final class OrderListArguments {

	public $orderDateFrom;
	public $orderDateTo;
	public $orderID;
	public $status;
	public $projectName;
	public $customerOrderNo;
	public $customerID;
	public $productID;
	public $orderNotes;

	public function __construct() {

		$startDate = new DateTime();
		$startDate->modify('-1 month');
		$defaultStartDate = $startDate->format('Y-m-d');

		$endDate = new DateTime();
		$endDate->modify('+1 month');
		$defaultEndDate = $endDate->format('Y-m-d');

		$this->orderDateFrom = $defaultStartDate;
		$this->orderDateTo = $defaultEndDate;
		$this->orderID = null;
		$this->status = null;
		$this->projectName = null;
		$this->customerOrderNo = null;
		$this->customerID = null;
		$this->productID = null;
		$this->orderNotes = null;
	}

}

/*

CREATE TABLE `order_OrderDetail` (
  `orderDetailID` int NOT NULL AUTO_INCREMENT,
  `siteID` int NOT NULL,
  `creator` int NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `deleted` int NOT NULL,
  `orderID` int NOT NULL,
  `orderDetailProductID` int NOT NULL,
  `orderDetailProductDescription` varchar(255) NOT NULL,
  `orderDetailQuantity` int NOT NULL,
  `orderDetailProductUnitPrice` decimal(13,4) NOT NULL,
  `orderDetailPrice` decimal(13,4) NOT NULL,
  PRIMARY KEY (`orderDetailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4

*/

final class OrderDetail extends ORM {

    public $orderDetailID;
    public $siteID;
    public $creator;
    public $created;
    public $updated;
    public $deleted;
    public $orderID;
    public $orderDetailProductID;
    public $orderDetailProductDescription;
    public $orderDetailQuantity;
    public $orderDetailProductUnitPrice;
    public $orderDetailPrice;

    public function __construct($orderDetailID = null) {

        $this->orderDetailID = 0;
        $this->siteID = $_SESSION['siteID'];
        $this->creator = $_SESSION['userID'];
        $this->created = date('Y-m-d H:i:s');
        $this->updated = null;
        $this->deleted = 0;
        $this->orderID = 0;
        $this->orderDetailProductID = 0;
        $this->orderDetailProductDescription = '';
        $this->orderDetailQuantity = 0;
        $this->orderDetailProductUnitPrice = 0;
        $this->orderDetailPrice = 0;

        if ($orderDetailID) {

            $nucleus = Nucleus::getInstance();

            $query = "SELECT * FROM order_OrderDetail WHERE orderDetailID = :orderDetailID LIMIT 1";
            $statement = $nucleus->database->prepare($query);
            $statement->execute(array(':orderDetailID' => $orderDetailID));
            if ($row = $statement->fetch()) {
                foreach ($row AS $key => $value) {
                    if (isset($this->$key)) {
                        $this->$key = $value;
                    }
                }
            }

        }

    }

    public function markAsDeleted() {

		$this->updated = date('Y-m-d H:i:s');
		$this->deleted = 1;
		$conditions = array('orderDetailID' => $this->orderDetailID);
		self::update($this, $conditions, true, false, 'order_');

	}

	public function create() {

		$orderDetailID = OrderDetail::insert($this, true, 'order_');

	}

}

final class OrderDetailList {

	private $details;

	public function __construct($orderID = null) {

		$this->details = array();

		if ($orderID) {

			$nucleus = Nucleus::getInstance();

			$whereClause = array();
			$whereClause[] = 'siteID = :siteID';
			$whereClause[] = 'deleted = 0';
			$whereClause[] = 'orderID = :orderID';

			$query = 'SELECT orderDetailID FROM order_OrderDetail WHERE ' . implode(' AND ',$whereClause) . ' ORDER BY orderDetailID ASC';

			$statement = $nucleus->database->prepare($query);
			$statement->bindParam(':siteID', $_SESSION['siteID'], PDO::PARAM_INT);
			$statement->bindParam(':orderID', $orderID, PDO::PARAM_INT);

			$statement->execute();

			while ($row = $statement->fetch()) { $this->details[] = $row['orderDetailID']; }

		}

	}

	public function details() {

		return $this->details;

	}

}

?>