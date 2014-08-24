<?php
class Wsu_Networksecurities_Sso_OrgloginController extends Mage_Core_Controller_Front_Action{

	/**
	* getToken and call profile user Orange
	**/
    public function loginAction() {     
		
		$org = Mage::getModel('wsu_networksecurities/sso_orglogin')->newOrg();            
		$coreSession = Mage::getSingleton('core/session');                      
		$user_info = $org->data;                 
		if(count($user_info)) {
			$frist_name = $user_info['openid_sreg_nickname'];
			$last_name = $user_info['openid_sreg_nickname'];
			$email = $user_info['openid_sreg_email'];                    
			
			//get website_id and sote_id of each stores
			$store_id = Mage::app()->getStore()->getStoreId();//add
			$website_id = Mage::app()->getStore()->getWebsiteId();//add
			
			$data = array('firstname'=>$frist_name, 'lastname'=>$last_name, 'email'=>$email);
			
			$customer = Mage::helper('wsu_networksecurities/customer')->getCustomerByEmail($data['email'],$website_id);
			if(!$customer || !$customer->getId()) {
				//Login multisite
				$customer = Mage::helper('wsu_networksecurities/customer')->createCustomerMultiWebsite($data, $website_id, $store_id );
				if (Mage::getStoreConfig('wsu_networksecurities/orglogin/is_send_password_to_customer')) {
					$customer->sendPasswordReminderEmail();
				}
			}
				// fix confirmation
			if ($customer->getConfirmation()) {
				try {
					$customer->setConfirmation(null);
					$customer->save();
				}catch (Exception $e) {
				}
			}
			Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
			Mage::helper('wsu_networksecurities/customer')->setJsRedirect(Mage::helper('wsu_networksecurities/customer')->_loginPostRedirect());
		}else{ $coreSession->addError('Login failed as you have not granted access.');			
		   Mage::helper('wsu_networksecurities/customer')->setJsRedirect(Mage::getBaseUrl());
		}           
    }
}