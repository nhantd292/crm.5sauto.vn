<?php
namespace ZendX\View\Helper\Url;
use Zend\View\Helper\AbstractHelper;

class LinkAdminStatus extends AbstractHelper {
	
	public function __invoke($id, $status, $options = null) {
    
        $class = ($status == 1) ? 'green' : 'default';
    
        return sprintf('<a title="Trạng thái" href="javascript:;" onclick="javascript:changeStatus(\'item\', \'%s\');" data-status="%s" class="btn btn-xs btn-status %s"><i class="fa fa-check"></i></a>', $id, $status, trim($class));
    }
}