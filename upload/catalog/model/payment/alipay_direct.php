<?php 
class ModelPaymentAlipayDirect extends Model {
  	public function getMethod($address) {
		$this->load->language('payment/alipay_direct');
		
		if ($this->config->get('alipay_direct_status')) {
      		$status = TRUE;
      	} else {
			$status = FALSE;
		}
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'         => 'alipay_direct',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('alipay_direct_sort_order')
      		);
    	}
	
    	return $method_data;
  	}
}
?>