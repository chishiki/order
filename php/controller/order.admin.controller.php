<?php

final class OrderAdminController implements StateControllerInterface {
	
	private $loc;
	private $input;
	private $modules;
	private $errors;
	private $messages;
	
	public function __construct($loc, $input, $modules) {
		
		$this->loc = $loc;
		$this->input = $input;
		$this->modules = $modules;
		$this->errors = array();
		$this->messages =  array();
		
	}
	
	public function setState() {
		
		$loc = $this->loc;
		$input = $this->input;
		
		if ($loc[0] == 'order' && $loc[1] == 'sales-orders') {
			
			if (isset($this->input['form-name']) && $this->input['form-name'] == 'sales-order-search') {
				$sosf = new OrderListArguments();
				foreach ($this->input AS $key => $value) { if (isset($sosf->$key)) { $_SESSION['salesOrderListArguments'][$key] = $value; } }
			}
			if ($loc[2] == 'create' && !empty($input)) { $this->createOrder($input); }
			if ($loc[2] == 'update' && ctype_digit($loc[3]) && !empty($input)) { $this->updateOrder($loc[3], $input); }
			if ($loc[2] == 'update' && ctype_digit($loc[3]) && $loc[4] == 'files') {
				if (isset($_FILES['perihelionFiles'])) { $this->uploadFiles($loc[3], $_FILES['perihelionFiles']); }
				if ($loc[5] == 'delete' && ctype_digit($loc[6])) { $this->deleteFiles($loc[3], $loc[6]); }
			}
			if ($loc[2] == 'delete' && ctype_digit($loc[3])) { $this->deleteOrder($loc[3]); }
			
		}
		
	}
	
	private function createOrder($input) {

		if (isset($input['estimateID'])) {

			$estimateID = $input['estimateID'];

			$so = new Order();
			$e = new Estimate($estimateID);
			$so->estimateID = $e->estimateID;
			$so->projectName = $e->projectName;
			$so->customerID = $e->customerID;
			$so->customerRepresentative = $e->customerRepresentative;
			$so->customerContact = $e->customerContact;
			$so->salesOrderDiscount = $e->estimateDiscount;
			$so->salesOrderDiscountYen = $e->estimateDiscountYen;
			$so->salesOrderSubtotalYen = $e->estimateSubtotalYen;
			$so->salesOrderTotalYen = $e->estimateTotalYen;

			$salesOrderID = Order::insert($so, true, 'order_');

			$edl = new EstimateDetailList($estimateID);
			$estimateDetails = $edl->details();

			foreach ($estimateDetails AS $estimateDetailID) {

				$ed = new EstimateDetail($estimateDetailID);
				foreach ($ed AS $key => $value) { $ed->$key = htmlspecialchars($value); }

				$product = new Product($ed->estimateDetailProductID);
				foreach ($product AS $key => $value) { $product->$key = htmlspecialchars($value); }

				$sod = new OrderDetail();
				$sod->salesOrderID = $salesOrderID;
				$sod->salesOrderDetailProductID = $ed->estimateDetailProductID;
				$sod->salesOrderDetailProductDescription = $ed->estimateDetailProductDescription;
				$sod->salesOrderDetailQuantity = $ed->estimateDetailQuantity;
				$sod->salesOrderDetailProductUnitPriceYen = round($ed->estimateDetailProductUnitPriceYen, 0, PHP_ROUND_HALF_UP);
				$sod->descriptionOfPriceList = $product->descriptionOfPriceList;
				$sod->salesOrderDetailProductUnitPriceDollar = number_format($ed->estimateDetailProductUnitPriceDollar, 2, '.', '');
				$sod->salesOrderDetailYen = round($ed->estimateDetailYen, 0, PHP_ROUND_HALF_UP);
				$sod->salesOrderDetailDollar = number_format($ed->estimateDetailDollar, 2, '.', '');

				$sod->create();

			}

			$successURL = '/' . Lang::prefix() . 'order/sales-orders/update/' . $salesOrderID . '/';
			header("Location: $successURL");

		}

		if (isset($input['customerID'])) {

			$so = new Order();
			$so->customerID = $input['customerID'];
			$salesOrderID = Order::insert($so, true, 'order_');
			$successURL = '/' . Lang::prefix() . 'order/sales-orders/update/' . $salesOrderID . '/';
			header("Location: $successURL");

		}

	}
	
