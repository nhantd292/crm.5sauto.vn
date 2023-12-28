<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class LinkCheckingTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                $select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_LINK_CHECKING .'.marketer_id',
                    array(), 'inner');
                
	            if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_LINK_CHECKING . '.created', $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_LINK_CHECKING .'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_LINK_CHECKING .'.created', $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo(TABLE_LINK_CHECKING .'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
				}

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select -> where -> equalTo(TABLE_LINK_CHECKING . '.marketer_id', $ssFilter['filter_marketer_id']);
                }

                if(!empty($ssFilter['filter_sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_USER . '.sale_branch_id', $ssFilter['filter_sale_branch_id']);
                }
    			
            })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();

                $select -> join(TABLE_USER, TABLE_USER .'.id = '. TABLE_LINK_CHECKING .'.marketer_id',
                    array('user_name' => 'name', 'user_sale_branch_id' => 'sale_branch_id', 'user_sale_group_id' => 'sale_group_id',
                    ), 'inner');
                
                if(!isset($options['paginator']) || $options['paginator'] == true) {
	    			$select -> limit($paginator['itemCountPerPage'])
	    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
    			
			    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_LINK_CHECKING . '.created', $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_LINK_CHECKING .'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_LINK_CHECKING .'.created', $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo(TABLE_LINK_CHECKING .'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
				}

                if(!empty($ssFilter['filter_marketer_id'])) {
                    $select -> where -> equalTo(TABLE_LINK_CHECKING . '.marketer_id', $ssFilter['filter_marketer_id']);
                }

                if(!empty($ssFilter['filter_sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_USER . '.sale_branch_id', $ssFilter['filter_sale_branch_id']);
                }
    		});
		}
		
		if($options['task'] == 'cache') {
		    $cache = $this->getServiceLocator()->get('cache');
		    $cache_key = 'AdminLinkChecking';
		    $result = $cache->getItem($cache_key);
		    if (empty($result)) {
		        $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
		            $select -> order(TABLE_CONTACT .'.name ASC');
		        });
		            $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
		            $cache->setItem($cache_key, $result);
		    }
		}
	    
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('id', $arrParam['id']);
    		})->toArray();
		}

		if($options['task'] == 'by-link') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('link', $arrParam['link']);
    		})->toArray();
		}
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData       = $arrParam['data'];
	    $arrItem       = $arrParam['item'];
	    $arrRoute      = $arrParam['route'];
	    
	    $dateFormat    = new \ZendX\Functions\Date();
	    $filter        = new \ZendX\Filter\Purifier();
	    $gid           = new \ZendX\Functions\Gid();
	    // Thêm mới liên hệ - NamNV
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                    => $id,
				'link'                  => $arrData['link'],
				'marketing_channel_id'  => $arrData['marketing_channel_id'],
				'product_group_id'      => $arrData['product_group_id'],
				'marketer_id'           => $this->userInfo->getUserInfo('id'),
				'content'               => $arrData['content'],
				'created'               => date('Y-m-d H:i:s'),
				'created_by'            => $this->userInfo->getUserInfo('id'),
			);

			$this->tableGateway->insert($data);
			
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
		    $data	= array(
//		        'link'                  => $arrData['link'], // không cho sửa link vì link không được nhập trùng
		        'marketing_channel_id'  => $arrData['marketing_channel_id'],
		        'product_group_id'      => $arrData['product_group_id'],
		        'content'               => $arrData['content'],
		    );
		    
			$this->tableGateway->update($data, array('id' => $id));
		    	
		    return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $result = $this->defaultDelete($arrParam, null);
	    }
	
	    return $result;
	}
}