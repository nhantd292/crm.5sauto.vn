<?php
namespace ZendX\Functions;

class Gid {
    protected $_string;
    
    public function __construct($string = null) {
//        $this->_string = $this->_string ? $this->_string : '0123456789abcde0123456789fghi0123456789jklm0123456789nopqrst0123456789uvwxyz0123456789';
        $this->_string = $this->_string ? $this->_string : 'kjasdofguas0rtlkwjer0q8r9udg9082q40trusqoidufg99a8syerqy93rqwydfy872q8734r59uqwefglakgt9w8e5ytkjdofgu0sd';
    }
    
    public function getId() {
//        return @time() . substr(str_shuffle($this->_string), 0, 12);
        return @time() . str_shuffle(substr(str_shuffle($this->_string), 0, 12));
    }
    
    public function random($length = 10) {
        return substr(str_shuffle($this->_string), 0, $length);
    }
}