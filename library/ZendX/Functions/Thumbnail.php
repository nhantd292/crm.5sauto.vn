<?php
namespace ZendX\Functions;

class Thumbnail {

    protected $url;
    protected $first;
    protected $last;
    
    public function __construct($url) {
        $this->url = $url;
        
        if(empty($url))
            return false;
        
        $obj = explode('/thumbs/images/', $url);
        if(empty($obj[1])) {
            $obj = explode('/medium/images/', $url);
            if(empty($obj[1])) {
                $obj = explode('/images/', $url);
            }
        }
        
        if(!empty($obj[1])) {
            $this->first    = $obj[0];
            $this->last     = $obj[1];
        }
    }
    
    public function getThumb(){
        if(!empty($this->last))
            return $this->first . '/thumbs/images/' . $this->last;
		
		return $this->url;
	}
	
    public function getMedium(){
		if(!empty($this->last))
            return $this->first . '/medium/images/' . $this->last;
		
		return $this->url;
	}
	
    public function getFull(){
        if(!empty($this->last))
            return $this->first . '/images/' . $this->last;
        
		return $this->url;
	}
}