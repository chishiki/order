


DROP TABLE IF EXISTS `order_Customer`;

CREATE TABLE `order_Customer` (
  `customerID` int(12) NOT NULL AUTO_INCREMENT,
  `siteID` int(12) NOT NULL,
  `creator` int(12) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `deleted` int(1) NOT NULL,
  `customerNameEnglish` varchar(100) NOT NULL,
  `customerNameJapanese` varchar(100) NOT NULL,
  `customerPersonInCharge` varchar(100) NOT NULL,
  `customerRepresentativeDepartment` varchar(100) NOT NULL,
  `customerRepresentativeTitle` varchar(100) NOT NULL,
  `customerHonorarySuffix` varchar(10) NOT NULL,
  `customerNotes` varchar(255) NOT NULL,
  `customerTelephone` varchar(50) NOT NULL,
  `customerFax` varchar(50) NOT NULL,
  `customerEmail` varchar(50) NOT NULL,
  `priceLevel` int(2) NOT NULL,
  PRIMARY KEY (`customerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



DROP TABLE IF EXISTS `order_SalesOrder`;

CREATE TABLE `order_SalesOrder` (
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



DROP TABLE IF EXISTS `order_SalesOrderDetail`;

CREATE TABLE `order_SalesOrderDetail` (
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



DROP TABLE IF EXISTS `order_Product`;

CREATE TABLE `order_Product` (
  `productID` int(12) NOT NULL AUTO_INCREMENT,
  `siteID` int(12) NOT NULL,
  `creator` int(12) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `deleted` int(1) NOT NULL,
  `productName` varchar(100) NOT NULL,
  `productNumber` int(20) NOT NULL,
  `productDescriptionEnglish` varchar(255) NOT NULL,
  `productDescriptionJapanese` varchar(255) NOT NULL,
  `productUnitPriceYen1` decimal(13,4) NOT NULL,
  `productUnitPriceYen2` decimal(13,4) NOT NULL,
  `productUnitPriceYen3` decimal(13,4) NOT NULL,
  `productUnitPriceYen4` decimal(13,4) NOT NULL,
  `productUnitPriceDollar` decimal(13,4) NOT NULL,
  `usCustomerUnitPrice` decimal(13,4) NOT NULL,
  `descriptionOfPriceList` varchar(100) NOT NULL,
  `productNotes` text NOT NULL,
  `productUsesSerialNumber` int(1) NOT NULL,
  `productType` varchar(10) NOT NULL,
  PRIMARY KEY (`productID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


