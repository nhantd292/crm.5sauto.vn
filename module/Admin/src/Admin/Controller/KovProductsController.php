<?php

namespace Admin\Controller;

use kcfinder\zipFolder;
use ZendX\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\FormInterface;

class KovProductsController extends ActionController{

    public function init()
    {
        // Thiết lập options
        $this->_options['tableName'] = 'Admin\Model\KovProductsTable';

        // Thiết lập session filter
        $action = str_replace('-', '_', $this->_params['action']);
        $ssFilter                                    = new Container(__CLASS__);
        $this->_params['ssFilter']['order_by']       = !empty($ssFilter->order_by) ? $ssFilter->order_by : 'name';
        $this->_params['ssFilter']['order']          = !empty($ssFilter->order) ? $ssFilter->order : 'DESC';

        $this->_params['ssFilter']['filter_keyword']        = $ssFilter->filter_keyword;
        $this->_params['ssFilter']['filter_categoryId']     = $ssFilter->filter_categoryId;
        $this->_params['ssFilter']['filter_branches']       = $ssFilter->filter_branches ;
        $this->_params['ssFilter']['filter_evaluate']       = $ssFilter->filter_evaluate;
        $this->_params['ssFilter']['filter_tailors']        = $ssFilter->filter_tailors;

        // Thiết lập lại thông số phân trang
        $this->_paginator['itemCountPerPage']  = !empty($ssFilter->pagination_option) ? $ssFilter->pagination_option : 50;
        $this->_paginator['currentPageNumber'] = $this->params()->fromRoute('page', 1);
        $this->_params['paginator']            = $this->_paginator;

        // Lấy dữ liệu post của form
        $this->_params['data'] = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        // Truyển dữ dữ liệu ra ngoài view
        $this->_viewModel['params'] = $this->_params;
    }

    public function filterAction()
    {
        if ($this->getRequest()->isPost()) {
            $action     = !empty($this->_params['data']['filter_action']) ? $this->_params['data']['filter_action'] : 'index';
            $ssFilter   = new Container(__CLASS__);
            $data       = $this->_params['data'];

            $ssFilter->pagination_option = intval($data['pagination_option']);
            $ssFilter->order_by          = $data['order_by'];
            $ssFilter->order             = $data['order'];

            $ssFilter->filter_categoryId    = $data['filter_categoryId'];
            $ssFilter->filter_keyword       = $data['filter_keyword'];
            $ssFilter->filter_branches      = $data['filter_branches'];
            $ssFilter->filter_evaluate      = $data['filter_evaluate'];
            $ssFilter->filter_tailors       = $data['filter_tailors'];
        }

        $this->goRoute(['action' => $action]);
    }

    public function webhooksAction(){
        // Cập nhật sản phẩm
//        $webhook = array(
//            'Webhook' => array(
//                'Type' => 'product.update',
//                //'Url' => 'https://webhook.site/7bf396a2-878d-4770-881d-7fd129331ba6', // link online test data webhook trả về
//                'Url' => 'http://crm.forewin.vn/xadmin/api/update-kov-productions',
//                'IsActive' => true,
//                'Description' => 'Webhook for product.update',
//            ),
//        );

        // Cập nhật kho hàng
            $webhook = array(
                'Webhook' => array(
                    'Type' => 'stock.update',
//                    'Url' => 'https://webhook.site/3043df2f-05e7-4430-876a-28070f7ff8d3',
                    'Url' => 'http://crm.forewin.vn/xadmin/api/update-kov-stock',
                    'IsActive' => true,
                    'Description' => 'Webhook for stock.update',
                ),
            );

//        $result = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/webhooks/219621', null, 'DELETE'); // Hủy webhook
//        $result = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/webhooks', $webhook, 'POST'); // Đăng ký webhook
        $result = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/webhooks');// Lấy danh sách webhook
        $result = json_decode($result, true);

        echo "<pre>";
        print_r($result);
        echo "</pre>";
        exit;
    }

