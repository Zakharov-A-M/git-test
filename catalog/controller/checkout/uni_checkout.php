<?php 
class ControllerCheckoutUniCheckout extends Controller { 	
	public function index()
    {
	
		if(isset($this->session->data['shipping_address_id']))	{
			unset($this->session->data['shipping_address_id']);
		}
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/checkout.css');
		
		$this->load->language('checkout/cart');
		$this->load->language('checkout/checkout');
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('checkout/checkout');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/uni_checkout', '', true)
		);
		
		//$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		
		$data['name_text'] = 1;//$uniset[$language_id]['checkout_name_text'];
		$data['lastname_text'] = 1;//$uniset[$language_id]['checkout_lastname_text'];
		$data['email_text'] = 1; //$uniset[$language_id]['checkout_email_text'];
		$data['phone_text'] = 1; //$uniset[$language_id]['checkout_phone_text'];
		//$data['password_text'] = $uniset[$language_id]['checkout_password_text'];
		//$data['password_confirm_text'] = $uniset[$language_id]['checkout_password_confirm_text'];
		//$data['products_related_after'] = isset($uniset['checkout_related_product_after']) ? $uniset['checkout_related_product_after'] : '';

		if (!isset($this->session->data['guest']['customer_group_id'])) {
			$this->session->data['guest']['customer_group_id'] = (int)$this->config->get('config_customer_group_id');
		}
		
