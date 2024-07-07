<?php
namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class ZaloNotifyResult extends InputFilter {
	
	public function __construct($options = null){
        $exclude = null;
        if(!empty($options['id'])) {
            $exclude = array(
                'field' => 'id',
                'value' => $options['id']
            );
        }
	}
}