	private function updateOrder($salesOrderID, $input) {

		if (!isset($input['reserve-secured-inventory']) && !isset($input['manage-reserved-inventory'])) {

			$val = array();

			$val['salesOrderSubtotalYen'] = 0;
			if (!empty($input['salesOrderDetails']['salesOrderDetailYen']['update'])) {
				foreach($input['salesOrderDetails']['salesOrderDetailYen']['update'] AS $x => $lineItemYen) {
					$val['salesOrderSubtotalYen'] += $lineItemYen;
				}
			}
			if (!empty($input['salesOrderDetails']['salesOrderDetailYen']['create'])) {
				foreach($input['salesOrderDetails']['salesOrderDetailYen']['create'] AS $y => $lineItemYen) {
					$val['salesOrderSubtotalYen'] += $lineItemYen;
				}
			}
			if ($val['salesOrderSubtotalYen'] != $input['salesOrderSubtotalYen']) {
				$this->errors[] = array('salesOrderSubtotalYen' => Lang::getLang('theEstimateSubtotalAppearsInaccuratePleaseTryAgain'));
			}

			if ($input['salesOrderDiscount'] != 0) {

				$val['salesOrderDiscount'] = $input['salesOrderDiscount'];
				$val['salesOrderDiscountYenBeforeRoundingDown'] = $input['salesOrderSubtotalYen'] * ($input['salesOrderDiscount']/100);
				$val['salesOrderDiscountYen'] = floor($val['salesOrderDiscountYenBeforeRoundingDown']/100) * 100;

				if ($val['salesOrderDiscountYen'] != $input['salesOrderDiscountYen']) {
					$this->errors[] = array('salesOrderDiscountYen' => Lang::getLang('theEstimateDiscountAppearsInaccuratePleaseTryAgain'));
				}

				if ($input['salesOrderTotalYen'] + $val['salesOrderDiscountYen'] != $input['salesOrderSubtotalYen']) {
					$this->errors[] = array('salesOrderTotalYen' => Lang::getLang('theEstimateTotalAppearsInaccuratePleaseTryAgain'));
				}

			} else {

				if ($input['salesOrderTotalYen'] != $input['salesOrderSubtotalYen']) {
					$this->errors[] = array('salesOrderTotalYen' => Lang::getLang('theEstimateTotalAppearsInaccuratePleaseTryAgain'));
				}

			}

		}

		if (empty($this->errors)) {

			if (isset($input['reserve-secured-inventory'])) {

				if (isset($input['sodReserveSecuredInventory'])) {

					// THIS ASSUMES THAT THERE ARE INVENTORY ITEMS WITH SALES ORDER DETAILS ATTACHED THAT ARE NOT RESERVED
					// ALSO ASSUMES THAT THERE ARE INVENTORY ITEMS THAT ARE SECURED THAT WE WILL NOW RESERVE FOR THE ABOVE SALES ORDER DETAILS

					// EXAMPLE INPUT
					/*
					Array(
						[reserve-secured-inventory] => hdxrkjbqzu
						[reserve-secured-inventory-limit] => 3
						[salesOrderDetailID] => 3067
						[sodReserveSecuredInventory] => Array(
							[3067] => Array (
								[0] => 1145
								[1] => 1146
							)
						)
					)
					*/

					$reservedSecuredInventoryLimit = $input['reserve-secured-inventory-limit'];
					$salesOrderDetailID = $input['salesOrderDetailID'];
					$toBeReserved = $input['sodReserveSecuredInventory'][$salesOrderDetailID];
					$toBeReservedCount = count($toBeReserved);

					$sod = new OrderDetail($salesOrderDetailID);

					// how many items in inventory have sales order only (are able to be reserved)
					$tia = new TheInventoryArguments();
					$tia->salesOrderOnly($salesOrderDetailID);
					$til = new TheInventoryList($tia);
					$reservable = $til->inventory();
					$reservableCount = $til->inventoryCount();

					// IF THE CURRENT SECURED AMOUNT AVAILABLE IS LESS THAN THE DECLARED LIMIT THEN LOWER THE LIMIT eg
					// --> other actions occur
					// --> after opening the Sales Order update page
					// --> but before submitting it
					$tia = new TheInventoryArguments();
					$tia->isSecured($sod->salesOrderDetailProductID);
					$til = new TheInventoryList($tia);
					$totalSecured = $til->inventoryCount();
					if ($reservedSecuredInventoryLimit > $totalSecured) {
						$reservedSecuredInventoryLimit = $totalSecured;
					}

					// inventory to be updated must not exceed:
					// --> secured count ($reservedSecuredInventoryLimit)
					// --> reservable count ($reservableCount)
					// --> amount selected to be reserved ($toBeReservedCount)
					for ($x = 0; $x < $reservedSecuredInventoryLimit && $x < $reservableCount && $x < $toBeReservedCount; $x++) {

						$oldInventory = new Inventory($reservable[$x]);
						$newInventory = new Inventory($toBeReserved[$x]);

						if ($newInventory->hasOrder()) {
							$this->errors[] = array('errorReservingInventory' => Lang::getLang('thereWasAnErrorReservingInventory'));
						} else {
							$oldInventory->unreserve();
							$newInventory->reserve($salesOrderDetailID);
						}


					}

				}

			} elseif (isset($input['manage-reserved-inventory'])) {

				if (isset($input['manage-reserved-inventory-required-qty'])) {

					$salesOrderID = $input['salesOrderID'];
					$salesOrderDetailID = $input['salesOrderDetailID'];
					$requiredQty = $input['manage-reserved-inventory-required-qty'];

					$i = 0;
					$new = array();
					$old = array();

					if (isset($input['sodReserveInventory']) && isset($input['sodReserveInventoryOriginal'])) {
						foreach ($input['sodReserveInventory'] as $salesOrderDetailID => $inventory) {
							foreach ($inventory AS $inventoryID) {
								$i++;
								$new[] = $inventoryID;
							}
						}
						foreach ($input['sodReserveInventoryOriginal'] as $salesOrderDetailID => $inventory) {
							foreach ($inventory AS $inventoryID) {
								$old[] = $inventoryID;
							}
						}
						$unreserve = array_diff($old, $new);
						$reserve = array_diff($new, $old);
					}

					if ($i != $requiredQty) {

						$this->errors[] = array('reserveInventory' => Lang::getLang('reservedInventoryQuantityCannotChangeHere'));

					} else {


						foreach ($unreserve AS $inventoryID) {
							$iOld = new Inventory($inventoryID);
							$iOld->salesOrderID = 0;
							$iOld->salesOrderDetailID = 0;
							$cond = array('inventoryID' => $inventoryID);
							Inventory::update($iOld,$cond,true,false,'order_');
						}

						foreach ($reserve AS $inventoryID) {
							$iNew = new Inventory($inventoryID);
							$iNew->salesOrderID = $salesOrderID;
							$iNew->salesOrderDetailID = $salesOrderDetailID;
							$iNew->secured = 0;
							$iNew->securedExpirationDate = '0000-00-00 00:00:00';
							$cond = array('inventoryID' => $inventoryID);
							Inventory::update($iNew,$cond,true,false,'order_');
						}

					}

				}

			} else {

				$dt = new DateTime();

				$salesOrder = new Order($salesOrderID);
				$salesOrder->updated = $dt->format('Y-m-d H:i:s');

				foreach ($input AS $property => $value) {
					if (isset($salesOrder->$property)) {
						if ($property == 'salesOrderNotes') {
							$salesOrder->$property = strip_tags($value);
						} else {
							$salesOrder->$property = $value;
						}
					}
				}

				$conditions = array('salesOrderID' => $salesOrderID);
				Order::update($salesOrder, $conditions, true, false, 'order_');

				$inputDetails = array();
				if (isset($input['salesOrderDetails'])) { $inputDetails = $input['salesOrderDetails']; }
				$this->processOrderDetails('update', $salesOrderID, $inputDetails);

				$this->messages[] = Lang::getLang('salesOrderUpdateSuccessful');

			}

		}
		
	}
	
