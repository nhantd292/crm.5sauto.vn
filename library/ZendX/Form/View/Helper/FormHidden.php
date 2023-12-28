<?php

namespace ZendX\Form\View\Helper;

use Zend\Form\View\Helper\FormHidden AS ZendFormHidden;
use Zend\Form\Element\Hidden;

class FormHidden extends ZendFormHidden {

    public function __invoke($name, $value) {
        $element = new Hidden($name);
    	$element->setValue($value);
    	
    	return $this->render($element);
    }
}
