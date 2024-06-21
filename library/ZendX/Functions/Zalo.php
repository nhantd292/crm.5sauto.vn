<?php
namespace ZendX\Functions;

use ZendX\Controller\ActionController;

class Zalo {
    
    public function __construct() {
    }

    public function formatToData($dataSource){
        $result     = null;
        if($dataSource != '') {
            $result = preg_replace('/\D/', '', $dataSource);
            if(substr(trim($dataSource), 0, 1) == '-') {
                $result = '-'. preg_replace('/\D/', '', $dataSource);
            }
        }
		return (int)$result;
	}
}