<?php
namespace ZendX\Filter;

use Zend\Filter\FilterInterface;

class RemoveCircumflex implements FilterInterface {
	
	public function filter($value){
		$charaterA			= '#(á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ)#imsU';
		$replaceCharaterA	= 'a';
		$value	= preg_replace($charaterA, $replaceCharaterA, $value);
		
		$charaterD 			= '#(đ|Đ)#imsU';
		$replaceCharaterD 	= 'd';
		$value = preg_replace($charaterD,$replaceCharaterD,$value);
		
		$charaterE 			= '#(é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ)#imsU';
		$replaceCharaterE 	= 'e';
		$value = preg_replace($charaterE,$replaceCharaterE,$value);
		
		$charaterI 			= '#(í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị)#imsU';
		$replaceCharaterI 	= 'i';
		$value = preg_replace($charaterI,$replaceCharaterI,$value);
		
		$charaterO = '#(ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ)#imsU';
		$replaceCharaterO = 'o';
		$value = preg_replace($charaterO,$replaceCharaterO,$value);
		
		$charaterU = '#(ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự)#imsU';
		$replaceCharaterU = 'u';
		$value = preg_replace($charaterU,$replaceCharaterU,$value);
		
		$charaterY = '#(ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ)#imsU';
		$replaceCharaterY = 'y';
		$value = preg_replace($charaterY,$replaceCharaterY,$value);
		
		$value = strtolower($value);
		
		return $value;
	}
}