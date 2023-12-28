<?php
namespace ZendX\Mail;

use Zend\Mime\Mime;
class Mail {
    
    protected $_config = array(
        'name'				=> 'localhost',
        'host'				=> 'smtp.gmail.com',
        'port'				=> 587,
        'connection_class'	=> 'login',
        'connection_config'	=> array(
            'username'      => 'no-reply@langmaster.edu.vn',
            'password'      => 'langmaster@222',
            'ssl'           => 'tls'
        ),
    );
    
    public function __construct($config = null) {
        if(!empty($config)) {
            $this->_config = array_merge($this->_config, $config);
        }
    }

    public function sendMail($options) {
        $config	= new \Zend\Mail\Transport\SmtpOptions($this->_config);
        
        $message = new \Zend\Mail\Message();
        $message->setFrom($this->_config['connection_config']['username'], $options['fromName']);
        $message->setSubject($options['subject']);
        $message->setEncoding('UTF-8');
        
        if(!empty($options['to'])) {
            $message->setTo($options['to'], $options['toName']);
        }
        
        if(!empty($options['cc'])) {
            $message->setCc($options['cc'], $options['toName']);
        }
        
        if(!empty($options['bcc'])) {
            $message->setBcc($options['bcc'], $options['toName']);
        }
        
        if(!empty($options['reply'])) {
            $message->setReplyTo($options['reply']);
        }
        
        // HTML
        $content			= new \Zend\Mime\Part($options['content']);
        $content->type		= Mime::TYPE_HTML;
        $content->charset	= 'UTF-8';
        
        $mimeMessage		= new \Zend\Mime\Message();
        $mimeMessage->setParts(array($content));
        
        $message->setBody($mimeMessage);
        
        $transport	= new \Zend\Mail\Transport\Smtp($config);
        $transport->send($message);
    }
}