<?php
namespace ZendX\View\Helper\Url;
use Zend\View\Helper\AbstractHelper;

class LinkAdminHtml extends AbstractHelper {
	
	public function __invoke($url, $options = null) {
	    $urlHelper = $this->getView()->plugin('url');
	    $type      = $options['type'];
	    $attrs     = '';
	    if(!empty($options['title'])) {
	        $attrs .= ' title="'. $options['title'] .'"';
	    }
	    if(!empty($options['class'])) {
	        $attrs .= ' class="'. $options['class'] .'"';
	    }
	    if(!empty($options['target'])) {
	        $attrs .= ' target="'. $options['target'] .'"';
	    }
	    if(!empty($options['onclick'])) {
	        $attrs .= ' onclick="'. $options['onclick'] .'"';
	    }
	    
	    switch ($type) {
	        case 'icon': 
	            $value = '<i class="'. $options['icon'] .'"></i>';
	            break;
            case 'icontext':
                $value = '<i class="'. $options['icon'] .'"></i> '. $options['value'];
                break;
            default:
                $value = $options['value'];
                break;
	    }
	    
	    $xhtml = sprintf('<a href="%s"%s>%s</a>', $url, $attrs, $value);
	    
        return $xhtml;
    }
}