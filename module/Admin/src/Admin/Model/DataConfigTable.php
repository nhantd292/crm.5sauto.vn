<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class DataConfigTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                
	            if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo( TABLE_DATA_CONFIG . '.created', $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_DATA_CONFIG .'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_DATA_CONFIG .'.created', $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo(TABLE_DATA_CONFIG .'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
				}

                if(!empty($ssFilter['filter_sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_DATA_CONFIG . '.sale_branch_id', $ssFilter['filter_sale_branch_id']);
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
                
                if(!isset($options['paginator']) || $options['paginator'] == true) {
	    			$select -> limit($paginator['itemCountPerPage'])
	    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
    			
			    if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> NEST
                    			     -> greaterThanOrEqualTo(TABLE_DATA_CONFIG . '.created', $date->formatToData($ssFilter['filter_date_begin']))
                    			     ->AND
                    			     -> lessThanOrEqualTo(TABLE_DATA_CONFIG .'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                    			     -> UNNEST;
    			} elseif (!empty($ssFilter['filter_date_begin'])) {
    			    $select -> where -> greaterThanOrEqualTo(TABLE_DATA_CONFIG .'.created', $date->formatToData($ssFilter['filter_date_begin']));
    			} elseif (!empty($ssFilter['filter_date_end'])) {
    			    $select -> where -> lessThanOrEqualTo(TABLE_DATA_CONFIG .'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
				}

                if(!empty($ssFilter['filter_sale_branch_id'])) {
                    $select -> where -> equalTo(TABLE_DATA_CONFIG . '.sale_branch_id', $ssFilter['filter_sale_branch_id']);
                }
    		});
		}

        if($options['task'] == 'list-item-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();

                if (!empty($ssFilter['filter_type'])) {
                    $select -> where -> equalTo( 'type', $ssFilter['filter_type']);
                }
                $select -> where -> equalTo( 'status', 1);
            });
        }
	    
		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
			    $select -> where -> equalTo('id', $arrParam['id']);
    		})->toArray();
		}
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData       = $arrParam['data'];
	    $arrItem       = $arrParam['item'];
	    $arrRoute      = $arrParam['route'];
	    $gid           = new \ZendX\Functions\Gid();

        $user_branch_ids      = $arrData['user_branch_ids'] ? implode(",", $arrData['user_branch_ids']) : '';
	    // Thêm mới cấu hình data NTD
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			$data	= array(
				'id'                    => $id,
				'title'                 => $arrData['title'],
				'sale_branch_id'        => $arrData['sale_branch_id'],
				'number'                => $arrData['number'],
				'options'               => $user_branch_ids,
                'type'                  => $arrData['type'],
                'status'                => $arrData['status'],
				'created'               => date('Y-m-d H:i:s'),
				'created_by'            => $this->userInfo->getUserInfo('id'),
			);
			$this->tableGateway->insert($data);
			return $id;
		}

		// Sửa cấu hình data NTD
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
            $data	= array(
                'title'                 => $arrData['title'],
                'sale_branch_id'        => $arrData['sale_branch_id'],
                'number'                => $arrData['number'],
                'status'                => $arrData['status'],
                'options'               => $user_branch_ids,
            );
			$this->tableGateway->update($data, array('id' => $id));
		    	
		    return $id;
		}

		// Đảo thứ tự các nhân sự được chọn để chia data tự động
		if($options['task'] == 'order-user-options') {
		    $id = $arrItem['id'];
		    $options = !empty($arrItem['options']) ? explode(',', $arrItem['options']) : null;

		    if(count($options) > 1){
                $options1  = array_slice($options,0,1);
                $options2  = array_slice($options,1);
                $options   = array_merge($options2, $options1);

                $data	= array(
                    'options' => implode(',', $options),
                );
                $this->tableGateway->update($data, array('id' => $id));

                return $id;
            }
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $result = $this->defaultDelete($arrParam, null);
	    }
	
	    return $result;
	}
}