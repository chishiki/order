<?php

final class OrderView {
	
	private $loc;
	private $input;
	private $modules;
	private $errors;
	private $messages;
	
	public function __construct($loc, $input, $modules = array(), $errors = array(), $messages = array()) {
		
		$this->loc = $loc;
		$this->input = $input;
		$this->modules = $modules;
		$this->errors = $errors;
		$this->messages = $messages;
		
	}

	public function orderSearchForm(OrderListArguments $arg) {

	    foreach ($arg AS $key => $value) { $arg->$key = htmlspecialchars($value); }
	    
	    $dv = new CustomerView($this->loc, $this->input, $this->modules, $this->errors, $this->messages);
	    $pv = new ProductView($this->loc, $this->input, $this->modules, $this->errors, $this->messages);

		$dmp = new CustomerModalParameters();
		$dmp->customerID = $arg->customerID;
		$dmp->size = 'sm';
		$dmp->fieldName = 'customerID';
		$dmp->includeModal = true;
		$dmp->modalKey = 'customer_modal_order_search';

		$pmp = new ProductModalParameters();
		$pmp->productID = $arg->productID;
		$pmp->size = 'sm';

		$form = '

			<form id="order_search_form" method="post" action="/' . Lang::prefix() . 'order/admin/">

				<div class="form-row">

					<!-- 受注日 -->
					<div class="form-group col-6 col-sm-4">
						<label for="orderDateFrom">' . Lang::getLang('orderStartDate') . '</label>
						<input type="date" class="form-control form-control-sm" name="orderDateFrom" value="' . $arg->orderDateFrom . '">
					</div>
					
					<div class="form-group col-6 col-sm-4">
						<label for="orderDateTo">' . Lang::getLang('orderEndDate') . '</label>
						<input type="date" class="form-control form-control-sm" name="orderDateTo" value="' . $arg->orderDateTo . '">
					</div>

					<!-- Status -->
					<div class="form-group col-12 col-sm-4">
						<label for="status">' . Lang::getLang('status') . '</label>
						' . $this->orderStatusSelect($arg->status, true, 'sm') . '
					</div>

				</div>

				<div class="form-row">
					
					<!-- 顧客名 -->
					<div class="form-group col-12 col-md-4">
						<label for="customerName">' . Lang::getLang('customerName') . '</label>
                        ' . $dv->customerListAutocomplete($dmp) . '
					</div>

					<!-- 顧客注文番号 -->
					<div class="form-group col-12 col-md-4">
						<label for="customerOrderNo">' . Lang::getLang('customerOrderNo') . '</label>
						<input type="text" class="form-control form-control-sm" name="customerOrderNo" value="' . $arg->customerOrderNo . '">
					</div>

					<!-- 製品名 -->
					<div class="form-group col-12 col-md-4">
						<label for="productName">' . Lang::getLang('productName') . '</label>
						' . $pv->productListAutocomplete($pmp) . '
					</div>
					
				</div>

				<!-- 検索ボタンなど -->
				<div class="form-row">
					<div class="form-group col-6 col-sm-4 col-md-3 col-lg-2">
						<button type="submit" name="order-admin-search-reset" class="btn btn-block btn-outline-secondary btn-reset enter-trigger">' . Lang::getLang('reset') . '</button>
					</div>
					<div class="form-group col-6 col-sm-4 offset-sm-4 col-md-3 offset-md-6 col-lg-2 offset-lg-8">
						<button type="submit" name="order-admin-search" class="btn btn-block btn-outline-primary enter-trigger">' . Lang::getLang('search') . '</button>
					</div>
				</div>

			</form>
		';
		
		$card = new CardView('order_search_form', array('container-fluid','mb-4'), '', array('col-12'), Lang::getLang('ordersSearch'), $form, true);
		return $card->card();
		
	}

