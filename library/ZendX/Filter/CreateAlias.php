<?php
namespace ZendX\Filter;

use Zend\Filter\FilterInterface;

class CreateAlias implements FilterInterface {
	
	public function filter($value){
		$filterChain	= new \Zend\Filter\FilterChain();
		$filterChain->attach(new \Zend\Filter\StringTrim())
					->attach(new \Zend\Filter\PregReplace(array('pattern' => '#(@|&|,|-|\'|\.)+#', 'replacement' => '')))
					->attach(new \Zend\Filter\PregReplace(array('pattern' => '#\s+#', 'replacement' => ' ')))
					->attach(new \ZendX\Filter\RemoveCircumflex())
					->attach(new \Zend\Filter\StringToLower('UTF-8'))
					->attach(new \Zend\Filter\Word\SeparatorToDash());
		 
		return $filterChain->filter($value);
	}
}