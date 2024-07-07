<?php
namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\Json\Json;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

use Zend\Http\Response;

class ApiController extends ActionController {

    public function init() {
        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }

    public function deleteCacheAction() {
        $cache = $this->getServiceLocator()->get('cache');
        $cache = $cache->clearExpired();
    }
    /**
     * Cập nhật trạng thái đơn hàng theo file excel up lên
     */
    public function updateContractAction(){
        if (!empty($this->_params['data']['file_import']['tmp_name'])) {
            $upload      = new \ZendX\File\Upload();
            $file_import = $upload->uploadFile('file_import', PATH_FILES . '/import/', array());
        }

        require_once PATH_VENDOR . '/Excel/PHPExcel/IOFactory.php';
        $objPHPExcel = \PHPExcel_IOFactory::load(PATH_FILES . '/import/' . $file_import);

        $sheetData = $objPHPExcel->getActiveSheet(1)->toArray(null, true, true, true);
        $labels = [];
        $columns = ['code' => 'Mã số đơn','production_department_type' => 'Trạng thái sản xuất','production_note' => 'Ghi chú sản xuất','production_date' => 'Ngày hoàn thành sản xuất','shipper_id' => 'Nhân viên giao hàng'];
        foreach($columns as $column) {
            $labels[$column] = ['label' => $column, 'found' => 0];
        }
        foreach($sheetData[1] as $key=>$val) {
            $val = trim($val);
            if (in_array($val,array_values($columns))) {
                $labels[$val]['found'] = 1;
                $labels[$val]['column'] = $key;
                $labels[$val]['code'] = array_search($val,$columns);
            }
        }
        $labels = array_values($labels);
        $found_false = array_search(0,array_column($labels,'found'));
        if ($found_false!==false) {
            echo json_encode(array('success'=>false,'heading'=>$sheetData,'label'=>$labels,'found_false'=>$found_false,'msg'=>'Không tìm thấy cột '.($labels[$found_false]['label']?:'')));die();
        }
        $contracts = [];
        $product_types = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array( "where" => array( "code" => "production-department" )), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
        $shippers = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-positons-care')), array('key' => 'name', 'value' => 'id'));
        
        foreach($sheetData as $key=>$item) {
            if ($key>1 && isset($item['A']) && strlen($item['A'])) {
                $object_info = [];
                $msg = '';
                foreach($labels as $column_info) {
                    $object_info[$column_info['code']] = $item[$column_info['column']];
                }
                $object_info['production_department_type'] = (new \ZendX\Filter\CreateAlias())->filter($object_info['production_department_type']);
                if (!in_array($object_info['production_department_type'],array_keys($product_types))) {
                    $msg = 'Không tìm thấy Trạng thái sản xuất';
                }  else {
                    $object_info['original_production_department_type'] = $object_info['production_department_type'];
                    $object_info['production_department_type'] = $product_types[$object_info['production_department_type']]['name'];
                }
                if (!empty($object_info['shipper_id'])&&!in_array($object_info['shipper_id'],array_keys($shippers))) {
                    $msg = 'Không tìm thấy Nhân viên giao hàng';
                } else {
                    $object_info['original_shipper_id'] = $object_info['shipper_id'];
                    $object_info['shipper_id'] = $shippers[$object_info['shipper_id']];
                }
                $contract_info = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $object_info['code']), array('task' => 'by-code'));
                if (!$contract_info) $msg = 'Không tìm thấy Mã số đơn';
                if ($contract_info['lock']){
                    $object_info['lock'] = 1;
                    $msg = 'Đơn hàng đã khóa';
                }
                $object_info['msg'] = $msg;
                $object_info['id'] = $contract_info['id'];
                $contracts[] = $object_info;
            }
        }
        $index = 0;
        foreach($contracts as $key=>$item) {
            $contracts[$key]['index'] = ++$index;
            $contracts[$key]['success'] = !empty($item['id']) && $item['id'] && empty($item['lock']) == $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('data'=>array(
                'id' => $item['id'],
                'production_department_type' => $item['original_production_department_type'],
                'production_note' => $item['production_note'],
                'production_date' => $item['production_date'],
                'shipper_id' => $item['shipper_id'],
            )), array('task' => 'update-production'));
            $contracts[$key]['production_note'] = $contracts[$key]['production_note']?:'';
            $contracts[$key]['production_date'] = $contracts[$key]['production_date']?:'';
            $contracts[$key]['shipper_id']      = $contracts[$key]['shipper_id']?:'';
        }
        echo json_encode(array('product_types'=>$product_types,'success'=>true,'labels'=>$labels,'data'=>$contracts,'msg'=>'Cập nhật trạng thái '.count($ids).' đơn hàng thành công.'));die();
    }
    public function selectAction() {
        $db             = $this->_params['data']['data-db'] ? $this->_params['data']['data-db'] : 'dbConfig';
        $adapter        = $this->getServiceLocator()->get($db);
        $tableGateway   = new TableGateway($this->_params['data']['data-table'], $adapter, null);
        $table          = new \Admin\Model\ApiTable($tableGateway);

        $items = $table->listItem($this->_params, null);

        $results = array();

        if($items->count() > 0) {
            $data_id = $this->_params['data']['data-id'];
            $data_text = explode(',', $this->_params['data']['data-text']);

            $results[] = array('id' => '', 'text' => ' - Chọn - ');
            foreach ($items AS $item) {
                $text = '';
                for ($i = 0; $i < count($data_text) ; $i++) {
                    if($i == 0) {
                        $text .= $item[$data_text[$i]];
                    } else {
                        if(!empty($item[$data_text[$i]])) {
                            $text .= ' - ' . $item[$data_text[$i]];
                        }
                    }
                }
                $results[] = array(
                    'id' => $item[$data_id],
                    'text' => $text,
                );
            }
        }

        if(!empty($this->_params['route']['id'])) {
            $results = $results[1];
        }

        if(!empty($this->_params['data']['term'])) {
            $termResults = array();
            foreach ($results AS $result) {
                $pos = strpos(strtolower($result['text']), strtolower($this->_params['data']['term']));
                if($pos !== false) {
                    $termResults[] = $result;
                }
            }
            echo Json::encode($termResults);
        } else {
            echo Json::encode($results);
        }

        return $this->response;
    }

    public function listAction() {
        $adapter        = $this->getServiceLocator()->get('dbConfig');
        $tableGateway   = new TableGateway($this->_params['data']['data-table'], $adapter, null);
        $table          = new \Admin\Model\ApiTable($tableGateway);

        $results        = $table->listItem($this->_params, array('task' => 'list-item'));

        if(!empty($this->_params['data']['id'])) {
            $results    = $results[0];
        }

        echo Json::encode($results);
        return $this->response;
    }

    public function saleGroupAction() {
        if(empty($this->_params['data']['sale_branch_id'])) {
            return $this->response;
        }
        $items = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group', 'document_id' => $this->_params['data']['sale_branch_id'])), array('task' => 'list-all'));

        $this->_viewModel['items'] = $items;
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function discountsDetailAction() {
        $type   = $this->_params['data']['discounts_type'];
        $option = $this->_params['data']['discounts_option'];
        $details = [];
        if(isset($this->_params['data']['discounts_detail'])){
            $details = unserialize($this->_params['data']['discounts_detail']);
        }

        if(empty($type) && empty($option)) {
            return $this->response;
        }
        else{
            if($type == 'hoa-don'){
                if($option == 'giam-gia-hoa-don'){

                }
                elseif ($option == 'tang-hang'){

                }
                elseif ($option == 'giam-gia-hang'){

                }
            }
            if($type == 'hang-hoa'){
                if($option == 'mua-hang-giam-gia-hang'){

                }
                elseif ($option == 'mua-hang-tang-hang'){

                }
                elseif ($option == 'gia-ban-theo-so-luong-mua'){

                }
            }
        }

        $this->_viewModel['type']       = $type;
        $this->_viewModel['option']     = $option;
        $this->_viewModel['details']    = $details;
        $this->_viewModel['products']   = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function discountsOptionAction() {
        if(empty($this->_params['data']['alias'])) {
            return $this->response;
        }
        $type = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->getItem($this->_params['data'], array('task' => 'by-custom-alias'));
        $items = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('document_id' => $type->id)), array('task' => 'list-all'));

        $this->_viewModel['items'] = $items;
        $this->_viewModel['discounts_option'] = $this->_params['data']['discounts_option'];
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function loadKovProductsAction() {
        $itemPerpage = 20;
        $curentPage = $this->_params['data']['curentPage'] ? $this->_params['data']['curentPage'] : 1;
        $curentItem = ($curentPage - 1) * $itemPerpage;
        $search_params = '';
        if(!empty($this->_params['data']['categoriId']))
            $search_params .= '&categoryId='.$this->_params['data']['categoriId'];
        if(!empty($this->_params['data']['filter_keyword']))
            $search_params .= '&name='.urlencode($this->_params['data']['filter_keyword']);


        $return = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?currentItem='.$curentItem.'&isActive=1&includeInventory=true'.$search_params);
        $kovProducts = json_decode($return,true);

        $this->_viewModel['kovProducts'] = $kovProducts['data'];
        $this->_viewModel['count'] = $kovProducts['total'];
        $this->_viewModel['itemPerpage'] = $itemPerpage;
        $this->_viewModel['curentPage']  = $curentPage;
        $this->_viewModel['kov_branch_id']  = $this->_userInfo->getUserInfo('kov_branch_id');

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function addProductToListAction() {
        if($this->_params['data']['id']){
            $return = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products/'.$this->_params['data']['id']);
            $product = json_decode($return,true);
            $this->_viewModel['product'] = $product;
            $this->_viewModel['data'] = $this->_params['data'];
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
            return $viewModel;
        }

    }

    public function checkGiftAction() {
        $this->_viewModel['data']               = $this->_params['data'];
        $this->_viewModel['discounts']          = $this->getServiceLocator()->get('Admin\Model\KovDiscountsTable')->listItem($this->_params['data'], array('task' => 'list-check'));
        $this->_viewModel['discounts_type']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'discounts-type')), array('task' => 'cache-alias'));
        $this->_viewModel['discounts_option']   = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'discounts-option')), array('task' => 'cache-alias'));
        $this->_viewModel['products']           = $this->getServiceLocator()->get('Admin\Model\KovProductsTable');
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function getDiscountsDetailAction() {
        $data = explode('_', $this->_params['data']['item']);
        $branch_id = $this->_params['data']['branch_id'];
        $number   = new \ZendX\Functions\Number();

        $discouts = $this->getServiceLocator()->get('Admin\Model\KovDiscountsTable')->getItem(array('id' => $data[0]));
        $detail = unserialize($discouts['detail']);
        if($discouts['discounts_option'] == 'giam-gia-hoa-don'){
            $res = array(
                'type'          => 'giam-gia-hoa-don',
                'unit_type'     => $detail[$data[1]]['unit_type'],
                'value'    => $detail[$data[1]]['discount_value'],
            );
            echo json_encode(array('success' => true, 'data'=>$res));
        }
        $text = '(Hàng tặng)';
        if($discouts['discounts_option'] == 'tang-hang'){
            $product = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $data[1], 'branchId' => $branch_id));
            $xhtml = '<tr class="product_gif">
                            <td width="100px" class="text-bold text-red text-middle">'.$product['code'].'</td>
                            <td width="100px" class="text-middle">'.$product['fullName'].$text.'</td>
                            <td class="product_name"><input class="form-control" type="text" name="contract_product[product_name][]" value=""></td>
                            <td class="numbers text-right text-middle" style="padding:0px 17px;" > 1 <input class="form-control  hidden" type="text" name="contract_product[numbers][]" value="1" min="1"></td>
                            <td class="price"><input class="form-control money text-right" type="text" name="contract_product[price][]" value="0" min="0"></td>
                            <td class="total"><input class="form-control text-right" type="text" name="contract_product[total][]" value="0" readonly></td>
                            <td class="hidden">
                                <input class="form-control" type="text" name="contract_product[is_gif][]" value="1">
                                <input class="form-control" type="text" name="contract_product[product_return_id][]" value="">
                                <input class="form-control" type="text" name="contract_product[product_id][]" value="'.$product['productId'].'">
                                <input class="form-control" type="text" name="contract_product[full_name][]" value="'.$product['fullName'].$text.'">
                                <input class="form-control" type="text" name="contract_product[code][]" value="'.$product['code'].'">
                                <input class="form-control" type="text" name="contract_product[branch_id][]" value="'.$product['branchId'].'">
                                <input class="form-control" type="text" name="contract_product[listed_price][]" value="'.$product['basePrice'].'"> 
                                <input class="form-control" type="text" name="contract_product[cost][]" value="'.$product['cost'].'"> 
                                <input class="form-control" type="text" name="contract_product[cost_new][]" value="'.$product['cost_new'].'"> 
                                <input class="form-control" type="text" name="contract_product[fee][]" value="'.$product['fee'].'"> 
                                <input class="form-control" type="text" name="contract_product[capital_default][]" value="'.($product['cost'] + ($product['cost'] * $product['cost_new'] / 100) + $product['fee']).'"> 
                            </td>
                            <td><i class="fa fa-trash delete-row" aria-hidden="true" style="color:red; margin: 5px 0 0 5px; font-size: 20px;"></i></td>
                        </tr>';

            $res = array(
                'type'     => 'tang-hang',
                'value'    => $xhtml,
            );
            echo json_encode(array('success' => true, 'data'=>$res));
        }
        if($discouts['discounts_option'] == 'giam-gia-hang'){

            $product = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $data[2], 'branchId' => $branch_id));
            $discount_detail = $detail[$data[1]];
            if($discount_detail['unit_type'] == 1){
                $price = $product['basePrice'] - $number->formatToData($discount_detail['discount_value']);
                $price = $price > 0 ? $price : 0;
                $text = '(Giảm giá '.$discount_detail['discount_value'].' )';
            }
            if($discount_detail['unit_type'] == 2){
                $price = $product['basePrice'] - ($number->formatToData($discount_detail['discount_value']) * $product['basePrice'] / 100);
                $price = $price > 0 ? $price : 0;
                $text = '(Giảm giá '.$discount_detail['discount_value'].' %)';
            }

            $xhtml = '<tr class="product_gif">
                            <td width="100px" class="text-bold text-red text-middle">'.$product['code'].'</td>
                            <td width="100px" class="text-middle">'.$product['fullName'].$text.'</td>
                            <td class="product_name"><input class="form-control" type="text" name="contract_product[product_name][]" value=""></td>
                            <td class="numbers text-right text-middle" style="padding:0px 17px;" > 1 <input class="form-control  hidden" type="text" name="contract_product[numbers][]" value="1" min="1"></td>
                            <td class="price"><input class="form-control money text-right" type="text" name="contract_product[price][]" value="'.number_format($price).'" min="0"></td>
                            <td class="total"><input class="form-control text-right" type="text" name="contract_product[total][]" value="'.number_format($price).'" readonly></td>
                            <td class="hidden">
                                <input class="form-control" type="text" name="contract_product[is_gif][]" value="1">
                                <input class="form-control" type="text" name="contract_product[product_return_id][]" value="">
                                <input class="form-control" type="text" name="contract_product[product_id][]" value="'.$product['productId'].'">
                                <input class="form-control" type="text" name="contract_product[full_name][]" value="'.$product['fullName'].$text.'">
                                <input class="form-control" type="text" name="contract_product[code][]" value="'.$product['code'].'">
                                <input class="form-control" type="text" name="contract_product[branch_id][]" value="'.$product['branchId'].'">
                                <input class="form-control" type="text" name="contract_product[listed_price][]" value="'.$product['basePrice'].'"> 
                                <input class="form-control" type="text" name="contract_product[cost][]" value="'.$product['cost'].'"> 
                                <input class="form-control" type="text" name="contract_product[cost_new][]" value="'.$product['cost_new'].'"> 
                                <input class="form-control" type="text" name="contract_product[fee][]" value="'.$product['fee'].'"> 
                                <input class="form-control" type="text" name="contract_product[capital_default][]" value="'.($product['cost'] + ($product['cost'] * $product['cost_new'] / 100) + $product['fee']).'"> 
                            </td>
                            <td><i class="fa fa-trash delete-row" aria-hidden="true" style="color:red; margin: 5px 0 0 5px; font-size: 20px;"></i></td>
                        </tr>';
            $res = array(
                'type'          => 'giam-gia-hang',
                'value'    => $xhtml,
            );
            echo json_encode(array('success' => true, 'data'=>$res));
        }
        exit;
    }

    // Lấy danh sách user theo cơ sở
    public function userBranchAction() {
        if(empty($this->_params['data']['sale_branch_id'])) {
            return $this->response;
        }
        else{
            $admin_id = $this->_userInfo->getUserInfo('id');
            if ($admin_id == '2222222222222222222222' || $admin_id == '1111111111111111111111') {
                $items = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem($this->_params, array('task' => 'list-sale'));
            }
        }

        $this->_viewModel['items'] = $items;
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function getContactAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $adapter        = $this->getServiceLocator()->get('dbConfig');
            $tableGateway   = new TableGateway(TABLE_CONTACT, $adapter, null);
            $table          = new \Admin\Model\ApiTable($tableGateway);

            $result         = $table->getItem($this->_params['data']);;
            if(!empty($result)) {
                $date = new \ZendX\Functions\Date();

                // Danh sách bảng dữ liệu tham số
                $user = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
                $sale_group = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
                $sale_branch = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));

                $result['date']                = $date->formatToView($result['date'], 'd/m/Y H:i:s');
                $result['created']             = $date->formatToView($result['created'], 'd/m/Y H:i:s');
                $result['store']               = !empty($result['store']) ? $date->formatToView($result['store'], 'd/m/Y H:i:s') : null;
                $result['history_created']     = !empty($result['history_created']) ? $date->formatToView($result['history_created'], 'd/m/Y H:i:s') : null;
                $result['history_return']      = !empty($result['history_return']) ? $date->formatToView($result['history_return'], 'd/m/Y') : null;
                $result['birthday']            = !empty($result['birthday']) ? $date->formatToView($result['birthday'], 'd/m/Y') : null;
                $result['birthday_year']       = $result['birthday_year'];
                $result['user_name']           = $user[$result['user_id']]['name'];
                $result['sale_group_name']     = $sale_group[$result['sale_group_id']]['name'];
                $result['sale_branch_name']    = $sale_branch[$result['sale_branch_id']]['name'];
                $result['options']             = !empty($result['options']) ? unserialize($result['options']) : null;

                if(!empty($result['options'])) {
                    foreach ($result['options'] AS $key => $val) {
                        $result[$key] = $val;
                    }
                }

                $result['name_received'] = $result['options']['contact_received']['name'];
                $result['phone_received'] = $result['options']['contact_received']['phone'];
                $result['address_received'] = $result['options']['contact_received']['address'];

                $result['product_name'] = $result['options']['note'];

                $result['my-manager'] = 0;
                if($this->_userInfo->getUserInfo('id') == $result['user_id']) {
                    $result['my-manager'] = 1;
                }
                echo Json::encode($result);
            } else {
                echo 'not-found';
            }
        } else {
            $this->goRoute();
        }

        return $this->response;
    }

    public function listContractAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $items = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem($this->_params, array('task' => 'list-ajax'));

            $this->_viewModel['items']          = $items;
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['product']        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['edu_class']      = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache-basic'));
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listContractByPhoneAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $items = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem($this->_params, array('task' => 'list-ajax'));

            $this->_viewModel['items']          = $items;
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['product']        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['edu_class']      = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache-basic'));

            $this->_viewModel['status_product']         = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'production-department')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
            $this->_viewModel['status_check']           = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-check')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
            $this->_viewModel['status_accounting']      = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'status-acounting')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));

            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listProductAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $items = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('id' => $this->_params['data']['contract_id']), null);
            $this->_viewModel['items']          = $items;
            $this->_viewModel['product']        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['kovProduct']     = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['unit']           = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'unit')), array('task' => 'cache'));
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listBillAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $items = $this->getServiceLocator()->get('Admin\Model\BillTable')->listItem($this->_params, array('task' => 'list-ajax'));

            $this->_viewModel['items']              = $items;
            $this->_viewModel['type']               = array('paid' => 'Thu', 'accrued' => 'Chi', 'surcharge' => 'Phụ phí');
            $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['bill_type']          = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-bill-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));

            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listHistoryAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $items = $this->getServiceLocator()->get('Admin\Model\HistoryTable')->listItem(array('data' => array('contact_id' => $this->_params['data']['contact_id'])), array('task' => 'list-ajax'));

            $this->_viewModel['items']    	            = $items;
            $this->_viewModel['user']                   = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']             = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['sale_history_action']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-action')), array('task' => 'cache'));
            $this->_viewModel['sale_history_result']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-result')), array('task' => 'cache'));
            $this->_viewModel['sale_history_type']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-history-type')), array('task' => 'cache'));

            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
            return $viewModel;
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listLogsAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            // Thiết lập lại thông số phân trang
            $this->_paginator['itemCountPerPage']   = 20;
            $this->_paginator['currentPageNumber']  = $this->_params['data']['page'] ? $this->_params['data']['page'] : 1;
            $this->_params['paginator'] = $this->_paginator;

            $items = $this->getServiceLocator()->get('Admin\Model\LogsTable')->listItem($this->_params, array('task' => 'list-ajax'));
            $count = $this->getServiceLocator()->get('Admin\Model\LogsTable')->countItem($this->_params, array('task' => 'list-ajax'));

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['items']          = $items;
            $this->_viewModel['count']          = $count;
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['document']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(null, array('task' => 'cache'));
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listLogsParentAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            // Thiết lập lại thông số phân trang
            $this->_paginator['itemCountPerPage']   = 20;
            $this->_paginator['currentPageNumber']  = $this->_params['data']['page'] ? $this->_params['data']['page'] : 1;
            $this->_params['paginator'] = $this->_paginator;

            $items = $this->getServiceLocator()->get('Admin\Model\LogsTable')->listItem($this->_params, array('task' => 'list-ajax-parent'));
            $count = $this->getServiceLocator()->get('Admin\Model\LogsTable')->countItem($this->_params, array('task' => 'list-ajax-parent'));

            $this->_viewModel['params']         = $this->_params;
            $this->_viewModel['items']          = $items;
            $this->_viewModel['count']          = $count;
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['product']        = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['edu_class']      = $this->getServiceLocator()->get('Admin\Model\EduClassTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['document']       = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(null, array('task' => 'cache'));
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listMatterAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $items = $this->getServiceLocator()->get('Admin\Model\MatterTable')->listItem($this->_params, array('task' => 'list-ajax'));

            $this->_viewModel['items']          = $items;
            $this->_viewModel['matter']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'matter')), array('task' => 'cache'));
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['company_branch'] = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'company-branch')), array('task' => 'cache'));
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listBcAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $items = $this->getServiceLocator()->get('Admin\Model\BcTable')->listItem($this->_params, array('task' => 'list-ajax'));

            $this->_viewModel['items']          = $items;
            $this->_viewModel['user']           = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']     = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']    = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function listBcBillAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $items = $this->getServiceLocator()->get('Admin\Model\BcBillTable')->listItem($this->_params, array('task' => 'list-ajax'));

            $this->_viewModel['items']              = $items;
            $this->_viewModel['user']               = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'cache'));
            $this->_viewModel['sale_group']         = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'lists-group')), array('task' => 'cache'));
            $this->_viewModel['sale_branch']        = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
            $this->_viewModel['bill_type']          = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-bill-type')), array('task' => 'cache')), array('key' => 'alias', 'value' => 'object'));
            $viewModel = new ViewModel($this->_viewModel);
            $viewModel->setTerminal(true);
        } else {
            return $this->redirect()->toRoute('routeAdmin/type', array('controller' => 'notice', 'action' => 'not-found'));
        }
        return $viewModel;
    }

    public function countAction() {
        $adapter        = $this->getServiceLocator()->get('dbConfig');
        $tableGateway   = new TableGateway($this->_params['data']['data-table'], $adapter, null);
        $table          = new \Admin\Model\ApiTable($tableGateway);

        $task 			= $this->_params['data']['task'] ? array('task' => $this->_params['data']['task']) : null;
        $results        = $table->countItem($this->_params, $task);

        echo Json::encode($results);
        return $this->response;
    }

    public function updateStoreAction() {
        $tableContact   = new \Admin\Model\ApiTable(new TableGateway(TABLE_CONTACT, $this->getServiceLocator()->get('dbConfig'), null));
        $updateStore    = $tableContact->updateStore(array('day_in_store' => $this->_settings['General.Contact.DayInStore']['value']), null);

        return $this->response;
    }

    public function contactHistoryReturnAction() {
        $tableContact = new \Admin\Model\ApiTable(new TableGateway(TABLE_CONTACT, $this->getServiceLocator()->get('dbConfig'), null));
        echo $tableContact->countItem(null, array('task' => 'contact-history-return'));

        return $this->response;
    }

    public function contractNotifiFalseAction() { // Thông báo đơn hàng bán sai
        $items = $this->getServiceLocator()->get('Admin\Model\NotifiUserTable')->listItem(null, array('task' => 'list-item-unread'))->toArray();
        $count = $this->getServiceLocator()->get('Admin\Model\NotifiUserTable')->countItem(null, array('task' => 'list-item-unread'));

        $this->_viewModel['data']          = $this->_params['data'];
        $this->_viewModel['items']         = $items;
        $this->_viewModel['count']         = $count;
        $this->_viewModel['curent_id']     = $this->_userInfo->getUserInfo('id');

        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function contactHistoryStatusAction() {
        $tableContact = new \Admin\Model\ApiTable(new TableGateway(TABLE_CONTACT, $this->getServiceLocator()->get('dbConfig'), null));
        echo $tableContact->countItem(null, array('task' => 'contact-history-status'));

        return $this->response;
    }

    public function pendingAction() {
//        $tableContact = new \Admin\Model\ApiTable(new TableGateway(TABLE_CONTRACT, $this->getServiceLocator()->get('dbConfig'), null));
//        echo $tableContact->countItem(null, array('task' => 'pending'));

        return $this->response;
    }

    public function userGroupAction() {
        $items = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem($this->_params, array('task' => 'list-sale'));
        $this->_viewModel['items'] = $items;
        $viewModel = new ViewModel($this->_viewModel);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    // Tạo zalo token từ code
    public function createTokenZaloAction() {
        $response = new Response();
        $app_id = $this->getServiceLocator()->get('Admin\Model\SettingTable')->getItem(array('code' => 'General.zalo.app_id'), array('task' => 'code'));
        $data = array(
            'app_id'        => $app_id['value'],
            'code'          => $this->getRequest()->getQuery('code'),
            'grant_type'    => 'authorization_code',
        );
        $result = json_decode($this->zalo_update_token($data), true);
        if(isset($result['access_token'])){
            $description = date('Y-m-d H:i:s', time() + $result['expires_in']);
            $access_token_update = array(
                'code'  => 'General.zalo.access_token',
                'value' => $result['access_token'],
                'description' => 'Có thời gian sử dụng tới: '.$description
            );
            $refresh_token_update = array(
                'code'  => 'General.zalo.refresh_token',
                'value' => $result['refresh_token']
            );
            $access_token_code  = $this->getServiceLocator()->get('Admin\Model\SettingTable')->saveItem(array('data' => $access_token_update), array('task' => 'update-by-code'));
            $refresh_token_code = $this->getServiceLocator()->get('Admin\Model\SettingTable')->saveItem(array('data' => $refresh_token_update), array('task' => 'update-by-code'));
            $response->setStatusCode(Response::STATUS_CODE_200);
            $response->setContent(json_encode(array('success' => true, 'message' => 'update token success', 'data' => array('access_token_code' => $access_token_code, 'refresh_token_code' => $refresh_token_code))));
        }
        else{
            $response->setStatusCode(Response::STATUS_CODE_200);
            $response->setContent(json_encode($result));
        }
        header('Content-Type: application/json');
        return $response;
    }

    // Cập nhật zalo token từ refresh token
    public function updateTokenZaloAction() {
        $response = new Response();
        $app_id = $this->getServiceLocator()->get('Admin\Model\SettingTable')->getItem(array('code' => 'General.zalo.app_id'), array('task' => 'code'));
        $refresh_token = $this->getServiceLocator()->get('Admin\Model\SettingTable')->getItem(array('code' => 'General.zalo.refresh_token'), array('task' => 'code'));
        $data = array(
            'app_id' => $app_id['value'],
            'refresh_token' => $refresh_token['value'],
            'grant_type' => 'refresh_token'
        );
        $result = json_decode($this->zalo_update_token($data), true);
        if(isset($result['access_token'])){
            $description = date('Y-m-d H:i:s', time() + $result['expires_in']);
            $access_token_update = array(
                'code'  => 'General.zalo.access_token',
                'value' => $result['access_token'],
                'description' => 'Có thời gian sử dụng tới: '.$description
            );
            $refresh_token_update = array(
                'code'  => 'General.zalo.refresh_token',
                'value' => $result['refresh_token']
            );
            $access_token_code  = $this->getServiceLocator()->get('Admin\Model\SettingTable')->saveItem(array('data' => $access_token_update), array('task' => 'update-by-code'));
            $refresh_token_code = $this->getServiceLocator()->get('Admin\Model\SettingTable')->saveItem(array('data' => $refresh_token_update), array('task' => 'update-by-code'));
            $response->setStatusCode(Response::STATUS_CODE_200);
            $response->setContent(json_encode(array('success' => true, 'message' => 'update token success', 'data' => array('access_token_code' => $access_token_code, 'refresh_token_code' => $refresh_token_code))));
        }
        else{
            $response->setStatusCode(Response::STATUS_CODE_200);
            $response->setContent(json_encode($result));
        }
        header('Content-Type: application/json');
        return $response;
    }

    // Tự động gửi tin nhắn zalo chăm sóc khách hàng
    public function guiTinNhanZaloChamSocAction() {
        $response = new Response();
        $sale_time = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-time')), array('task' => 'list-all')),array('key' => 'alias', 'value' => 'content'));
        
        if(isset($sale_time['zalo-time'])){
            $timestamp = strtotime("-{$sale_time['zalo-time']} days");
            $output_date = date('Y-m-d', $timestamp);
            $where_contract = array(
                'filter_date_begin'         => $output_date,
                'filter_date_end'           => $output_date,
                'filter_date_type'          => "date_success",
            );
            $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ssFilter' => $where_contract), array('task' => 'list-item', 'paginator' => false));
            $numberFormat = new \ZendX\Functions\Number();
            $result = [];
            $number = [];
            foreach($contracts as $contract_item){
                if($contract_item['send_zalo_notifi_care'] == 0){
                    $res = $this->zalo_send_notify(ZALO_NOTIFY_CONFIG_CHAMSOC, $numberFormat->convertToInternational($contract_item['phone']), $contract_item);
                    $res = json_decode($res, true);
                    if($res['message'] == "Success"){
                        $number[] = $contract_item['code'];
                        $data_update = array(
                            'id' => $contract_item['id'],
                            'send_zalo_notifi_care' => 1,
                        );
                        $result = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('item'=> $contract_item, 'data' => $data_update), array('task' => 'update-item'));
                    }
                    else{
                        $result[$contract_item['code']] = $res;
                    }
                }
            }

            $response->setStatusCode(Response::STATUS_CODE_200);
            $response->setContent(json_encode(array('success' => true, 'message' => 'send notify success', 'data_success' => $number, 'data_error' => $result)));
        }

        header('Content-Type: application/json');
        return $response;
    }

    // Tự động gửi tin nhắn zalo bị lỗi trong khung giờ 22h-6h
    public function guiTinNhanZalo2206Action() {
        $response = new Response();
        $condition = array(
            'filter_result_error'          => "-133",
        );
        $items = $this->getServiceLocator()->get('Admin\Model\ZaloNotifyResultTable')->listItem(array('ssFilter' => $condition), array('task' => 'list-item', 'paginator' => false));
        foreach($items as $item){
            if($item['result_error'] == '-133'){
                $data_send['phone']         = $item['phone'];
                $data_send['template_id']   = $item['template_id'];
                $data_send['template_data'] = unserialize($item['template_data']);

                $res = json_decode($this->zalo_call('/message/template', $data_send, 'POST'), true);
                if($res['error'] == 0){
                    $cid_update[] = $data_send['phone'];
                }
                $this->getServiceLocator()->get('Admin\Model\ZaloNotifyResultTable')->saveItem(array('item' => $item, 'data' => $data_send, 'res' => $res), array('task' => 'update-item'));
            }
        }

        $response->setStatusCode(Response::STATUS_CODE_200);
        $response->setContent(json_encode(array('success' => true, 'message' => 'send notify success', 'data_success' => $cid_update)));
        header('Content-Type: application/json');
        return $response;
    }

    public function updateTokenViettelAction() {
        $viettel_key = $this->_params['data']['viettel_key'];
        return $this->updateToken($viettel_key);
    }

    // Hàm để log thông tin request
    function logRequest($data) {
        // Lấy thông tin về request
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $timestamp = date('Y-m-d H:i:s');

        // Tạo nội dung log
        $logContent = "$timestamp - IP: $ipAddress - Method: $requestMethod - URI: $requestUri - data: ".json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Tạo một instance của Zend\Log\Logger
        $logger = new Logger();

        // Thêm một writer để ghi log vào file
        $logFilePath = PATH_APPLICATION . '/public/log/file.log';
        $writer = new Stream($logFilePath);
        $logger->addWriter($writer);

        // Ghi nội dung vào file log
        $logger->info($logContent);
    }

    function updateWebhookStatus($arrParam, $contract_item){
        // Cập nhật trạng thái cho đơn hàng
        $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(array('data' => $arrParam, 'item' => $contract_item),  array('task' => 'update-webhook-status'));
        $this->check_send_zalo_notify($arrParam, $contract_item);
    }

    // webhook cập nhật trạng thái từ ghtk đẩy về crm
    public function updateShipmentAction(){
        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_500);
        try {
            if($this->getRequest()->isPost()){
                $hass = $this->getRequest()->getQuery('hash');
                if($hass == HASS){
//                    $this->postJson(file_get_contents('php://input'));
                    $data = json_decode(file_get_contents('php://input'), true);
                    $code = $data['partner_id'];
                    $ghtk_code = $data['label_id'];

                    $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('ghtk_code' => $ghtk_code),  array('task' => 'ghtk-code'));
                    if(empty($contract_item)){
                        $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $code),  array('task' => 'by-code'));
                    }
                    if(!empty($contract_item)){
                        // Tạo hóa đơn kov trừ số lượng hàng trong kho
                        if($data['status_id'] == 3){ // trạng thái Đã lấy hàng/Đã nhập kho trên ghtk
                            $this->updateNumberKiotviet($contract_item);
                        }
                        if(($data['status_id'] == 5 || $data['status_id'] == 6) && empty($contract_item['date_success'])) {
                            $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('data' => array('id' => $contract_item['id'])), array('task' => 'update-contract-succes'));
                        }

                        $arrParam['id']             = $contract_item['id'];
                        $arrParam['ghtk_status']    = $data['status_id'];
                        $arrParam['ghtk_code']      = $data['label_id'];
                        $arrParam['price_transport']= $data['fee'];
                        $arrParam['status_history'] = $data;
                        $this->updateWebhookStatus($arrParam, $contract_item);
//                        $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(array('data' => $arrParam, 'item' => $contract_item),  array('task' => 'update-webhook-status'));

                        $response->setStatusCode(Response::STATUS_CODE_200);
                        $response->setContent(json_encode(array('success' => true, 'message' => 'update status success')));
                    }
                    else{
                        $response->setStatusCode(Response::STATUS_CODE_200);
                        $response->setContent(json_encode(array('success' => false, 'message' => 'partner_id invalid')));
                    }
                }
                else{
                    $response->setStatusCode(Response::STATUS_CODE_200);
                    $response->setContent(json_encode(array('success' => false, 'message' => 'hash invalid')));
                }
            }
            else{
                $response->setStatusCode(Response::STATUS_CODE_401);
                $response->setContent(json_encode(array('success' => false, 'message' => 'method invalid')));
            }
        } catch (Exception $e) {
            $response->setStatusCode(Response::STATUS_CODE_500);
            $response->setContent(json_encode(array('success' => false, 'message' => 'invalid')));
        }
        header('Content-Type: application/json');
        return $response;
    }

    // webhook cập nhật trạng thái từ giao hành nhanh đẩy về crm
    public function updateOrderStatusGHNAction(){
        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_500);
        try {
            if($this->getRequest()->isPost()){
                $hass = $this->getRequest()->getQuery('hash');
                if($hass == HASS){
//                    $this->postJson(file_get_contents('php://input'));
                    $data = json_decode(file_get_contents('php://input'), true);
                    $code = $data['ClientOrderCode'];
                    $ghtk_code = $data['OrderCode'];

                    $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('ghtk_code' => $ghtk_code),  array('task' => 'ghtk-code'));
                    if(empty($contract_item)){
                        $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $code),  array('task' => 'by-code'));
                    }
                    if(!empty($contract_item)){
                        // Tạo hóa đơn kov trừ số lượng hàng trong kho
                        if($data['Status'] == 'picked'){ // trạng thái Đã lấy hàng/Đã nhập kho trên ghtk
                            $this->updateNumberKiotviet($contract_item);
                        }
                        if($data['Status'] == 'delivered' && empty($contract_item['date_success'])) {
                            $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('data' => array('id' => $contract_item['id'])), array('task' => 'update-contract-succes'));
                        }

                        $arrParam['id']             = $contract_item['id'];
                        $arrParam['ghtk_status']    = $data['Status'];
                        $arrParam['price_transport']= $data['TotalFee'];
                        $arrParam['status_history'] = $data;
                        $this->updateWebhookStatus($arrParam, $contract_item);