		if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}
		
		if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
			$data['error_warning'] = $this->language->get('error_stock');
		}
		
		if ($this->customer->isLogged())	{
			$data['customer_id'] = $this->session->data['customer_id'];
			
			unset($this->session->data['shipping_method']);							
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_address_id']);
			unset($this->session->data['payment_address']);
			unset($this->session->data['payment_address_id']);
			unset($this->session->data['payment_method']);	
			unset($this->session->data['payment_methods']);

			unset($this->session->data['guest']);
			unset($this->session->data['account']);
			unset($this->session->data['shipping_country_id']);
			unset($this->session->data['shipping_zone_id']);
			unset($this->session->data['payment_country_id']);
			unset($this->session->data['payment_zone_id']);
		}

		$data['firstname'] = isset($this->session->data['firstname']) ? $this->session->data['firstname'] : '';
		$data['lastname'] = isset($this->session->data['lastname']) ? $this->session->data['lastname'] : '';
		$data['email'] = isset($this->session->data['email']) ? $this->session->data['email'] : '';
		$data['telephone'] = isset($this->session->data['telephone']) ? $this->session->data['telephone'] : '';
				
		if ($this->customer->isLogged()){
			$this->load->model('account/address');
			$data['firstname'] = $this->customer->getFirstName();
			$data['lastname'] = $this->customer->getLastName();
			$data['email'] = $this->customer->getEmail();
			$data['telephone'] = $this->customer->getTelephone();
		}
		
		$data['comment'] = isset($this->session->data['comment']) ? $this->session->data['comment'] : '';
		
		$this->load->model('account/customer_group');

		$data['customer_groups'] = [];
		
		if (is_array($this->config->get('config_customer_group_display'))) {
			$customer_groups = $this->model_account_customer_group->getCustomerGroups();
			
			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$data['customer_groups'][] = $customer_group;
				}
			}
		}
		
		$data['customer_group_id'] = isset($this->session->data['guest']['customer_group_id']) ? $this->session->data['guest']['customer_group_id'] : $this->config->get('config_customer_group_id');
		
		$this->load->model('account/custom_field');
		
		//$data['custom_fields'] = $this->custom_field('account');
		
		$data['is_logged'] = $this->customer->isLogged() ? true : false;
		$data['is_shipping'] = $this->cart->hasShipping() ? true : false;
		
		$data['confirm'] = isset($this->session->data['confirm']) ? $this->session->data['confirm'] : '';
		
		$data['text_confirm'] = '';
		
		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info) {
				$data['text_confirm'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_checkout_id'), 'SSL'), $information_info['title'], $information_info['title']);
			}
		}
		
		$data['checkout_guest'] = $this->config->get('config_checkout_guest');
		
		$data['show_lastname'] = isset($uniset['checkout_lastname']) ? $uniset['checkout_lastname'] : '';
		$data['show_email'] = isset($uniset['checkout_email']) ? $uniset['checkout_email'] : '';
		$data['show_phone'] = isset($uniset['checkout_phone']) ? $uniset['checkout_phone'] : '';
		$data['checkout_phone_mask'] = isset($uniset['checkout_phone_mask']) ? $uniset['checkout_phone_mask'] : '';
		$data['checkout_passgen'] = isset($uniset['checkout_passgen']) ? true : false;
		$data['show_password_confirm'] = isset($uniset['checkout_password_confirm']) ? $uniset['checkout_password_confirm'] : '';
		
		//$data['address'] = $this->address();
		//$data['shipping_method'] = $this->shipping_method();
		//$data['payment_method'] = $this->payment_method();
		$data['cart'] = $this->cart();
		
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('checkout/uni_checkout', $data));
  	}
	
	public function validate() {
		$this->load->language('checkout/cart');
		$this->load->language('checkout/checkout');
		
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		
		$this->load->model('account/custom_field');
		$this->load->model('account/customer');
		$this->load->model('account/customer_group');
		
		$json = [];
		
		if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
			$json['error']['error_warning'] = $this->language->get('error_stock');
		}
		
		if (!$this->cart->hasProducts() && !empty($this->session->data['vouchers'])) {
			unset($json['error']);
		}
		
		//customer data
		
		$firstname = isset($this->request->post['firstname']) ? trim($this->request->post['firstname']) : '';
		$lastname = isset($this->request->post['lastname']) ? trim($this->request->post['lastname']) : '';
		$telephone = isset($this->request->post['telephone']) ? trim($this->request->post['telephone']) : '';
		
		if(isset($this->request->post['add-new-customer'])) {
			$email = isset($this->request->post['email']) ? $this->request->post['email'] : '';
		} else {
			$email = isset($this->request->post['email']) && $this->request->post['email'] != '' ? $this->request->post['email'] : $uniset['checkout_mail_cap'];
		}
		
		//validate
		
		if (utf8_strlen($firstname) < 3 || utf8_strlen($firstname) > 32) {
			$json['error']['firstname'] = $this->language->get('error_firstname');
		} else {
			$this->session->data['firstname'] = htmlspecialchars(strip_tags($firstname));
		}
		
		if(isset($uniset['checkout_lastname'])) {
			if (utf8_strlen($lastname) < 3 || utf8_strlen($lastname) > 32) {
				$json['error']['lastname'] = $this->language->get('error_lastname');
			} else {
				$this->session->data['lastname'] = htmlspecialchars(strip_tags($lastname));
			}
		} else {
			$this->session->data['lastname'] = $this->customer->isLogged() ? $this->customer->getLastName() : '';
		}
		
		if(isset($uniset['checkout_email'])) {
			if (utf8_strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$json['error']['email'] = $this->language->get('error_email');
			} else {
				$this->session->data['email'] = htmlspecialchars(strip_tags($email));
			}
		} else {
			$this->session->data['email'] = $this->customer->isLogged() ? $this->customer->getEmail() : $uniset['checkout_mail_cap'];
		}
		
		if(!$this->customer->isLogged() && (isset($this->request->post['add-new-customer']) || isset($uniset['checkout_passgen']))) {
			if ($this->model_account_customer->getTotalCustomersByEmail($email)) {
				$json['error']['email'] = $this->language->get('error_exists');
			}
		}
		
		if(isset($uniset['checkout_phone'])) {
			if (utf8_strlen($telephone) < 4 || utf8_strlen($telephone) > 32) {
				$json['error']['telephone'] = $this->language->get('error_telephone');
			} else {
				$this->session->data['telephone'] = htmlspecialchars(strip_tags($telephone));
			}
		} else {
			$this->session->data['telephone'] = $this->customer->isLogged() ? $this->customer->getTelephone() : '';
		}
		
		if(!$this->customer->isLogged() && isset($this->request->post['add-new-customer'])) {
			if (isset($this->request->post['add-new-customer']) && ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20))) {
				$json['error']['password'] = $this->language->get('error_password');
			}
			
			if(isset($uniset['checkout_password_confirm'])) {
				if (isset($this->request->post['password-confirm']) && ($this->request->post['password-confirm'] != $this->request->post['password'])) {
					$json['error']['confirm'] = $this->language->get('error_confirm');
				}
			}
		}
		
		if($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
		} else {
			if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
				$customer_group_id = $this->request->post['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
		}
		
		//shipping address
		if($this->cart->hasShipping()) {
			if (isset($this->request->post['existing-address'])) {
				$this->load->model('account/address');
						
				if (empty($this->request->post['address_id'])) {
					$json['error']['warning'] = $this->language->get('error_address');
				} elseif (!in_array($this->request->post['address_id'], array_keys($this->model_account_address->getAddresses()))) {
					$json['error']['warning'] = $this->language->get('error_address');
				}
			} else {
				if(isset($uniset['checkout_country_zone'])) {
					if (!isset($this->request->post['country_id']) || ($this->request->post['country_id'] == '')) {
						$json['error']['country_id'] = $this->language->get('error_country');
					}
				}
				
				if(isset($uniset['checkout_country_zone'])) {				
					if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
						$json['error']['zone_id'] = $this->language->get('error_zone');
					}
				}
				
				if(isset($uniset['checkout_city'])) {
					if (!isset($this->request->post['city']) || ( (utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 32))) {
					$json['error']['city'] = $this->language->get('error_city');
					}
				}
				
				if(isset($uniset['checkout_postcode'])) {
					$this->load->model('localisation/country');
					$country_info = isset($this->request->post['country_id']) ? $this->model_localisation_country->getCountry($this->request->post['country_id']) : '';
					if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
						$json['error']['postcode'] = $this->language->get('error_postcode');
					}
				}
				
				if(isset($uniset['checkout_address'])) {
					if (!isset($this->request->post['address_1']) || ( (utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128))) {
						$json['error']['address_1'] = $this->language->get('error_address_1');
					}
				}
				
				if(isset($uniset['checkout_address2'])) {
					if (!isset($this->request->post['address_2']) || ((utf8_strlen(trim($this->request->post['address_2'])) < 3) || (utf8_strlen(trim($this->request->post['address_2'])) > 128))) {
						//$json['error']['address_2'] = $this->language->get('error_address_2');
					}
				}
			}		
		}
		
		//shipping method
		if ($this->cart->hasProducts() && $this->cart->hasShipping()) {
			if (!isset($this->request->post['shipping_method'])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			} else {
				$shipping = explode('.', $this->request->post['shipping_method']);
				if (!isset($shipping[0]) || !isset($shipping[1])/* || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])*/) {
					$json['error']['warning'] = $this->language->get('error_shipping');
				}
			}						
		}
		
		//payment method
		if ($this->cart->hasProducts()) {
			if (!isset($this->request->post['payment_method'])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			}						
		}
		
		//agree
		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
				
			if ($information_info && !isset($this->request->post['confirm'])) {
				$json['error']['confirm'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}
		
		$this->load->model('account/custom_field');

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
				 $json['error']['custom_field['.$custom_field['location'].']['.$custom_field['custom_field_id'].']'] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                $json['error']['custom_field['.$custom_field['location'].']['.$custom_field['custom_field_id'].']'] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
            }
		}
		
		if (isset($this->request->post['custom_field']['account'])) {
			$this->session->data['custom_field'] = $this->request->post['custom_field']['account'];
		} else {
			$this->session->data['custom_field'] = [];
		}
		
		//print_r($this->session->data);
		
		$this->session->data['comment'] = isset($this->request->post['comment']) ? strip_tags($this->request->post['comment']) : '';
		
		if(!$this->cart->hasProducts()) {
			$json['error']['warning'] = $this->language->get('error_stock');	
		}
		
		if (!$json) {
			//guest
			if (!$json && !$this->customer->isLogged()) {
				if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$customer_group_id = $this->request->post['customer_group_id'];
				} else {
					$customer_group_id = $this->config->get('config_customer_group_id');
				}
		
				$this->session->data['account'] = 'guest';

				$this->session->data['guest']['firstname'] = $this->session->data['firstname'];
				$this->session->data['guest']['lastname'] = $this->session->data['lastname'];
				$this->session->data['guest']['email'] = $this->session->data['email'];
				$this->session->data['guest']['telephone'] = $this->session->data['telephone'];
				$this->session->data['guest']['customer_group_id'] = $customer_group_id;
				$this->session->data['guest']['fax'] = isset($this->request->post['fax']) ? $this->request->post['fax'] : '';
			}
		
			//add new customer
			if(!$this->customer->isLogged() && (isset($this->request->post['add-new-customer']) || isset($uniset['checkout_passgen'])) && ($email != $uniset['checkout_mail_cap'])) {
				$this->addNewCustomer();
			}
		
			//add new address
			if (isset($this->request->post['new-address'])) {
				$this->addNewAddress();
			}
		
			$cart = new Cart\Cart($this->registry);
		
			//confirm checkout
			$json['success'] = $this->addOrder();
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}	
	
	private function addNewCustomer() {
		$uniset = $this->config->get('config_unishop2');
		
		$this->load->model('account/customer');
		$this->load->model('account/customer_group');
		
		$this->session->data['account'] = 'register';
		$this->session->data['checkout_customer_id'] = true;
		
		unset($this->session->data['approve_customer']);
		
		if (isset($uniset['checkout_passgen'])) {
			$this->request->post['password'] = $this->getNewPassword();
			
			$this->config->set('new_user_email', $this->request->post['email']);
			$this->config->set('new_user_password', $this->request->post['password']);
		}
			
		$customer_id = $this->model_account_customer->addCustomer($this->request->post);
		
		$this->customer->login($this->request->post['email'], $this->request->post['password']);

		$this->session->data['customer_id'] = $customer_id;
		
		$customer_info = $this->model_account_customer->getCustomer($customer_id);
		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_info['customer_group_id']);
			
		if ($customer_group_info['approval']) {
			$this->session->data['approve_customer'] = true;
		}
		
		$this->addNewAddress();
			
		unset($this->session->data['guest']);
	}
	
	private function addNewAddress() {
		$this->load->model('account/customer');
		$this->load->model('account/address');
		
		if(!$this->customer->isLogged() && isset($this->session->data['approve_customer'])) {
			$customer_id = $this->session->data['customer_id'];
		} else {
			$customer_id = $this->customer->getId();
		}
		
		$this->request->post['default'] = true;
			
		$this->model_account_address->addAddress($customer_id, $this->request->post);
	}

	public function address() {
		$this->load->language('checkout/cart');
		$this->load->language('checkout/checkout');
		$this->load->language('extension/module/uni_othertext');
		
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		
		$data['city_text'] = $uniset[$language_id]['checkout_city_text'];
		$data['postcode_text'] = $uniset[$language_id]['checkout_postcode_text'];
		$data['address_text'] = $uniset[$language_id]['checkout_address_text'];
		$data['address2_text'] = $uniset[$language_id]['checkout_address2_text'];
		
		$data['blocked'] = isset($uniset['checkout_address_blocked']) ? true : false;
		
		$custom_field = [];
		
		if (isset($this->request->post['custom_field']['address'])) {
			$custom_field = $this->request->post['custom_field']['address'];
		} elseif (isset($this->session->data['payment_address']['custom_field'])) {
			$custom_field = $this->session->data['payment_address']['custom_field'];
		}
		
		$data['new_address'] = $new_address = isset($this->request->post['new-address']) ? true : false;
		
		//address for guest or new address for registered customer
		if(!$this->customer->isLogged() || $new_address) {
			
			$address_1 = '';
			
			if(isset($this->request->post['address_1'])) {
				$address_1 = $this->request->post['address_1'];
			} elseif (isset($this->session->data['payment_address']['address_1'])) {
				$address_1 = $this->session->data['payment_address']['address_1'];
			}
			
			$address_2 = '';
			
			if(isset($this->request->post['address_2'])) {
				$address_2 = $this->request->post['address_2'];
			} elseif (isset($this->session->data['payment_address']['address_2'])) {
				$address_2 = $this->session->data['payment_address']['address_2'];
			}
			
			$company = '';
			
			if(isset($this->request->post['company'])) {
				$company = $this->request->post['company'];
			} elseif (isset($this->session->data['payment_address']['company'])) {
				$company = $this->session->data['payment_address']['company'];
			}
			
			$postcode = '';
			
			if(isset($this->request->post['postcode'])) {
				$postcode = $this->request->post['postcode'];
			} elseif (isset($this->session->data['payment_address']['postcode'])) {
				$postcode = $this->session->data['payment_address']['postcode'];
			}
			
			$city = '';
			
			if(isset($this->request->post['city'])) {
				$city = $this->request->post['city'];
			} elseif (isset($this->session->data['payment_address']['city'])) {
				$city = $this->session->data['payment_address']['city'];
			}
			
			$zone_id = $this->config->get('config_zone_id');
			
			if(isset($this->request->post['zone_id'])) {
				$zone_id = $this->request->post['zone_id'];
			} elseif (isset($this->session->data['payment_address']['zone_id'])) {
				$zone_id = $this->session->data['payment_address']['zone_id'];
			}
			
			$this->load->model('localisation/zone');	
			$zone_info = $this->model_localisation_zone->getZone($zone_id);
			
			$zone_name = isset($zone_info['name']) ? $zone_info['name'] : '';
			$zone_code = isset($zone_info['code']) ? $zone_info['code'] : '';
			
			$country_id = $this->config->get('config_country_id');
			
			if(isset($this->request->post['country_id'])) {
				$country_id = $this->request->post['country_id'];
			} elseif (isset($this->session->data['payment_address']['country_id'])) {
				$country_id = $this->session->data['payment_address']['country_id'];
			}
			
			$this->load->model('localisation/country');
			$country_info = $this->model_localisation_country->getCountry($country_id);
			
			$country		= isset($country_info['name']) ? $country_info['name'] : '';
			$iso_code_2		= isset($country_info['iso_code_2']) ? $country_info['iso_code_2'] : '';
			$iso_code_3		= isset($country_info['iso_code_3']) ? $country_info['iso_code_3'] : '';
			$address_format = isset($country_info['address_format']) ? $country_info['address_format'] : '';
			
			$address = array(
				'address_1'			=> $address_1,
				'address_2' 		=> $address_2,
				'company' 			=> $company,
				'postcode'			=> $postcode,
				'city' 				=> $city,
				'zone_id' 			=> $zone_id,
				'zone' 				=> $zone_name,
				'zone_code' 		=> $zone_code,
				'country_id'		=> $country_id,
				'country' 			=> $country,
				'iso_code_2' 		=> $iso_code_2,
				'iso_code_3' 		=> $iso_code_3,
				'address_format' 	=> $address_format,
				'custom_field'		=> $custom_field
			);
		}
		
		//address for registered customer
		if($this->customer->isLogged() && !$new_address) {
			$this->load->model('account/address');	
			$data['address_id'] = $address_id = isset($this->request->post['address_id']) ? $this->request->post['address_id'] : $this->customer->getAddressId();
			$this->session->data['payment_address_id'] = $this->session->data['shipping_address_id'] = $address_id;
			$address = $this->model_account_address->getAddress($address_id);
		}
		
		//if address is empty
		if(!$address) {
			$this->load->model('localisation/zone');	
			$zone_info = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));
			
			$zone_name = isset($zone_info['name']) ? $zone_info['name'] : '';
			$zone_code = isset($zone_info['code']) ? $zone_info['code'] : '';
			
			$this->load->model('localisation/country');
			$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
			
			$country		= isset($country_info['name']) ? $country_info['name'] : '';
			$iso_code_2		= isset($country_info['iso_code_2']) ? $country_info['iso_code_2'] : '';
			$iso_code_3		= isset($country_info['iso_code_3']) ? $country_info['iso_code_3'] : '';
			$address_format = isset($country_info['address_format']) ? $country_info['address_format'] : '';
		
			$address = array(
				'address_1'			=> '',
				'address_2' 		=> '',
				'company' 			=> '',
				'postcode'			=> '',
				'city' 				=> '',
				'zone_id' 			=> $this->config->get('config_zone_id'),
				'zone' 				=> $zone_name,
				'zone_code' 		=> $zone_code,
				'country_id'		=> $this->config->get('config_country_id'),
				'country' 			=> $country,
				'iso_code_2' 		=> $iso_code_2,
				'iso_code_3' 		=> $iso_code_3,
				'address_format' 	=> $address_format,
				'custom_field'		=> $custom_field
			);
		}
		
		$this->session->data['shipping_address'] = $this->session->data['payment_address'] = $address;
		
		$data['customer_id'] = $this->customer->isLogged() ? $this->customer->getId() : '';
		$data['is_shipping'] = $this->cart->hasShipping() ? true : false;
		
		$this->load->model('account/address');
		$data['addresses'] = $this->customer->getId() ? $this->model_account_address->getAddresses() : [];
		
		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();
		
		$data['city'] = $address['city'];
		$data['postcode'] = $address['postcode'];
		$data['address_1'] = $address['address_1'];
		$data['address_2'] = $address['address_2'];
		
		$data['country'] = $address['country'];
		$data['country_id'] = $address['country_id'];
		$data['zone'] = $address['zone'];
		$data['zone_id'] = $address['zone_id'];
		
		$data['show_country_zone'] = isset($uniset['checkout_country_zone']) ? $uniset['checkout_country_zone'] : '';
		$data['show_city'] = isset($uniset['checkout_city']) ? $uniset['checkout_city'] : '';
		$data['show_postcode'] = isset($uniset['checkout_postcode']) ? $uniset['checkout_postcode'] : '';
		$data['show_address'] = isset($uniset['checkout_address']) ? $uniset['checkout_address'] : '';
		$data['show_address2'] = isset($uniset['checkout_address2']) ? $uniset['checkout_address2'] : '';
		
		$data['custom_fields'] = $this->custom_field('address');
		
		$result = $this->load->view('checkout/uni_address', $data);

		if(isset($this->request->get['render'])) {
			$this->response->setOutput($result);
		} else {
			return $result;
		}
	}
	
	public function cart(){
		$data = [];
		$this->load->language('product/product');
		$this->load->language('checkout/cart');
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('checkout/checkout');
		
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
        
		if (!isset($this->session->data['vouchers'])) {
			$this->session->data['vouchers'] = [];
		}
			
		$points = $this->customer->getRewardPoints();
		$points_total = 0;
			
		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} elseif (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
      		$data['error_warning'] = $this->language->get('error_stock');		
		} else {
			$data['error_warning'] = '';
		}
			
		if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
			$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
		} else {
			$data['attention'] = '';
		}
						
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$currency = $this->session->data['currency'];
						
		$data['weight'] = $this->config->get('config_cart_weight') && $this->cart->getWeight() ? $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point')) : '';
				
		$this->load->model('tool/image');

        $data['products'] = [];

        $products = $this->cart->getProducts();
			
        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			}

            if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_'.$this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_'.$this->config->get('config_theme') . '_image_cart_height'));
			} else {
                $image = '';
            }

            $option_data = [];

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
					if (isset($option['option_value']))	{
						$value = $option['option_value'];
					} else if (isset($option['value']))	{
						$value = $option['value'];
					} else {
						$value = '';
					}
                } else {
                    $filename = $this->encryption->decrypt(isset($option['option_value'])?$option['option_value']:isset($option['value'])?$option['value']:'');
					$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
                }

                $option_data[] = array(
                    'name'  => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                );
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $currency);
            } else {
                $price = false;
            }

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $currency);
            } else {
                $total = false;
            }
			
			$recurring = '';

			if ($product['recurring']) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year')
				);

				if ($product['recurring']['trial']) {
					$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
				}

				if ($product['recurring']['duration']) {
					$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
				} else {
					$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
				}
			}

            $data['products'][] = array(
				'cart_id'  			  => $product['cart_id'],
                'product_id'          => $product['product_id'],
                'thumb'               => $image,
                'name'                => $product['name'],
                'model'               => $product['model'],
                'option'              => $option_data,
                'quantity'            => $product['quantity'],
                'stock'               => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                'reward'              => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                'price'               => $price,
                'total'               => $total,
                'href'                => $this->url->link('product/product', 'product_id='.$product['product_id']),
                'remove'              => $this->url->link('checkout/cart', 'remove='.$product['product_id']),
                'recurring'           => $recurring,
            );
		}
			
		//$data['related'] = isset($uniset['checkout_related_product']) ? $uniset['checkout_related_product'] : '';
		$data['checkout_related_text'] = isset($uniset[$language_id]['checkout_related_text']) ? $uniset[$language_id]['checkout_related_text'] : '';
		$data['products_related'] = $this->products_related();
		$data['products_related_after'] = isset($uniset['checkout_related_product_after']) ? $uniset['checkout_related_product_after'] : '';

        $data['products_recurring'] = [];
            
		$data['vouchers'] = [];
		
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$data['vouchers'][] = array(
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
					'remove'      => $this->url->link('checkout/cart', 'remove='.$key)   
				);
			}
		}
						 
		//$data['coupon_status'] = $this->config->get('total_coupon_status');
			
		if (isset($this->request->post['coupon'])) {
			$data['coupon'] = $this->request->post['coupon'];			
		} elseif (isset($this->session->data['coupon'])) {
			$data['coupon'] = $this->session->data['coupon'];
		} else {
			$data['coupon'] = '';
		}
			
		//$data['voucher_status'] = $this->config->get('total_voucher_status');
			
		if (isset($this->request->post['voucher'])) {
			$data['voucher'] = $this->request->post['voucher'];				
		} elseif (isset($this->session->data['voucher'])) {
			$data['voucher'] = $this->session->data['voucher'];
		} else {
			$data['voucher'] = '';
		}
			
		//$data['reward_status'] = ($points && $points_total && $this->config->get('total_reward_status'));
			
		if (isset($this->request->post['reward'])) {
			$data['reward'] = $this->request->post['reward'];				
		} elseif (isset($this->session->data['reward'])) {
			$data['reward'] = $this->session->data['reward'];
		} else {
			$data['reward'] = '';
		}							
		
		$this->load->model('setting/extension');
			
		$total_data = [];					
		$total = 0;
		$taxes = $this->cart->getTaxes();
		
		$totals = [];
		
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);
			 
		$results = $this->model_setting_extension->getExtensions('total');
			
		$sort_order = []; 
			
		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}
			
		array_multisort($sort_order, SORT_ASC, $results);
			
		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}
			
		$sort_order = []; 
		
		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		
		array_multisort($sort_order, SORT_ASC, $totals);
		
		$data['totals'] = [];
		
		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
			);
		}
		
		$result = $this->load->view('checkout/uni_cart', $data);
		
		if(isset($this->request->get['render'])) {
			$this->response->setOutput($result);
		} else {
			return $result;
		}
	}
		
	private function addOrder() {
		$this->load->language('checkout/checkout');
		
		$this->load->model('account/customer');
		$this->load->model('setting/extension');

		if (!$this->cart->hasShipping()) {
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}
		
		$currency = $this->session->data['currency'];

		$order_data = [];
		
		$total_data = [];
		$total = 0;
		$taxes = $this->cart->getTaxes();
		
		$totals = [];
		
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		$sort_order = [];

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_'.$value['code'].'_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}

		$sort_order = []; 

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		
		array_multisort($sort_order, SORT_ASC, $totals);
		
		$order_data['totals'] = $totals;

		$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['store_id'] = $this->config->get('config_store_id');
		$order_data['store_name'] = $this->config->get('config_name');
		$order_data['store_url'] = $order_data['store_id'] ? $this->config->get('config_url') : $this->config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER;
		
		$order_data['firstname'] = $this->session->data['firstname'];
		$order_data['lastname'] = $this->session->data['lastname'];
		$order_data['email'] = $this->session->data['email'];
		$order_data['telephone'] = $this->session->data['telephone'];
		
		if ($this->customer->isLogged()) {
			$customer_id = $this->customer->getId();
			$customer_info = $this->model_account_customer->getCustomer($customer_id);
			$order_data['customer_id'] = $customer_id;
			$order_data['customer_group_id'] = $customer_info['customer_group_id'];
			$order_data['fax'] = $customer_info['fax'];
		} elseif(isset($this->session->data['approve_customer'])) {
			$customer_id = $this->session->data['customer_id'];
			$customer_info = $this->model_account_customer->getCustomer($customer_id);
			$order_data['customer_id'] = $customer_id;
			$order_data['customer_group_id'] = $customer_info['customer_group_id'];
			$order_data['fax'] = $customer_info['fax'];
			
			unset($this->session->data['approve_customer']);
		} else {
			$order_data['customer_id'] = 0;
			$order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
			$order_data['fax'] = $this->session->data['guest']['fax'];
		}
			
		$order_data['custom_field'] = isset($this->session->data['custom_field']) ? $this->session->data['custom_field'] : [];

		$order_data['payment_firstname'] = $order_data['firstname'];
		$order_data['payment_lastname'] = $order_data['lastname'];
		$order_data['payment_company'] = $this->session->data['payment_address']['company'];
		$order_data['payment_city'] = $this->session->data['payment_address']['city'];
		$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
		$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
		$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
		$order_data['payment_country'] = $this->session->data['payment_address']['country'];
		$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
		$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
		$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());
		$order_data['payment_address_1'] =  $this->session->data['payment_address']['address_1'];
		$order_data['payment_address_2'] =  $this->session->data['payment_address']['address_2'];

		$order_data['payment_method'] = isset($this->session->data['payment_method']['title']) ? $this->session->data['payment_method']['title'] : '';
		$order_data['payment_code'] = isset($this->session->data['payment_method']['code']) ? $this->session->data['payment_method']['code'] : '';

		if ($this->cart->hasShipping()) {
			$order_data['shipping_firstname'] = $order_data['firstname'];
			$order_data['shipping_lastname'] = $order_data['lastname'];
			$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
			$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
			$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
			$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
			$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
			$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
			$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
			$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
			$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());
			$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
			$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];

			$order_data['shipping_method'] = isset($this->session->data['shipping_method']['title']) ? $this->session->data['shipping_method']['title'] : '';
			$order_data['shipping_code'] = isset($this->session->data['shipping_method']['code']) ? $this->session->data['shipping_method']['code'] : '';
		} else {
			$order_data['shipping_firstname'] = '';
			$order_data['shipping_lastname'] = '';
			$order_data['shipping_company'] = '';
			$order_data['shipping_address_1'] = '';
			$order_data['shipping_address_2'] = '';
			$order_data['shipping_city'] = '';
			$order_data['shipping_postcode'] = '';
			$order_data['shipping_zone'] = '';
			$order_data['shipping_zone_id'] = '';
			$order_data['shipping_country'] = '';
			$order_data['shipping_country_id'] = '';
			$order_data['shipping_address_format'] = '';
			$order_data['shipping_custom_field'] = [];
			$order_data['shipping_method'] = '';
			$order_data['shipping_code'] = '';
		}

		$order_data['products'] = [];

		foreach ($this->cart->getProducts() as $product) {
			$option_data = [];

			foreach ($product['option'] as $option) {
				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type']
				);
			}

			$order_data['products'][] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']
			);
		}

		$order_data['vouchers'] = [];

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$order_data['vouchers'][] = array(
					'description'      => $voucher['description'],
					'code'             => substr(md5(mt_rand()), 0, 10),
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount']
				);
			}
		}

		$order_data['comment'] = $this->session->data['comment'];
			
		$order_data['total'] = $total;
		
		if (isset($this->request->cookie['tracking'])) {
			$order_data['tracking'] = $this->request->cookie['tracking'];

			$subtotal = $this->cart->getSubTotal();

			// Affiliate
			$affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

			if ($affiliate_info) {
				$order_data['affiliate_id'] = $affiliate_info['customer_id'];
				$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
			}

			// Marketing
			$this->load->model('checkout/marketing');

			$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

			if ($marketing_info) {
				$order_data['marketing_id'] = $marketing_info['marketing_id'];
			} else {
				$order_data['marketing_id'] = 0;
			}
		} else {
			$order_data['affiliate_id'] = 0;
			$order_data['commission'] = 0;
			$order_data['marketing_id'] = 0;
			$order_data['tracking'] = '';
		}

		$order_data['language_id'] = $this->config->get('config_language_id');
		$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
		$order_data['currency_code'] = $this->session->data['currency'];
		$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
		} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
		} else {
			$order_data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
		} else {
			$order_data['user_agent'] = '';
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
		} else {
			$order_data['accept_language'] = '';
		}

		$this->load->model('checkout/order');
		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
		
		header('Content-Type:text/html; charset=UTF-8');		
		return $this->load->controller('extension/payment/'.$this->session->data['payment_method']['code']);
  	}
	
	public function country() {
		$json = [];
		
		$this->load->model('localisation/country');

    	$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);
		
		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']		
			);
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	private function getNewPassword() {
		$chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP'; 
		$pass_length = 8; 
		$chars_length = utf8_strlen($chars)-1; 
		$password = ''; 

		while($pass_length--) {
			$password .= $chars[rand(0, $chars_length)];
		}
		
		return $password;
	}
	
	private function products_related() {
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		
		$this->load->model('tool/image');
		$this->load->model('extension/module/uni_related');
		$this->load->model('extension/module/uni_new_data');
		
		$currency = $this->session->data['currency'];
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$data['checkout_related_text'] = $uniset[$language_id]['checkout_related_text'];						
			
		$products = [];
		
		if($this->cart->getProducts()) {
			
			$results = $this->model_extension_module_uni_related->getRelated();
				
			foreach ($results as $result) {

				$image = $result['image'] ? $this->model_tool_image->resize($result['image'], 110, 110) : '';
				
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$price = false;
				}
						
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$special = false;
				}
					
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $currency);
				} else {
					$tax = false;
				}
				
				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
				
				$new_data = $this->model_extension_module_uni_new_data->getNewData($result);
				$show_description = isset($uniset['show_description']) && !isset($uniset['show_description_alt']) || isset($uniset['show_description_alt']) && !$new_data['attributes'] ? true : false;
			
				if($new_data['special_date_end']) {
					$data['show_timer'] = true;
				}
					
				if($result['quantity'] > 0)	{
					$products[] = array(
						'product_id' 			=> $result['product_id'],
						'thumb'   	 			=> $image,
						'name'    				=> $result['name'],
						'description' 			=> utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
						'price'   	 			=> $price,
						'special' 	 			=> $special,
						'price_value' 			=> $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
						'special_value' 		=> $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
						'tax'        			=> $tax,
						'rating'     			=> $rating,
						'additional_image'		=> '',
						'num_reviews' 			=> $result['reviews'],
						'quantity' 				=> $result['quantity'],
						'minimum' 				=> $result['minimum'],
						'stickers' 				=> $new_data['stickers'],
						'options'				=> $new_data['options'],
						'reviews'   			=> sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
						'href'    				=> $this->url->link('product/product', 'product_id=' . $result['product_id']),
						'cart_btn_disabled' 	=> $result['quantity'] <= 0 && isset($uniset['cart_btn_disabled']) ? $uniset['cart_btn_disabled'] : '',
						'cart_btn_icon_mobile' 	=> $result['quantity'] <= 0 && isset($uniset['cart_btn_icon_disabled_mobile']) ? $uniset['cart_btn_icon_disabled_mobile'] : '',
						'cart_btn_icon' 		=> $result['quantity'] > 0 ? $uniset[$language_id]['cart_btn_icon'] : $uniset[$language_id]['cart_btn_icon_disabled'],
						'cart_btn_text' 		=> $result['quantity'] > 0 ? $uniset[$language_id]['cart_btn_text'] : $uniset[$language_id]['cart_btn_text_disabled'],
					);
				}
			}
		}
				
		return $products;
	}
}