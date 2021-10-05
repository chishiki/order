
DROP TABLE IF EXISTS `order_Order`;

CREATE TABLE `order_Order` (
  `salesOrderID` int(12) NOT NULL AUTO_INCREMENT,
  `siteID` int(12) NOT NULL,
  `creator` int(12) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `deleted` int(1) NOT NULL,
  `salesOrderDate` date NOT NULL,
  `status` varchar(10) NOT NULL,
  `estimateID` int(12) NOT NULL,
  `projectName` varchar(100) NOT NULL,
  `customerOrderNo` varchar(30) NOT NULL,
  `customerID` int(12) NOT NULL,
  `customerPersonInCharge` varchar(30) NOT NULL,
  `customerContact` varchar(50) NOT NULL,
  `salesOrderNotes` text NOT NULL,
  `salesOrderFinal` int(1) NOT NULL,
  `salesOrderDiscount` decimal(13,4) NOT NULL,
  `salesOrderDiscountYen` decimal(13,4) NOT NULL,
  `salesOrderSubtotalYen` decimal(13,4) NOT NULL,
  `salesOrderTotalYen` decimal(13,4) NOT NULL,
  PRIMARY KEY (`salesOrderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



DROP TABLE IF EXISTS `order_OrderDetail`;

CREATE TABLE `order_OrderDetail` (
  `salesOrderDetailID` int(12) NOT NULL AUTO_INCREMENT,
  `siteID` int(12) NOT NULL,
  `creator` int(12) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `deleted` int(1) NOT NULL,
  `salesOrderID` int(12) NOT NULL,
  `salesOrderDetailProductID` int(12) NOT NULL,
  `salesOrderDetailProductDescription` varchar(255) NOT NULL,
  `salesOrderDetailQuantity` int(12) NOT NULL,
  `salesOrderDetailProductUnitPriceDollar` decimal(13,4) NOT NULL,
  `salesOrderDetailProductUnitPriceYen` decimal(13,4) NOT NULL,
  `salesOrderDetailYen` decimal(13,4) NOT NULL,
  `salesOrderDetailDollar` decimal(13,4) NOT NULL,
  `descriptionOfPriceList` varchar(100) NOT NULL,
  PRIMARY KEY (`salesOrderDetailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