//                        $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(array('data' => $arrParam, 'item' => $contract_item),  array('task' => 'update-webhook-status'));

                        $response->setStatusCode(Response::STATUS_CODE_200);
                        $response->setContent(json_encode(array('success' => true, 'message' => 'update status success')));
                    }
                    else{
                        $response->setStatusCode(Response::STATUS_CODE_200);
                        $response->setContent(json_encode(array('success' => false, 'message' => 'OrderCode invalid')));
                    }
                }
                else{
                    $response->setStatusCode(Response::STATUS_CODE_200);
                    $response->setContent(json_encode(array('success' => false, 'message' => 'hash invalid')));
                }
            }
            else{
                $response->setStatusCode(Response::STATUS_CODE_401);
                $response->setContent(json_encode(array('success' => false, 'message' => 'method invalid')));
            }
        } catch (Exception $e) {
            $response->setStatusCode(Response::STATUS_CODE_500);
            $response->setContent(json_encode(array('success' => false, 'message' => 'invalid')));
        }
        header('Content-Type: application/json');
        return $response;
    }

    // webhook cập nhật trạng thái từ viettel post đẩy về crm
    public function updateOrderStatusViettelPostAction(){
        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_500);
        try {
            if($this->getRequest()->isPost()){
                $data_post = json_decode(file_get_contents('php://input'), true);
                $token = $data_post['TOKEN'];
                if($token == TOKENUPDATE){
                    $data = $data_post['DATA'];
//                    $this->postJson(file_get_contents('php://input'));
                    $code = $data['ORDER_REFERENCE'];
                    $ghtk_code = $data['ORDER_NUMBER'];

                    $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('ghtk_code' => $ghtk_code),  array('task' => 'ghtk-code'));
                    if(empty($contract_item)){
                        $contract_item = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $code),  array('task' => 'by-code'));
                    }
                    if(!empty($contract_item)){
                        // Tạo hóa đơn kov trừ số lượng hàng trong kho
//                        if($data['ORDER_STATUS'] == 105 || $data['ORDER_STATUS'] == 103){ // trạng thái Đã lấy hàng/Đã nhập kho trên viettel post
                        if($data['ORDER_STATUS'] == 105 || $data['ORDER_STATUS'] == 200){ // trạng thái Đã lấy hàng/Đã nhập kho trên viettel post
                            $this->updateNumberKiotviet($contract_item);
                        }
                        if($data['ORDER_STATUS'] == 501 && empty($contract_item['date_success'])) {
                            $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('data' => array('id' => $contract_item['id'])), array('task' => 'update-contract-succes'));
                        }

                        $arrParam['id']             = $contract_item['id'];
                        $arrParam['ghtk_status']    = $data['ORDER_STATUS'];
                        $arrParam['ghtk_code']      = $data['ORDER_NUMBER'];
                        $arrParam['price_transport']= $data['MONEY_TOTAL'];
                        $arrParam['status_history'] = $data;
                        $this->updateWebhookStatus($arrParam, $contract_item);