	public function orderForm($type, $orderID = null) {

		$form = $this->orderFormNavTabs();

			$order = new Order($orderID);

			if (!empty($this->input)) {
				foreach($this->input AS $key => $value) { if(isset($order->$key)) { $order->$key = htmlspecialchars($value); } }
			}
			$d = new Customer($order->customerID);

			$deleteButton = '';
			if ($type == 'update') {
				$deleteButton = '<button type="button" class="btn btn-outline-danger" disabled><span class="far fa-trash-alt"></span> ' . Lang::getLang('delete') . '</button>';
			}

			$form .= '
			
			<form id="order_form_' . ucfirst($type) . '" method="post" action="/' . Lang::prefix() . 'order/admin/update/' . $orderID .'/">

				<input type="hidden" name="orderID" value="' . ($orderID?$orderID:'0') . '">

				<div class="form-row">

					<div class="form-group col-12 col-sm-3 col-md-3">
						<label for="orderDate">' . Lang::getLang('orderDate') . '</label>
						<input type="date" class="form-control form-control-sm" name="orderDate" id="orderDate" value="' . $order->orderDate . '">
					</div>

					<div class="form-group col-12 col-sm-6 col-md-6">
						<label for="projectName">' . Lang::getLang('projectName') . '</label>
						<input type="text" class="form-control form-control-sm" name="projectName" id="projectName" value="' . $order->projectName . '">
					</div>

				</div>

				<div class="form-row">

					<div class="form-group col-9 col-lg-3">
						<label for="customerID">' . Lang::getLang('customerName') . '</label>
						<input type="hidden" name="customerID" id="customerID" value="' . $order->customerID . '">
                        <input type="text" class="form-control form-control-sm" value="' . ($order->customerID?$d->name():'') . '" tabindex="-1" disabled="true">
					</div>

					<div class="form-group col-3 col-lg-1">
						<label for="priceLevel">' . Lang::getLang('priceLevel') . '</label>
						<input type="hidden" id="customer_price_level" value="' . $d->priceLevel . '">
                        <input type="text" class="form-control form-control-sm" value="' . $d->priceLevel . '" tabindex="-1" disabled="true">
					</div>

					<div class="form-group col-12 col-sm-4 col-lg-3">
						<label for="customerRepresentative">' . Lang::getLang('customerRepresentative') . '</label>
						<input type="text" class="form-control form-control-sm" name="customerRepresentative" id="customerRepresentative" value="' . $order->customerRepresentative . '">
					</div>

					<div class="form-group col-12 col-sm-4 col-lg-3">
						<label for="customerContact">' . Lang::getLang('customerContact') . '</label>
						<input type="text" class="form-control form-control-sm" name="customerContact" id="customerContact" value="' . $order->customerContact . '">
					</div>

					<div class="form-group col-12 col-sm-4 col-lg-2">
						<label for="customerOrderNo">' . Lang::getLang('customerOrderNo') . '</label>
						<input type="text" class="form-control form-control-sm" name="customerOrderNo" id="customerOrderNo" value="' . $order->customerOrderNo . '">
					</div>

				</div>

				<div class="form-row">

					<div class="form-group col-12">
						<label for="orderNotes">' . Lang::getLang('orderNotes') . '</label>
						<textarea class="form-control form-control-sm" name="orderNotes" id="orderNotes">' . $order->orderNotes . '</textarea>
					</div>

				</div>

				<hr />

				' . $this->orderDetailListForm($orderID) . '

				<hr />

				<div class="form-row">
				
					<div class="form-group col-12 col-sm-4 offset-sm-4 col-lg-3 offset-lg-6 col-xl-2 offset-xl-8 text-left text-sm-right">
						' .  Lang::getLang('orderSubtotalYen') . '
					</div>
					
					<div class="form-group col-12 col-sm-4 col-lg-3 col-xl-2">
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><div class="input-group-text">&yen;</div></div>
							<input type="text" class="form-control text-right" id="order_subtotal_yen" name="orderSubtotal" value="' . round($order->orderSubtotal, 0, PHP_ROUND_HALF_UP) . '" tabindex="-1" readonly>
						</div>
					</div>
					
				</div>
				
				<hr />
								
				<div class="form-row">

					<div class="form-group col-12 col-sm-4 col-lg-3 offset-lg-3 col-xl-2 offset-xl-6 text-left text-sm-right">
						' .  Lang::getLang('orderDiscount') . '
					</div>
	
					<div class="form-group col-12 col-sm-4 col-lg-3 col-xl-2">
	
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><div class="input-group-text">&#37;</div></div>
							<input type="text" class="form-control text-right" id="order_discount" name="orderDiscount" value="' . round($order->orderDiscount, 0, PHP_ROUND_HALF_UP) . '">
						</div>
	
					</div>
					
					<div class="form-group col-12 col-sm-4 col-lg-3 col-xl-2">
	
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><div class="input-group-text">&yen;</div></div>
							<input type="text" class="form-control text-right" id="order_discount_yen" name="orderDiscount" value="' . round($order->orderDiscount, 0, PHP_ROUND_HALF_UP) . '" readonly>
							<div class="input-group-append"><div class="input-group-text"><span class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="' . Lang::getLang('roundsDownToNearestHundred') . '"></span></div></div>
						</div>
	
					</div>

				</div>

				<hr />

				<div class="form-row">

					<div class="form-group col-12 col-sm-4 offset-sm-4 col-lg-3 offset-lg-6 col-xl-2 offset-xl-8 text-left text-sm-right">
						' .  Lang::getLang('orderTotalAmount') . '
					</div>

					<div class="form-group col-12 col-sm-4 col-lg-3 col-xl-2">

						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><div class="input-group-text">&yen;</div></div>
							<input type="text" class="form-control form-control-sm text-right" id="order_total_yen" name="orderTotal" value="' . round($order->orderTotal, 0, PHP_ROUND_HALF_UP) . '" tabindex="-1" readonly>
						</div>
	
					</div>

				</div>
				
				<hr />

				<div class="d-flex">
					<div class="mb-2 flex-grow-1">
						<a href="/' . Lang::prefix() . 'order/admin/highlight/' . $orderID . '/" class="btn btn-outline-secondary" role="button"><span class="fas fa-arrow-left" aria-hidden="true"></span> ' . Lang::getLang('returnToOrders') . '</a>
					</div>
					<div class="mb-2">
						<button type="submit" value="update-'. $type . '" class="enter-trigger btn btn-outline-primary"><span class="far fa-save" aria-hidden="true"></span> ' . Lang::getLang('orderSave'). '</button>
						<a href="/' . Lang::prefix() . 'pdf/order/admin/' . $orderID . '/" class="btn btn-outline-info"><span class="far fa-file-pdf"></span> ' . Lang::getLang('print') . '</a>
						' . $deleteButton . '
					</div>
				</div>
					

			</form>
			';

		$header = Lang::getLang('orders'.ucfirst($type)) . ($orderID?' ['. $orderID . ']':'');
		$card = new CardView('order_form_'.$type, array('container-fluid'), '', array('col-12'), $header, $form);
		return $card->card();
		
	}

