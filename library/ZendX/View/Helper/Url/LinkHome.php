<?php
namespace ZendX\View\Helper\Url;
use Zend\View\Helper\AbstractHelper;

class LinkHome extends AbstractHelper {
	
	public function __invoke($options = null) {
	    $urlHelper	= $this->getView()->plugin('url');
	    
	    $result = $urlHelper('routeHome');
	    
        return $result;
    }
}