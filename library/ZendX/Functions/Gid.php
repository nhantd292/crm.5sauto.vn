<?php
namespace ZendX\Functions;

class Gid {
    protected $_string;
    
    public function __construct($string = null) {
        $this->_string = $this->_string ? $this->_string : '0123456789abcde0123456789fghi0123456789jklm0123456789nopqrst0123456789uvwxyz0123456789';
    }
    
    public function getId() {
        return @time() . substr(str_shuffle($this->_string), 0, 12);
    }
    
    public function random($length = 10) {
        return substr(str_shuffle($this->_string), 0, $length);
    }
}