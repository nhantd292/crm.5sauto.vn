<?php

namespace ZendX\Form\View\Helper;

use Zend\Form\View\Helper\FormButton AS ZendFormButton;
use Zend\Form\Element\Button;

class FormButton extends ZendFormButton {

    public function __invoke($name, $value, $label, $attributes = null) {
        $attributes['class'] = trim('btn ' . (!empty($attributes['class']) ? $attributes['class'] : 'input-small'));
        $attributes['type'] = trim(!empty($attributes['type']) ? $attributes['type'] : 'submit');
        $attributes['id'] = trim(!empty($attributes['id']) ? $attributes['id'] : $name);
        
        $element = new Button($name);
    	$element->setValue($value);
    	$element->setAttributes($attributes);
    	$element->setLabel($label);
    	
    	return $this->render($element);
    }
}
