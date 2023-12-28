<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;

class TaskProjectContent extends InputFilter {
	
	public function __construct($options = null){
	    $exclude = null;
	    if(!empty($options['id'])) {
	        $exclude = array(
	            'field' => 'id',
	            'value' => $options['id']
	        );
	    }
	    
		// Name
		$this->add(array(
			'name'		=> 'name',
			'required'	=> true,
			'validators'	=> array(
				array(
					'name'		=> 'NotEmpty',
				    'options'	=> array(
				        'messages'	=> array(
				            \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
				        )
				    ),
					'break_chain_on_failure'	=> true
				)
			)
		));
	    
		$this->add(array(
			'name'		=> 'content_by',
			'required'	=> false,
		));
	    
		$this->add(array(
			'name'		=> 'camera_by',
			'required'	=> false,
		));
	    
		$this->add(array(
			'name'		=> 'editor_by',
			'required'	=> false,
		));
	    
		$this->add(array(
			'name'		=> 'youtube_by',
			'required'	=> false,
		));
	    
		$this->add(array(
			'name'		=> 'facebook_by',
			'required'	=> false,
		));
	}
}