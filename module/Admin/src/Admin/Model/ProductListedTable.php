<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ProductListedTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                
	            if(isset($ssFilter['filter_product']) && $ssFilter['filter_product'] != '') {
                    $select -> where -> equalTo('product_id', $ssFilter['filter_product']);
				}
				
				if(isset($ssFilter['filter_product']) && $ssFilter['filter_product'] != '') {
                    $select -> where -> equalTo('product_id', $ssFilter['filter_product']);
				}
				
			    if(isset($ssFilter['filter_carpet_color']) && $ssFilter['filter_carpet_color'] != '') {
                    $select -> where -> equalTo('group_carpet_color_id', $ssFilter['filter_carpet_color']);
				}
				
			    if(isset($ssFilter['filter_tangled_color']) && $ssFilter['filter_tangled_color'] != '') {
                    $select -> where -> equalTo('group_tangled_color_id', $ssFilter['filter_tangled_color']);
                }

                if(!empty($ssFilter['filter_type'])) {
                    $select -> where -> equalTo('type', $ssFilter['filter_type']);
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
                $number     = new \ZendX\Functions\Number();
                
                if(!isset($options['paginator']) || $options['paginator'] == true) {
	    			$select -> limit($paginator['itemCountPerPage'])
	    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
    			
			    if(isset($ssFilter['filter_product']) && $ssFilter['filter_product'] != '') {
                    $select -> where -> equalTo('product_id', $ssFilter['filter_product']);
				}
				
			    if(isset($ssFilter['filter_carpet_color']) && $ssFilter['filter_carpet_color'] != '') {
                    $select -> where -> equalTo('group_carpet_color_id', $ssFilter['filter_carpet_color']);
				}
				
			    if(isset($ssFilter['filter_tangled_color']) && $ssFilter['filter_tangled_color'] != '') {
                    $select -> where -> equalTo('group_tangled_color_id', $ssFilter['filter_tangled_color']);
                }

                if(!empty($ssFilter['filter_type'])) {
                    $select -> where -> equalTo('type', $ssFilter['filter_type']);
                }
    		});
		}

		if($options['task'] == 'list-all') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $number     = new \ZendX\Functions\Number();

			    if(isset($ssFilter['filter_product']) && $ssFilter['filter_product'] != '') {
                    $select -> where -> equalTo('product_id', $ssFilter['filter_product']);
				}

			    if(isset($ssFilter['filter_carpet_color']) && $ssFilter['filter_carpet_color'] != '') {
                    $select -> where -> equalTo('group_carpet_color_id', $ssFilter['filter_carpet_color']);
				}

			    if(isset($ssFilter['filter_tangled_color']) && $ssFilter['filter_tangled_color'] != '') {
                    $select -> where -> equalTo('group_tangled_color_id', $ssFilter['filter_tangled_color']);
                }

                if(!empty($ssFilter['filter_type'])) {
                    $select -> where -> equalTo('type', $ssFilter['filter_type']);
                }
    		});
		}

		if ($options['task'] == 'cache') {
			$cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminProductListed';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'product_id', 'value' => 'object'));
                 
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

		if($options['task'] == 'by-ajax') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
				$select -> where -> equalTo('product_id', $arrParam['data']['product_id']);
				$select -> where -> equalTo('type', $arrParam['data']['type']);

				if (!empty($arrParam['data']['group_tangled_color_id'])) {
					$select -> where -> equalTo('group_tangled_color_id', $arrParam['data']['group_tangled_color_id']);
				} else {
					$select -> where -> isNull('group_tangled_color_id');
				}

				if (!empty($arrParam['data']['group_carpet_color_id'])) {
					$select -> where -> equalTo('group_carpet_color_id', $arrParam['data']['group_carpet_color_id']);
				} else {
					$select -> where -> isNull('group_carpet_color_id');
				}

				if (!empty($arrParam['data']['flooring_id'])) {
					$select -> where -> equalTo('flooring_id', $arrParam['data']['flooring_id']);
				} else {
					$select -> where -> isNull('flooring_id');
				}
    		})->toArray();
		}
		
		return current($result);
	}
	public function getListedPrice($dataParams){
		
        $dataProduct    = $dataParams['product'];
        $dataCarpet     = $dataParams['carpet_color'];
        $dataTangled    = $dataParams['tangled_color'];
        $dataFlooring   = $dataParams['flooring'];
        $type           = $dataParams['type'];

        $carpetColor       = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $tangledColor      = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $colorGroup        = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'cache'));
        
        $parentCarpet       = $carpetColor[$dataCarpet]['parent'];
        $parentTangled      = $tangledColor[$dataTangled]['parent'];

        $productListed = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')
                                                    ->getItem(array(
                                                        'data' => array(
                                                        'product_id' => $dataProduct,
                                                        'group_carpet_color_id' => $parentCarpet,
                                                        'group_tangled_color_id' => $parentTangled,
                                                        'flooring_id' => $dataFlooring,
                                                        'type' => $type,
                                                        )
                                                    ), array('task' => 'by-ajax'));
		$results = $productListed['price'] ? $productListed['price'] : 0;
		return $results;
	}
	public function getListedPercenter($dataParams){
        $dataProduct    = $dataParams['product'];
        $dataCarpet     = $dataParams['carpet_color'];
        $dataTangled    = $dataParams['tangled_color'];
        $dataFlooring   = $dataParams['flooring'];
        $type           = $dataParams['type'];

        $carpetColor       = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $tangledColor      = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $colorGroup        = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'cache'));

        $parentCarpet       = $carpetColor[$dataCarpet]['parent'];
        $parentTangled      = $tangledColor[$dataTangled]['parent'];

        $productListed = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')
                                                    ->getItem(array(
                                                        'data' => array(
                                                        'product_id' => $dataProduct,
                                                        'group_carpet_color_id' => $parentCarpet,
                                                        'group_tangled_color_id' => $parentTangled,
                                                        'flooring_id' => $dataFlooring,
                                                        'type' => $type,
                                                        )
                                                    ), array('task' => 'by-ajax'));
		$results = $productListed['percenter'] ? $productListed['percenter'] : 0;
		return $results;
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
				'product_id'              => !empty($arrData['product_id']) ? $arrData['product_id'] : null,
				'type'                    => !empty($arrData['type']) ? $arrData['type'] : null,
				'group_tangled_color_id'  => !empty($arrData['group_tangled_color_id']) ? $arrData['group_tangled_color_id'] : null,
				'group_carpet_color_id'   => !empty($arrData['group_carpet_color_id']) ? $arrData['group_carpet_color_id'] : null,
				'flooring_id'         	  => !empty($arrData['flooring_id']) ? $arrData['flooring_id'] : null,
				'price'              	  => !empty($arrData['price']) ? $number->formatToNumber($arrData['price']) : null,
				'percenter'               => !empty($arrData['percenter']) ? $number->formatToNumber($arrData['percenter']) : 0,
				'created'                 => date('Y-m-d H:i:s'),
				'created_by'              => $this->userInfo->getUserInfo('id'),
			);
			
			$this->tableGateway->insert($data); // Thực hiện lưu database
			
			// Thêm lịch sử hệ thống
			$arrParamLogs = array(
				'data' => array(
					'title'          => 'Niêm yết sản phẩm',
					'action'         => 'Thêm mới',
					'id'    	     => $id,
					'options'        => array(
						'product_id'             => $arrData['product_id'],
						'type'                   => $arrData['type'],
						'group_tangled_color_id' => $arrData['group_tangled_color_id'],
						'group_carpet_color_id'  => $arrData['group_carpet_color_id'],
						'flooring_id'            => $data['flooring_id'],
						'price'          		 => $data['price'],
						'percenter'          	 => $data['percenter'],
					)
				)
			);
			$logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
			$arrContact = $arrParam['contact'];
			$id = $arrData['id'];
			$data = array();
			if(isset($arrData['price'])) {
			    $data['price'] = $number->formatToNumber($arrData['price']);
			}
			if(!empty($arrData['product_id'])) {
			    $data['product_id'] = $arrData['product_id'];
			}
			if(!empty($arrData['group_tangled_color_id'])) {
			    $data['group_tangled_color_id'] = $arrData['group_tangled_color_id'];
			}
			if(!empty($arrData['group_carpet_color_id'])) {
			    $data['group_carpet_color_id'] = $arrData['group_carpet_color_id'];
			}
			if(!empty($arrData['flooring_id'])) {
			    $data['flooring_id'] = $arrData['flooring_id'];
			}
			$data = [
				'price'          			=> !empty($arrData['price']) ? $number->formatToNumber($arrData['price']) : null,
				'percenter'          		=> !empty($arrData['percenter']) ? $number->formatToNumber($arrData['percenter']) : 0,
				'product_id'          		=> !empty($arrData['product_id']) ? $arrData['product_id'] : null,
				'group_tangled_color_id'    => !empty($arrData['group_tangled_color_id']) ? $arrData['group_tangled_color_id'] : null,
				'group_carpet_color_id'     => !empty($arrData['group_carpet_color_id']) ? $arrData['group_carpet_color_id'] : null,
				'flooring_id'           	=> !empty($arrData['flooring_id']) ? $arrData['flooring_id'] : null,
			];
			
			// Cập nhật
			$this->tableGateway->update($data, array('id' => $id));
			
			// Thêm lịch sử hệ thống
			if(!empty($id)) {
			    if(!empty($arrCheckResult)) {
			        $arrParamLogs = array(
			            'data' => array(
			                'title'          		=> 'Niêm yết sản phẩm',
			                'product_id'          	=> !empty($arrData['product_id']) ? $arrData['product_id'] : null,
			                'tangled_color_id'      => $arrData['tangled_color_id'],
			                'carpet_color_id'       => $arrData['carpet_color_id'],
			                'action'          		=> 'Sửa',
							'flooring_id'     		=> $arrData['flooring_id'],
							'type'     		        => $arrData['type'],
			            )
			        );
			        $logs = $this->getServiceLocator()->get('Admin\Model\LogsTable')->saveItem($arrParamLogs, array('task' => 'add-item'));
			    }
			}
			
			return $id;
		}

		// Import - Insert giá niêm yết
		if($options['task'] == 'import-insert') {	
			$id = $gid->getId();			
			$data	= array(
				'id'						=> $id,
			    'product_id'             	=> $arrData['product_id'],
			    'group_carpet_color_id'     => $arrData['group_carpet_color_id'],
			    'group_tangled_color_id'    => $arrData['group_tangled_color_id'],
			    'flooring_id'               => $arrData['flooring_id'],
			    'price'             		=> $arrData['price'],
			    'type'             		    => 'price',
			);
			$this->tableGateway->insert($data);
			
			return $id;
		}

		// Import - Insert giá niêm yết
		if($options['task'] == 'import-insert-capital-default') {
			$id = $gid->getId();
			$data	= array(
				'id'						=> $id,
			    'product_id'             	=> $arrData['product_id'],
			    'group_carpet_color_id'     => $arrData['group_carpet_color_id'],
			    'group_tangled_color_id'    => $arrData['group_tangled_color_id'],
			    'flooring_id'               => $arrData['flooring_id'],
			    'price'             		=> $arrData['price'],
			    'type'             		    => 'default',
			);
			$this->tableGateway->insert($data);

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