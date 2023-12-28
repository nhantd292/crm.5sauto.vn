<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class LocationsTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result = $this->defaultCount($arrParam, null);
	    }

	    return $result;
	}

	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->defaultList($arrParam, null);
		}


        if($options['task'] == 'list-parent') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> order(array('ordering' => 'ASC', 'name' => 'ASC'));
                if(!empty($arrParam['level'])) {
                    $select -> where -> equalTo('level', $arrParam['level']);
                }
                if(!empty($arrParam['parent'])) {
                    $select -> where -> equalTo('parent', $arrParam['parent']);
                }
            });
        }

	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminLocations'. $arrParam['level'];
	        $result = $cache->getItem($cache_key);

	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select -> order(array('ordering' => 'ASC', 'name' => 'ASC'));
	                if(!empty($arrParam['level'])) {
	                    $select -> where -> equalTo('level', $arrParam['level']);
	                }
	                if(!empty($arrParam['parent'])) {
	                    $select -> where -> equalTo('parent', $arrParam['parent']);
	                }
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'code', 'value' => 'object'));

                $cache->setItem($cache_key, $result);
	        }
	    }

		return $result;
	}

	public function getItem($arrParam = null, $options = null){
		if($options == null) {
            $result = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> where -> equalTo('code', $arrParam['code']);
            })->toArray();
        }
        return current($result);
	}

	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];

	    $filter   = new \ZendX\Filter\Purifier();
	    $number   = new \ZendX\Functions\Number();
	    $gid      = new \ZendX\Functions\Gid();
	    $code     = new \ZendX\Filter\CreateAlias();

		if($options['task'] == 'add-item') {
			$data	= array(
				'code'          => $arrData['code'],
				'alias'         => $arrData['alias'],
				'name'          => $arrData['name'],
				'fullname'      => $arrData['fullname'],
			    'parent'        => $arrData['parent'],
			    'type'          => $arrData['type'],
				'level'      	=> $arrData['level'],
				'ordering'      => $arrData['ordering'],
				'status'        => $arrData['status'],
				'created'       => date('Y-m-d H:i:s'),
				'created_by'    => $this->userInfo->getUserInfo('id'),
			);

			$this->tableGateway->insert($data);
			return $id;
		}
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
			    'code'          => $arrData['code'],
				'alias'         => $arrData['alias'],
				'name'          => $arrData['name'],
				'fullname'      => $arrData['fullname'],
			    'parent'        => $arrData['parent'],
			    'type'          => $arrData['type'],
				'level'      	=> $arrData['level'],
				'ordering'      => $arrData['ordering'],
				'status'        => $arrData['status'],
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

	public function changeStatus($arrParam = null, $options = null){
	    if($options['task'] == 'change-status') {
	        $result = $this->defaultStatus($arrParam, null);
	    }

	    return $result;
	}

	public function changeOrdering($arrParam = null, $options = null){
	    if($options['task'] == 'change-ordering') {
	        $result = $this->defaultOrdering($arrParam, null);
	    }
	    return $result;
	}
}