<?php
namespace ZendX\Functions;

class CreateArray {

    public static function create($datasource, $options = null){
        $array = array();
        if($options['value'] != 'object') {
            $space = '';
            $sprintf = $options['sprintf'] ? $options['sprintf'] : '%s';
            
            foreach ($datasource AS $data) {
                if($options['level'] == true) {
                    $level_start = !empty($options['level_start']) ? $options['level_start'] : 0;
                    $space = str_repeat('---- ', $data['level'] - $level_start);
                }
                
                $optionValue = explode(',', $options['value']);
                $resultValue = array();
                foreach ($optionValue AS $value) {
                    $resultValue[] = $data[trim($value)];
                }
                
                if($options['strtolower'] === true) {
                    $array[mb_strtolower($data[$options['key']])] = $space . vsprintf($sprintf, $resultValue);
                } else {
                    $array[$data[$options['key']]] = $space . vsprintf($sprintf, $resultValue);
                }
            }
        } else {
            foreach ($datasource AS $data) {
                if($options['strtolower'] === true) {
                    $array[mb_strtolower($data[$options['key']])] = $data;
                } else {
                    $array[$data[$options['key']]] = $data;
                }
            }
        }
		
		return $array;
	}

    public static function createSelect($datasource, $options = null){
        $array = array();
        $space = '';
        $symbol = $options['symbol'] ? $options['symbol'] : ' | ';
        
        foreach ($datasource AS $data) {
            if($options['level'] == true) {
                $level_start = !empty($options['level_start']) ? $options['level_start'] : 0;
                $space = str_repeat('---- ', $data['level'] - $level_start);
            }
            
            $optionValue = explode(',', $options['value']);
            $resultValue = array();
            foreach ($optionValue AS $value) {
                if(!empty($data[trim($value)])) {
                    $resultValue[] = $data[trim($value)];
                }
            }
            
            $array[$data[$options['key']]] = $space . implode($symbol, $resultValue);
        }
		
		return $array;
	}
}