    public function indexAction(){
        $categories = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/categories?pageSize=100&hierachicalData=true');
        $categories = json_decode($categories, true)['data'];
        $categories = $this->getNameCat($this->addNew($categories), $result);

        $ssFilter = new Container(__CLASS__. 'sales');
        $ssFilter->currentPageNumber = $this->_paginator['currentPageNumber'];

        $myForm	= new \Admin\Form\Search\KovProduct($this, $categories);
        $myForm->setData($this->_params['ssFilter']);
        $items = $this->getTable()->listItem($this->_params, array('task' => 'list-item'));
        $count = $this->getTable()->countItem($this->_params, array('task' => 'list-item'));

        $this->_viewModel['myForm']	                = $myForm;
        $this->_viewModel['items']                  = $items;
        $this->_viewModel['count']                  = $count;
        $this->_viewModel['categories']             = $categories;
        $this->_viewModel['sale_branch']            = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'cache'));
        $this->_viewModel['caption']                = 'Danh sách sản phẩm';

        return new ViewModel($this->_viewModel);
    }

    // Đồng bộ kho từ kiotviet
    public function updateAction(){
        $sale_branchs = $this->getServiceLocator()->get('Admin\Model\DocumentTable')->listItem(array('where' => array('code' => 'sale-branch')), array('task' => 'list-all'));
        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $product = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products/'.$this->_params['data']['id']);
                $product = json_decode($product, true);

                $item = $this->getTable()->getItem(array('id' => $this->_params['data']['id']));
                if($item){
                    $pid = $this->getTable()->saveItem(array('data' => $product), array('task' => 'update'));
                }
                else {
                    $pid = $this->getTable()->saveItem(array('data' => $product), array('task' => 'add'));
                }

                if(isset($product['inventories'])){
                    $inven = $product['inventories'][0];
                    foreach($sale_branchs as $key => $banch){
                        $inven['branchId'] = $banch->id;
                        $item_inven = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $product['id'], 'branchId' => $banch->id));
                        if($item_inven){
                            $iid = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->saveItem(array('data' => $inven), array('task' => 'update'));
                        }
                        else{
                            $iid = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->saveItem(array('data' => $inven), array('task' => 'add'));
                        }
                    }
                }

                if($pid && $iid){
                    echo '<i class="fa fa-check-square success"></i>';
                }
                else{
                    echo '<i class="fa fa-exclamation-circle error"></i>';
                }
                exit;
            }
        }
        else {
            // nếu muốn cập nhật một sản phẩm đã biết mã sản phẩm : comment trước khi gán $product_data
//            $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products/code/SP001407?pageSize=100');
//            $product_data[] = $products;

            $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token, '/products?pageSize=100');
            $products = json_decode($products, true);

            $total = $products['total'];
            $pageSize = $products['pageSize'];
            $pageTotal = (int)($total / $pageSize) + 1;

            $product_data = [];
            for ($index = 0; $index < $pageTotal; $index++) {
                $currentItem = $index * $pageSize;
                $products = $this->kiotviet_call(RETAILER, $this->kiotviet_token,
                    '/products?pageSize=100&includeInventory=true&currentItem=' . $currentItem);
                $products = json_decode($products, true);
                $product_data = array_merge($product_data, $products['data']);
            }

            $this->_viewModel['items'] = $product_data;
            $this->_viewModel['count'] = count($product_data);
        }
        $this->_viewModel['caption']                = 'Cập nhật sản phẩm từ kiotviet';

        return new ViewModel($this->_viewModel);
    }

    public function statusAction() {
        if($this->getRequest()->isXmlHttpRequest()) {
            $this->getTable()->changeStatus($this->_params, array('task' => 'change-status'));
        } else {
            $this->goRoute();
        }

        return $this->response;
    }

    // Hàng có sẵn
    public function availableAction() {
        $this->_params['data']['product_type'] = 1;
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'change-available'));
        $this->flashMessenger()->addMessage('Chuyển '.$result.' sản phẩm sang hàng bán sẵn');
        $this->goRoute();
    }

    // Hàng sản xuất
    public function unavailableAction() {
        $this->_params['data']['product_type'] = 2;
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'change-available'));
        $this->flashMessenger()->addMessage('Chuyển '.$result.' sản phẩm sang hàng sản xuất');
        $this->goRoute();
    }

    // Hàng có sẵn
    public function tailorsAction() {
        $this->_params['data']['evaluate'] = 1;
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'change-tailors'));
        $this->flashMessenger()->addMessage('Chuyển '.$result.' sản phẩm sang có đánh giá thợ may');
        $this->goRoute();
    }

    // Hàng sản xuất
    public function untailorsAction() {
        $this->_params['data']['evaluate'] = 0;
        $result = $this->getTable()->saveItem($this->_params, array('task' => 'change-tailors'));
        $this->flashMessenger()->addMessage('Chuyển '.$result.' sản phẩm sang không đánh giá thợ may');
        $this->goRoute();
    }

    // xuất mẫu nhập giá vốn và phụ phí
    public function exportTemplateAction() {
        $items = $this->getTable()->listItem(array('ids' => $this->_params['data']['cid'], 'branches' => $this->_params['data']['filter_branches']), array('task' => 'list-export-template'));
        //Include PHPExcel
        require_once PATH_VENDOR . '/Excel/PHPExcel.php';

        // Config
        $config = array(
            'sheetData' => 0,
            'headRow' => 1,
            'startRow' => 2,
            'startColumn' => 0,
        );

        // Column
        $arrColumn = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ');

        // Data Export
        $arrData = array(
            array('field' => 'id', 'title' => 'ID SẢN PHẨM'),
            array('field' => 'branch_id', 'title' => 'ID KHO'),
            array('field' => 'code', 'title' => 'MÃ SẢN PHẨM'),
            array('field' =>'fullName','title' => 'TÊN SẢN PHẨM'),
            array('field' =>'branch_name','title' => 'KHO'),
            array('field' =>'branch_cost','title' => 'GIÁ VỐN KIOTVIET'),
            array('field' =>'branch_cost_new','title' => 'GIÁ VỐN TRÊN CRM'),
            array('field' =>'branch_fee','title' => 'PHỤ PHÍ'),
        );

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator($this->_userInfo->getUserInfo('name'))
            ->setLastModifiedBy($this->_userInfo->getUserInfo('username'))
            ->setTitle("Don_san_xuat_".date('d-m-Y'));

        // Dữ liệu tiêu đề cột
        $startColumn = $config['startColumn'];
        foreach ($arrData AS $key => $data) {
            $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $config['headRow'], $data['title']);
            $objPHPExcel->getActiveSheet()->getStyle($arrColumn[$startColumn] . $config['headRow'])->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension($arrColumn[$startColumn])->setAutoSize(true);
            $startColumn++;
        }

        // Dữ liệu data
        $startRow = $config['startRow'];
        foreach ($items AS $item) {
            $startColumn = $config['startColumn'];
            foreach ($arrData AS $key => $data) {
                $value = $item[$data['field']];
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->setCellValue($arrColumn[$startColumn] . $startRow,
                    $value);
                $objPHPExcel->setActiveSheetIndex($config['sheetData'])->getStyle($arrColumn[$startColumn] . $startRow)->getAlignment()->setWrapText(true);
                $startColumn++;
            }
            $startRow++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.'Mau_import_gia_von_san_pham_'.date('d-m-Y').'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

        return $this->response;
    }

    public function importAction()
    {
        $dateFormat = new \ZendX\Functions\Date();
        $myForm = new \Admin\Form\Contact\Import($this->getServiceLocator(), $this->_params);
        $myForm->setInputFilter(new \Admin\Filter\Contact\Import($this->_params));

        $this->_viewModel['caption'] = 'Import Giá vốn';
        $this->_viewModel['myForm']  = $myForm;
        $viewModel                   = new ViewModel($this->_viewModel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $item_inven = $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->getItem(array('productId' => $this->_params['data']['productId'], 'branchId' => $this->_params['data']['branchId']));

                if(!empty($item_inven)) {
                    $this->getServiceLocator()->get('Admin\Model\KovProductBranchTable')->saveItem($this->_params, array('task' => 'import-item'));
                    echo 'Hoàn thành';
                }
                else{
                    echo 'Sản phẩm không có trong kho';
                }
                return $this->response;
            }
        }
        else {
            if ($this->getRequest()->isPost()) {
                $myForm->setData($this->_params['data']);

                if ($myForm->isValid()) {
                    if (!empty($this->_params['data']['file_import']['tmp_name'])) {
                        $upload      = new \ZendX\File\Upload();
                        $file_import = $upload->uploadFile('file_import', PATH_FILES . '/import/', array());
                    }
                    $viewModel->setVariable('file_import', $file_import);
                    $viewModel->setVariable('import', true);

                    require_once PATH_VENDOR . '/Excel/PHPExcel/IOFactory.php';
                    $objPHPExcel = \PHPExcel_IOFactory::load(PATH_FILES . '/import/' . $file_import);

                    $sheetData = $objPHPExcel->getActiveSheet(1)->toArray(null, true, true, true);

                    $viewModel->setVariable('sheetData', $sheetData);
                }
            }
        }

        return $viewModel;
    }
}


