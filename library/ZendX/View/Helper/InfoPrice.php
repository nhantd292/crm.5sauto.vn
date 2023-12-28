<?php
namespace ZendX\View\Helper;
use Zend\View\Helper\AbstractHelper;

class InfoPrice extends AbstractHelper {
	
	public function __invoke($price, $sale_off = null, $options = null){
	    $strPrice = '';
	    if(!empty($price)) {
            $strPrice       = '<span class="price_value"><span class="mask_currency">'. $price . '<span><span class="unit">'. $options['unit'] .'</span></span>';
            if(!empty($sale_off['sale_percent']) || !empty($sale_off['sale_price'])) {
                $price_old      = $price;
                $price_value    = '';
                
                if(!empty($sale_off['sale_percent'])) {
                    $price_value  = $price - ($price * $sale_off['sale_percent'])/100;
                }
                
                if(!empty($sale_off['sale_price'])) {
                    if(!empty($price_value)) {
                        $price_value  = $price_value - $sale_off['sale_price'];
                    } else {
                        $price_value  = $price - $sale_off['sale_price'];
                    }
                }
                    
                $strPrice   = sprintf('<span class="price_old"><span class="mask_currency">%s</span><span class="unit">%s</span></span><span class="price_value"><span class="mask_currency">%s</span><span class="unit">%s</span></span>', $price_old, $options['unit'], $price_value, $options['unit']);
            }
            
            $strPrice = '<div class="price">'. $strPrice .'</div>';
	    }
	    
        return $strPrice;
	}
}