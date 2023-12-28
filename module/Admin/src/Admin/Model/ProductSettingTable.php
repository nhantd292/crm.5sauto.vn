<?php

namespace Admin\Model;

use Zend\Db\Sql\Select;

class ProductSettingTable extends DefaultTable
{

    public function countItem($arrParam = null, $options = null)
    {
        //	    if($options['task'] == 'list-item') {
        //	        $result = $this->defaultCount($arrParam, null);
        //	    }

        if ($options['task'] == 'list-item') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options) {
                $ssFilter  = $arrParam['ssFilter'];
                $date      = new \ZendX\Functions\Date();

                $select->columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                if (!empty($ssFilter['filter_product_group_id'])) {
                    $select->where->equalTo('product_group_id', $ssFilter['filter_product_group_id']);
                }
            })->current();
        }

        return $result->count;
    }

    public function listItem($arrParam = null, $options = null)
    {
        if ($options['task'] == 'list-item') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options) {
                $paginator  = $arrParam['paginator'];
                $ssFilter   = $arrParam['ssFilter'];
                // $number     = new \ZendX\Functions\Number();


                if (!empty($ssFilter['order_by']) && !empty($ssFilter['order'])) {
                    $select->ORder(array($ssFilter['order_by'] => strtoupper($ssFilter['order'])));
                }

                if (!empty($options['paginator']) || $options['paginator'] == true) {
                    $select->limit($paginator['itemCountPerPage'])
                        ->offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);
                }

                if (!empty($ssFilter['filter_product_group_id'])) {
                    $select->where->equalTo('product_group_id', $ssFilter['filter_product_group_id']);
                }
            });
            $result = \ZendX\Functions\CreateArray::create($result, array('key' => 'id', 'value' => 'object'));
        }
        if ($options['task'] == 'cache-active') {
            $cache = $this->getServiceLocator()->get('cache');
            $cache_key = 'AdminProductActive' . $arrParam['type'];
            $result = $cache->getItem($cache_key);

            if (empty($result)) {
                $items    = $this->tableGateway->select(function (Select $select) use ($arrParam) {
                    $select->order(array('ordering' => 'ASC', 'name' => 'ASC'));
                    $select->where->equalTo('status', 1);
                });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));

                $cache->setItem($cache_key, $result);
            }
        }
        if ($options['task'] == 'cache') {
            $cache = $this->getServiceLocator()->get('cache');
            $cache_key = 'AdminProduct' . $arrParam['type'];
            $result = $cache->getItem($cache_key);

            if (empty($result)) {
                $items    = $this->tableGateway->select(function (Select $select) use ($arrParam) {
                    $select->order(array('ordering' => 'ASC', 'name' => 'ASC'));
                });
                $result = \ZendX\Functions\CreateArray::create($items, array('key' => 'id', 'value' => 'object'));

                $cache->setItem($cache_key, $result);
            }
        }

        return $result;
    }

    public function getItem($arrParam = null, $options = null)
    {

        if ($options == null) {
            $result    = $this->defaultGet($arrParam, array('by' => 'id'));
        }

        if ($options['task'] == 'by-name') {
            $result    = $this->tableGateway->select(function (Select $select) use ($arrParam, $options) {
                $select->where->equalTo('name', $arrParam['name']);
            })->current();
        }

        return $result;
    }

    public function saveItem($arrParam = null, $options = null)
    {
        $arrData  = $arrParam['data'];
        $arrRoute = $arrParam['route'];

        $filter   = new \ZendX\Filter\Purifier();
        $number   = new \ZendX\Functions\Number();
        $gid      = new \ZendX\Functions\Gid();

        if ($options['task'] == 'add-item') {
            $id = $gid->getId();
            $data    = array(
                'id'                => $id,
                'name'              => $arrData['name'],
                'code'              => $arrData['code'],
                'price'             => $arrData['price'] ? $number->formatToData($arrData['price']) : 0,
                'listed_price'      => $arrData['listed_price'] ? $number->formatToData($arrData['listed_price']) : 0,
                'unit_id'              => $arrData['unit_id'] ? $arrData['unit_id'] : null,
                'product_group_id'  => $arrData['product_group_id'] ? $arrData['product_group_id'] : null,
                'ordering'          => $arrData['ordering'],
                'status'            => $arrData['status'],
                'created'           => date('Y-m-d H:i:s'),
                'created_by'        => $this->userInfo->getUserInfo('id'),
            );

            $this->tableGateway->insert($data);
            return $id;
        }
        if ($options['task'] == 'edit-item') {
            $id = $arrData['id'];
            $data    = array(
                'name'              => $arrData['name'],
                'code'              => $arrData['code'],
                'price'             => $arrData['price'] ? $number->formatToData($arrData['price']) : 0,
                'listed_price'      => $arrData['listed_price'] ? $number->formatToData($arrData['listed_price']) : 0,
                'unit_id'              => $arrData['unit_id'] ? $arrData['unit_id'] : null,
                'product_group_id'  => $arrData['product_group_id'] ? $arrData['product_group_id'] : null,
                'type'              => $arrData['type'],
                'ordering'          => $arrData['ordering'],
                'status'            => $arrData['status'],
            );

            $this->tableGateway->update($data, array('id' => $id));
            return $id;
        }
    }

    public function deleteItem($arrParam = null, $options = null)
    {
        if ($options['task'] == 'delete-item') {
            $result = $this->defaultDelete($arrParam, null);
        }

        return $result;
    }

    public function changeStatus($arrParam = null, $options = null)
    {
        if ($options['task'] == 'change-status') {
            $result = $this->defaultStatus($arrParam, null);
        }

        return $result;
    }

    public function changeOrdering($arrParam = null, $options = null)
    {
        if ($options['task'] == 'change-ordering') {
            $result = $this->defaultOrdering($arrParam, null);
        }
        return $result;
    }
}
