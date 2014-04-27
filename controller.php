<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

class CoreCommerceFreeOrderPackage extends Package {

	protected $pkgHandle = 'core_commerce_free_order';
	protected $appVersionRequired = '5.6.2.1';
	protected $pkgVersion = '0.9';
	
	public function getPackageDescription() {
		return t("A payment method to handle zero value orders in eCommerce");
	}
	
	public function getPackageName() {
		return t("Free Order method for eCommerce");
	}
	
	public function getPackageHandle() {
		return $this->pkgHandle;
	}
     
     
    public function on_start() {
     	Events::extend('core_commerce_on_get_payment_methods', 'CoreCommerceFreeOrderPackage', 'adjustMethods', __FILE__);
    
    }
    
    public function adjustMethods($order, $methods) {
    	$output = array();
    	 
    	if ($order->getOrderTotal() == 0) {
    		foreach($methods as $m) {
    			if ($m->paymentMethodHandle == 'free_order') {
    				$output[] = $m;	
    			}
    		}
    		
    	} else {
    		foreach($methods as $m) {
    			if ($m->paymentMethodHandle != 'free_order') {
    				$output[] = $m;	
    			}
    		}
     	}
   	
   		return $output;  	
    }
    
    
    
	public function install() {
		$installed = Package::getInstalledHandles();
		
		if( !(is_array($installed) && in_array('core_commerce',$installed)) ) {
			throw new Exception(t('This package requires that at least version 2.8.3 of the <a href="http://www.concrete5.org/marketplace/addons/ecommerce/" target="_blank">eCommerce package</a> is installed<br/>'));	
		}
		
		$pkg = Package::getByHandle('core_commerce');
		if (!is_object($pkg) || version_compare($pkg->getPackageVersion(), '2.8.3', '<')) {
			throw new Exception(t('You must upgrade the eCommerce add-on to version 2.8.3 or higher.'));
		}
		
		$pkg = parent::install();
		Loader::model('payment/method','core_commerce'); 
		
		// add payment method
		CoreCommercePaymentMethod::add('free_order', 'Free Order', false, NULL, $pkg);	 
	}
	 
	public function uninstall() {
		// remove the payment method from the database
		Loader::model('payment/method','core_commerce'); 
		$pm = CoreCommercePaymentMethod::getByHandle('free_order');
		
		if ($pm) {
			$pm->delete();
		}
		
		parent::uninstall();
	}
     
}