	public function orderDetailListForm($orderID) {

		$form = '
			<div class="table-responsive">
				<table id="order_detail_list_form" class="table table-bordered table-striped table-sm">
					<thead class="thead-light">
						<tr>
							<th scope="col" class="text-center text-nowrap">' . Lang::getLang('orderDetailProduct') . '</th>
							<th scope="col" class="text-center text-nowrap">' . Lang::getLang('orderDetailProductDescription') . '</th>
							<th scope="col" class="text-center text-nowrap">' . Lang::getLang('orderDetailQuantity') . '</th>
							<th scope="col" class="text-center text-nowrap">' . Lang::getLang('orderDetailProductUnitPrice') . '</th>
                            <th scope="col" class="text-center text-nowrap">' . Lang::getLang('orderDetailPrice') . '</th>
                            <th scope="col" class="text-center text-nowrap">' . Lang::getLang('action') . '</th>
						</tr>
					</thead>
					<tbody> ' . $this->orderDetailListFormRows($orderID) . '</tbody>
				</table>
			</div>
		';

		$form .= '<button id="addOrderDetailRow" type="button" class="enter-trigger btn btn-outline-success"><span class="fas fa-plus" aria-hidden="true"></span> ' . Lang::getLang('addNewRow') . '</button>';
		
		return $form;

	}

