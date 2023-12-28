<?php
namespace ZendX\View\Helper\Url;
use Zend\View\Helper\AbstractHelper;

class LinkAdminDefault extends AbstractHelper {
	
	public function __invoke($options = null) {
	    $urlHelper	= $this->getView()->plugin('url');
	    
	    $options['controller'] = !empty($options['controller']) ? $options['controller'] : 'index';
	    $options['action']     = !empty($options['action']) ? $options['action'] : 'index';
	    $options['route']      = !empty($options['route']) ? $options['route'] : 'routeAdmin/default';
	    $options['id']         = !empty($options['id']) ? $options['id'] : null;
	    
	    $arrParam = array( 'controller' => $options['controller'], 'action' => $options['action']);
	    if(!empty($options['id'])) {
	        $arrParam['id'] == $options['id'];
	    }
	    
        return $urlHelper($options['route'], $arrParam);
    }
}