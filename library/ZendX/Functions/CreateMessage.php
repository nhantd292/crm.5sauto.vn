<?php
namespace ZendX\Functions;

use Zend\Session\Container;

class CreateMessage {
    const SUCCESS = 'success';
    const DANGER = 'danger';
    
    public function __construct($string = null) {

    }

    public function createMessage($params) {
        if(!empty($params['message'])){
            $string = '<div class="alert alert-block alert-'.$params['type'].'">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                            <p>'.$params['message'].'</p>
                        </div>';
            return $string;
        }
        else{
            return null;
        }
    }

    public function createMessageSuccess($message) {
        return $this->createMessage(array('message' => $message, 'type' => self::SUCCESS));
    }

    public function createMessageDanger($message) {
        return $this->createMessage(array('message' => $message, 'type' => self::DANGER));
    }
}