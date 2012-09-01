<?php
require_once("alipay_direct_service.php");
require_once("alipay_direct_notify.php");

/*  日志消息,把支付宝反馈的参数记录下来*/	
function  log_result($word) {
	
	$fp = fopen("../../../log_alipay_direct_" . strftime("%Y%m%d",time()) . ".txt","a");	
	flock($fp, LOCK_EX) ;
	fwrite($fp,$word."::Date：".strftime("%Y-%m-%d %H:%I:%S",time())."\t\n");
	flock($fp, LOCK_UN); 
	fclose($fp);
	
}

class ControllerPaymentAlipayDirect extends Controller {
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
		$this->data['business'] = $this->config->get('alipay_direct_seller_email');
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
		$seller_email = $this->config->get('alipay_direct_seller_email');		// 商家邮箱
		$security_code = $this->config->get('alipay_direct_security_code');	//安全检验码
		$partner = $this->config->get('alipay_direct_partner');				//合作伙伴ID
		$currency_code = $this->config->get('alipay_direct_currency_code');				//人民币代号（CNY）
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
		$notify_url     = HTTP_SERVER . 'catalog/controller/payment/alipay_direct_notify_url.php';
		$return_url		= HTTPS_SERVER . 'index.php?route=checkout/success';
		$show_url       = "";        //你网站商品的展示地址
		$out_trade_no = $order_id;
		$subject = 'Order ID ' . $order_id . ' of ' .$item_name;       //商品名称，必填
		$body = 'for ' . $last_name . ' ' . $first_name;       //商品描述，必填			
		$total_fee = $amount;

		//扩展功能参数——默认支付方式
		$paymethod    = "directPay";	//默认支付方式，四个值可选：bankPay(网银); cartoon(卡通); directPay(余额); CASH(网点支付)
		$defaultbank  = "";

		$pay_mode	  =  !empty($this->session->data['alipay_direct_method']) ? $this->session->data['alipay_direct_method'] : '';
		if ($pay_mode != '') {			
			$paymethod    = "bankPay";		//默认支付方式，四个值可选：bankPay(网银); cartoon(卡通); directPay(余额); CASH(网点支付)
			$defaultbank  = $pay_mode;		//默认网银代号，代号列表见http://club.alipay.com/read.php?tid=8681379
		}

		//扩展功能参数——防钓鱼
		//请慎重选择是否开启防钓鱼功能
		//exter_invoke_ip、anti_phishing_key一旦被设置过，那么它们就会成为必填参数
		//开启防钓鱼功能后，服务器、本机电脑必须支持远程XML解析，请配置好该环境。
		//若要使用防钓鱼功能，请打开class文件夹中alipay_function.php文件，找到该文件最下方的query_timestamp函数，根据注释对该函数进行修改
		//建议使用POST方式请求数据
		$anti_phishing_key  = '';			//防钓鱼时间戳
		$exter_invoke_ip = '';				//获取客户端的IP地址，建议：编写获取客户端IP地址的程序
		//如：
		//$exter_invoke_ip = '202.1.1.1';
		//$anti_phishing_key = query_timestamp($partner);		//获取防钓鱼时间戳函数


		//扩展功能参数——其他
		$extra_common_param = '';			//自定义参数，可存放任何内容（除=、&等特殊字符外），不会显示在页面上
		$buyer_email		= '';			//默认买家支付宝账号

		//扩展功能参数——分润(若要使用，请按照注释要求的格式赋值)
		$royalty_type		= "";			//提成类型，该值为固定值：10，不需要修改
		$royalty_parameters	= "";
		//提成信息集，与需要结合商户网站自身情况动态获取每笔交易的各分润收款账号、各分润金额、各分润说明。最多只能设置10条
		//各分润金额的总和须小于等于total_fee
		//提成信息集格式为：收款方Email_1^金额1^备注1|收款方Email_2^金额2^备注2
		//如：
		//royalty_type = "10"
		//royalty_parameters	= "111@126.com^0.01^分润备注一|222@126.com^0.01^分润备注二"

		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service"			=> "create_direct_pay_by_user",	//接口名称，不需要修改
				"payment_type"		=> "1",               			//交易类型，不需要修改

				//获取配置文件(alipay_config.php)中的值
				"partner"			=> $partner,
				"seller_email"		=> $seller_email,
				"return_url"		=> $return_url,
				"notify_url"		=> $notify_url,
				"_input_charset"	=> $_input_charset,
				"show_url"			=> $show_url,

				//从订单数据中动态获取到的必填参数
				"out_trade_no"		=> $out_trade_no,
				"subject"			=> $this->config->get('config_name') . ' - #' . $order_id,
				"body"				=> $this->config->get('config_name') . ' - #' . $order_id,
				"total_fee"			=> $total_fee,

				//扩展功能参数——网银提前
				"paymethod"			=> $paymethod,
				"defaultbank"		=> $defaultbank,

				//扩展功能参数——防钓鱼
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,

				//扩展功能参数——自定义参数
				"buyer_email"		=> $buyer_email,
				"extra_common_param"=> $extra_common_param,
				