//                        $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(array('data' => $arrParam, 'item' => $contract_item),  array('task' => 'update-webhook-status'));

                        $response->setStatusCode(Response::STATUS_CODE_200);
                        $response->setContent(json_encode(array('status' => '200', 'success' => true, 'message' => 'update status success')));
                    }
                    else{
//                        $response->setStatusCode(Response::STATUS_CODE_404);
//                        $response->setContent(json_encode(array('status' => '404', 'success' => false, 'message' => 'Order reference invalid')));
                        # trả về status 200 ghi log và bypass
                        $this->logRequest(file_get_contents('php://input'));
                        $response->setStatusCode(Response::STATUS_CODE_200);
                        $response->setContent(json_encode(array('status' => '200', 'success' => true, 'message' => 'Order reference invalid - bypass')));
                    }
                }
                else{
//                    $response->setStatusCode(Response::STATUS_CODE_404);
//                    $response->setContent(json_encode(array('status' => '404', 'success' => false, 'message' => 'Token invalid')));
                    $this->logRequest(file_get_contents('php://input'));
                    $response->setStatusCode(Response::STATUS_CODE_200);
                    $response->setContent(json_encode(array('status' => '200', 'success' => true, 'message' => 'Token invalid - bypass')));
                }
            }
            else{
                $response->setStatusCode(Response::STATUS_CODE_401);
                $response->setContent(json_encode(array('status' => '401', 'success' => false, 'message' => 'method invalid')));
            }
        } catch (Exception $e) {
            $response->setStatusCode(Response::STATUS_CODE_500);
            $response->setContent(json_encode(array('status' => '500', 'success' => false, 'message' => 'invalid')));
        }

        header('Content-Type: application/json');
        return $response;
    }

    // Thêm data từ langding page.
    public function addAction() {
        if($this->getRequest()->isPost()) {
            $this->_params['data']['name']       = $_POST['name'];
            $this->_params['data']['phone']      = $_POST['phone'];
            $this->_params['data']['message']    = $_POST['message'];
            $this->_params['data']['product_group_id']    = $_POST['product_group_id'];

            $form = $this->getServiceLocator()->get('Admin\Model\LinkCheckingTable')->getItem(array('link' => $_POST['link']), array('task' => 'by-link'));
            if(!empty($form)){
                $contact = $this->getServiceLocator()->get('Admin\Model\ContactTable')->getItem(array('phone' => $this->_params['data']['phone']), array('task' => 'by-phone'));
                // tồn tại data trong kho
                $item_coin_phone = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->getItem(array('phone' => $this->_params['data']['phone']), array('task' => 'by-phone'));
                // data marketer đó đã từng nhập
                $item_coin_phone_mkt = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->getItem(array('phone' => $this->_params['data']['phone'], 'marketer_id' => $form['marketer_id']), array('task' => 'by-condition'));
                // check data này có trùng với data của marketer khác và cùng ngày không
                $param_date = date('Y-m-d');
                $item_coin_other = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->countItem(array('phone' => $this->_params['data']['phone'], 'marketer_id' => $form['marketer_id'], 'date' => $param_date), array('task' => 'list-data-coin'));

                $task = "no action";

                // Trùng liên hệ tạo thông báo
                if(!empty($contact)){
                    $condition = array(
                        'phone' => $contact['phone'],
                    );
                    // Lấy thông tin người quản lý liên hệ hiện tại.
                    $manager_user = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $contact['user_id']));
                    $content = '';
                    $user_ids = "";
                    if($manager_user['status'] == 1){
                        $content = 'Khách hàng '.$contact['name'].'('.$contact['phone'].') đăng kí lại cần chăm sóc ngay';
                        $user_ids = $contact['user_id'];
                    }
                    else{
                        $content = 'Khách hàng '.$contact['name'].'('.$contact['phone'].') đăng kí lại nhưng nhân viên ('.$manager_user['name'].') đã nghỉ cần chuyển cho sale khác chăm sóc';
                        $list_sale_admin = $this->getServiceLocator()->get('Admin\Model\UserTable')->listItem(null, array('task' => 'list-sale-admin'));
                        if(!empty($list_sale_admin)){
                            foreach ($list_sale_admin as $index => $u_item) {
                                $user_ids .= $u_item['id'].',';
                            }
                        }
                        else{
                            $user_ids = '2222222222222222222222';
                        }
                    }
                    $arrNotify['data'] = array(
                        'content'   => $content,
                        'user_ids'  => $user_ids,
                        'type'      => 'phone',
                        'link'      => '/xadmin/contact/index/',
                        'options'   => serialize($condition),
                    );
                    $this->getServiceLocator()->get('Notifycation\Model\NotifyTable')->saveItem($arrNotify, array('task' => 'add-item'));

                    // Cập nhật liên hệ trùng
//                    $arr_data = array(
//                        'id'                => $contact['id'],
//                        'marketer_id'       => $form['marketer_id'],
//                        'product_group_id'  => $form['product_group_id'],
//                    );
//                    $this->getServiceLocator()->get('Admin\Model\ContactTable')->saveItem($arr_data, array('task' => 'update-new-data'));

                    // Thêm mới data trùng
                    $this->_params['data']['marketer_id']          = $form['marketer_id'];
                    $this->_params['data']['marketing_channel_id'] = $form['marketing_channel_id'];
//                    $this->_params['data']['product_group_id']     = $form['product_group_id'];
                    $this->_params['data']['contact_coin']         = 1;
                    $this->_params['data']['contact_id']           = $contact['id'];
                    $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($this->_params, array('task' => 'add-data-landing'));

                    $task = "Trùng liên hệ - Thêm data trùng";
//                    $task = "Trùng liên hệ - thông báo tới user";
                }
                else {
                    if ($item_coin_phone) {
//                        if (!empty($item_coin_phone_mkt)) {
//                            $task = "Trùng data của chính mình";
//                        } else if ($item_coin_other > 0) {
//                            $task = "Trùng data với marketer khác trong ngày";
//                        } else {
                            $this->_params['data']['marketer_id']          = $form['marketer_id'];
                            $this->_params['data']['marketing_channel_id'] = $form['marketing_channel_id'];
//                            $this->_params['data']['product_group_id']     = $form['product_group_id'];
                            $this->_params['data']['contact_coin']         = 1;
                            $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($this->_params, array('task' => 'add-data-landing'));
                            $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem(array('contact_coin' => 1, 'phone' => $this->_params['data']['phone']), array('task' => 'update-contact-coin'));

                            $task = "Tạo data trùng";
//                        }
                    } else {
                        $this->_params['data']['marketer_id']          = $form['marketer_id'];
                        $this->_params['data']['marketing_channel_id'] = $form['marketing_channel_id'];
//                        $this->_params['data']['product_group_id']     = $form['product_group_id'];

                        $user                                    = $this->getServiceLocator()->get('Admin\Model\UserTable')->getItem(array('id' => $form['marketer_id']));
                        $this->_params['data']['sale_branch_id'] = $user['sale_branch_id'];
                        $this->_params['data']['sale_group_id']  = $user['sale_group_id'];

                        $this->_params['data']['source']  = 'landing_page';
                        $this->_params['data']['form_id'] = $form['id']; // lưu link tracking id

                        $this->getServiceLocator()->get('Admin\Model\FormDataTable')->saveItem($this->_params, array('task' => 'add-data-landing'));

                        $task = "Tạo data thành công";
                    }
                }
            }
            echo $task;

            // echo json_encode($result);
        }
        return $this->response;
    }

    // webhook cập nhật sản phẩm
    public function updateKovProductionsAction(){

//        $post_data_test = '{"Id":"d42dcce1-3919-4303-b829-d473a163845f","Attempt":1,"Notifications":[{"Action":"product.update.500400541","Data":[{"__type":"KiotViet.OmniChannelCore.Api.Shared.Model.WebhookProductUpdateRes, KiotViet.OmniChannelCore.Api.Shared","Id":26254255,"RetailerId":500400541,"Code":"SP000150","Name":"Sản phẩm test không bán","FullName":"Sản phẩm test không bán","CategoryId":438730,"CategoryName":"Thảm Sàn Nhựa Đúc Ôtô","AllowsSale":true,"Type":2,"HasVariants":false,"BasePrice":1500000,"ConversionValue":1,"Description":"","ModifiedDate":"2024-03-04T23:39:24.1470000+07:00","isActive":true,"IsRewardPoint":false,"OrderTemplate":"","TradeMarkId":166913,"TradeMarkName":"Ford","Attributes":[],"Units":[],"Inventories":[{"ProductId":26254255,"ProductCode":"SP000150","ProductName":"Sản phẩm test không bán","BranchId":45341,"BranchName":"CÔNG TY NỘI THẤT Ô TÔ FOREWIN","Cost":800000,"OnHand":15,"Reserved":1,"MinQuantity":0,"MaxQuantity":999999999,"isActive":true}],"PriceBooks":[],"Serials":[],"Images":[]}]}]}';
//        $data_post =  json_decode($post_data_test, true);

//        $this->postJson(file_get_contents('php://input'));// Đẩy dữ liệu sang webhook.site để kiểm tra
        $data_post =  json_decode(file_get_contents('php://input'), true);

        $notifications = $data_post['Notifications'];
        foreach($notifications as $notifi){
            foreach($notifi['Data'] as $item_get){
                // Cập nhật sản phẩm.
                $item_product = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->getItem(array('id' => $item_get['Id']));
                $data = array(
                    'id'                => $item_get['Id'],
                    'code'              => $item_get['Code'],
                    'name'              => $item_get['Name'],
                    'fullName'          => $item_get['FullName'],
                    'categoryId'        => $item_get['CategoryId'],
                    'basePrice'         => $item_get['BasePrice'],
                    'images'            => serialize($item_get['Images']),
                );
                if($item_product){
                    $pid = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->saveItem(array('data' => $data), array('task' => 'update'));
                }
                else {
                    $pid = $this->getServiceLocator()->get('Admin\Model\KovProductsTable')->saveItem(array('data' => $data), array('task' => 'add'));
                }
                if($pid){
                    echo 'Cập nhật sản phẩm thành công! ';
                }
                $sale_branchs = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'list-all'));

                // Cập nhật kho hàng.
                if(isset($item_get['Inventories'])){
                    foreach($sale_branchs as $key => $banch){
                        $data_inven = array(
                            'branchId'          => $banch->id,
                            'productId'         => $item_get['Inventories'][0]['ProductId'],
                            'branchName'        => $item_get['Inventories'][0]['BranchName'],
                            'cost'              => $item_get['Inventories'][0]['Cost'],
                            'onHand'            => $item_get['Inventories'][0]['OnHand'],
                            'reserved'          => $item_get['Inventories'][0]['Reserved'],
                        );
                        $item_inven = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $item_get['Id'], 'branchId' => $banch->id));
                        if($item_inven){
                            $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->saveItem(array('data' => $data_inven), array('task' => 'update'));
                            if($data_inven['cost'] != $item_inven['cost']){
                                $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(array('ssFilter' => array('filter_product_id' => $item_inven['productId'], 'filter_sale_branch' => $banch->id)), array('task' => 'list-item-update-cost',));
                                if(!empty($contracts)){
                                    foreach($contracts as $contract){
                                        $options = unserialize($contract->options);
                                        $products = $options['product'];
                                        foreach($products as $key => $pro){
                                            if($pro['product_id'] == $data_inven['productId']){
                                                $options['product'][$key]['cost'] = $data_inven['cost'];

                                                $capital_default = (int)($data_inven['cost'] + $item_inven['cost_new']) * $pro['numbers'];
                                                $options['product'][$key]['capital_default'] = (int)$capital_default;
                                                $options['product'][$key]['cost_new'] = $item_inven['cost_new'];
                                                $options['product'][$key]['fee'] = $item_inven['fee'];
                                            }
                                        }
                                        $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(array('data' => array('options' => $options, 'id' => $contract['id'])), array('task' => 'update-product-cost-auto'));
                                    }
                                }
                            }
                        }
                        else{
                            $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->saveItem(array('data' => $data_inven), array('task' => 'add'));
                        }
                    }
                }
            }
        }
        exit;
    }

    // webhook cập nhật sản phẩm
    public function updateKovStockAction(){
        // Data mẫu
        // $post_data_test = '{"Id":"71676945-a9ef-4ca0-aff6-856731f30a49","Attempt":1,"Notifications":[{"Action":"stock.update.925719","Data":[{"__type":"KiotViet.OmniChannelCore.Api.Shared.Model.WebhookProductStockRes, KiotViet.OmniChannelCore.Api.Shared","ProductId":8352705,"ProductCode":"SP001591","ProductName":"SP_Test nhập kho","BranchId":13083,"BranchName":"Tân Triều","Cost":200000,"OnHand":10,"Reserved":0,"MinQuantity":0,"MaxQuantity":0,"isActive":true}]}]}';
        // $data_post =  json_decode($post_data_test, true);

//        $this->postJson(file_get_contents('php://input'));// Đẩy dữ liệu sang webhook.site để kiểm tra
        $data_post =  json_decode(file_get_contents('php://input'), true);
        $notifications = $data_post['Notifications'];

        foreach($notifications as $notifi){
            foreach($notifi['Data'] as $item_get){
                $item_inven = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $item_get['ProductId'], 'branchId' => $item_get['BranchId']));

                $convert['productId']       = $item_get['ProductId'];
                $convert['productCode']     = $item_get['ProductCode'];
                $convert['productName']     = $item_get['ProductName'];
                $convert['branchId']        = $item_get['BranchId'];
                $convert['branchName']      = $item_get['BranchName'];
                $convert['cost']            = $item_get['Cost'];
                $convert['onHand']          = $item_get['OnHand'];
                $convert['reserved']        = $item_get['Reserved'];

                if($item_inven){
                    $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->saveItem(array('data' => $convert), array('task' => 'update'));
                }
                else{
                    $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->saveItem(array('data' => $convert), array('task' => 'add'));
                }
            }
        }
        exit;
    }

    // Chia data tự động
    public function shareDataAutoAction() {
        $params['ssFilter']['filter_type'] = 'auto_share_data';
        $list_data_config = $this->getServiceLocator()->get('Admin\Model\DataConfigTable')->listItem($params, array('task' => 'list-item-all'))->toArray();

        if (count($list_data_config) > 0){
            foreach ($list_data_config as $key => $item) {
                if(!empty($item['sale_branch_id']) && !empty($item['options'])){
                    $data_where['filter_sale_branch'] = $item['sale_branch_id'];
                    $data_where['filter_limit']       = (int)$item['number'];
                    // Lấy danh sách data cần chia
                    $list_data_mkt = $this->getServiceLocator()->get('Admin\Model\FormDataTable')->listItem(array('ssFilter' => $data_where), array('task' => 'list-all-item'));
                    // Lấy danh sách user cần chia
                    $users = explode(',', $item['options']);

                    // Chia data cho user
                    $arrParam['data']['items'] = $list_data_mkt->toArray();
                    $arrParam['data']['user_id'] = $users;
                    $this->getServiceLocator()->get('Admin\Model\FormDataTable')->shareData($arrParam);
                }
            }
            // Cập nhật lại thứ tự nhân sự sau khi chia data
            foreach ($list_data_config as $key => $value) {
                $this->getServiceLocator()->get('Admin\Model\DataConfigTable')->saveItem(array('item' => $value), array('task' => 'order-user-options'));
            }
        }
        return $this->response;
    }
    // Lấy giá niêm yết của sản phẩm
    public function getProductListedAction() {
        $results = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->getListedPrice($this->_params['data']);
        echo $results;
        return $this->response;
    }
    public function fixContractAction(){
        $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(['paginator'=>['itemCountPerPage'=>$this->_params['data']['size'],'currentPageNumber'=>1]],['task'=>'list-item'])->toArray();
        $carpetColor       = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
        $tangledColor      = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
        $colorGroup        = $this->getServiceLocator()->get('Admin\Model\ColorGroupTable')->listItem(null, array('task' => 'cache'));
        
        foreach($contracts as $contract){
            $options = unserialize($contract['options'])?:[];
            $update = false;
            foreach($options['product'] as $key => $product) {
                if (!$product['capital_default'] && $product['product_id'] && $product['carpet_color_id'] && $product['tangled_color_id'] && $product['flooring_id']) {
                    /* Find price */
                    
                    $parentCarpet       = $carpetColor[$product['carpet_color_id']]['parent'];
                    $parentTangled      = $tangledColor[$product['tangled_color_id']]['parent'];

                    $productListed = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')
                                                                ->getItem(array(
                                                                    'data' => array(
                                                                    'product_id' => $product['product_id'],
                                                                    'group_carpet_color_id' => $parentCarpet,
                                                                    'group_tangled_color_id' => $parentTangled,
                                                                    'flooring_id' => $product['flooring_id'],
                                                                    'type' => 'default',
                                                                    )
                                                                ), array('task' => 'by-ajax'));
                    $price = $productListed['price'] ? $productListed['price'] : 0;
                    if ($price) {
                        $update = true;
                        $options['product'][$key]['capital_default'] = $price;
                    }
                }
            }
            if ($update) {
                $errors[][$contract['id']] = $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(['data'=>['id'=>$contract['id'],'options'=>$options]],['task'=>'update-options']);
            }
        }
        echo json_encode(['success'=>true,'data'=>$errors]);die();
    }
    public function productSettingAction(){
        $setting = $this->getServiceLocator()->get('Admin\Model\SettingTable')->getItem(['code'=>'General.Contract.ProductSetting'],['task'=>'code']);
        if (isset($this->_params['data']['items'])&&count($this->_params['data']['items'])) {
            $setting_data = json_decode($setting['content'],true);
            
            foreach($this->_params['data']['items'] as $key=>$val) {
                if (!in_array($val,$setting_data) && strlen($val)==91){ 
                    $setting_data[] = $val;
                }
            }
            $setting_data = array_values($setting_data);
            $setting['content'] = json_encode($setting_data);
            $res = $this->getServiceLocator()->get('Admin\Model\SettingTable')->saveItem(['data'=>$setting],['task'=>'edit-item']);
            echo Json::encode(['success'=>true,'res'=>$setting,'msg'=>'Lưu thành công']);die();
        }
        $filters = [
            'product_id' => $this->_params['data']['product_id'],
            'carpet_color_id' => $this->_params['data']['carpet_color_id'],
            'tangled_color_id' => $this->_params['data']['tangled_color_id'],
            'flooring_id' => $this->_params['data']['flooring_id'],
        ];
        $settings = json_decode($setting['content'],true);
        //Delete item
        if ($this->_params['data']['action']=='delete') {
            $found = array_search($this->_params['data']['key'], $settings);
            unset($settings[$found]);
            $settings = array_values($settings);
            $setting['content'] = json_encode($settings);
            $res = $this->getServiceLocator()->get('Admin\Model\SettingTable')->saveItem(['data'=>$setting],['task'=>'edit-item']);
            echo Json::encode(['success'=>true,'res'=>$setting,'msg'=>'Lưu thành công']);die();
        }
        //Delete item
        $settings_filtered = [];
        foreach($settings as $key=>$val) {
            $filtered = true;
            if (!empty($filters['product_id'])) {
                $filtered = strpos($val,$filters['product_id'])!==false;
            }
            if ($filtered && !empty($filters['carpet_color_id'])) {
                $filtered = strpos($val,$filters['carpet_color_id'])!==false;
            }
            if ($filtered && !empty($filters['tangled_color_id'])) {
                $filtered = strpos($val,$filters['tangled_color_id'])!==false;
            }
            if ($filtered && !empty($filters['flooring_id'])) {
                $filtered = strpos($val,$filters['flooring_id'])!==false;
            }
            if ($filtered) $settings_filtered[] = $val;
        }
        $settings = $settings_filtered;
        $len = 50;
        $page = $this->_params['data']['page']?:1;
        $skip = ($page-1)*50;
        echo Json::encode(['success'=>true,
        'filters'=>$filters,
        'setting_pages'=>ceil(count($settings)/$len),
        'setting'=> $this->_params['data']['pagination']=='false' ? $settings : array_slice($settings,$skip,$len),
        'data'=>[
            'product'        => array_map(function($item){return ['id'=>$item['id'],'name'=>$item['name']];}, array_values($this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active')))),
            'type_of_carpet' => array_map(function($item){return ['id'=>$item['id'],'name'=>$item['name']];}, array_values($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache')))),
            'carpet_color'   => array_map(function($item){return ['id'=>$item['id'],'name'=>$item['name']];}, array_values($this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache')))),
            'tangled_color'  => array_map(function($item){return ['id'=>$item['id'],'name'=>$item['name']];}, array_values($this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache')))),
            'row_seats'      => array_map(function($item){return ['id'=>$item['id'],'name'=>$item['name']];}, array_values($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'row-seats')), array('task' => 'cache')))),
            'flooring'       => array_map(function($item){return ['id'=>$item['id'],'name'=>$item['name']];}, array_values($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache')))),
        ]]);die();
    }
    public function uploadAction(){
        $file = $this->_params['data']['file'];
        $msg = '';
        if ($file['error']) $msg = 'Tệp bị lỗi';
        if (empty($msg) && $file['size'] > 10*1024*1024) $msg = 'Tệp phải có kích dưới dưới 10Mb';
        if (empty($msg) && !in_array($file['type'],['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel'])) $msg = 'Tệp sai định dạng cho phép';
        if ($msg) {
            echo json_encode(['success'=>false,'msg'=>$msg]);die();    
        }
        
        $upload         = new \ZendX\File\Upload();
        $file    = $upload->uploadFile('file', PATH_FILES . '/import/', array());
         
        require_once PATH_VENDOR . '/Excel/PHPExcel/IOFactory.php';
        $objPHPExcel = \PHPExcel_IOFactory::load(PATH_FILES . '/import/'. $file);
         
        $sheetData = $objPHPExcel->getActiveSheet(1)->toArray(null, true, true, true);
        $heading = [];
        
        $options['product']        = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache-active')),array('key' => 'name', 'value' => 'object'));
        $options['type_of_carpet'] = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'type-of-carpet')), array('task' => 'cache')),array('key' => 'name', 'value' => 'object'));
        $options['carpet_color']   = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache')),array('key' => 'name', 'value' => 'object'));
        $options['tangled_color']  = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache')),array('key' => 'name', 'value' => 'object'));
        $options['flooring']       = \ZendX\Functions\CreateArray::create($this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache')),array('key' => 'name', 'value' => 'object'));
        
        foreach($sheetData as $key=>$val){
            if ($key==1) {
                foreach($val as $k=>$v) $heading[] = $v;
            } else {
                foreach($val as $k=>$v) {
                    if ($k=='A')
                    $data[$key-2][] = $options['product'][$v?:'']['id']?:'';
                    if ($k=='B')
                    $data[$key-2][] = $options['carpet_color'][$v?:'']['id']?:'';
                    if ($k=='C')
                    $data[$key-2][] = $options['tangled_color'][$v?:'']['id']?:'';
                    if ($k=='D')
                    $data[$key-2][] = $options['flooring'][$v?:'']['id']?:'';
                }
            }
        }
        $results = [];
        foreach($data as $key=>$val) {
            if (count($val)==4 && strlen(implode('-',$val))==91) {
                $results[] = implode('-',$val);
            } 
        }
        if (count($results)) {
            
            $setting = $this->getServiceLocator()->get('Admin\Model\SettingTable')->getItem(['code'=>'General.Contract.ProductSetting'],['task'=>'code']);
//            $setting_data = [];// json_decode($setting['content'],true);
            $setting_data = json_decode($setting['content'],true);

            foreach($results as $key=>$val) {
                if (!in_array($val,$setting_data) && strlen($val)==91){ 
                    $setting_data[] = $val;
                }
            }
            $setting['content'] = json_encode(array_values($setting_data));
            $res = $this->getServiceLocator()->get('Admin\Model\SettingTable')->saveItem(['data'=>$setting],['task'=>'edit-item']);
            $results = [];
        }
        echo json_encode(['success'=>true,'res'=>$res,'heading'=>$heading,'data'=>$results]);die();
    }
    public function healContractAction(){
        // foreach($this->_params['data']['ids'] as $id) {
        //     $ids[] = $this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem(['data'=>['id'=>$id,'capital_default'=>0]],['task'=>'edit-capital-default']);
        // }
        // echo Json::encode(['success'=>true,'ids'=>$ids]);die();
        $contracts = $this->getServiceLocator()->get('Admin\Model\ContractTable')->listItem(['paginator'=>['itemCountPerPage'=>13000,'currentPageNumber'=>1]],['task'=>'list-item'])->toArray();
        foreach($contracts as $contract) {
            $options = unserialize($contract['options'])?:[];
            // $codes = explode('-',$contract['code']);
            // $code = $codes[0];
            $changed = false;
            foreach($options['product'] as $key=> $product) {
                //Cập nhật giá vốn mặc định, giá niêm yết
                // if (!$product['capital_default'] && $product['product_id'] && $product['carpet_color_id'] && $product['tangled_color_id'] && $product['flooring_id']) {
                //     $listed_price = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->getListedPrice([
                //         'product'       => $product['product_id'],
                //         'carpet_color'  => $product['carpet_color_id'],
                //         'tangled_color' => $product['tangled_color_id'],
                //         'flooring'      => $product['flooring_id'],
                //         'type'          => 'default'
                //     ]);
                //     if($listed_price) {
                //         $options['product'][$key]['capital_default'] = $listed_price;
                //         $changed = true;
                //     }
                // }
                // if (!$product['listed_price'] && $product['product_id'] && $product['carpet_color_id'] && $product['tangled_color_id'] && $product['flooring_id']) {
                //     $listed_price = $this->getServiceLocator()->get('Admin\Model\ProductListedTable')->getListedPrice([
                //         'product'       => $product['product_id'],
                //         'carpet_color'  => $product['carpet_color_id'],
                //         'tangled_color' => $product['tangled_color_id'],
                //         'flooring'      => $product['flooring_id'],
                //         'type'          => 'price'
                //     ]);
                //     if($listed_price) {
                //         $options['product'][$key]['listed_price'] = $listed_price;
                //         $changed = true;
                //     }
                // }
                if (1) {
                    if ($product['capital_default'] && $product['sale_branch_id'] == $contract['sale_branch_id']) {
                        $options['product'][$key]['capital_default'] = 0;
                        $changed = true;
                    }
                }
            }
            if ($contract['code'] && $changed) {
                $changed_ids[] = $this->getServiceLocator()->get('Admin\Model\ContractTable')->updateItem(['data'=>['id'=>$contract['id'],'options'=>($options)]],['task'=>'update-options']);
            }
        }
        echo Json::encode(['success'=>true,'data'=>$changed_ids]);die();
        echo Json::encode($this->getServiceLocator()->get('Admin\Model\ContractTable')->saveItem($this->_params,['task'=>'edit-capital-default']));die();
    }
     // Lấy đơn hàng có sẵn
     public function getContractAvailableAction() {
         $dataCode                          = $this->_params['data']['code'];
         $getCode                           = $this->getServiceLocator()->get('Admin\Model\ContractTable')->getItem(array('code' => $dataCode, 'status_acounting_id' => STATUS_CONTRACT_ACOUNTING_RETURN), array('task' => 'by-code'));
         $this->_viewModel['carpet_color']  = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
         $this->_viewModel['tangled_color'] = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
         $this->_viewModel['flooring']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
         $this->_viewModel['product']       = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));

         $options                                     = unserialize($getCode['options']);
         $this->_viewModel['items']                   = $options['product'];
         $this->_viewModel['code']                    = $dataCode;
         $this->_viewModel['sale_branch']             = $getCode['sale_branch_id'];// cơ sở đơn hàng bán lại.
         $this->_viewModel['curent_sale_branch_id']   = $this->_userInfo->getUserInfo('sale_branch_id');// Cơ sở của người lên đơn
         $this->_viewModel['data']                    = $this->_params['data'];
         $viewModel                                   = new ViewModel($this->_viewModel);
         $viewModel->setTerminal(true);
         return $viewModel;
    }

     // sản phẩm combo
     public function getComboProductAction() {
         $id                                = $this->_params['data']['id'];
         $combo                             = $this->getServiceLocator()->get('Admin\Model\ComboProductTable')->getItem(array('id' => $id), null);
         $this->_viewModel['carpet_color']  = $this->getServiceLocator()->get('Admin\Model\CarpetColorTable')->listItem(null, array('task' => 'cache'));
         $this->_viewModel['tangled_color'] = $this->getServiceLocator()->get('Admin\Model\TangledColorTable')->listItem(null, array('task' => 'cache'));
         $this->_viewModel['flooring']      = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'flooring')), array('task' => 'cache'));
         $this->_viewModel['product']       = $this->getServiceLocator()->get('Admin\Model\ProductTable')->listItem(null, array('task' => 'cache'));

         $options                                     = unserialize($combo['options']);
         $this->_viewModel['items']                   = $options['product'];
         $this->_viewModel['code']                    = $id;
         $this->_viewModel['data']                    = $this->_params['data'];
         $viewModel                                   = new ViewModel($this->_viewModel);
         $viewModel->setTerminal(true);
         return $viewModel;
    }
}














