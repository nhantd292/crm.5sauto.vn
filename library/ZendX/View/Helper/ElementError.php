<?php
namespace ZendX\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormElementErrors;

class ElementError extends FormElementErrors {
	
	public function __invoke(ElementInterface $element = null, array $attributes = array()) {
       	$messages = $element->getMessages();
       	if(empty($messages)) {
       	    return '';
       	}
       	
       	return sprintf('<p class="show-error">%s</p>', current($messages));
    }
}