	private function orderDetailListFormRows($orderID) {

		$sodl = new OrderDetailList($orderID);
		$details = $sodl->details();

		$rows = '';

		foreach ($details AS $orderDetailID) {

			$d = new OrderDetail($orderDetailID);
			$p = new Product($d->orderDetailProductID);

			$rowClass = 'sales-order-details-row';
			if ($this->loc[4] == 'highlight' && is_numeric($this->loc[5]) && $this->loc[5] == $orderDetailID) { $rowClass .= ' table-success'; }

			$rows .= '
				<tr class="' . $rowClass . '">
					<input type="hidden" name="orderDetails[orderID][update][]" value="' . $d->orderID . '">
					<input type="hidden" name="orderDetails[orderDetailID][update][]" value="' . $d->orderDetailID . '">
					<input type="hidden" name="orderDetails[orderDetailProductID][update][]" value="' . $d->orderDetailProductID . '">
					<th scope="row">' . $p->productName() . '</th>
					<td><input type="text" class="form-control form-control-sm" name="orderDetails[orderDetailProductDescription][update][]" value="' . $d->orderDetailProductDescription . '"></td>
					<td><input type="number" class="form-control form-control-sm text-center" name="orderDetails[orderDetailQuantity][update][]" value="' . $d->orderDetailQuantity . '" min="0" step="1" tabindex="-1" readonly></td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><div class="input-group-text">&dollar;</div></div>
							<input type="text" class="form-control form-control-sm text-right" name="orderDetails[orderDetailProductUnitPriceDollar][update][]" value="' . number_format($d->orderDetailProductUnitPrice,2,'.','') . '" tabindex="-1" readonly>
						</div>
					</td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><div class="input-group-text">&yen;</div></div>
							<input type="text" class="form-control form-control-sm text-right" name="orderDetails[orderDetailPrice][update][]" value="' . number_format($d->orderDetailPrice,0,'.','') . '" tabindex="-1" readonly>
						</div>
					</td>
					<td class="table-action-column text-center">' . $this->orderDetailDeleteButton($orderDetailID) . '</td>
				</tr>
			';

		}

		return $rows;

	}

	public function orderList(OrderListArguments $arg) {
		
		$orderList = new OrderList($arg);
		$orders = $orderList->orders();

		$table = '<div class="table-container">';

			$table .= '
				<div class="form-row">
					<div class="form-group col-12 col-sm-6 offset-sm-6 col-md-4 offset-md-8 col-lg-3 offset-lg-9 col-xl-2 offset-xl-10">
						<a href="/' . Lang::prefix() . 'order/admin/create/" class="btn btn-block btn-outline-success">
						<span class="fas fa-plus"></span> ' . Lang::getLang('createOrder') . '</a>
					</div>
				</div>
			';

			$table .= '';
				if (!empty($orders)) {	
					$table .= '
						<div class="table-responsive">
						<table class="table table-bordered table-striped table-sm small">
							<thead class="thead-light">
								<tr>
									<th scope="col" class="text-center">' . Lang::getLang('orderNumber') . '</th>
									<th scope="col" class="text-center">' . Lang::getLang('orderDate') . '</th>
									<th scope="col" class="text-center">' . Lang::getLang('projectName') . '</th>
									<th scope="col" class="text-center">' . Lang::getLang('customerOrderNo') . '</th>
									<th scope="col" class="text-center">' . Lang::getLang('customerName') . '</th>
									<th scope="col" class="text-center">' . Lang::getLang('orderNotes') . '</th>
									<th scope="col" class="table-action-column text-center">' . Lang::getLang('action') . '</th>
								</tr>
							</thead>
							<tbody>' . $this->orderListRows($orders) . '</tbody>
						</table>
						</div>
					';
				} else {
					$table .= '<hr />' . Lang::getLang('noResults');
				}
				

		$table .= '</div>';

		$card = new CardView('order_list',array('container-fluid'),'',array('col-12'),Lang::getLang('orderList'),$table);
		return $card->card();
		
	}

