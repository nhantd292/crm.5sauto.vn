/**
 * Author: NamNV
 * Desciption: Tham số mặc định của hệ thống
 */
var url_application		= '';
var moduleAdmin			= url_application + '/xadmin';
var moduleReport		= url_application + '/xreport';
var moduleApi			= url_application + '/xapi';
var urlTemplate			= url_application + '/public/template/backend';
var formAdmin 			= $('#adminForm').length ? '#adminForm' : '#adminFormFilter';
var moduleName 			= $('body').attr('data-module');
var controllerName 		= $('body').attr('data-controller');
var actionName 			= $('body').attr('data-action');

var xMessage = {
	'no-access': 'Bạn không có quyền thực hiện chức năng này',
	'no-checked': 'Bạn cần chọn những phần tử muốn thao tác',
	'record-exists': 'Dữ liệu đã tồn tại và không thể thêm',
	'success': 'Cập nhật dữ liệu thành công'
};