<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class EduClassTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){
        if($options['task'] == 'list-item') {
    	    $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter   = $arrParam['ssFilter'];
                $date       = new \ZendX\Functions\Date();
                $number     = new \ZendX\Functions\Number();
                
                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));
                
                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                                     -> greaterThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                                     -> AND
                                     -> lessThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                                     -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
                
    	        if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select -> where -> equalTo('status', $ssFilter['filter_status']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> like('name', '%'. trim($ssFilter['filter_keyword']) .'%');
    			}
    			
    			if(!empty($ssFilter['filter_product'])) {
    			    $select -> where -> equalTo('product_id', $ssFilter['filter_product']);
    			}
    			
    			if(!empty($ssFilter['filter_location'])) {
    			    $select -> where -> equalTo('location_id', $ssFilter['filter_location']);
    			}
    			
    			if(!empty($ssFilter['filter_room'])) {
    			    $select -> where -> equalTo('room_id', $ssFilter['filter_room']);
    			}
    			
    			if(!empty($ssFilter['filter_time'])) {
    			    $select -> where -> equalTo('time', $ssFilter['filter_time']);
    			}
    			
    	        if(!empty($ssFilter['filter_teacher'])) {
    			    $select -> where -> like('teacher_ids', '%'. $ssFilter['filter_teacher'] .'%');
    			}
    			
    	        if(!empty($ssFilter['filter_coach'])) {
    			    $select -> where -> like('coach_ids', '%'. $ssFilter['filter_coach'] .'%');
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
                
                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                                     -> greaterThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']))
                                     -> AND
                                     -> lessThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                                     -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo($ssFilter['filter_date_type'], $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
                
                if(!isset($options['paginator']) || $options['paginator'] == true) {
        			$select -> limit($paginator['itemCountPerPage'])
        			        -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }
    			
    			if(!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
    			    $select -> order(array($ssFilter['order_by'] => strtoupper($ssFilter['order'])));
    			}
    			
    			if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
    			    $select -> where -> equalTo('status', $ssFilter['filter_status']);
    			}
    			
    			if(isset($ssFilter['filter_keyword']) && $ssFilter['filter_keyword'] != '') {
    		        $select -> where -> like('name', '%'. trim($ssFilter['filter_keyword']) .'%');
    			}
    			
    			if(!empty($ssFilter['filter_product'])) {
    			    $select -> where -> equalTo('product_id', $ssFilter['filter_product']);
    			}
    			
    			if(!empty($ssFilter['filter_location'])) {
    			    $select -> where -> equalTo('location_id', $ssFilter['filter_location']);
    			}
    			
    			if(!empty($ssFilter['filter_room'])) {
    			    $select -> where -> equalTo('room_id', $ssFilter['filter_room']);
    			}
    			
    			if(!empty($ssFilter['filter_time'])) {
    			    $select -> where -> equalTo('time', $ssFilter['filter_time']);
    			}
    			
    			if(!empty($ssFilter['filter_teacher'])) {
    			    $select -> where -> like('teacher_ids', '%'. $ssFilter['filter_teacher'] .'%');
    			}
    			
    			if(!empty($ssFilter['filter_coach'])) {
    			    $select -> where -> like('coach_ids', '%'. $ssFilter['filter_coach'] .'%');
    			}
            });
		}
		
		if($options['task'] == 'list-all') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $paginator = $arrParam['paginator'];
                
    			if(!empty($options['order'])) {
    			    $select -> order($options['order']);
    			}
    			
    			if(isset($arrParam['status'])) {
    			    $select -> where -> equalTo('status', $arrParam['status']);
    			}
    			
    			if(!empty($arrParam['not_id'])) {
    			    $select -> where -> notEqualTo('id', $arrParam['not_id']);
    			}
    			
    			if(!empty($arrParam['teacher_ids'])) {
    			    $select -> where -> in('teacher_ids', $arrParam['teacher_ids']);
    			}
            });
		}
		
		if($options['task'] == 'public') {
			$result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $dateFormat = new \ZendX\Functions\Date();
                
			    $select -> order(array('public_date' => 'DESC'))
			            -> where -> greaterThanOrEqualTo('end_date', date('Y-m-d'));
            });
		}
		
	    if($options['task'] == 'cache') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminEduClass';
	        $result = $cache->getItem($cache_key);
	         
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select -> columns(array('id', 'name', 'product_id', 'location_id', 'room_id', 'public_status', 'public_date', 'student_max', 'schedule', 'sessions', 'time', 'student_total', 'teacher_ids', 'coach_ids', 'status', 'created', 'created_by'))
	                        -> order(array('public_date' => 'ASC', 'name' => 'DESC'));
	            });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));
                 
                $cache->setItem($cache_key, $result);
	        }
	    }
		
	    if($options['task'] == 'cache-basic') {
	        $cache = $this->getServiceLocator()->get('cache');
	        $cache_key = 'AdminEduClassBasic'. $arrParam['product_id'];
	        $result = $cache->getItem($cache_key);
	        
	        if (empty($result)) {
	            $items	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	                $select -> columns(array('id', 'name', 'product_id', 'location_id', 'room_id', 'public_status', 'public_date'))
	                        -> order(array('public_date' => 'ASC', 'name' => 'DESC'));
	                
                    if(!empty($arrParam['product_id'])) {
                        $select -> where -> equalTo('product_id', $arrParam['product_id']);
                    }
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
	
		return current($result);
	}
	
	public function saveItem($arrParam = null, $options = null){
	    $arrData  = $arrParam['data'];
	    $arrRoute = $arrParam['route'];
	    $arrItem  = $arrParam['item'];
	    
	    $gid      = new \ZendX\Functions\Gid();
	    $date     = new \ZendX\Functions\Date();
	    $number   = new \ZendX\Functions\Number();
	    $filter   = new \ZendX\Filter\Purifier();
	    
		if($options['task'] == 'add-item') {
			$id = $gid->getId();
			
			// Xác định những phần tử lưu vào options
			$item_options = array();
			$item_options['note']    = $arrData['note'];
			
			$data	= array(
				'id'            => $id,
				'name'          => $arrData['name'],
				'product_id'    => $arrData['product_id'],
				'location_id'   => $arrData['location_id'],
				'room_id'       => $arrData['room_id'],
				'public_date'   => $date->formatToData($arrData['public_date']),
				'end_date'      => $date->formatToData($arrData['end_date']),
				'student_max'   => $number->formatToNumber($arrData['student_max']),
			    'time'          => $arrData['time'],
			    'teacher_ids'   => $arrData['teacher_ids'] ? implode(',', $arrData['teacher_ids']) : null,
			    'coach_ids'     => $arrData['coach_ids'] ? implode(',', $arrData['coach_ids']) : null,
			    'schedule'      => $arrData['schedule'] ? implode(',', $arrData['schedule']) : '',
			    'route'         => $this->schedule($arrData) ? serialize($this->schedule($arrData)) : null,
			    'sessions'      => $arrData['sessions'],
				'status'        => $arrData['status'],
			    'public_status' => $arrData['public_status'],
			    'options'       => serialize($item_options),
				'created'       => date('Y-m-d H:i:s'),
				'created_by'    => $this->userInfo->getUserInfo('id'),
			);
			
			if(empty($data['end_date']) && !empty($data['route'])) {
			    $end_route = end(unserialize($data['route']));
			    $data['end_date'] = $date->formatToData($end_route['day']);
			}
			
			$this->tableGateway->insert($data);
			return $id;
		}
		
		if($options['task'] == 'edit-item') {
		    $id = $arrData['id'];
		    
		    $item_options = !empty($arrItem['options']) ? unserialize($arrItem['options']) : array();
		    $item_options['note'] = $arrData['note'];
		    
			$data	= array(
				'name'          => $arrData['name'],
				'product_id'    => $arrData['product_id'],
				'location_id'   => $arrData['location_id'],
				'room_id'       => $arrData['room_id'],
				'public_date'   => $date->formatToData($arrData['public_date']),
			    'end_date'      => $date->formatToData($arrData['end_date']),
				'student_max'   => $number->formatToNumber($arrData['student_max']),
			    'time'          => $arrData['time'],
			    'teacher_ids'   => $arrData['teacher_ids'] ? implode(',', $arrData['teacher_ids']) : null,
			    'coach_ids'     => $arrData['coach_ids'] ? implode(',', $arrData['coach_ids']) : null,
			    'schedule'      => $arrData['schedule'] ? implode(',', $arrData['schedule']) : '',
			    'route'         => $this->schedule($arrData) ? serialize($this->schedule($arrData)) : null,
			    'sessions'      => $arrData['sessions'],
				'status'        => $arrData['status'],
			    'public_status' => $arrData['public_status'],
			    'options'       => serialize($item_options),
			);
			
			if(!empty($arrData['public_date'])) {
			    $data['route'] = serialize($this->schedule($arrData, array('route' => $arrItem['route'], 'muster' => $arrItem['muster'], 'task' => 'update')));
			    
			    if(empty($data['end_date'])) {
			        $end_route = end(unserialize($data['route']));
			        $data['end_date'] = $date->formatToData($end_route['day']);
			    }
			}
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
		
		if($options['task'] == 'update-student') {
		    $id = $arrData['id'];
		    
		    if($options['type'] == 'up') {
    			$data	= array(
    				'student_total' => new Expression('(`student_total` + ?)', array(1)),
    			);
		    } elseif ($options['type'] == 'down') {
    			$data	= array(
    				'student_total' => new Expression('(`student_total` - ?)', array(1)),
    			);
		    } elseif (!empty($arrData['student_total'])) {
		        $data	= array(
		            'student_total' => $arrData['student_total'],
		        );
		    }
			
			$this->tableGateway->update($data, array('id' => $id));
			return $id;
		}
		
		if($options['task'] == 'update-note') {
		    $arrContract = $arrParam['contract'];
		    
		    $id = $arrItem['id'];
		    $note = $arrItem['note'] ? unserialize($arrItem['note']) : array();
		    
		    if(!empty($arrData['note_student'])) {
		        $note[$arrContract['id']][] = array(
		            'note_date' => date('Y-m-d H:i:s'),
		            'note_content' => $arrData['note_student'],
		            'note_by' => $this->userInfo->getUserInfo('id')
		        );
		    }
		
		    if(!empty($note)) {
    		    $data['note'] = serialize($note);
    		
    		    $this->tableGateway->update($data, array('id' => $id));
		    }
		    return $id;
		}
		
		if($options['task'] == 'update-muster') {
		    $id = $arrData['id'];
		    $muster = !empty($arrItem['muster']) ? unserialize($arrItem['muster']) : array();
		    
	        foreach ($arrData['muster'] AS $key => $value) {
	            foreach ($value AS $key_2 => $value_2) {
	                if(!is_array($value_2)) {
	                    $muster[$key][$key_2] = $value_2;
	                } else {
	                    foreach ($value_2 AS $key_3 => $value_3) {
	                        $muster[$key][$key_2][$key_3] = $value_3;
	                        if($value_3 == '') {
	                            unset($muster[$key][$key_2]);
	                        }
	                    }
	                }
	            }
	            $muster[$key]['success'] = 1;
	            if(count($muster[$key]) == 1) {
	                unset($muster[$key]);
	            }
	        }
		    
	        if(!empty($muster)) {
		        $data['muster'] = serialize($muster);
		        $this->tableGateway->update($data, array('id' => $id));
		    }
		    return $id;
		}
		
		if($options['task'] == 'update-exercise') {
		    $id = $arrData['id'];
		    $exercise = !empty($arrItem['exercise']) ? unserialize($arrItem['exercise']) : array();
		    
	        foreach ($arrData['exercise'] AS $key => $value) {
	            foreach ($value AS $key_2 => $value_2) {
	                if(!is_array($value_2)) {
	                    $exercise[$key][$key_2] = $value_2;
	                } else {
	                    foreach ($value_2 AS $key_3 => $value_3) {
	                        $exercise[$key][$key_2][$key_3] = $value_3;
	                        if($value_3 == '') {
	                            unset($exercise[$key][$key_2]);
	                        }
	                    }
	                }
	            }
	            $exercise[$key]['success'] = 1;
	            if(count($exercise[$key]) == 1) {
	                unset($exercise[$key]);
	            }
	        }
		    
	        if(!empty($exercise)) {
		        $data['exercise'] = serialize($exercise);
		        $this->tableGateway->update($data, array('id' => $id));
		    }
		    return $id;
		}
		
		if($options['task'] == 'update-route') {
		    $id = $arrData['id'];
		    $route = !empty($arrItem['route']) ? unserialize($arrItem['route']) : array();
	        foreach ($arrData['route'] AS $key => $value) {
	            foreach ($value AS $key_2 => $value_2) {
	                if(is_array($value_2)) {
                        $route[$key][$key_2] = implode(',', $value_2);
	                } else {
                        $route[$key][$key_2] = $value_2;
	                }
	            }
	        }
		    
	        $end_date = $arrItem['end_date'];
	        foreach ($route AS $key => $val) {
	            if(!empty($val['day'])) {
	                $end_date = $date->formatToData($val['day']);
	            }
	        }
	        
	        if(!empty($route)) {
		        $data['route'] = serialize($route);
		        $data['end_date'] = $end_date;
		        $this->tableGateway->update($data, array('id' => $id));
		    }
		    return $id;
		}
		
		if($options['task'] == 'update-point') {
		    $id = $arrData['id'];
		    
		    $point = $arrItem['point'] ? unserialize($arrItem['point']) : array();
		    $point[$arrData['contract_id']][$arrData['point_type_id']][$arrData['point_id']] = $arrData['point'];
		    
		    $data['point'] = serialize($point);
		    
		    $this->tableGateway->update($data, array('id' => $id));
		    return $id;
		}
	}
	
	public function deleteItem($arrParam = null, $options = null){
	    if($options['task'] == 'delete-item') {
	        $arrData  = $arrParam['data'];
	        $arrRoute = $arrParam['route'];
	         
	        $where = new Where();
	        $where->in('id', $arrData['cid']);
	        $where->equalTo('student_total', 0);
	        $this->tableGateway->delete($where);
	        
	        $result = count($arrData['cid']);
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
	
	public function report($arrParam = null, $options = null){
	    if($options['task'] == 'date-route') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	             
	            $columns = array('id', 'name', 'product_id', 'location_id', 'room_id', 'public_status', 'public_date', 'end_date', 'time', 'route', 'teacher_ids', 'coach_ids', 'status');
	            if(!empty($options['columns'])) {
	                array_merge($columns, $options['columns']);
	            }
	            $select -> columns($columns)
        	            -> where -> lessThanOrEqualTo('public_date', $dateFormat->formatToData($arrData['date_begin']) .' 00:00:00')
        	                     -> greaterThanOrEqualTo('end_date', $dateFormat->formatToData($arrData['date_begin']) .' 23:59:59');
	             
	            if(!empty($arrData['edu_location_id'])) {
	                $select -> where -> equalTo('location_id', $arrData['edu_location_id']);
	            }
	        });
	    }
	    
	    if($options['task'] == 'public_status') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
	            $arrData  = $arrParam['data'];
	            $arrRoute = $arrParam['route'];
	            $dateFormat = new \ZendX\Functions\Date();
	             
	            $columns = array('id', 'name', 'product_id', 'location_id', 'room_id', 'public_status', 'public_date', 'end_date', 'time', 'route', 'teacher_ids', 'coach_ids', 'status');
	            if(!empty($options['columns'])) {
	                array_merge($columns, $options['columns']);
	            }
	            $select -> columns($columns)
	                    -> order(array('end_date' => 'DESC'))
        	            -> where -> greaterThanOrEqualTo('end_date', date('Y-m-d') .' 23:59:59');
	             
	            if(!empty($arrData['edu_location_id'])) {
	                $select -> where -> equalTo('location_id', $arrData['edu_location_id']);
	            }
	        });
	    }
	    
	    return $result;
	}
	
    public function schedule($arrParam = null, $options = null) {
	    $date = new \ZendX\Functions\Date();
	    
	    // Phân tích lộ trình học
	    $weekday = date_format(date_create($date->formatToData($arrParam['public_date'])), 'N');
	    $weekday = strtolower($weekday);
	    $weekData = array(1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN');
	    	
	    $arrDay = array();
	    asort($arrParam['schedule']);
	    foreach ($arrParam['schedule'] AS $schedule) {
	        if($schedule == $weekData[$weekday]) {
	            $arrDay[] = $date->formatToData($arrParam['public_date']);
	        } else {
	            foreach ($weekData AS $key => $val) {
	                if(strtolower($schedule) == strtolower($val)) {
	                    if($key < $weekday) {
	                        $nextDay = $key + (7 - $weekday);
	                    } else {
	                        $nextDay = $key - $weekday;
	                    }
	                    $arrDay[] = $date->add($arrParam['public_date'], $nextDay, 'Y-m-d');
	                    break;
	                }
	            }
	        }
	    }
	    asort($arrDay);
	    $i = 0;
	    $start = false;
	    $arrFirstDay = $arrDay;
	    $arrLastDay = array();
	    foreach ($arrDay AS $key => $day) {
	        if($day == $arrParam['public_date']) {
	            $start = true;
	        }
	        if($start == true) {
	            $arrListDay[] = $day;
	            unset($arrLastDay[$key]);
	        }
	    }
	    $arrDay = array_merge($arrFirstDay, $arrLastDay);
	    
	    $arrRoute = array();
	    $day = 1;
	    $nextWeek = 0;
	    for($i = 1; $i <= ceil($arrParam['sessions'] / count($arrDay)); $i++){
	        if($i == 1) {
	            foreach ($arrDay AS $item) {
	                $arrRoute['day-'.$day]['day'] = $date->formatToView($item);
	                $arrRoute['day-'.$day]['teacher_id'] = !empty($arrParam['teacher_ids']) ? implode(',', $arrParam['teacher_ids']) : null;
	                $arrRoute['day-'.$day]['coach_id'] = !empty($arrParam['coach_ids']) ? implode(',', $arrParam['coach_ids']) : null;
	                $day++;
	            }
	        } else {
	            $nextWeek = $nextWeek + 7;
	    
	            foreach ($arrDay AS $item) {
	                $arrRoute['day-'.$day]['day'] = $date->add($item, $nextWeek, 'd/m/Y');
	                $arrRoute['day-'.$day]['teacher_id'] = !empty($arrParam['teacher_ids']) ? implode(',', $arrParam['teacher_ids']) : null;
	                $arrRoute['day-'.$day]['coach_id'] = !empty($arrParam['coach_ids']) ? implode(',', $arrParam['coach_ids']) : null;
	                $day++;
	            }
	        }
	    }
	    	
	    // Xóa phần tử cuối cùng nếu như số ngày học là lẻ
	    $unset_key = $arrParam['sessions'] + 1;
	    if(!empty($arrRoute['day-'. $unset_key])) {
	        unset($arrRoute['day-'. $unset_key]);
	    }
	    
		$dataRoute = $arrRoute;
	    if($options['task'] == 'update') {
	    	if(!empty($options['route'])) {
	    		$dataRouteItem = unserialize($options['route']);
	    		$dataMusterItem = unserialize($options['muster']);
	    		if(count($dataRouteItem) > count($dataRoute)) {
    	    		foreach ($dataRouteItem AS $key => $value) {
	    		        if(!empty($dataRoute[$key])) {
	    		            if(empty($dataMusterItem['day-1']['success'])) {
	    		                $dataRouteItem[$key]['day'] = $dataRoute[$key]['day'];
	    		            }
	    		            if(empty($value['teacher_id'])) {
	    		                $dataRouteItem[$key]['teacher_id'] = !empty($arrParam['teacher_ids']) ? implode(',', $arrParam['teacher_ids']) : null;
	    		            }
	    		            if(empty($value['coach_id'])) {
	    		                $dataRouteItem[$key]['coach_id'] = !empty($arrParam['coach_ids']) ? implode(',', $arrParam['coach_ids']) : null;
	    		            }
	    		        } elseif (empty($dataRouteItem[$key]['status'])) {
	    		            unset($dataRouteItem[$key]);
	    		        }
    	    		}
	    		} else {
            		foreach ($dataRoute AS $key => $value) {
            		    if(empty($dataMusterItem['day-1']['success'])) {
            		        $dataRouteItem[$key]['day'] = $dataRoute[$key]['day'];
            		    }
            		    if(empty($dataRouteItem[$key]['teacher_id'])) {
            		        $dataRouteItem[$key]['teacher_id'] = !empty($arrParam['teacher_ids']) ? implode(',', $arrParam['teacher_ids']) : null;
            		    }
            		    if(empty($dataRouteItem[$key]['coach_id'])) {
            		        $dataRouteItem[$key]['coach_id'] = !empty($arrParam['coach_ids']) ? implode(',', $arrParam['coach_ids']) : null;
            		    }
            			$dataRoute[$key] = $dataRouteItem[$key];
            		}
            		
            		$dataRouteItem = $dataRoute;
	    		}
	    	}
	    	
	    	$dataRoute = $dataRouteItem;
	    }
	    
	    return $dataRoute;
	}
}














