<?php

final class OrderAPI {
		
	    private $loc;
	    private $input;
	    
	    public function __construct($loc, $input) {
			
	        $this->loc = $loc;
	        $this->input = $input;
			
		}
		
		public function response() {

	    	if ($this->loc[0] == 'api' && $this->loc[1] == 'order') {

				$response = '{"api":"order"}';
				return $response;

			}

		}
		
	}

?>