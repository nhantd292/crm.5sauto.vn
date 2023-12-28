<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class LinkChecking extends InputFilter {
	
	public function __construct($options = null){
	    $userInfo      = new \ZendX\System\UserInfo();
	     
	    $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();
	    $optionId      = $options['id'];
	    $optionData    = $options['data'];
	    $optionRoute   = $options['route'];

        $exclude = null;
        if(!empty($options['id'])) {
            $exclude = array(
                'field' => 'id',
                'value' => $options['id']
            );
        }
	    
		// Name
		$this->add(array(
			'name'		=> 'link',
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
				),
				array(
                    'name'		=> 'DbNoRecordExists',
                    'options'	=> array(
                        'table'   => TABLE_LINK_CHECKING,
                        'field'   => 'link',
                        'adapter' => GlobalAdapterFeature::getStaticAdapter(),
                        'exclude' => $exclude,
                        'messages'	=> array(
                            \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Đã tồn tại'
                        )
                    ),
                    'break_chain_on_failure'	=> true
                )
			)
		));
		
		// Kênh marketing
		$this->add(array(
		    'name'		=> 'marketing_channel_id',
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

		// Kênh marketing
//		$this->add(array(
//		    'name'		=> 'product_group_id',
//		    'required'	=> true,
//		    'validators'	=> array(
//		        array(
//		            'name'		=> 'NotEmpty',
//		            'options'	=> array(
//		                'messages'	=> array(
//		                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
//		                )
//		            ),
//		            'break_chain_on_failure'	=> true
//		        )
//		    )
//		));
		
		// Campaign
		// $this->add(array(
		//     'name'		=> 'campaign',
		//     'required'	=> true,
		//     'validators'	=> array(
		//         array(
		//             'name'		=> 'NotEmpty',
		//             'options'	=> array(
		//                 'messages'	=> array(
		//                     \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
		//                 )
		//             ),
		//             'break_chain_on_failure'	=> true
		//         )
		//     )
		// ));
	}
}