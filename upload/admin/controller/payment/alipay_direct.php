<?php 
class ControllerPaymentAlipayDirect extends Controller {
	private $error = array(); 

	public function index() {
		// 加载语言数据
		$this->load->language('payment/alipay_direct');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		// 处理提交的表单
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			//$this->load->model('setting/setting');
			
			$this->model_setting_setting->editSetting('alipay_direct', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			//$this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment');
			$this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
		}

		// 存储模板需要的数据
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		
		$this->data['entry_seller_email'] = $this->language->get('entry_seller_email');
		$this->data['entry_security_code'] = $this->language->get('entry_security_code');
		$this->data['entry_partner'] = $this->language->get('entry_partner');
		$this->data['entry_currency_code'] = $this->language->get('entry_currency_code');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');	
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['entry_wait_buyer_pay'] = $this->language->get('entry_wait_buyer_pay');
		$this->data['entry_wait_seller_send'] = $this->language->get('entry_wait_seller_send');
		$this->data['entry_wait_buyer_confirm'] = $this->language->get('entry_wait_buyer_confirm');
		$this->data['entry_trade_finished'] = $this->language->get('entry_trade_finished');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
 		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

		if (isset($this->error['secrity_code'])) {
			$this->data['error_secrity_code'] = $this->error['secrity_code'];
		} else {
			$this->data['error_secrity_code'] = '';
		}

		if (isset($this->error['currency_code'])) {
			$this->data['error_currency_code'] = $this->error['currency_code'];
		} else {
			$this->data['error_currency_code'] = '';
		}

		if (isset($this->error['partner'])) {
			$this->data['error_partner'] = $this->error['partner'];
		} else {
			$this->data['error_partner'] = '';
		}

		// 后台层级菜单（如：Home:Payment...）
		// 后台层级菜单（如：Home:Payment...）
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/alipay_direct', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/alipay_direct&token=' . $this->session->data['token'];
		
		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];
		
		// 设置表单的值
		if (isset($this->request->post['alipay_direct_seller_email'])) {
			$this->data['alipay_direct_seller_email'] = $this->request->post['alipay_direct_seller_email'];
		} else {
			$this->data['alipay_direct_seller_email'] = $this->config->get('alipay_direct_seller_email');
		}

		if (isset($this->request->post['alipay_direct_security_code'])) {
			$this->data['alipay_direct_security_code'] = $this->request->post['alipay_direct_security_code'];
		} else {
			$this->data['alipay_direct_security_code'] = $this->config->get('alipay_direct_security_code');
		}

		if (isset($this->request->post['alipay_direct_partner'])) {
			$this->data['alipay_direct_partner'] = $this->request->post['alipay_direct_partner'];
		} else {
			$this->data['alipay_direct_partner'] = $this->config->get('alipay_direct_partner');
		}		

		if (isset($this->request->post['alipay_direct_currency_code'])) {
			$this->data['alipay_direct_currency_code'] = $this->request->post['alipay_direct_currency_code'];
		} else {
			$this->data['alipay_direct_currency_code'] = $this->config->get('alipay_direct_currency_code');
		}
		
		if (isset($this->request->post['alipay_direct_order_status_id'])) {
			$this->data['alipay_direct_order_status_id'] = $this->request->post['alipay_direct_order_status_id'];
		} else {
			$this->data['alipay_direct_order_status_id'] = $this->config->get('alipay_direct_order_status_id'); 
		} 

		$this->load->model('localisation/order_status');		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->load->model('localisation/currency');
		$this->data['currencies'] = $this->model_localisation_currency->getCurrencies();
		
		// 保留		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['alipay_direct_status'])) {
			$this->data['alipay_direct_status'] = $this->request->post['alipay_direct_status'];
		} else {
			$this->data['alipay_direct_status'] = $this->config->get('alipay_direct_status');
		}

		if (isset($this->request->post['alipay_direct_wait_buyer_pay'])) {
			$this->data['alipay_direct_wait_buyer_pay'] = $this->request->post['alipay_direct_wait_buyer_pay'];
		} else {
			$this->data['alipay_direct_wait_buyer_pay'] = $this->config->get('alipay_direct_wait_buyer_pay'); 
		} 
		
		if (isset($this->request->post['alipay_direct_wait_buyer_confirm'])) {
			$this->data['alipay_direct_wait_buyer_confirm'] = $this->request->post['alipay_direct_wait_buyer_confirm'];
		} else {
			$this->data['alipay_direct_wait_buyer_confirm'] = $this->config->get('alipay_direct_wait_buyer_confirm'); 
		} 		
		
		if (isset($this->request->post['alipay_direct_trade_finished'])) {
			$this->data['alipay_direct_trade_finished'] = $this->request->post['alipay_direct_trade_finished'];
		} else {
			$this->data['alipay_direct_trade_finished'] = $this->config->get('alipay_direct_trade_finished'); 
		} 		
		
		if (isset($this->request->post['alipay_direct_wait_seller_send'])) {
			$this->data['alipay_direct_wait_seller_send'] = $this->request->post['alipay_direct_wait_seller_send'];
		} else {
			$this->data['alipay_direct_wait_seller_send'] = $this->config->get('alipay_direct_wait_seller_send'); 
		} 
		
		if (isset($this->request->post['alipay_direct_sort_order'])) {
			$this->data['alipay_direct_sort_order'] = $this->request->post['alipay_direct_sort_order'];
		} else {
			$this->data['alipay_direct_sort_order'] = $this->config->get('alipay_direct_sort_order');
		}
		
		$this->template = 'payment/alipay_direct.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	// 验证
	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/alipay_direct')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['alipay_direct_seller_email']) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if (!$this->request->post['alipay_direct_security_code']) {
			$this->error['secrity_code'] = $this->language->get('error_secrity_code');
		}

		if (!$this->request->post['alipay_direct_partner']) {
			$this->error['partner'] = $this->language->get('error_partner');
		}

		if (!$this->request->post['alipay_direct_currency_code']) {
			$this->error['currency_code'] = $this->language->get('error_currency_code');
		}		
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>