	public function orderConfirmDelete($orderID) {
	    
	    $order = new Order($orderID);
	    
	    $form = '
			<pre>' . print_r($order,true) . '</pre>
			<form id="order_confirm_delete"">
				<div class="text-right">
					<a href="/' . Lang::prefix() . 'order/admin/delete/' . $orderID . '/" class="btn btn-outline-danger" role="button"><span class="far fa-trash-alt" aria-hidden="true"></span> ' . Lang::getLang('delete') . '</a>
					<a href="/' . Lang::prefix() . 'order/admin/" class="btn btn-outline-secondary" role="button">' . Lang::getLang('cancel') . '</a>
				</div>
			</form>
		';
	    
	    $card = new CardView('order_confirm_delete',array('container-fluid'),'',array('col-12'),Lang::getLang('orderConfirmDelete'),$form);
	    return $card->card();
	    
	}

	public function orderPrint($orderID) {
		
		$order = new Order($orderID);
		$sodl = new OrderDetailList($orderID);
		$details = $sodl->details();
		
		$d = new Customer($order->customerID);
		$ad = new AddressDefault('Customer', $order->customerID, true);
		$addy = new Address($ad->address());
		
		$addyArray = array();
		if (!empty($addy->streetAddress1)) { $addyArray[] = $addy->streetAddress1 . ','; }
		if (!empty($addy->streetAddress2)) { $addyArray[] = $addy->streetAddress2 . ','; }
		if (!empty($addy->city)) { $addyArray[] = $addy->city; }
		if (!empty($addy->state)) { $addyArray[] = $addy->state; }
		if (!empty($addy->postalCode)) { $addyArray[] = $addy->postalCode; }
		if (!empty($addy->country)) { $addyArray[] = $addy->country; }
		$billToString = implode('<br />',$addyArray);

        $doc = '<div style="background:repeating-linear-gradient(-45deg, #000000 0, #000000 20px, #ffffff 20px, #ffffff 40px); margin:0px; padding:0px;">';
            $doc .= '　';
        $doc .= '</div>';

		$doc .= '<div class="inline-block width-100">';
			$doc .= '<div class="inline-block width-50 float-left">';
				$doc .= '<img class="width-300px mb-10px" src="' . Config::read('web.root') . 'satellites/order/assets/images/sales-order-logo.png">';
			$doc .= '</div>';
			$doc .= '<div class="inline-block width-50 float-right text-right font-24 bold">';
				$doc .= "Sales Order - 受注";
			$doc .= '</div>';
		$doc .= '</div>'; 

		$doc .= '<hr class="blue" />';
		
		$doc .= '<div class="inline-block width-100">';

			$doc .= '<div class="inline-block width-33 float-right text-right pt-5 bold">';
				$doc .= '<span class="blue">Order Date:</span> ' . date('Y/m/d', strtotime($order->orderDate));
			$doc .= '</div>';
		
		$doc .= '</div>';

		$doc .= '<div class="inline-block width-100 mb-20 pb-20">';
		
			$doc .= '<div class="inline-block width-50 float-left font-18 bold">';
				$doc .= 'Customer Name: ' . $d->name() . '<br />';
				$doc .= 'Sales Orders No: ' . $order->orderID . '<br />';  #308
				if (!empty($order->customerOrderNo)) { $doc .= 'Customer PO No: ' . $order->customerOrderNo . '<br />' ; }
				if (!empty($order->projectName)) { $doc .= 'Project: ' . $order->projectName . '<br />' ; } #309
			$doc .= '</div>';

			$doc .= '<div class="inline-block width-40 float-right">';
				$doc .= '<span class="blue">Bill To:</span><br />';
				if (!empty($addy->postalCode)) { $doc .= $addy->postalCode . '<br />'; }
				if (!empty($addy->streetAddress1)) { $doc .= $addy->streetAddress1 . '<br />'; }
				if (!empty($addy->streetAddress2)) { $doc .= $addy->streetAddress2 . '<br />'; }
			$doc .= '</div>';
		
		$doc .= '</div>';
		
		$doc .= '<table class="stripes width-100 font-12">';
			$doc .= '<thead>';
				$doc .= '<tr style="border:1px solid #00008b;">';
					#323
					$doc .= '<th class="text-left blue" width="20%">Model</th>';
					$doc .= '<th class="text-center blue" width="5%">Qty</th>';
					$doc .= '<th class="text-left blue" width="40%">Description</th>';
					$doc .= '<th class="text-center blue" width="35%">Price</th>';
				$doc .= '</tr>';
			$doc .= '</thead>';
			$doc .= '<tbody>';
			
			foreach ($details AS $orderDetailID) {
				
				$sod = new OrderDetail($orderDetailID);
				$p = new Product($sod->orderDetailProductID);
				$doc .= '<tr>';
					$doc .= '<td><br><br><br>' . $p->productName() . '<br>　<br>　<br>　<br></td>';
					$doc .= '<td class="text-center red"><br><br><br>' . $sod->orderDetailQuantity . '<br>　<br>　<br>　<br></td>';
					$doc .= '<td><br><br><br>' . $sod->orderDetailProductDescription . '<br>　<br>　<br>　<br></td>';
					$doc .= '<td class="text-right"><br><br><br>¥' . number_format($sod->orderDetailPrice,0) . '<br>　<br>　<br>　<br></td>';
				$doc .= '</tr>';
				
			}
			
			$doc .= '</tbody>';
		$doc .= '</table>';

		if (!empty($order->orderNotes)) {
			$doc .= '<hr class="blue" />';
			$doc .= '<div class="inline-block width-70 float-right mb-20px">';
				$doc .= '<span class="red">NOTE:</span><br /><pre>' . $order->orderNotes . '</pre>';
			$doc .= '</div>';
			$doc .= '<div class="float-clear"></div>';
		}

		if ($order->orderDiscount > 0) {

			# SUBTOTAL
			$doc .= '<div class="text-right">';
				$doc .= 'Subtotal: &yen;' . number_format($order->orderSubtotal,0);
			$doc .= '</div>';
			$doc .= '<div class="float-clear"></div>';

			# DISCOUNT
			if (0 != $order->orderDiscount) {

				$doc .= '<div class="inline-block width-100" style="background-color:#eeeeee;">';

					$doc .= '<div class="inline-block width-50 float-left text-center red">';
						$doc .= '案件特別値引き ' . round($order->orderDiscount) . '&#37;';
					$doc .= '</div>';

					$doc .= '<div class="inline-block width-40 float-right text-right">';
						$doc .= '&yen;' . number_format($order->orderDiscount, 0, '.', ',');
					$doc .= '</div>';

					$doc .= '<div class="float-clear"></div>';

				$doc .= '</div>';

			}

			# TOTAL
			$doc .= '<div class="text-right bold">';
				$doc .= 'Total: &yen;' . number_format($order->orderTotal,0);
			$doc .= '</div>';
			$doc .= '<div class="float-clear"></div>';

		}


		
		return $doc;

	}