				//扩展功能参数——分润
				"royalty_type"		=> $royalty_type,
				"royalty_parameters"=> $royalty_parameters
		);
		/*
		$parameter = array(
			"service"        => "create_partner_trade_by_buyer",  //交易类型
			"partner"        => $partner,         //合作商户号
			"return_url"     => $return_url,      //同步返回
			"notify_url"     => $notify_url,      //异步返回
			"_input_charset" => $_input_charset,  //字符集，默认为GBK
			"subject"        => 'Order ID ' . $order_id . ' of ' .$item_name,       //商品名称，必填
			"body"           => 'for ' . $last_name . ' ' . $first_name,       //商品描述，必填			
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
		*/

		$alipay = new alipay_service($parameter,$security_code,$sign_type);
		$action=$alipay->create_url();

		$this->data['action'] = $action;
		$this->id = 'payment';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/alipay_direct.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/alipay_direct.tpl';
		} else {
			$this->template = 'default/template/payment/alipay_direct.tpl';
		}	
		
		
		$this->render();	
	}

	
	// 支付返回后的处理
	public function callback() {
		$oder_success = FALSE;

		// 获取商家信息
		$this->load->library('encryption');
		$seller_email = $this->config->get('alipay_direct_seller_email');		// 商家邮箱
		$security_code = $this->config->get('alipay_direct_security_code');	//安全检验码
		$partner = $this->config->get('alipay_direct_partner');				//合作伙伴ID
		$_input_charset = "utf-8"; //字符编码格式  目前支持 GBK 或 utf-8
		$sign_type = "MD5"; //加密方式  系统默认(不要修改)		
		$transport = 'http';//访问模式,你可以根据自己的服务器是否支持ssl访问而选择http以及https访问模式(系统默认,不要修改)
		log_result("callback start.");
		
		// 获取支付宝返回的数据
		$alipay = new alipay_notify($partner,$security_code,$sign_type,$_input_charset,$transport);
		$verify_result = $alipay->notify_verify();		

		if($verify_result) {   //认证合格

		 //获取支付宝的反馈参数
			$order_id   = $_POST['out_trade_no'];   //获取支付宝传递过来的订单号

			$this->load->model('checkout/order');
			
			// 获取订单ID
			$order_info = $this->model_checkout_order->getOrder($order_id);
		
			// 存储订单至系统数据库
			if ($order_info) {
				$order_status_id = $order_info["order_status_id"];

				$alipay_direct_order_status_id = $this->config->get('alipay_direct_order_status_id');
				$alipay_direct_wait_buyer_pay = $this->config->get('alipay_direct_wait_buyer_pay');
				$alipay_direct_wait_buyer_confirm = $this->config->get('alipay_direct_wait_buyer_confirm');
				$alipay_direct_trade_finished = $this->config->get('alipay_direct_trade_finished');
				$alipay_direct_wait_seller_send = $this->config->get('alipay_direct_wait_seller_send');

				if (1 > $order_status_id){
					log_result('order->confirm order_status_id=' . $order_status_id);
					$this->model_checkout_order->confirm($order_id, $alipay_direct_order_status_id);
				}

				// 避免处理已完成的订单
				log_result('order_id=' . $order_id . ' order_status_id=' . $order_status_id);
				
				if ($order_status_id != $alipay_direct_trade_finished) {
					log_result("No finished.");					
					
					// 获取原始订单的总额
					$currency_code = $this->config->get('alipay_direct_currency_code');				//人民币代号（CNY）
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
						//TRADE_FINISHED(表示交易已经成功结束，通用即时到帐反馈的交易状态成功标志);
						//TRADE_SUCCESS(表示交易已经成功结束，高级即时到帐反馈的交易状态成功标志);
					*/

					log_result("trade_status:" . $_POST['trade_status']);

					if($_POST['trade_status'] == 'WAIT_BUYER_PAY') {
						log_result('==alipay_direct_wait_buyer_pay==');

						//该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款
					
						//判断该笔订单是否在商户网站中已经做过处理（可参考“集成教程”中“3.4返回数据处理”）
						//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
						//如果有做过处理，不执行商户的业务程序
						if($order_status_id != $alipay_direct_trade_finished && $order_status_id != $alipay_direct_wait_buyer_confirm && $order_status_id != $alipay_direct_wait_seller_send){
							$this->model_checkout_order->update($order_id, $alipay_direct_wait_buyer_pay);							

							echo "success - alipay_direct_wait_buyer_pay";		//请不要修改或删除
							
							//调试用，写文本函数记录程序运行情况是否正常
							log_result('success - alipay_direct_wait_buyer_pay');
						}
					}
					else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
						log_result('==alipay_direct_wait_seller_send==');
						//该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货
					
						//判断该笔订单是否在商户网站中已经做过处理（可参考“集成教程”中“3.4返回数据处理”）
						//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
						//如果有做过处理，不执行商户的业务程序

						if($order_status_id != $alipay_direct_trade_finished && $order_status_id != $alipay_direct_wait_buyer_confirm){
							$this->model_checkout_order->update($order_id, $alipay_direct_wait_seller_send);

							echo "success - alipay_direct_wait_seller_send";		//请不要修改或删除
						
							//调试用，写文本函数记录程序运行情况是否正常
							log_result('success - alipay_direct_wait_seller_send');
						}

						
					}
					else if($_POST['trade_status'] == 'TRADE_FINISHED' ||$_POST['trade_status'] == 'TRADE_SUCCESS') {    //交易成功结束
						//这里放入你自定义代码,比如根据不同的trade_status进行不同操作
						$this->model_checkout_order->update($order_id, $alipay_direct_trade_finished);

						echo "success - alipay_direct_trade_finished";		//请不要修改或删除
						
						//调试用，写文本函数记录程序运行情况是否正常
						log_result('success - alipay_direct_trade_finished');						

						log_result("finished.");
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