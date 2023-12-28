<?php
namespace Admin\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use ZendX\System\UserInfo;

class ComboProductTable extends DefaultTable {

    public function countItem($arrParam = null, $options = null){

        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $ssFilter  = $arrParam['ssFilter'];

                $select -> columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(1)')));

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_COMBO_PRODUCT.'.status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select->where->like(TABLE_COMBO_PRODUCT.'.name', '%'.$ssFilter['filter_keyword'].'%');
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
            })->current();
        }

        if($options['task'] == null) {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $select -> columns(array('count' => new Expression('COUNT(1)')));
            })->current();
        }
        return $result->count;
    }

    public function listItem($arrParam = null, $options = null){
        if($options['task'] == 'list-item') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $date = new \ZendX\Functions\Date();
                $paginator = $arrParam['paginator'];
                $ssFilter  = $arrParam['ssFilter'];

                $select -> limit($paginator['itemCountPerPage'])
                    -> offset(($paginator['currentPageNumber'] - 1) * $paginator['itemCountPerPage']);

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_COMBO_PRODUCT.'.status', $ssFilter['filter_status']);
                }

                if(isset($ssFilter['filter_keyword']) && trim($ssFilter['filter_keyword']) != '') {
                    $select->where->like(TABLE_COMBO_PRODUCT.'.name', '%'.$ssFilter['filter_keyword'].'%');
                }

                if(!empty($ssFilter['filter_date_begin']) && !empty($ssFilter['filter_date_end'])) {
                    $select -> where -> NEST
                        -> greaterThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_begin']))
                        ->AND
                        -> lessThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59')
                        -> UNNEST;
                } elseif (!empty($ssFilter['filter_date_begin'])) {
                    $select -> where -> greaterThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_begin']));
                } elseif (!empty($ssFilter['filter_date_end'])) {
                    $select -> where -> lessThanOrEqualTo(TABLE_COMBO_PRODUCT.'.created', $date->formatToData($ssFilter['filter_date_end']) . ' 23:59:59');
                }
            });
        }

        if($options['task'] == 'list-all') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam, $options){
                $ssFilter  = $arrParam['ssFilter'];

                $select -> order('name ASC');

                if(isset($ssFilter['filter_status']) && $ssFilter['filter_status'] != '') {
                    $select->where->equalTo(TABLE_COMBO_PRODUCT.'.status', $ssFilter['filter_status']);
                }

            });
        }

        return $result;
    }

    public function getItem($arrParam = null, $options = null){

        if($options['task'] == 'by-name') {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select->where->equalTo('name', $arrParam['name']);
            })->current();
        }

        if($options == null) {
            $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
                $select->where->equalTo('id', $arrParam['id']);
                $select->where->equalTo('status', 1);
            })->current();
        }
        return $result;
    }

    public function saveItem($arrParam = null, $options = null){
        $arrData  = $arrParam['data'];
        $date     = new \ZendX\Functions\Date();
        $number   = new \ZendX\Functions\Number();
        $gid      = new \ZendX\Functions\Gid();

        if($options['task'] == 'add-item') {
            // Thêm đơn hàng
            $id = $gid->getId();
            $contract_product = $arrData['contract_product'];
            $combo_options['product']  = array();
            $price_total = 0;
            for($i = 0; $i < count($contract_product['product_id']); $i++){
                if(!empty($contract_product['product_id'][$i])) {
                    $combo_options['product'][$i]['key_id']           = !empty($contract_product['key_id'][$i]) ? $contract_product['key_id'][$i] : $gid->getId(); // Tạo mã đối chiếu trong trường hợp bán lại hàng có sẵn.
                    $combo_options['product'][$i]['product_id']       = !empty($contract_product['product_id'][$i]) ? $contract_product['product_id'][$i] : null; // sản phẩm
                    $combo_options['product'][$i]['product_name']     = !empty($contract_product['product_name'][$i]) ? $contract_product['product_name'][$i] : null; // tên xe - năm sản xuất
                    $combo_options['product'][$i]['stock']            = !empty($contract_product['stock'][$i]) ? $contract_product['stock'][$i] : null; // Hàng có sẵn
                    $combo_options['product'][$i]['carpet_color_id']  = !empty($contract_product['carpet_color_id'][$i]) ? $contract_product['carpet_color_id'][$i] : null; // màu thảm
                    $combo_options['product'][$i]['tangled_color_id'] = !empty($contract_product['tangled_color_id'][$i]) ? $contract_product['tangled_color_id'][$i] : null; // màu rối
                    $combo_options['product'][$i]['flooring_id']      = !empty($contract_product['flooring_id'][$i]) ? $contract_product['flooring_id'][$i] : null; // loại sản phẩm
                    $combo_options['product'][$i]['numbers']          = !empty($contract_product['numbers'][$i]) ? $contract_product['numbers'][$i] : 1; // số lượng của đơn hàng
                    $combo_options['product'][$i]['price']            = !empty($contract_product['price'][$i]) ? $number->formatToNumber($contract_product['price'][$i]) : null; // giá bán
                    $combo_options['product'][$i]['listed_price']     = !empty($contract_product['listed_price'][$i]) ? $number->formatToNumber($contract_product['listed_price'][$i]) : null; // giá niêm yết
                    $combo_options['product'][$i]['capital_default']  = !empty($contract_product['capital_default'][$i]) ? $number->formatToNumber($contract_product['capital_default'][$i]) : null; // giá vốn mặc định
                    $combo_options['product'][$i]['sale_price']       = !empty($contract_product['sale_price'][$i]) ? $number->formatToNumber($contract_product['sale_price'][$i]) : null; // giảm giá
                    $combo_options['product'][$i]['vat']              = !empty($contract_product['vat'][$i]) ? $number->formatToNumber($contract_product['vat'][$i]) : null; // vat
                    $combo_options['product'][$i]['total']            = !empty($contract_product['total'][$i]) ? $number->formatToNumber($contract_product['total'][$i]) : null; // tổng tiền (chính là cột thành tiền)
                    $combo_options['product'][$i]['type']             = !empty($contract_product['type'][$i]) ? $contract_product['type'][$i] : null;
                    $combo_options['product'][$i]['sale_branch_id']   = !empty($contract_product['sale_branch_id'][$i]) ? $contract_product['sale_branch_id'][$i] : null;

                    $product                                             = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
                    $combo_options['product'][$i]['product_alias']    = $product[$contract_product['product_id'][$i]]['code']; // alias của sản phẩm
                    $combo_options['product'][$i]['product_group_id'] = $product[$contract_product['product_id'][$i]]['product_group_id']; // nhóm sản phẩm
                    $combo_options['product'][$i]['total_production'] = !empty($contract_product['total_production'][$i]) ? $number->formatToNumber($contract_product['total_production'][$i]) : 0;

                    $combo_options['product'][$i]['number_production'] = !empty($contract_product['number_production'][$i]) || $contract_product['number_production'][$i] == 0 ? $contract_product['number_production'][$i] : null; // số lượng sx của sản phẩm (CM sản phẩm)
                    $combo_options['product'][$i]['number_carpet']     = !empty($contract_product['number_carpet'][$i]) || $contract_product['number_carpet'][$i] == 0 ? $contract_product['number_carpet'][$i] : null; // số lượng sản xuất màu thảm (CM thảm)
                    $combo_options['product'][$i]['number_tangled']    = !empty($contract_product['number_tangled'][$i]) || $contract_product['number_tangled'][$i] == 0 ? $contract_product['number_tangled'][$i] : null; //số lượng sản xuất màu rối (CM rối)
                    $combo_options['product'][$i]['price_production']  = !empty($contract_product['price_production'][$i]) ? $number->formatToNumber($contract_product['price_production'][$i]) : 0; // Giá vốn
                    $combo_options['product'][$i]['keyUpdate']         = isset($contract_product['keyUpdate'][$i]) ? $contract_product['keyUpdate'][$i] : null;

                    $price_total += !empty($contract_product['price'][$i]) ? $number->formatToNumber($contract_product['price'][$i]) : 0; // tổng giá bán
                }
            }

            $data = array(
                'id'                    => $id,
                'name'                  => $arrData['name'],
                'note'                  => $arrData['note'],
                'price_total'           => $price_total,
                'created'               => date('Y-m-d H:i:s'),
                'created_by'            => $this->userInfo->getUserInfo('id'),
                'options'               => serialize($combo_options)
            );

            $this->tableGateway->insert($data); // Thực hiện lưu database
            return $id;
        }

        if($options['task'] == 'edit-item') {
            $id = $arrData['id'];
            $contract_product = $arrData['contract_product'];
            $combo_options['product']  = array();
            $price_total = 0;
            for($i = 0; $i < count($contract_product['product_id']); $i++){
                if(!empty($contract_product['product_id'][$i])) {
                    $combo_options['product'][$i]['key_id']           = !empty($contract_product['key_id'][$i]) ? $contract_product['key_id'][$i] : $gid->getId(); // Tạo mã đối chiếu trong trường hợp bán lại hàng có sẵn.
                    $combo_options['product'][$i]['product_id']       = !empty($contract_product['product_id'][$i]) ? $contract_product['product_id'][$i] : null; // sản phẩm
                    $combo_options['product'][$i]['product_name']     = !empty($contract_product['product_name'][$i]) ? $contract_product['product_name'][$i] : null; // tên xe - năm sản xuất
                    $combo_options['product'][$i]['stock']            = !empty($contract_product['stock'][$i]) ? $contract_product['stock'][$i] : null; // Hàng có sẵn
                    $combo_options['product'][$i]['carpet_color_id']  = !empty($contract_product['carpet_color_id'][$i]) ? $contract_product['carpet_color_id'][$i] : null; // màu thảm
                    $combo_options['product'][$i]['tangled_color_id'] = !empty($contract_product['tangled_color_id'][$i]) ? $contract_product['tangled_color_id'][$i] : null; // màu rối
                    $combo_options['product'][$i]['flooring_id']      = !empty($contract_product['flooring_id'][$i]) ? $contract_product['flooring_id'][$i] : null; // loại sản phẩm
                    $combo_options['product'][$i]['numbers']          = !empty($contract_product['numbers'][$i]) ? $contract_product['numbers'][$i] : 1; // số lượng của đơn hàng
                    $combo_options['product'][$i]['price']            = !empty($contract_product['price'][$i]) ? $number->formatToNumber($contract_product['price'][$i]) : null; // giá bán
                    $combo_options['product'][$i]['listed_price']     = !empty($contract_product['listed_price'][$i]) ? $number->formatToNumber($contract_product['listed_price'][$i]) : null; // giá niêm yết
                    $combo_options['product'][$i]['capital_default']  = !empty($contract_product['capital_default'][$i]) ? $number->formatToNumber($contract_product['capital_default'][$i]) : null; // giá vốn mặc định
                    $combo_options['product'][$i]['sale_price']       = !empty($contract_product['sale_price'][$i]) ? $number->formatToNumber($contract_product['sale_price'][$i]) : null; // giảm giá
                    $combo_options['product'][$i]['vat']              = !empty($contract_product['vat'][$i]) ? $number->formatToNumber($contract_product['vat'][$i]) : null; // vat
                    $combo_options['product'][$i]['total']            = !empty($contract_product['total'][$i]) ? $number->formatToNumber($contract_product['total'][$i]) : null; // tổng tiền (chính là cột thành tiền)
                    $combo_options['product'][$i]['type']             = !empty($contract_product['type'][$i]) ? $contract_product['type'][$i] : null;
                    $combo_options['product'][$i]['sale_branch_id']   = !empty($contract_product['sale_branch_id'][$i]) ? $contract_product['sale_branch_id'][$i] : null;

                    $product                                             = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'list-item'));
                    $combo_options['product'][$i]['product_alias']    = $product[$contract_product['product_id'][$i]]['code']; // alias của sản phẩm
                    $combo_options['product'][$i]['product_group_id'] = $product[$contract_product['product_id'][$i]]['product_group_id']; // nhóm sản phẩm
                    $combo_options['product'][$i]['total_production'] = !empty($contract_product['total_production'][$i]) ? $number->formatToNumber($contract_product['total_production'][$i]) : 0;

                    $combo_options['product'][$i]['number_production'] = !empty($contract_product['number_production'][$i]) || $contract_product['number_production'][$i] == 0 ? $contract_product['number_production'][$i] : null; // số lượng sx của sản phẩm (CM sản phẩm)
                    $combo_options['product'][$i]['number_carpet']     = !empty($contract_product['number_carpet'][$i]) || $contract_product['number_carpet'][$i] == 0 ? $contract_product['number_carpet'][$i] : null; // số lượng sản xuất màu thảm (CM thảm)
                    $combo_options['product'][$i]['number_tangled']    = !empty($contract_product['number_tangled'][$i]) || $contract_product['number_tangled'][$i] == 0 ? $contract_product['number_tangled'][$i] : null; //số lượng sản xuất màu rối (CM rối)
                    $combo_options['product'][$i]['price_production']  = !empty($contract_product['price_production'][$i]) ? $number->formatToNumber($contract_product['price_production'][$i]) : 0; // Giá vốn
                    $combo_options['product'][$i]['keyUpdate']         = isset($contract_product['keyUpdate'][$i]) ? $contract_product['keyUpdate'][$i] : null;

                    $price_total += !empty($contract_product['price'][$i]) ? $number->formatToNumber($contract_product['price'][$i]) : 0; // tổng giá bán
                }
            }

            $data = array(
                'name'                  => $arrData['name'],
                'note'                  => $arrData['note'],
                'price_total'                  => $price_total,
                'options'               => serialize($combo_options)
            );

            $this->tableGateway->update($data, array('id' => $id));
            return $id;
        }

        if($options['task'] == 'import-item') {
            $id = $gid->getId();

            $combo_options['product']  = $arrData['detail']['product'];
            $data = array(
                'id'                    => $id,
                'name'                  => $arrData['combo_name'],
                'price_total'           => $arrData['detail']['price_total'],
                'created'               => date('Y-m-d H:i:s'),
                'created_by'            => $this->userInfo->getUserInfo('id'),
                'options'               => serialize($combo_options)
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