	private function orderListRows($orders) {
		
		$rows = '';
		foreach ($orders AS $orderID) {
			
			$order = new Order($orderID);
			foreach ($order AS $key => $value) { $order->$key = htmlspecialchars($value); }
			
			$customer = new Customer($order->customerID);
			foreach ($customer AS $key => $value) { $customer->$key = htmlspecialchars($value); }

			$rowClass = 'sales-order-detail';
			if ($this->loc[2] == 'highlight' && is_numeric($this->loc[3]) && $orderID == $this->loc[3]) { $rowClass .= ' table-success'; }

			$rows .= '<tr id="order_id_' . $orderID . '" class="' . $rowClass . '">';
				$rows .= '<th scope="row" class="text-center">' . $orderID . '</th>';
				$rows .= '<td class="text-center">' . $order->orderDate . '</td>';
				$rows .= '<td class="text-center">' . $order->projectName . '</td>';
				$rows .= '<td class="text-center">' . $order->customerOrderNo . '</td>';
				$rows .= '<td class="text-center">' . $customer->name() . '</td>';
				$rows .= '<td class="text-left">' . $order->orderNotes . '</td>';
				$rows .= '<td class="table-action-column text-center">';
					$rows .= '<a href="/' . Lang::prefix() . 'order/admin/update/' . $orderID . '/" class="btn btn-outline-primary btn-sm mb-2 mb-xl-0"><span class="fas fa-pencil-alt"></span> ' . Lang::getLang('orderEdit') . '</a> '; #161
				$rows .= '</td>';
			$rows .= '</tr>';
			
		}
		
		return $rows;
		
	}