	private function uploadFiles($salesOrderID, $files) {

		$this->errors = File::uploadFiles($files,'Order',$salesOrderID);

	}
	
	private function deleteFiles($salesOrderID, $fileID) {
		
		$successURL = "/" . Lang::languageUrlPrefix() . "order/sales-orders/update/" . $salesOrderID . "/files/";
		$fu = new OrderFileUtilities();
		$this->errorArray = $fu->delete($fileID, $successURL);
				
	}
	
	private function deleteOrder($salesOrderID) {

		$so = new Order($salesOrderID);
		$shipmentStatus = $so->shipmentStatus();
		if ($shipmentStatus != 'salesOrderShipmentStatusPending') {
			$this->errors['salesOrder'][] = Lang::getLang('salesOrderNumber') . ' #' . $salesOrderID . ' ' . Lang::getLang('cannotBeDeleted');
		}

		if (empty($this->errors)) {
		        
			$salesOrder = new Order($salesOrderID);
			$salesOrder->markAsDeleted();
			$this->messages[] = Lang::getLang('salesOrderDeleteSuccessful');
		
		}
		
	}

	private function processOrderDetails($type, $salesOrderID, $inputDetails = array()) {
	    
	    $sodl = new OrderDetailList($salesOrderID);
	    $existingDetails = $sodl->details();
	    $updatedDetails = array();
	    
	    if (!empty($inputDetails)) {
	        
	        if (isset($inputDetails['salesOrderDetailID']['update']) && $type == 'update') {
	            
	            
	            $idArray = $inputDetails['salesOrderDetailID']['update'];
	            $numberOfUpdates = count($idArray);
	            
	            for ($u = 0; $u < $numberOfUpdates; $u++) {
	                
	                $salesOrderDetailID = $inputDetails['salesOrderDetailID']['update'][$u];
	                $updatedDetails[] = $salesOrderDetailID;
	                $sod = new OrderDetail($salesOrderDetailID);
	                $originalQuantity = $sod->salesOrderDetailQuantity;
	                $sod->updated = date('Y-m-d H:i:s');
	                $keys = array_keys(get_object_vars($sod));
	                foreach ($keys AS $fieldName) {
	                    if (isset($inputDetails[$fieldName]['update'][$u])) { $sod->$fieldName = $inputDetails[$fieldName]['update'][$u]; }
	                }
	                $conditions = array('salesOrderDetailID' => $salesOrderDetailID);
	                $p = new Product($sod->salesOrderDetailProductID);

	                $sop = new OrderPurchasingPurchaseOrders($salesOrderDetailID);
	                $qtyOnOrder = $sop->quantityOnOrderForThisOrder();
	                
	                $updateErrors = array();
	                if ($sod->salesOrderDetailQuantity < 0) {
	                	$updateErrors['salesOrderDetailQuantity'][] = $p->productName . ' ' . Lang::getLang('quantityCannotBeNegative') . ': ' . Lang::getLang('productWasNotUpdated');
	                }
	                if ($qtyOnOrder > 1 && $originalQuantity != $sod->salesOrderDetailQuantity) {
						$updateErrors['salesOrderDetailQuantity'][] = $p->productName . ' ' . Lang::getLang('quantityCannotBeChangedWhenThereAreAssociatedPurchaseOrders') . ': ' . Lang::getLang('productWasNotUpdated');
					}
	                if ($sod->salesOrderDetailProductUnitPriceDollar < 0) { $updateErrors['salesOrderDetailProductUnitPriceDollar'][] = $p->productName . ' ' . Lang::getLang('dollarUnitPriceCannotBeNegative') . ': ' . Lang::getLang('productWasNotUpdated'); }
	                if ($sod->salesOrderDetailProductUnitPriceYen < 0) { $updateErrors['salesOrderDetailProductUnitPriceYen'][] = $p->productName . ' ' . Lang::getLang('yenUnitPriceCannotBeNegative') . ': ' . Lang::getLang('productWasNotUpdated'); }
	                
	                if (empty($updateErrors)) {
	                	OrderDetail::update($sod, $conditions, true, false, 'order_');
	                } else {
	                	foreach ($updateErrors AS $k => $v) { foreach ($v AS $error) { $this->errors[$k][] = $error; } }
	                }
	                
	            }
	            
	            
	        }
	        
	        if (isset($inputDetails['salesOrderDetailID']['create']) && in_array($type,array('create','update'))) {

	            $idArray = $inputDetails['salesOrderDetailID']['create'];
	            $numberOfCreates = count($idArray);
	            
	            for ($c = 0; $c < $numberOfCreates; $c++) {
	                
	                unset($inputDetails['salesOrderDetailID']['create'][$c]);
	                $sod = new OrderDetail();
	                $keys = array_keys(get_object_vars($sod));
	                foreach ($keys AS $fieldName) {
	                    if (isset($inputDetails[$fieldName]['create'][$c])) { $sod->$fieldName = $inputDetails[$fieldName]['create'][$c]; }
	                }
	                $sod->salesOrderID = $salesOrderID;
	                
	                $p = new Product($sod->salesOrderDetailProductID);
	                
	                $createErrors = array();
	                if ($sod->salesOrderDetailQuantity < 0) { $createErrors['salesOrderDetailQuantity'][] = $p->productName . ' ' . Lang::getLang('quantityCannotBeNegative') . ': ' . Lang::getLang('productWasNotAddedToOrder'); }
	                if ($sod->salesOrderDetailProductUnitPriceDollar < 0) { $createErrors['salesOrderDetailProductUnitPriceDollar'][] = $p->productName . ' ' . Lang::getLang('dollarUnitPriceCannotBeNegative') . ': ' . Lang::getLang('productWasNotAddedToOrder'); }
	                if ($sod->salesOrderDetailProductUnitPriceYen < 0) { $createErrors['salesOrderDetailProductUnitPriceYen'][] = $p->productName . ' ' . Lang::getLang('yenUnitPriceCannotBeNegative') . ': ' . Lang::getLang('productWasNotAddedToOrder'); }
	                
	                if (empty($createErrors)) {
	                	$sod->create();
	                } else {
	                	foreach ($createErrors AS $k => $v) { foreach ($v AS $error) { $this->errors[$k][] = $error; } }
	                }
	                
	            }
	            
	        }
	        
	    }
	    
	    $detailsToDelete = array_diff($existingDetails, $updatedDetails);

	    foreach ($detailsToDelete AS $salesOrderDetailID) {
	        
	        $sod = new OrderDetail($salesOrderDetailID);
	        $sod->markAsDeleted();

	    }
	    
	}

	public function getErrors() {
		return $this->errors;
	}
	
	public function getMessages() {
		return $this->messages;
	}
	
}

?>