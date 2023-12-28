<?php
namespace Admin\Filter\Production;

use Zend\InputFilter\InputFilter;

class Edit extends InputFilter {
	
	public function __construct($sm, $options = null){
		// Bộ phận sản xuất
		$production = $sm->get('Admin\Model\ContractTable')->getItem(array('id' => $options['data']['id']));
		$productionType = $sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-type')), array('task' => 'cache'));
		$productionTypeAlias = $productionType[$production['production_type_id']]['alias'];

		$param_data = $options['data'];
        $validateShipper = false;
		if ($productionTypeAlias == DON_HA_NOI) {
			if ($param_data['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST || $production['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST) {
				$validateShipper = true;
			}
		}

		if ($production['production_department_type'] == STATUS_CONTRACT_PRODUCT_POST) {
			$validate = false;
		} else {
			$validate = true;
		}

		// Bộ phận sản xuất
		$this->add(array(
		    'name'		=> 'production_department_type',
            'required'	=> $validate,
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
        
		// Ngày hoàn thành sản xuất
		$this->add(array(
		    'name'		=> 'production_date',
            'required'	=> false,
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
        
		// Ghi chú sản xuất
		$this->add(array(
		    'name'		=> 'production_note',
		    'required'	=> false,
		));

		// Nhân viên giao hàng
		$this->add(array(
		    'name'		=> 'shipper_id',
            'required'	=> $validateShipper,
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