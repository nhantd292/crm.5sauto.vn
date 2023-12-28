<?php
namespace ZendX\Functions;

class CreateIcon {

    public static function create($value, $options = null){
        $xIcon = '';
        if(!empty($value)) {
            $xIcon = '<i class="fa '. $value .'"></i> ';
        }
		
		return $xIcon;
	}
}