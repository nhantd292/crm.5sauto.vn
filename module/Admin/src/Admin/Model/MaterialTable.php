<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class MaterialTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }
                
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->equalTo('id', $ssFilter['filter_keyword'])
                			      ->UNNEST;
    			}
            })->count();
	    }

	    if($options['task'] == 'list-item-type') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_COLOR, TABLE_COLOR .'.id = '. TABLE_MATERIAL .'.material_id', array(), 'inner');

                if(!empty($ssFilter['filter_month'])) {
                    $select->where->equalTo('month', $ssFilter['filter_month']);
                }

                if(!empty($ssFilter['filter_year'])) {
                    $select->where->equalTo('year', $ssFilter['filter_year']);
                }

                if(!empty($ssFilter['filter_type'])) {
                    $select->where->equalTo('type', $ssFilter['filter_type']);
                }

                if(!empty($ssFilter['color_group_id'])) {
                    $select->where->equalTo(TABLE_COLOR.'.parent', $ssFilter['color_group_id']);
                }

            })->count();
	    }
	    
	    return $result;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                
    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			
    			$select -> order(array('year' => 'DESC', 'month' => 'DESC'));
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->or
                			      ->equalTo('id', $ssFilter['filter_keyword'])
                			      ->UNNEST;
    			}
    		});
		}

        // Lấy danh sách
		if($options['task'] == 'list-item-type') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> join(TABLE_COLOR, TABLE_COLOR .'.id = '. TABLE_MATERIAL .'.material_id', array('color_group_id' => 'parent'), 'inner');

                $select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);

                if(!empty($ssFilter['filter_month'])) {
                    $select->where->equalTo('month', $ssFilter['filter_month']);
                }

                if(!empty($ssFilter['filter_year'])) {
                    $select->where->equalTo('year', $ssFilter['filter_year']);
                }

                if(!empty($ssFilter['filter_type'])) {
                    $select->where->equalTo('type', $ssFilter['filter_type']);
                }

                if(!empty($ssFilter['color_group_id'])) {
                    $select->where->equalTo(TABLE_COLOR.'.parent', $ssFilter['color_group_id']);
                }
    		});
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminSaleTarget';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select->order(array('ordering' => 'ASC', 'name' => 'ASC'));
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	    }
	    
		return $result;
	}

	public function report($arrParam = null, $options = null){
        // Lấy danh sách theo kiểu taget
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                if(!empty($ssFilter['month'])) {
                    $select->where->equalTo('month', $ssFilter['month']);
                }

                if(!empty($ssFilter['year'])) {
                    $select->where->equalTo('year', $ssFilter['year']);
                }
    		});
		}

		return $result->toArray();
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('id', $arrParam['id']);
    		})->current();
		}
		
		if($options['task'] == 'month-year') {
		    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
		        $select -> where -> equalTo('month', $arrParam['month'])
		                         -> equalTo('year', $arrParam['year'])
		                         -> equalTo('material_id', $arrParam['material_id']);
		    })->current();
		}
		
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrItem  = $arrParam['item'];
	    $arrRoute = $arrParam['route'];
	    
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();
	    
	    if($options['task'] == 'add-all') {
	        $id = $gid->getId();
	        
	        $data	= array(
	            'id'            => $id,
	            'date'          => $date->formatToData($arrData['date']),
	            'month'         => $arrData['month'],
	            'material_id'   => $arrData['material_id'],
	            'year'          => $arrData['year'],
	            'type'          => $arrData['type'],
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );
	        	
	        $this->tableGateway->insert($data);
	        return $id;
	    }

	    if($options['task'] == 'add-item') {
	        $id = $gid->getId();

	        $data	= array(
	            'id'            => $id,
	            'date'          => $date->formatToData($arrData['date']),
	            'day'           => $arrData['day'],
	            'month'         => $arrData['month'],
	            'year'          => $arrData['year'],
	            'type'          => $arrData['type'],
	            'material_id'   => $arrData['material_id'],
	            'params'        => !empty($arrData['params']) ? serialize($arrData['params']): '',
	            'created'       => date('Y-m-d H:i:s'),
	            'created_by'    => $this->userInfo->getUserInfo('id'),
	        );

	        $this->tableGateway->insert($data);
	        return $id;
	    }

	    if($options['task'] == 'edit-item') {
	        $id = $arrData['id'];
            $params = !empty($arrItem['params']) ? unserialize($arrItem['params']) : array();

            foreach ($arrData['params'] as $key => $value) {
                $params[$key] = $value;
            }

	        $data	= array(
	            'params'       => !empty($params) ? serialize($params) : '',
	        );
	        	
	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }

	    if($options['task'] == 'save-ajax') {
	        $id = $arrData['id'];
            $params = !empty($arrItem['params']) ? unserialize($arrItem['params']) : array();

            foreach ($arrData['params'] as $key => $value) {
                $params[$key] = $value;
            }

	        $data	= array(
	            'params'       => !empty($params) ? serialize($params) : '',
	        );

	        $this->tableGateway->update($data, array('id' => $id));
	        return $id;
	    }
	}
}