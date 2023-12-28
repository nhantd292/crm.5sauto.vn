<?php
namespace ZendX\Functions;

class WordFirst {

    protected $_delimiter;
    protected $_firstWord;
    
    public function __construct($delimiter = '-', $firstWord = '') {
        $this->_delimiter = $delimiter;
        $this->_firstWord = $firstWord;
    }
    
    public function upperFirst($string) {
        $arrTmp = explode($this->_delimiter, $string);
	    $return = '';
	    foreach ($arrTmp AS $word) {
	        $return .= $this->_firstWord . ucfirst($word);
	    }
	    
	    return trim($return);
    }
    
    public function lowerFirstUpper($string) {
        return strtolower(preg_replace('/\B([A-Z])/', '-$1', $string));
    }
}