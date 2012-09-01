<?php
require_once("alipay_service.php");
require_once("alipay_notify.php");

/*  日志消息,把支付宝反馈的参数记录下来*/	
function  log_result($word) {
	
	$fp = fopen("../../../log_alipay_" . strftime("%Y%m%d",time()) . ".txt","a");	
	flock($fp, LOCK_EX) ;
	fwrite($fp,$word."::Date：".strftime("%Y-%m-%d %H:%I:%S",time())."\t\n");
	flock($fp, LOCK_UN); 
	fclose($fp);
	
}

class ControllerPaymentAlipay extends Controller {
	protected function index() {
		// 为 alipay.tpl 准备数据
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');

		// url

		$this->data['return'] = HTTPS_SERVER . 'index.php?route=checkout/success';
		
		if ($this->request->get['route'] != 'checkout/guest_step_3') {
			$this->data['cancel_return'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
		} else {
			$this->data['cancel_return'] = HTTPS_SERVER . 'index.php?route=checkout/guest_step_2';
		}
		
		$this->load->library('encryption');
		
		$encryption = new Encryption($this->config->get('config_encryption'));
		
		$this->data['custom'] = $encryption->encrypt($this->session->data['order_id']);
		
		if ($this->request->get['route'] != 'checkout/guest_step_3') {
			$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
		} else {
			$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/guest_step_2';
		}

		// 获取订单数据
		$this->load->model('checkout/order');

		$order_id = $this->session->data['order_id'];
		
		$order_info = $this->model_checkout_order->getOrder($order_id);

		
		/*
		$this->data['business'] = $this->config->get('alipay_seller_email');
		$this->data['item_name'] = html_entity_decode($this->config->get('config_store'), ENT_QUOTES, 'UTF-8');				
		$this->data['currency_code'] = $order_info['currency'];
		$this->data['tgw'] = $this->session->data['order_id'];
		$this->data['amount'] = $this->currency->format($order_info['total'], $order_info['currency'], $order_info['value'], FALSE);
		$this->data['total'] = $order_info['total'];
		$this->data['currency'] = $order_info['currency'];
		$this->data['value'] = $order_info['value'];
		$this->data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');	
		$this->data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');	
		$this->data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');	
		$this->data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');	
		$this->data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');	
		$this->data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');	
		$this->data['country'] = $order_info['payment_iso_code_2'];
		$this->data['notify_url'] = $this->url->http('payment/alipay/callback');
		$this->data['email'] = $order_info['email'];
		$this->data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		$this->data['lc'] = $this->session->data['language'];
		*/
		// 计算提交地址
		$seller_email = $this->config->get('alipay_seller_email');		// 商家邮箱
		$security_code = $this->config->get('alipay_security_code');	//安全检验码
		$partner = $this->config->get('alipay_partner');				//合作伙伴ID
		$currency_code = $this->config->get('alipay_currency_code');				//人民币代号（CNY）
		$item_name = $this->config->get('config_store');
		$first_name = $order_info['payment_firstname'];	
		$last_name = $order_info['payment_lastname'];

		$total = $order_info['total'];
		if($currency_code == ''){
			$currency_code = 'CNY';
		}
		
		$currency_value = $this->currency->getValue($currency_code);
		$amount = $total * $currency_value;
		$amount = number_format($amount,2,'.','');
		//$this->data['amount'] = html_entity_decode($this->config->get('config_store'), ENT_QUOTES, 'GB2312');
		

		$_input_charset = "utf-8";  //字符编码格式  目前支持 GBK 或 utf-8
		$sign_type      = "MD5";    //加密方式  系统默认(不要修改)
		$transport      = "http";  //访问模式,你可以根据自己的服务器是否支持ssl访问而选择http以及https访问模式(系统默认,不要修改)
		$notify_url     = HTTP_SERVER . 'catalog/controller/payment/alipay_notify_url.php';
		$return_url		= HTTPS_SERVER . 'index.php?route=checkout/success';
		$show_url       = "";        //你网站商品的展示地址

		$parameter = array(
			"service"        => "create_partner_trade_by_buyer",  //交易类型
			"partner"        => $partner,         //合作商户号
			"return_url"     => $return_url,      //同步返回
			"notify_url"     => $notify_url,      //异步返回
			"_input_charset" => $_input_charset,  //字符集，默认为GBK
			"subject"        => $this->config->get('config_name') . ' - #' . $order_id,       //商品名称，必填
			"body"           => $this->config->get('config_name') . ' - #' . $order_id,       //商品描述，必填			
			"out_trade_no"   => $order_id,//'3',//date('Ymdhms'),     //商品外部交易号，必填（保证唯一性）
			"price"          => $amount,           //商品单价，必填（价格不能为0）
			"payment_type"   => "1",              //默认为1,不需要修改
			"quantity"       => "1",              //商品数量，必填
				
			"logistics_fee"      =>'0.00',        //物流配送费用
			"logistics_payment"  =>'BUYER_PAY',   //物流费用付款方式：SELLER_PAY(卖家支付)、BUYER_PAY(买家支付)、BUYER_PAY_AFTER_RECEIVE(货到付款)
			"logistics_type"     =>'EXPRESS',     //物流配送方式：POST(平邮)、EMS(EMS)、EXPRESS(其他快递)

			"show_url"       => $show_url,        //商品相关网站
			"seller_email"   => $seller_email     //卖家邮箱，必填
		);

		$alipay = new alipay_service($parameter,$security_code,$sign_type);
		$action=$alipay->create_url();

		$this->data['action'] = $action;
		$this->id = 'payment';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/alipay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/alipay.tpl';
		} else {
			$this->template = 'default/template/payment/alipay.tpl';
		}	
		
		
		$this->render();	
	}

	
	// 支付返回后的处理
	public function callback() {
		$oder_success = FALSE;

		// 获取商家信息
		$this->load->library('encryption');
		$seller_email = $this->config->get('alipay_seller_email');		// 商家邮箱
		$security_code = $this->config->get('alipay_security_code');	//安全检验码
		$partner = $this->config->get('alipay_partner');				//合作伙伴ID
		$_input_charset = "utf-8"; //字符编码格式  目前支持 GBK 或 utf-8
		$sign_type = "MD5"; //加密方式  系统默认(不要修改)		
		$transport = 'http';//访问模式,你可以根据自己的服务器是否支持ssl访问而选择http以及https访问模式(系统默认,不要修改)
		log_result("callback start.");
		
		// 获取支付宝返回的数据
		$alipay = new alipay_notify($partner,$security_code,$sign_type,$_input_charset,$transport);
		$verify_result = $alipay->notify_verify();	
		
		log_result('verify_result=' . $verify_result);

		if($verify_result) {   //认证合格

			//获取支付宝的反馈参数
			$order_id   = $_POST['out_trade_no'];   //获取支付宝传递过来的订单号
			
			log_result('out_trade_no=' . $order_id);

			$this->load->model('checkout/order');
			
			// 获取订单ID
			$order_info = $this->model_checkout_order->getOrder($order_id);
		
			// 存储订单至系统数据库
			if ($order_info) {
				$order_status_id = $order_info["order_status_id"];

				$alipay_order_status_id = $this->config->get('alipay_order_status_id');
				$alipay_wait_buyer_pay = $this->config->get('alipay_wait_buyer_pay');
				$alipay_wait_buyer_confirm = $this->config->get('alipay_wait_buyer_confirm');
				$alipay_trade_finished = $this->config->get('alipay_trade_finished');
				$alipay_wait_seller_send = $this->config->get('alipay_wait_seller_send');

				if (1 > $order_status_id){
					log_result('order->confirm order_status_id=' . $order_status_id);
					$this->model_checkout_order->confirm($order_id, $alipay_order_status_id);
				}			

				// 避免处理已完成的订单
				log_result('order_id=' . $order_id . ' order_status_id=' . $order_status_id);

				if ($order_status_id != $alipay_trade_finished) {
					log_result("No finished.");
					// 获取原始订单的总额
					$currency_code = $this->config->get('alipay_currency_code');				//人民币代号（CNY）
					$total = $order_info['total'];
					log_result('total=' . $total);
					if($currency_code == ''){
						$currency_code = 'CNY';
					}					
					$currency_value = $this->currency->getValue($currency_code);
					log_result('currency_value=' . $currency_value);
					$amount = $total * $currency_value;
					$amount = number_format($amount,2,'.','');
					log_result('amount=' . $amount);

					// 支付宝付款金额
					$total     = $_POST['total_fee'];      // 获取支付宝传递过来的总价格
					log_result('total_fee=' . $total);
					/*
					$receive_name    =$_POST['receive_name'];    //获取收货人姓名
					$receive_address =$_POST['receive_address']; //获取收货人地址
					$receive_zip     =$_POST['receive_zip'];     //获取收货人邮编
					$receive_phone   =$_POST['receive_phone'];   //获取收货人电话
					$receive_mobile  =$_POST['receive_mobile'];  //获取收货人手机
					*/
					
					/*
						获取支付宝反馈过来的状态,根据不同的状态来更新数据库 
						WAIT_BUYER_PAY(表示等待买家付款);
						WAIT_SELLER_SEND_GOODS(表示买家付款成功,等待卖家发货);
						WAIT_BUYER_CONFIRM_GOODS(表示卖家已经发货等待买家确认);
						TRADE_FINISHED(表示交易已经成功结束);
					*/
					if($_POST['trade_status'] == 'WAIT_BUYER_PAY') {                   //等待买家付款
						//这里放入你自定义代码,比如根据不同的trade_status进行不同操作
						if($order_status_id != $alipay_trade_finished && $order_status_id != $alipay_wait_buyer_confirm && $order_status_id != $alipay_wait_seller_send){
							$this->model_checkout_order->update($order_id, $alipay_wait_buyer_pay);							

							echo "success - alipay_wait_buyer_pay";		//请不要修改或删除
							
							//调试用，写文本函数记录程序运行情况是否正常
							log_result('success - alipay_wait_buyer_pay');
						}
					}
					else if($total < $amount){	// 付款不足
							$this->model_checkout_order->update($order_id, 10);
							log_result('order_id=' . $order_id . "Total Error:total=" . $total . "<amount" .$amount);
					}
					else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {      //买家付款成功,等待卖家发货
						//这里放入你自定义代码,比如根据不同的trade_status进行不同操作
						if($order_status_id != $alipay_trade_finished && $order_status_id != $alipay_wait_buyer_confirm){
							$this->model_checkout_order->update($order_id, $alipay_wait_seller_send);

							echo "success - alipay_wait_seller_send";		//请不要修改或删除
						
							//调试用，写文本函数记录程序运行情况是否正常
							log_result('success - alipay_wait_seller_send');
						}
					}
					else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {    //卖家已经发货等待买家确认
						//这里放入你自定义代码,比如根据不同的trade_status进行不同操作
						if($order_status_id != $alipay_trade_finished){
							$this->model_checkout_order->update($order_id, $alipay_wait_buyer_confirm);

							echo "success - alipay_wait_buyer_confirm";		//请不要修改或删除
						
							//调试用，写文本函数记录程序运行情况是否正常
							log_result('success - alipay_wait_buyer_confirm');
						}
					}
					else if($_POST['trade_status'] == 'TRADE_FINISHED') {              //交易成功结束
						//这里放入你自定义代码,比如根据不同的trade_status进行不同操作
						$this->model_checkout_order->update($order_id, $alipay_trade_finished);

						echo "success - alipay_trade_finished";		//请不要修改或删除
						
						//调试用，写文本函数记录程序运行情况是否正常
						log_result('success - alipay_trade_finished');
					}
					else {
						echo "fail";
						log_result ("verify_failed");
					}
				}
			}
		}
	}
}
?>