<?php
namespace ZendX\Functions;

class CreateAttribute {

    public static function create($value, $options = null){
        $xAttr = '';
        if(!empty($value)) {
            foreach ($value AS $key => $value) {
                $xAttr .= ' '. $key . '="' . $value . '"';
            }
        }
		
		return $xAttr;
	}
}