<?php
class Wsu_Networksecurities_Sso_WploginController extends Mage_Core_Controller_Front_Action{

	/**
	* getToken and call profile user WordPress
	**/
    public function loginAction($name_blog) {
		
		$wp = Mage::getModel('wsu_networksecurities/sso_wplogin')->newWp();       
		$userId = $wp->mode;        
		$coreSession = Mage::getSingleton('core/session');
		if(!$userId) {
            $wp_session = Mage::getModel('wsu_networksecurities/sso_wplogin')->setWpIdlogin($aol, $name_blog);
            $url = $wp_session->authUrl();
			echo "<script type='text/javascript'>top.location.href = '$url';</script>";
			exit;
		}else{ if (!$wp->validate()) {                
               $wp_session = Mage::getModel('wsu_networksecurities/sso_wplogin')->setWpIdlogin($aol, $name_blog);
                $url = $wp_session->authUrl();
                echo "<script type='text/javascript'>top.location.href = '$url';</script>";
                exit;
            }else{ $user_info = $wp->getAttributes();                 
                if(count($user_info)) {
                    $frist_name = $user_info['namePerson/first'];
                    $last_name = $user_info['namePerson/last'];
                    $email = $user_info['contact/email'];
					
					//get website_id and sote_id of each stores
					$store_id = Mage::app()->getStore()->getStoreId();
					$website_id = Mage::app()->getStore()->getWebsiteId();
					
                    if(!$frist_name) {
                        if($user_info['namePerson/friendly']) {
                        $frist_name = $user_info['namePerson/friendly'] ;   
                        }else{ $email = explode("@", $email);
                            $frist_name = $email['0'];
                        }                   
                    }

                    if(!$last_name) {
                        $last_name = '_wp';
                    }
                    $data = array('firstname'=>$frist_name, 'lastname'=>$last_name, 'email'=>$user_info['contact/email']);
                    $customer = Mage::helper('wsu_networksecurities/customer')->getCustomerByEmail($data['email'], $website_id);
                    if(!$customer || !$customer->getId()) {
						//Login multisite
						$customer = Mage::helper('wsu_networksecurities/customer')->createCustomerMultiWebsite($data, $website_id, $store_id );
						if (Mage::getStoreConfig('wsu_networksecurities/wplogin/is_send_password_to_customer')) {
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
    }
    
    public function setBlockAction() {             
        /*$template =  $this->getLayout()->createBlock('sociallogin/wplogin')
                ->setTemplate('sociallogin/au_wp.phtml')->toHtml();
        echo $template;*/
		$this->loadLayout();
		$this->renderLayout();
    }
    
    public function setBlogNameAction() {
        $data = $this->getRequest()->getPost();		
		$name = $data['name'];
        if($name) {            
            $url = Mage::getModel('wsu_networksecurities/sso_wplogin')->getWpLoginUrl($name);			
            $this->_redirectUrl($url);
        }else{ Mage::getSingleton('core/session')->addError('Please enter Blog name!');	
            Mage::helper('wsu_networksecurities/customer')->setJsRedirect(Mage::getBaseUrl());
        }
    }
}