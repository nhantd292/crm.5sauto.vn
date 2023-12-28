<?php
namespace ZendX\View\Helper;

use Zend\Form\View\Helper\FormElementErrors;

class ElementErrors extends FormElementErrors {
	
	public function __invoke(array $elementArray = null)
    {
    	if(empty($elementArray)) {
    	    return '';
    	}
    	
    	$result	= null;
    	foreach($elementArray as $key => $value){
    	    if(!empty($value['element'])) {
    	        $element = $value['element'];
    	        $messages = $element->getMessages();
    	        if(!empty($messages)) {
                    $result	.= sprintf('<p><b>%s:</b> %s</p>', ucfirst($value['label']),  current($messages)) ;
    	        }
    	    }
    	    if(!empty($value['elementGroup'])) {
    	        foreach ($value['elementGroup'] AS $element) {
        	        $messages = $element->getMessages();
        	        if(!empty($messages)) {
        	            $result	.= sprintf('<p><b>%s:</b> %s</p>', ucfirst($value['label']),  current($messages));
        	        }
    	        }
    	    }
    	}
    	
    	if($result == null) {
    	    return '';
    	}
       	return sprintf('<div class="alert alert-block alert-danger"><button type="button" class="close" data-dismiss="alert"></button>%s</div>', $result);
    }
}