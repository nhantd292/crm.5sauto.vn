<?php

namespace ZendX\Form\View\Helper;

use Zend\Form\View\Helper\FormInput AS ZendFormInput;
use Zend\Form\Element\Text;

class FormInput extends ZendFormInput {

    public function __invoke($name, $value, $attributes = null) {
        $attributes['id'] = trim(!empty($attributes['id']) ? $attributes['id'] : $name);
        
        $element = new Text($name);
    	$element->setValue($value);
    	$element->setAttributes($attributes);
    	
    	return $this->render($element);
    }
}
