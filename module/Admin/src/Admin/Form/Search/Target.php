<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;

class Target extends Form{
    
	public function __construct($sm, $params){
		parent::__construct();
		
		// FORM Attribute
		$this->setAttributes(array(
			'action'	=> '',
			'method'	=> 'POST',
			'class'		=> 'horizontal-form',
			'role'		=> 'form',
			'name'		=> 'adminForm',
			'id'		=> 'adminForm',
		));
		
		// Keyword
		$this->add(array(
		    'name'			=> 'filter_keyword',
		    'type'			=> 'Text',
		    'attributes'	=> array(
		        'placeholder'   => 'Nhân viên',
		        'class'			=> 'form-control input-sm',
		        'id'			=> 'filter_keyword',
		    ),
		));

        $this->add(array(
            'name'			=> 'filter_date_begin',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker not-push',
                'placeholder'	=> 'Từ ngày',
            )
        ));

        $this->add(array(
            'name'			=> 'filter_date_end',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'		    => 'form-control date-picker not-push',
                'placeholder'	=> 'Đến ngày',
            )
        ));

        // Cơ sở kinh doanh
        $this->add(array(
            'name'			=> 'filter_sale_branch',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Cơ sở -',
                'value_options'	=> \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache')), array('key' => 'id', 'value' => 'name')),
            ),
        ));

        // Đội nhóm sales
        $this->add(array(
            'name'			=> 'filter_sale_group',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Đội nhóm sales -',
                'value_options'	=> \ZendX\Functions\CreateArray::createSelect($sm->get('Admin\Model\DocumentTable')->listItem(array('data' => array('document_id' => $params['filter_sale_branch']),'where' => array('type' => 'sales')), array('task' => 'list-parent')), array('key' => 'id', 'value' => 'name,content', 'symbol' => ' - ')),
            )
        ));
		
		// Submit
		$this->add(array(
		    'name'			=> 'filter_submit',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Tìm',
		        'class'		=> 'btn btn-sm green',
		    ),
		));
		
		// Xóa
		$this->add(array(
		    'name'			=> 'filter_reset',
		    'type'			=> 'Submit',
		    'attributes'	=> array(
		        'value'     => 'Xóa',
		        'class'		=> 'btn btn-sm red',
		    ),
		));
		
		// Order
		$this->add(array(
		    'name'			=> 'order',
		    'type'			=> 'Hidden',
		));
		
		// Order By
		$this->add(array(
		    'name'			=> 'order_by',
		    'type'			=> 'Hidden',
		));
	}
}