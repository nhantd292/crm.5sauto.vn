<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserTable extends AbstractTableGateway implements ServiceLocatorAwareInterface {
	
    protected $tableGateway;
	protected $userInfo;
	protected $serviceLocator;
	
	public function __construct(TableGateway $tableGateway) {
	    $this->tableGateway	= $tableGateway;
	    $this->userInfo	= new \ZendX\System\UserInfo();
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
	    $this->serviceLocator = $serviceLocator;
	}
	
	public function getServiceLocator() {
	    return $this->serviceLocator;
	}
	
	public function countItem($arrParam = null, $options = null){
	    if($options == null) {
	        $result	= $this->tableGateway->select()->count();
	    }
	    
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $ssFilter  = $arrParam['ssFilter'];
	            
	            $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
	            
	            if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
	                $select -> where -> equalTo('status', $ssFilter['filter_status']);
	            }
	            
	            if(isset($ssFilter['filter_permission']) && $ssFilter['filter_permission'] != '') {
	                $select -> where -> literal('FIND_IN_SET(\''. $ssFilter['filter_permission'] .'\', permission_ids)');
	            }
	            
	            if(!empty($ssFilter['filter_company_branch'])) {
	                $select -> where -> equalTo('company_branch_id', $ssFilter['filter_company_branch']);
	            }
	            
	            if(!empty($ssFilter['filter_company_department'])) {
	                $select -> where -> equalTo('company_department_id', $ssFilter['filter_company_department']);
	            }
	            
	            if(!empty($ssFilter['filter_company_position'])) {
	                $select -> where -> equalTo('company_position_id', $ssFilter['filter_company_position']);
	            }
	            
	            if(!empty($ssFilter['filter_sale_branch'])) {
	                $select -> where -> equalTo('sale_branch_id', $ssFilter['filter_sale_branch']);
	            }
	            
	            if(!empty($ssFilter['filter_sale_group'])) {
	                $select -> where -> equalTo('sale_group_id', $ssFilter['filter_sale_group']);
	            }

	            if(!empty($ssFilter['filter_kov_branch_id'])) {
	                $select -> where -> equalTo('kov_branch_id', $ssFilter['filter_kov_branch_id']);
	            }
	            
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
			        $select -> where -> NEST
                    			     -> like('name', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->OR
                    			     -> like('username', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->OR
                    			     -> like('phone', '%'. $ssFilter['filter_keyword'] . '%')
                    			     ->OR
                    			     -> like('email', '%'. $ssFilter['filter_keyword'] . '%')
                    			     -> UNNEST;
				}
				
				if($this->userInfo->getUserInfo('id') != '1111111111111111111111') {
				    $select -> where -> notEqualTo('id', '1111111111111111111111');
				}
                $select -> where -> notEqualTo('id', '3333333333333333333333');
	        })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $paginator = $arrParam['paginator'];
	            $ssFilter  = $arrParam['ssFilter'];

                $select -> where -> notEqualTo('id', '1111111111111111111111');

	            if(!isset($options['paginator']) || $options['paginator'] == true) {
	    			$select -> limit($paginator['itemCountPerPage'])
	    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
	    
	            if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
	                $select ->ORder(array(TABLE_USER .'.'. $ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
	            }
	    
	            if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
	                $select -> where -> equalTo(TABLE_USER .'.status', $ssFilter['filter_status']);
	            }
	    
	            if(isset($ssFilter['filter_permission']) && $ssFilter['filter_permission'] != '') {
	                $select -> where -> literal('FIND_IN_SET(\''. $ssFilter['filter_permission'] .'\', permission_ids)');
	            }
	            
	            if(!empty($ssFilter['filter_company_branch'])) {
	                $select -> where -> equalTo('company_branch_id', $ssFilter['filter_company_branch']);
	            }
	            
	            if(!empty($ssFilter['filter_company_department'])) {
	                $select -> where -> equalTo('company_department_id', $ssFilter['filter_company_department']);
	            }
	            
	            if(!empty($ssFilter['filter_company_position'])) {
	                $select -> where -> equalTo('company_position_id', $ssFilter['filter_company_position']);
	            }
	            
	            if(!empty($ssFilter['filter_sale_branch'])) {
	                $select -> where -> equalTo('sale_branch_id', $ssFilter['filter_sale_branch']);
	            }
	            
	            if(!empty($ssFilter['filter_sale_group'])) {
	                $select -> where -> equalTo('sale_group_id', $ssFilter['filter_sale_group']);
	            }

	            if(!empty($ssFilter['filter_kov_branch_id'])) {
	                $select -> where -> equalTo('kov_branch_id', $ssFilter['filter_kov_branch_id']);
	            }
	            
	            if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
	                $select -> where -> NEST
                	                 -> like(TABLE_USER .'.name', '%'. $ssFilter['filter_keyword'] . '%')
                	                 ->OR
                	                 -> like(TABLE_USER .'.username', '%'. $ssFilter['filter_keyword'] . '%')
                	                 ->OR
                	                 -> like(TABLE_USER .'.phone', '%'. $ssFilter['filter_keyword'] . '%')
                	                 ->OR
                	                 -> like(TABLE_USER .'.email', '%'. $ssFilter['filter_keyword'] . '%')
                	                 -> UNNEST;
	            }
	    
	            if($this->userInfo->getUserInfo('id') != '1111111111111111111111') {
	                $select -> where -> notEqualTo('id', '1111111111111111111111');
	            }
                $select -> where -> notEqualTo('id', '3333333333333333333333');
	        });
	            	
	    }
	    
	    if($options['task'] == 'list-all') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	             
	            $select ->ORder(array('name' => 'ASC'));
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

	            if(!empty($arrData['sale_group_id'])) {
	                $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
	            }
	             
	            if(!empty($arrData['sale_branch_id'])) {
	                $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
	            }
	             
	            if(!empty($arrData['company_position_id'])) {
	                $select -> where -> equalTo('company_position_id', $arrData['company_position_id']);
	            }
	             
	            if(!empty($arrData['status'])) {
	                $select -> where -> equalTo('status', $arrData['status']);
	            }

	            if(!empty($arrData['notifi'])) {
	                $select -> where -> equalTo('notifi', $arrData['notifi']);
	            }
	             
	        })->toArray();
	    }

        if($options['task'] == 'list-user-department') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $arrData  = $arrParam;

                $select ->ORder(array('name' => 'ASC'));
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '2222222222222222222222');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                if(!empty($arrData['company_department_id'])) {
                    $select -> where -> equalTo('company_department_id', $arrData['company_department_id']);
                }

                if(!empty($arrData['sale_branch_id'])){
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }

                if(!empty($arrData['sale_group_id'])){
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }

            })->toArray();
        }
	    
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminUser';
	        if(!empty($arrParam['company_position_id'])) {
	            $cache_key .= 'Position'. $arrParam['company_position_id'];
	        }
	        $result = $cache->getItem($cache_key);
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select ->ORder(TABLE_USER .'.name ASC');
	                
	                if(!empty($arrParam['company_position_id'])) {
	                    $select -> where -> equalTo('company_position_id', $arrParam['company_position_id']);
	                }
	            });
	            $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	            $cache->setItem($cache_key, $result);
	        }
	    }

        // lấy danh sách theo đội nhóm
        if ($options['task'] == 'list-item-by-group') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> order('name ASC');
                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                $group = [];
                $permission = [];
                if(!empty($arrParam['sale_group_ids'])) {
                    $group = explode(',', $arrParam['sale_group_ids']);
                }
                if(!empty($arrParam['permission_ids'])) {
                    $permission = explode(',', $arrParam['permission_ids']);
                }
                if(!in_array(SYSTEM, $permission) && !in_array(ADMIN, $permission)){
                    $select -> where -> in('sale_group_id', $group);
                }
            })->toArray();
        }
	    
	    if($options['task'] == 'cache-status') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminUserStatus';
	        $result = $cache->getItem($cache_key);
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select ->ORder(TABLE_USER .'.name ASC')
	                        -> where -> equalTo(TABLE_USER .'.status', 1);
	            });
	            $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
	            $cache->setItem($cache_key, $result);
	        }
	    }

        if ($options['task'] == 'list-sale') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData = $arrParam['data'];

                $select -> order('name ASC');
                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                $select -> where -> equalTo('company_department_id', 'sales');

                if(!empty($arrData['sale_branch_id'])){
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrParam['filter_sale_branch'])){
                    $select -> where -> equalTo('sale_branch_id', $arrParam['filter_sale_branch']);
                }

                if(!empty($arrData['sale_group_id'])){
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }
            })->toArray();
        }

        if ($options['task'] == 'list-sale-admin') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData = $arrParam['data'];

                $select -> order('name ASC');
                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                $select -> where -> equalTo('company_department_id', 'sales');
                $select -> where -> like('permission_ids', '%saleadmin%');

                if(!empty($arrData['sale_branch_id'])){
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }
            })->toArray();
        }

        if ($options['task'] == 'list-marketing') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData = $arrParam['data'];

                $select -> order('name ASC');
                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                $select -> where -> equalTo('company_department_id', 'marketing');

                if(!empty($arrData['sale_branch_id'])){
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }

                if(!empty($arrData['sale_group_id'])){
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }
            })->toArray();
        }

        if ($options['task'] == 'list-care') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData = $arrParam['data'];

                $select -> order('name ASC');
                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                $select -> where -> equalTo('company_department_id', 'care');

                if(!empty($arrData['sale_branch_id'])){
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }

                if(!empty($arrData['sale_group_id'])){
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }

                if(!empty($arrData['care_id'])){
                    $select -> where -> equalTo('id', $arrData['care_id']);
                }
            })->toArray();
        }

        if ($options['task'] == 'list-positons-care') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData = $arrParam['data'];

                $select -> order('name ASC');
                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                $select -> where -> like('company_position_care_id', '%'.NHAN_VIEN_GIAO_HANG.'%');

                if(!empty($arrData['ids'])){
                    $select -> where -> in('id', $arrData['ids']);
                }
            })->toArray();
        }

		return $result;
	}

	public function report($arrParam = null, $options = null){
        if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $arrData  = $arrParam['data'];
                $arrRoute = $arrParam['route'];

                $select ->ORder(array('name' => 'ASC'));
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                if(!empty($arrData['company_department_id'])) {
                    $select -> where -> equalTo('company_department_id', $arrData['company_department_id']);
                }

                if(!empty($arrData['sale_group_id'])) {
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }

                if(!empty($arrData['sale_branch_id'])) {
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }

                if(!empty($arrData['company_position_id'])) {
                    $select -> where -> equalTo('company_position_id', $arrData['company_position_id']);
                }

                if(!empty($arrData['delivery_id'])) {
                    $select -> where -> equalTo('id', $arrData['delivery_id']);
                }

                if(!empty($arrData['status'])) {
                    $select -> where -> equalTo('status', $arrData['status']);
                }

            })->toArray();
        }

        if ($options['task'] == 'list-sale') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData = $arrParam['data'];

                $select -> order('name ASC');
