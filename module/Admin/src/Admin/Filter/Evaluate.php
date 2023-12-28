<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Evaluate extends InputFilter {
	
	public function __construct($options = null){
        $data = $options['data'];

		// Mức độ hài lòng nhân viên sale
		$this->add(array(
		    'name'		=> 'contract_id',
		    'required'	=> true,

		));

		$this->add(array(
		    'name'		=> 'sale_level',
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
		if(!empty($data['sale_level']) && $data['sale_level'] < 4){
            $this->add(array(
                'name'		=> 'sale_note',
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
		}

		// Mức độ hài lòng sản phẩm
		$this->add(array(
		    'name'		=> 'technical_level',
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
            'name'		=> 'technical_product',
            'required'	=> false,
        ));
        if(!empty($data['technical_level']) && $data['technical_level'] < 4){
            $this->add(array(
                'name'		=> 'technical_note',
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
                'name'		=> 'technical_product',
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
        }

        if($data['evaluate_tailors']){
            // Mức độ hài lòng thợ may
            $this->add(array(
                'name'		=> 'tailors_level',
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
                'name'		=> 'tailors_product',
                'required'	=> false,
            ));
            if(!empty($data['tailors_level']) && $data['tailors_level'] < 4){
                $this->add(array(
                    'name'		=> 'tailors_note',
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
                    'name'		=> 'tailors_product',
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
            }
        }
        else{
            $this->add(array(
                'name'		=> 'tailors_level',
                'required'	=> false,
            ));
            $this->add(array(
                'name'		=> 'tailors_product',
                'required'	=> false,
            ));
            $this->add(array(
                'name'		=> 'tailors_note',
                'required'	=> false,
            ));
        }

	}
}