	private function orderFormNavTabs() {
		
		$type = $this->loc[2];
		$orderID = $this->loc[3];
		$subForm = $this->loc[4];

		$filesDisabled = true;
		$auditDisabled = true;
		$orderURL = '#';
		$filesURL = '#';
		$auditURL = '#';
		$activeTab = 'orders';

		if ($type == 'update') {
			$filesDisabled = false;
			$auditDisabled = false;
			$orderURL = '/' . Lang::prefix() . 'order/admin/update/' . $orderID . '/';
			$filesURL = $orderURL . 'files/';
			$auditURL = $orderURL . 'audit/';
			if ($subForm == 'files') { $activeTab = 'files'; }
			// if ($subForm == 'audit') { $activeTab = 'audit'; }
		}
		
		$t = '<ul id="order_form_nav_tabs" class="nav nav-tabs">';
			$t .= '<li class="nav-item">';
				$t .= '<a class="nav-link' . ($activeTab=='orders'?' active':'') . '" href="' . $orderURL . '">' . Lang::getLang('salesOrder') . '</a>';
			$t .= '</li>';
			$t .= '<li class="nav-item">';
				$t .= '<a class="nav-link' . ($filesDisabled?' disabled':'') . ($activeTab=='files'?' active':'') . '" href="' . $filesURL . '"' . ($filesDisabled?' tabindex="-1" aria-disabled="true"':'') . '>';
					$t .= Lang::getLang('files');
				$t .= '</a>';
			$t .= '</li>';
			$t .= '<li class="nav-item">';
				$t .= '<a class="nav-link' . ($auditDisabled?' disabled':'') . ($activeTab=='audit'?' active':'') . '" href="' . $auditURL . '"' . ($auditDisabled?' tabindex="-1" aria-disabled="true"':'') . '>';
					$t .= Lang::getLang('audit');
				$t .= '</a>';
			$t .= '</li>';
		$t .= '</ul>';
		
		return $t;
		
	}
	
	private function orderStatusSelect($orderStatus, $filter = false, $size = null) {
		
		$dropdown = '<select class="form-control' . ($size?' form-control-'.$size:'') . '" name="status">';
			if ($filter) { $dropdown .= '<option value="">--</option>'; }
			$dropdown .= '<option value="pending"' . ($orderStatus=='pending'?' selected':'') . '>' . Lang::getLang('orderShipmentStatusPending') . '</option>';
			$dropdown .= '<option value="partial"' . ($orderStatus=='partial'?' selected':'') . '>' . Lang::getLang('orderShipmentStatusPartial') . '</option>';
			$dropdown .= '<option value="complete"' . ($orderStatus=='complete'?' selected':'') . '>' . Lang::getLang('orderShipmentStatusComplete') . '</option>';
		$dropdown .= '</select>';
		return $dropdown;
		
	}

	private function orderDetailDeleteButton($orderDetailID) {

		$deleteButton = '<button class="btn btn-sm btn-outline-danger mb-2 mb-xl-0 sales-order-detail-delete enter-trigger">';
			$deleteButton .= '<span class="far fa-trash-alt" aria-hidden="true"></span> ' . Lang::getLang('delete');
		$deleteButton .= '</button>';

		return $deleteButton;

	}

}

?>