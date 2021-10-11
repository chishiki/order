
DROP TABLE IF EXISTS `order_Order`;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



DROP TABLE IF EXISTS `order_OrderDetail`;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
