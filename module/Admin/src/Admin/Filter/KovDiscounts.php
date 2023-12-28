<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class KovDiscounts extends InputFilter {
	
	public function __construct($options = null){
	    $validators = array(
            array(
                'name'		=> 'NotEmpty',
                'options'	=> array(
                    'messages'	=> array(
                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Giá trị này không được để trống'
                    )
                ),
                'break_chain_on_failure'	=> true
            )
        );

		// Name
		$this->add(array(
			'name'		=> 'name',
			'required'	=> true,
			'validators'	=> $validators,
		));

		$this->add(array(
			'name'		=> 'status',
			'required'	=> true,
			'validators'	=> $validators,
		));

		$this->add(array(
			'name'		=> 'date_begin',
			'required'	=> true,
			'validators'	=> $validators,
		));

		$this->add(array(
			'name'		=> 'date_end',
			'required'	=> true,
			'validators'	=> $validators,
		));

		$this->add(array(
			'name'		=> 'discounts_range_branchs',
			'required'	=> true,
			'validators'	=> $validators,
		));

        $validate_braches = $options['data']['discounts_range_branchs'] == 'all' ? false : true;
        $this->add(array(
            'name'		=> 'discounts_range_branchs_detail',
            'required'	=> $validate_braches,
            'validators'	=> $validators,
        ));

		$this->add(array(
			'name'		=> 'discounts_range_sales',
			'required'	=> true,
			'validators'	=> $validators,
		));

        $validate_sales = $options['data']['discounts_range_sales'] == 'all' ? false : true;
        $this->add(array(
            'name'		=> 'discounts_range_sales_detail',
            'required'	=> $validate_sales,
            'validators'	=> $validators,
        ));

		$this->add(array(
			'name'		=> 'discounts_range_customers',
			'required'	=> true,
			'validators'	=> $validators,
		));

        $validate_customer = $options['data']['discounts_range_customers'] == 'all' ? false : true;
        $this->add(array(
            'name'		=> 'discounts_range_customers_detail',
            'required'	=> $validate_customer,
            'validators'	=> $validators,
        ));

        $this->add(array(
            'name'		=> 'discounts_type',
            'required'	=> true,
            'validators'	=> $validators,
        ));

        $this->add(array(
            'name'		=> 'discounts_option',
            'required'	=> true,
            'validators'	=> $validators,
        ));
	}
}