//                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                $select -> where -> equalTo('company_department_id', 'sales');

                if(!empty($arrData['sale_branch_id'])){
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }

                if(!empty($arrData['sale_group_id'])){
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }

                if(!empty($arrData['sale_id'])){
                    $select -> where -> equalTo('id', $arrData['sale_id']);
                }

                if($arrData['sale-store-status'] == 'sales-store'){
                    $select -> where -> like('permission_ids', '%sales-store%');
                }
            })->toArray();
        }

        if ($options['task'] == 'list-marketing') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData = $arrParam['data'];

                $select -> order('name ASC');
//                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');
                $select -> where -> equalTo('company_department_id', 'marketing');

                if(!empty($arrData['sale_branch_id'])){
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }
                if(!empty($arrData['sale_group_id'])){
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }
                if(!empty($arrData['marketer_id'])){
                    $select -> where -> equalTo('id', $arrData['marketer_id']);
                }
                if(!empty($arrData['user_id'])){
                    $select -> where -> equalTo('id', $arrData['user_id']);
                }
            })->toArray();
        }

        if ($options['task'] == 'list-care') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $arrData = $arrParam['data'];

                $select -> order('name ASC');
//                $select -> where -> equalTo('status', 1);
                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '3333333333333333333333');

                $select -> where -> equalTo('company_department_id', 'care');

                if(!empty($arrData['sale_branch_id'])){
                    $select -> where -> equalTo('sale_branch_id', $arrData['sale_branch_id']);
                }

                if(!empty($arrData['sale_group_id'])){
                    $select -> where -> equalTo('sale_group_id', $arrData['sale_group_id']);
                }

                if(!empty($arrData['care_id'])){
                    $select -> where -> equalTo('id', $arrData['care_id']);
                }
            })->toArray();
        }

		return $result;
	}
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
					$select->where->equalTo('id', $arrParam['id']);
			})->toArray();
		}
		
		if($options['task'] == 'by-username') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
			    $select->where->equalTo('username', $arrParam['username']);
			})->toArray();
		}

		if($options['task'] == 'by-code') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
			    $select->where->equalTo('code', $arrParam['code']);

                $select -> where -> notEqualTo('id', '1111111111111111111111');
                $select -> where -> notEqualTo('id', '2222222222222222222222');
                $select -> where -> notEqualTo('id', '3333333333333333333333');
			})->toArray();
		}
	
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    $arrItem  = $arrParam['item'];
	     
	    $image    = new \ZendX\Functions\Thumbnail($arrData['image']);
	    $filter   = new \ZendX\Filter\Purifier();
	    $gid      = new \ZendX\Functions\Gid();

	    $permission_ids     = $arrData['permission_ids'] ? implode(',', $arrData['permission_ids']) : '';
	    $position_care_id   = $arrData['company_position_care_id'] ? implode(',', $arrData['company_position_care_id']) : '';
	    $sale_group_ids     = $arrData['sale_group_ids'] ? implode(',', $arrData['sale_group_ids']) : '';

		if($options['task'] == 'add-item') {
            $encode_phone       = $arrData['encode_phone'] ? implode(',', $arrData['encode_phone']) : '';
			$id = $gid->getId();
			$data	= array(
				'id'                    => $id,
				'name'                  => $arrData['name'],
				'username'              => $arrData['username'],
				'code'                  => $arrData['code'],
				'password'              => md5($arrData['password']),
				'email'                 => $arrData['email'],
				'phone'                 => $arrData['phone'],
				'status'                => $arrData['status'],
				'notifi'                => !empty($arrData['notifi']) ? $arrData['notifi'] : null,
				'created'               => date('Y-m-d H:i:s'),
				'created_by'            => $this->userInfo->getUserInfo('id'),
				'permission_ids'        => $permission_ids,
				'company_branch_id'     => $arrData['company_branch_id'],
				'company_department_id' => $arrData['company_department_id'],
				'company_position_id'   => $arrData['company_position_id'],
				'company_position_care_id'   => $position_care_id,
				'sale_branch_id'        => $arrData['sale_branch_id'],
				'sale_group_id'         => $arrData['sale_group_id'],
				'kov_branch_id'         => $arrData['kov_branch_id'],
				'sale_group_ids'        => $sale_group_ids,
				'encode_phone'          => $encode_phone,
				'branch_sale_group_id'  => $arrData['branch_sale_group_id'],
			);
			
			$arrOptions = array('password_status' => $arrData['password_status']);
			$data['options'] = serialize($arrOptions);
			
			$this->tableGateway->insert($data);

			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'name'                  => $arrData['name'],
				'username'              => $arrData['username'],
			    'code'                  => $arrData['code'],
				'email'                 => $arrData['email'],
			    'phone'                 => $arrData['phone'],
				'status'                => $arrData['status'],
                'notifi'                => !empty($arrData['notifi']) ? $arrData['notifi'] : null,
			    'permission_ids'        => $permission_ids,
			    'company_branch_id'     => $arrData['company_branch_id'],
			    'company_department_id' => $arrData['company_department_id'],
			    'company_position_id'   => $arrData['company_position_id'],
                'company_position_care_id'   => $position_care_id,
			    'sale_branch_id'        => $arrData['sale_branch_id'],
				'sale_group_id'         => $arrData['sale_group_id'],
				'kov_branch_id'         => $arrData['kov_branch_id'],
			    'sale_group_ids'        => $sale_group_ids,
                'branch_sale_group_id'  => $arrData['branch_sale_group_id'],
			);

			// Chỉ có admin mới được cập nhật
			$curent_user_id = $this->userInfo->getUserInfo('id');
            if($curent_user_id == '1111111111111111111111' || $curent_user_id == '2222222222222222222222'){
                $encode_phone       = $arrData['encode_phone'] ? implode(',', $arrData['encode_phone']) : '';
                $data['encode_phone'] = $encode_phone;
            }

            if(implode(',', $arrItem['permission_ids']) != $permission_ids || implode(',', $arrItem['encode_phone']) != $encode_phone){
                $data['flag'] = 1;
            }
			
			if(!empty($arrData['password'])) {
			    $data['password'] = md5($arrData['password']);
			}
			
			$arrOptions = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
			$arrOptions['password_status'] = $arrData['password_status'];
			
			$data['options'] = serialize($arrOptions);
			
			$this->tableGateway->update($data, array('id' => $id));
			return $arrData['id'];
		}
		
		if($options['task'] == 'change-password') {
		    $data	= array(
		        'password' => md5($arrData['password_new']),
		    );
		    	
		    $this->tableGateway->update($data, array('id' => $this->userInfo->getUserInfo('id')));
		    return $this->userInfo->getUserInfo('id');
		}
		
		if($options['task'] == 'update-password') {
		    $item = $this->getItem(array('id' => $this->userInfo->getUserInfo('id')));
		    $options = unserialize($item['options']);
		    $options['password_status'] = 0;
		    
		    $data	= array(
		        'password'    => md5($arrData['password_new']),
		        'options'     => serialize($options)
		    );
		    
		    $this->tableGateway->update($data, array('id' => $this->userInfo->getUserInfo('id')));
		    return $this->userInfo->getUserInfo('id');
		}
		
		if($options['task'] == 'update-login') {
		    $data	= array(
		        'login_ip'        => $_SERVER['REMOTE_ADDR'],
		        'login_time'      => date('Y-m-d H:i:s'),
                'flag'            => 0,
		    );
		    	
		    $id = $this->tableGateway->update($data, array('id' => $this->userInfo->getUserInfo('id')));
            // Cập nhật chấm công
            if(!empty($id)) {
                $user = $this->getItem(array('id' => $this->userInfo->getUserInfo('id')), null);
                $this->getServiceLocator()->get('Admin\Model\CheckInTable')->saveItem(array("data" => $user), array('task' => 'time-check-in'));
            }

		    return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    if($options['task'] == 'delete-item') {
	        $where = new Where();
	        $where -> in('id', $arrData['cid']);
	        $where -> notEqualTo('id', '1111111111111111111111');
	        $this -> tableGateway -> delete($where);
	        
	        return count($arrData['cid']);
	    }
	
	    return false;
	}
	
    public function changeStatus($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    if($options['task'] == 'change-status') {
	        if(!empty($arrData['cid'])) {
    	        $data = array( 
    	            'status' => !$arrData['status']
    	        );
    	        
    	        $where = new Where();
    	        $where -> in('id', $arrData['cid']);
    			$this -> tableGateway -> update($data, $where);
	        }
	        return true;
	    }
	    
	    return false;
	}
	
	public function changeOrdering($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    
	    if($options['task'] == 'change-ordering') {
            foreach ($arrData['cid'] AS $id) {
                $data	= array( 'ordering'	=> $arrData['ordering'][$id] );
                $where  = array('id' => $id);
                $this->tableGateway->update($data, $where);
            }
            
            return count($arrData['cid']);
	    }
	    return false;
	}
}