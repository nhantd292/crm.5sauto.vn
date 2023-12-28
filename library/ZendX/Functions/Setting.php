<?php
namespace ZendX\Functions;

class Setting {

    protected $_setting;
    
    public function __construct($setting) {
        $this->_setting = $setting;
    }
    
    public function getByCode($code, $field = null, $options = null) {
        $result = array();
        
        $left = 0;
        $right = 0;
        foreach ($this->_setting AS $setting) {
            if($left != 0) {
                if($setting['left'] >= $left && $setting['right'] <= $right) {
                    $result[$setting['id']] = $setting;
                } else {
                    break;
                }
            }
            
            if($setting['code'] == $code) {
                $result[$setting['id']] = $setting;
                $left = $setting['left'];
                $right = $setting['right'];
                
                if($right - $left == 1) {
                    break;
                }
            }
        }
        
        if($field != null) {
            $result = current($result);
            $result = $result[$field];
        }
        
        return $result;
    }
}