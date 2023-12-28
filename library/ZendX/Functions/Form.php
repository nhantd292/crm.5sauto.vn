<?php
namespace ZendX\Functions;

class Form {

    protected $_setting;
    
    public function __construct($setting) {
        $this->_setting = $setting;
    }
    
    public function getSetting() {
        $setting = array(
            array('label' => 'Tên',             'name' => 'name',      'type' => 'text', 'require' => 'true', 'option' => 'null', 'list' => 'true'),
            array('label' => 'Số điện thoại',   'name' => 'phone',     'type' => 'text', 'require' => 'true', 'option' => 'null', 'list' => 'true'),
            array('label' => 'Email',           'name' => 'email',     'type' => 'text', 'require' => 'true', 'option' => 'null', 'list' => 'true'),
            array('label' => 'Địa chỉ',         'name' => 'address',   'type' => 'text', 'require' => 'true', 'option' => 'null', 'list' => 'true'),
        );
        
        $fields = preg_split('/\r\n|[\r\n]/', $this->_setting);
        if(!empty($fields)) {
            $tmpConfig = array();
            foreach ($fields AS $key => $value) {
                $attrs = explode('|', $value);
                $arrAttr = array();
                foreach ($attrs AS $attr) {
                    $field = explode('=', $attr);
                    $arrAttr[trim($field[0])] = trim($field[1]);
                }
                $tmpConfig[] = $arrAttr;
            }
            $setting = $tmpConfig;
        }
        
        return $setting;
    }
}