<?php
namespace ZendX\Form\View\Helper;

use Zend\Form\Element\Select;
use Zend\Form\View\Helper\FormSelect AS ZendFormSelect;

class FormSelect extends ZendFormSelect {
	
	public function __invoke($name, $emptyOption, $valueOptions, $keySelected, $options = null) {
	    
	    $options['size'] = !empty($options['size']) ? $options['size'] : 1;
	    $options['class'] = !empty($options['class']) ? $options['class'] : 'form-control';
	    
        $select = new Select($name);
        $select->setAttributes($options);
        $select->setEmptyOption($emptyOption);
        $select->setValueOptions($valueOptions);
        $select->setValue($keySelected);
        return $this->render($select);
    }
}