<?php
namespace Admin\Form\Search;
use \Zend\Form\Form as Form;
use Zend\Paginator\Adapter\Null;

class Logs extends Form{
    
	public function __construct($sm){
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
		        'placeholder'   => 'Từ khóa',
		        'class'			=> 'form-control input-sm',
		        'id'			=> 'filter_keyword',
		    ),
		));

        // Bắt đầu từ ngày
        $this->add(array(
            'name'			=> 'filter_date_begin',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'			=> 'form-control date-picker',
                'placeholder'	=> 'Từ ngày',
                'autocomplete'  => 'off'
            )
        ));

        // Ngày kết thúc
        $this->add(array(
            'name'			=> 'filter_date_end',
            'type'			=> 'Text',
            'attributes'	=> array(
                'class'			=> 'form-control date-picker',
                'placeholder'	=> 'Đến ngày',
                'autocomplete'  => 'off'
            )
        ));

        $users	= \ZendX\Functions\CreateArray::create($sm->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-all')), array('key' => 'id', 'value' => 'name'));

        $this->add(array(
            'name'			=> 'filter_user',
            'type'			=> 'Select',
            'attributes'	=> array(
                'class'		=> 'form-control select2 select2_basic',
            ),
            'options'		=> array(
                'empty_option'	=> '- Nhân viên -',
                'value_options'	=> $users,
            )
        ));
		
		// Status
		$this->add(array(
		    'name'			=> 'filter_exits',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Không check Trùng SĐT -',
		        'value_options'	=> array( 1	=> 'Check trùng sđt'),
		    )
		));

		// Status
		$this->add(array(
		    'name'			=> 'filter_status',
		    'type'			=> 'Select',
		    'attributes'	=> array(
		        'class'		=> 'form-control select2 select2_basic',
		    ),
		    'options'		=> array(
		        'empty_option'	=> '- Trạng thái -',
		        'value_options'	=> array( 1	=> 'Hiển thị', 0 => 'Không hiển thị'),
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