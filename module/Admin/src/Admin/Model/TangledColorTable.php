<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class TangledColorTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select -> where -> NEST
									-> like('name', '%'. $ssFilter['filter_keyword'] .'%')
									-> UNNEST;
				}
				
	            if(isset($ssFilter['filter_parent']) && $ssFilter['filter_parent'] != '') {
                    $select -> where -> equalTo('parent', $ssFilter['filter_parent']);
                }
				$select -> where -> notEqualTo('parent', 0);
				$select -> where -> equalTo('type', TANGLED_COLOR);
            })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                $number     = new \ZendX\Functions\Number();
                
                if(!isset($options['paginator']) || $options['paginator'] == true) {
	    			$select -> limit($paginator['itemCountPerPage'])
	    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
				
				if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
                    $select -> where -> NEST
									-> like('name', '%'. $ssFilter['filter_keyword'] .'%')
									-> UNNEST;
				}
				
			    if(isset($ssFilter['filter_parent']) && $ssFilter['filter_parent'] != '') {
                    $select -> where -> equalTo('parent', $ssFilter['filter_parent']);
				}
				$select -> where -> notEqualTo('parent', 0);
				$select -> where -> equalTo('type', TANGLED_COLOR);
    		});
		}

		if($options['task'] == 'list-all') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> notEqualTo('parent', 0);
				$select -> where -> equalTo('type', TANGLED_COLOR);
    		});
		}

		if ($options['task'] == 'cache') {
			$cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminTangledColor';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
					$select -> where -> notEqualTo('parent', 0);
					$select -> where -> equalTo('type', TANGLED_COLOR);
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
				$select -> where -> notEqualTo('parent', 0);
				$select -> where -> equalTo('type', TANGLED_COLOR);
    		})->toArray();
		}

        if($options['task'] == 'by-name') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select -> where -> equalTo('name', $arrParam['name']);
                $select -> where -> notEqualTo('parent', 0);
                $select -> where -> equalTo('type', TANGLED_COLOR);
            })->toArray();
        }
		
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrItem  = $arrParam['item'];
	    $arrRoute = $arrParam['route'];
	    
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
		
		if($options['task'] == 'add-item') {	    
			$id = $gid->getId();
			
			$data = array(
				'id'                      => $id,
				'name'              	  => !empty($arrData['name']) ? $arrData['name'] : null,
				'code'       			  => !empty($arrData['code']) ? $arrData['code'] : null,
				'parent'         		  => !empty($arrData['parent']) ? $arrData['parent'] : 0,
				'price'              	  => !empty($arrData['price']) ? $number->formatToNumber($arrData['price']) : null,
				'type'            		  => TANGLED_COLOR,
				'unit_id'                 => null,
				'price'              	  => null,
				'created'                 => date('Y-m-d H:i:s'),
				'created_by'              => $this->userInfo->getUserInfo('id'),
			);
			
			$this->tableGateway->insert($data); // Thực hiện lưu database
						
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
			$id = $arrData['id'];
			$data = array();

			$data = [
				'name'          		=> !empty($arrData['name']) ? $arrData['name'] : null,
				'code'     				=> !empty($arrData['code']) ? $arrData['code'] : null,
				'parent'          		=> !empty($arrData['parent']) ? $arrData['parent'] : 0,
				'price'              	=> !empty($arrData['price']) ? $number->formatToNumber($arrData['price']) : null,
			];
			
			// Cập nhật
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