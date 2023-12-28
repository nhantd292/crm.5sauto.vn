<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;

class TaskProjectContentTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
	    if($options['task'] == 'list-item') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                $select -> where -> equalTo('task_project_id', $ssFilter['filter_task_project']);
                
                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo('status', $ssFilter['filter_status']);
                }
                
                if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('name', '%'. trim($ssFilter['filter_keyword']) . '%')
                			      ->UNNEST;
    			}
            })->current();
	    }
	    
	    return $result->count;
	}
	
	public function listItem($arrParam = null, $options = null){
		if($options['task'] == 'list-item') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];
                
    			$select -> limit($paginator['itemCountPerPage'])
    			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
    			
    			$select -> where -> equalTo('task_project_id', $ssFilter['filter_task_project']);
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select -> order(array($ssFilter['order_by'] .' '. strtoupper($ssFilter['order'])));
    			}
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select->where->equalTo('status', $ssFilter['filter_status']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select->where->NEST
                			      ->like('name', '%'. $ssFilter['filter_keyword'] . '%')
                			      ->UNNEST;
    			}
    			
    		});
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminTaskProjectContent';
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
	
	public function getItem($arrParam = null, $options = null){
	
		if($options == null) {
			$result	= $this->defaultGet($arrParam, array('by' => 'id'));
		}
	
		return $result;
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData    = $arrParam['data'];
	    $arrRoute   = $arrParam['route'];
	    $ssFilter   = $arrParam['ssFilter'];
	    
	    $date = new \ZendX\Functions\Date();
	    $number = new \ZendX\Functions\Number();
	    
		if($options['task'] == 'add-item') {
		    $gid = new \ZendX\Functions\Gid();
			$id = $gid->getId();
			$data	= array(
				'id'                    => $id,
				'task_project_id'       => $ssFilter['filter_task_project'],
				'name'                  => $arrData['name'],
				'status'                => $arrData['status'],
			    'created'               => date('Y-m-d H:i:s'),
			    'created_by'            => $this->userInfo->getUserInfo('id'),
			    'date'                  => $date->formatToData($arrData['date']),
				'content_by'            => $arrData['content_by'],
				'content_link'          => $arrData['content_link'],
			    'content_date'          => $date->formatToData($arrData['content_date']),
				'content_status'        => $arrData['content_status'],
				'camera_by'             => $arrData['camera_by'],
			    'camera_date'           => $date->formatToData($arrData['camera_date']),
				'camera_status'         => $arrData['camera_status'],
				'editor_by'             => $arrData['editor_by'],
			    'editor_date'           => $date->formatToData($arrData['editor_date']),
				'editor_status'         => $arrData['editor_status'],
				'youtube_by'            => $arrData['youtube_by'],
				'youtube_link'          => $arrData['youtube_link'],
			    'youtube_date'          => $date->formatToData($arrData['youtube_date']),
				'youtube_status'        => $arrData['youtube_status'],
				'youtube_view'          => $number->formatToData($arrData['youtube_view']),
				'youtube_comment'       => $number->formatToData($arrData['youtube_comment']),
				'youtube_like'          => $number->formatToData($arrData['youtube_like']),
				'youtube_dislike'       => $number->formatToData($arrData['youtube_dislike']),
				'facebook_by'           => $arrData['facebook_by'],
				'facebook_link'         => $arrData['facebook_link'],
			    'facebook_date'         => $date->formatToData($arrData['facebook_date']),
				'facebook_status'       => $arrData['facebook_status'],
				'facebook_like'         => $number->formatToData($arrData['facebook_like']),
				'facebook_comment'      => $number->formatToData($arrData['facebook_comment']),
				'facebook_share'        => $number->formatToData($arrData['facebook_share']),
			);
			
			$this->tableGateway->insert($data);
			return $id;
		}
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
			$data	= array(
				'name'                  => $arrData['name'],
				'status'                => $arrData['status'],
			    'date'                  => $date->formatToData($arrData['date']),
				'content_by'            => $arrData['content_by'],
				'content_link'          => $arrData['content_link'],
			    'content_date'          => $date->formatToData($arrData['content_date']),
				'content_status'        => $arrData['content_status'],
				'camera_by'             => $arrData['camera_by'],
			    'camera_date'           => $date->formatToData($arrData['camera_date']),
				'camera_status'         => $arrData['camera_status'],
				'editor_by'             => $arrData['editor_by'],
			    'editor_date'           => $date->formatToData($arrData['editor_date']),
				'editor_status'         => $arrData['editor_status'],
				'youtube_by'            => $arrData['youtube_by'],
				'youtube_link'          => $arrData['youtube_link'],
			    'youtube_date'          => $date->formatToData($arrData['youtube_date']),
				'youtube_status'        => $arrData['youtube_status'],
				'youtube_view'          => $number->formatToData($arrData['youtube_view']),
				'youtube_comment'       => $number->formatToData($arrData['youtube_comment']),
				'youtube_like'          => $number->formatToData($arrData['youtube_like']),
				'youtube_dislike'       => $number->formatToData($arrData['youtube_dislike']),
				'facebook_by'           => $arrData['facebook_by'],
				'facebook_link'         => $arrData['facebook_link'],
			    'facebook_date'         => $date->formatToData($arrData['facebook_date']),
				'facebook_status'       => $arrData['facebook_status'],
				'facebook_like'         => $number->formatToData($arrData['facebook_like']),
				'facebook_comment'      => $number->formatToData($arrData['facebook_comment']),
				'facebook_share'        => $number->formatToData($arrData['facebook_share']),
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