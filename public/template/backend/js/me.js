/**
 * Author: NamNV
 * Desciption: Thực hiện submit form
 * 
 * @params: Mảng các tham số được truyền vào
 * @options: Mảng các tùy chọn hoặc phân vùng làm việc
 */
function submitForm(url, target="") {
	if(url != ""){
		$(formAdmin).attr('action', url);
	}
	if(target != ""){
		jQuery(formAdmin).attr('target', '_blank');
	}
	
	jQuery(formAdmin).submit();
}

/**
 * Author: NamNV
 * Desciption: Sự kiện nhấn enter ở ô input
 */
function keywordEnter() {
	jQuery(formAdmin + ' input[name="filter_keyword"]').keypress(function (event) {
		if(event.charCode == 13) {
			event.preventDefault();
			jQuery(formAdmin).submit();
		}
	});
}

/**
 * Author: NamNV
 * Desciption: Thực hiện sắp xếp danh sách
 * 
 * @orderColumn: Cột sẽ sắp xếp
 * @orderBy: Chiều sắp xếp
 */
function sortList(orderColumn, orderBy) {
	
	jQuery(formAdmin + ' input[name="order_by"]').val(orderColumn);
	jQuery(formAdmin + ' input[name="order"]').val(orderBy);
	
	jQuery(formAdmin).submit();
}

/**
 * Author: NamNV
 * Desciption: Lấy thông tin khách hàng qua số đt
 * 
 * @phone: Số điện thoại cần check
 * @type: Phân loại xử lý
 */
function checkContactExists(phone, type, add = false) {
	if(phone) {
		var ajaxUrl = moduleAdmin + '/api/get-contact';
		
		jQuery.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				phone: phone,
			},
			beforeSend: function() {
				pageLoading('loading', '.page-container');
			},
			success: function(result) {
				$('#result_check_contact').html('');
				$('.form-control').removeAttr('disabled');
				if(result != 'not-found') {
					var result = $.parseJSON(result);
					if(result['store'] != '' && result['store'] != null && result['store'] != undefined) {
						var html = '<div class="alert alert-block alert-info" style="margin-bottom: 15px;">' + 
										'<p><b>Liên hệ kho. Bạn sẽ là người quản lý</b></p>' +
										'<p>Nhấn vào <a href="'+ moduleAdmin +'/contact/store/id/'+ result['id'] +'" class="btn btn-xs red">Nhập lại kho</a> nếu như bạn muốn quản lý lại liên hệ này</p>' +
									'</div>';
						$('#result_check_contact').html(html);
						$('.form-control').attr('disabled', 'disabled');
						$('input[name="phone"]').removeAttr('disabled');
					} else {
						var html = '<div class="alert alert-block alert-danger" style="margin-bottom: 15px;">' + 
										'<p><b>Liên hệ này đã có người quản lý</b></p>' +
										'<p>Người quản lý: '+ result['user_name'] + ' - ' + result['sale_group_name'] + ' - ' + result['sale_branch_name'] +'</p>' +
										'<p>Ngày tạo: '+ result['date'] +'</p>' +
									'</div>';
						$('#result_check_contact').html(html);
						$('.form-control').attr('disabled', 'disabled');
						$('input[name="phone"]').removeAttr('disabled');
					}
					if(add == true){
						$('.form-control').removeAttr('disabled');
					}
				} else {
					console.log('Không tìm thấy liên hệ');
				}
				
				pageLoading('close', '.page-container');
			},
			error: function (request, status, error) {
				xToastr('error', 'Lỗi không thể kiểm tra được khách hàng', '');
			}
		});
	} else {
		$('#result_check_contact').html('');
	}
}

/**
 * Author: NamNV
 * Desciption: Lấy thông tin khách hàng qua id khách hàng để làm đơn hàng
 * 
 * @id: Số điện thoại cần check
 */
function checkContactToElement(id, option) {
	if(id) {
		var ajaxUrl = moduleAdmin + '/api/get-contact';
		
		jQuery.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				id: id,
			},
			beforeSend: function() {
				pageLoading('loading', '.page-container');
			},
			success: function(result) {
				$('#result_check_contact').html('');
				$('.form-control').removeAttr('disabled');
				if(option == 'element') {
					$('.alert-danger').remove();
					$('.has-error').removeClass('has-error');
				}
				if(result != 'not-found') {
					var result = $.parseJSON(result);
					var html = '';
					if(result['store'] != '' && result['store'] != null && result['store'] != undefined) {
						html = 	'<div class="alert alert-block alert-success" style="margin-bottom: 15px;">' + 
										'<p><b>Liên hệ kho. Bạn sẽ là người quản lý sau khi thêm đơn hàng</b></p>' +
									'</div>';
					} else {
						html = 	'<div class="alert alert-block alert-info" style="margin-bottom: 15px;">' + 
										'<p><b>Thông tin người quản lý</b></p>' +
										'<p>Người quản lý: '+ result['user_name'] + ' - ' + result['sale_group_name'] + ' - ' + result['sale_branch_name'] +'</p>' +
									'</div>';
					}
					
					$('#result_check_contact').html(html);
					
					if(option == 'element') {
						loadDataToElement(result, 'exists');
					}
				} else {
					var html = 	'<div class="alert alert-block alert-warning" style="margin-bottom: 15px;">' + 
									'<p><b>Liên hệ không tồn tại. Bạn sẽ là người quản lý sau khi thêm đơn hàng</b></p>' +
								'</div>';
					$('#result_check_contact').html(html);
					
					if(option == 'element') {
						loadDataToElement(result, 'not-exists');
					}
				}

				// $('input[name="phone"]').attr('disabled', 'disabled');
				pageLoading('close', '.page-container');
				
				// checkSubject();
			},
			error: function (request, status, error) {
				xToastr('error', 'Lỗi không thể kiểm tra được khách hàng', '');
			}
		});
	} else {
		$('#result_check_contact').html('');
		$('.form-control').attr('disabled', 'disabled');
		
		loadDataToElement(null, 'not-exists');
	}
}

/**
 * Author: NamNV
 * Desciption: Load data nhận được vào các element
 * 
 * @data: Dữ liệu đầu vào
 * @option: Phân loại thực hiện
 */
function loadDataToElement(data, option) {
	switch(option) {
	    case 'exists':
	    	if($('#adminFormModal').length) {
	    		formAdmin = '#adminFormModal';
	    	}
	    	$(formAdmin + ' .form-control').not('.not-push').each(function(key, value) {
				var name = $(this).attr('name');
				if(data[name]) {
					if($(this).hasClass('select2')) {
						var parent_name = $(this).attr('data-parent-name');
						if(parent_name) {
							$(this).attr('data-parent', data[parent_name]);
						}
						$(this).select2('val', data[name]);
					} else {
						var value = $(this).attr('data-value');
						if(value) {
							$(this).val(value);
						} else {
							$(this).val(data[name]);
						}
					}
				}
			});
	    	
	        break;
	    case 'not-exists':
	    	if($('#adminFormModal').length) {
	    		formAdmin = '#adminFormModal';
	    	}
	    	$(formAdmin + ' .form-control').not('.not-push').each(function(key, value) {
	    		var name = $(this).attr('name');
				if($(this).hasClass('select2')) {
					var option_first 		= $('option:first', this).val();
					var parent_name 		= $(this).attr('data-parent-name');
					var parent_option_first = $('option:first', 'select[name="'+ parent_name +'"]').val();
					if(parent_name) {
						$(this).attr('data-parent', parent_option_first);
					} else {
						$(this).removeAttr('data-parent');
					}
					$(this).select2('val', option_first);
				} else {
					var value 				= $(this).attr('data-value');
					if(name != 'phone') {
						if(value) {
							$(this).val(value);
						} else {
							$(this).val('');
						}
					}
				}
			});
	    	
	        break;
	}
}

/**
 * Author: NamNV
 * Desciption: Load data nhận được vào các element
 * 
 * @action: Action thực hiện
 * @option: Đối tượng mở rộng
 */
function contractTool(action, option) {
	Form.extendedModals('ajax');
		
	var ajaxUrl	= moduleAdmin + '/contract/' + action;
    var $modal 	= $('#ajax-modal');
    
    $('body').modalmanager('loading');
	$modal.load(ajaxUrl, option, function(){
  		$modal.modal();
  	});
    
    $modal.on('click', '.save-close', function(){
    	$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: $modal.find('form').serialize(),
			beforeSend: function() {
				$modal.modal('loading');
				$modal.find('.btn').addClass('disabled');
			},
			success: function(result) {
				if(result == 'success') {
					$modal.modal('hide');
					location.reload();
				} else {
					$modal.modal('loading');
					$modal.find('.btn').removeClass('disabled');
					$modal.find('.modal-body').html(result);
					reloadScript();
				}
			},
			error: function (request, status, error) {
				console.log(error);
			}
		});
	});
    
    $modal.on('shown.bs.modal', function (e) {
		reloadScript();
	});
	$modal.on('hidden.bs.modal', function (e) {
		$modal.html('');
	});
}

/*
* Xuất excel
*/
function contractExport(action='export') {
	var itemId = [];
	
	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});
	
	if(itemId.length > 0) {
		var ajaxUrl = jQuery(formAdmin).attr('action').replace('/filter', '/'+action);
		submitForm(ajaxUrl);
		jQuery(formAdmin).attr('action', jQuery(formAdmin).attr('action').replace('/'+action, '/filter'));
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/*
* Xuất mẫu nhập thợ kỹ thuật, thợ may
*/
function templateImportTechnicalTailors(url = 'export-template') {
	var itemId = [];

	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});

	if(itemId.length > 0) {
		var actionForm  = $(formAdmin).attr('action').split('/');
		var ajaxUrl 	= '/' + actionForm[1] + '/' + actionForm[2] + '/' + url;
		submitForm(ajaxUrl);
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}


/*
* Ẩn đơn hàng có sẵn
*/
// function contractHidden(url = 'hidden') {
function contractHidden(url) {
	var itemId = [];

	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});

	if(itemId.length > 0) {
		var actionForm  = $(formAdmin).attr('action').split('/');
		var ajaxUrl 	= '/' + actionForm[1] + '/' + actionForm[2] + '/' + url;
		submitForm(ajaxUrl);
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/*
* Hiển thị đơn hàng đãn ẩn
*/
function contractLock(url) {
	var itemId = [];

	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});

	if(itemId.length > 0) {
		var actionForm  = $(formAdmin).attr('action').split('/');
		var ajaxUrl 	= '/' + actionForm[1] + '/' + actionForm[2] + '/' + url;
		console.log(ajaxUrl, itemId)
		submitForm(ajaxUrl);
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/*
* In đơn hàng
* copy from ele project
*/
function contractPrint(target="") {
	var itemId = [];

	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});

	if(itemId.length > 0) {
		var ajaxUrl = jQuery(formAdmin).attr('action').replace('/filter', '/print-multi');
		submitForm(ajaxUrl, target);
		jQuery(formAdmin).attr('action', jQuery(formAdmin).attr('action').replace('/print-multi', '/filter'));
		location.reload();
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

function contractPrintProduction(target="") {
	var itemId = [];
	
	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});
	
	if(itemId.length > 0) {
		var ajaxUrl = jQuery(formAdmin).attr('action').replace('/filter', '/print-production');
		submitForm(ajaxUrl, target);
		jQuery(formAdmin).attr('action', jQuery(formAdmin).attr('action').replace('/print-production', '/filter'));
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/**
 * Author: NamNV
 * Desciption: Popup ajax đến action
 * 
 * @ajaxUrl: Link đến Action thực hiện
 * @option: Đối tượng mở rộng
 */
function popupAction(ajaxUrl, option) {
	Form.extendedModals('ajax');
	
	var $modal 	= $('#ajax-modal');
	
	$('body').modalmanager('loading');
	$modal.load(ajaxUrl, option, function(){
		$modal.modal();
	});
	
	$modal.on('click', '.save-close', function(){
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: $modal.find('form').serialize(),
			beforeSend: function() {
				$modal.modal('loading');
				$modal.find('.btn').addClass('disabled');
			},
			success: function(result) {
				if(result == 'success') {
					$modal.modal('hide');
					location.reload();
				}
				if(result == 'print') {
					// var pri = jQuery(formAdmin).attr('action').replace('/filter', '/print-multi')+'id/'+option['id'];
					// window.location = '/xadmin/production/print-multi/id/'+option['id']
					$modal.modal('hide');
					window.open('/xadmin/production/print-multi/id/'+option['id'], '_blank');
					location.reload();
				}
				else if(result == 'thank'){
					$modal.modal('loading');
					$modal.find('.btn').removeClass('disabled');
					$modal.find('.save-close').remove();
					$modal.find('.modal-body').html('' +
						'                <div class="col-md-12 title-form">' +
						'                    <div class="out"><img src="/public/files/upload/default/images/logo-ok2.png"></div>' +
						'                    <div class="center"><h3 class="title-section">Lụa chọn số 1 về nội thất ô tô tại việt nam</h3></div>' +
						'                    <div class="out"><img src="/public/files/upload/default/images/home.png" style="padding: 10px 0px; width: 30%;"></div>' +
						'                </div>' +
						'				<h3 class="text-center thank-text" style="margin-right: 0;">' +
						'					<i style="font-weight: bold;color: #3fb666;font-size: 30px;margin: 100px 0px;display: block">Cảm ơn quý khách!<br> Forewin chúc quý khách 1 ngày vui vẻ!</i>' +
						'				</h3>' +
						'				<h3 class="text-center"><img style="width: 120px;" src="/public/files/upload/default/images/hand.png"></h3>' +
						''
					);
					reloadScript();
				}
				else {
					$modal.modal('loading');
					$modal.find('.btn').removeClass('disabled');
					$modal.find('.modal-body').html(result);
					reloadScript();
				}
			},
			error: function (request, status, error) {
				console.log(error);
			}
		});
	});
	
	$modal.on('shown.bs.modal', function (e) {
		reloadScript();
	});
	$modal.on('hidden.bs.modal', function (e) {
		$modal.html('');
	});
}

function popupActionNew(ajaxUrl, option) {
	Form.extendedModals('ajax');

	var $modal 	= $('#ajax-modal');

	$('body').modalmanager('loading');
	$modal.load(ajaxUrl, option, function(){
		$modal.modal();
	});

	$modal.on('click', '.save-close', function(){
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: $modal.find('form').serialize(),
			beforeSend: function() {
				$modal.modal('loading');
				$modal.find('.btn').addClass('disabled');
			},
			success: function(result) {
				if(result['type'] == 'success') {
					$modal.modal('hide');
					location.reload();
				}
				if(result['type'] == 'print_contract_order_ghtk') {
					$modal.modal('hide');
					window.open('/xadmin/contract/print-multi-order?token='+result.token+'&ids='+result.ids, '_blank');
					location.reload();
				}
				if(result['type'] == 'print_contract_order_ghn') {
					$modal.modal('hide');
					window.open(result.link_in, '_blank');
					location.reload();
				}
				else {
					$modal.modal('loading');
					$modal.find('.btn').removeClass('disabled');
					$modal.find('.modal-body').html(result);
					reloadScript();
				}
			},
			error: function (request, status, error) {
				console.log(error);
			}
		});
	});

	$modal.on('shown.bs.modal', function (e) {
		reloadScript();
	});
	$modal.on('hidden.bs.modal', function (e) {
		$modal.html('');
	});
}


function popupInfor(ajaxUrl, option) {
	Form.extendedModals('alert');

	var $modal 	= $('#alert-modal');

	$('body').modalmanager('loading');
	$modal.load(ajaxUrl, option, function(){
		$modal.modal();
	});

	$modal.on('click', '.save-close', function(){
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: $modal.find('form').serialize(),
			beforeSend: function() {
				$modal.modal('loading');
				$modal.find('.btn').addClass('disabled');
			},
			success: function(result) {
				if(result == 'success') {
					$modal.modal('hide');
					location.reload();
				}
				else {
					$modal.modal('loading');
					$modal.find('.btn').removeClass('disabled');
					$modal.find('.modal-body').html(result);
					reloadScript();
				}
			},
			error: function (request, status, error) {
				console.log(error);
			}
		});
	});

	$modal.on('shown.bs.modal', function (e) {
		reloadScript();
	});
	$modal.on('hidden.bs.modal', function (e) {
		$modal.html('');
	});
}

/**
 * Author: NamNV
 * Desciption: Load url action
 */
function load_action(parent, url, params, load=true) {
	$.ajax({
		url: url,
		type: 'POST',
		data: params,
		beforeSend: function() {
			if(load){
				$(parent).html('Đang tải dữ liệu...');
			}
		},
		success: function(result) {
			$(parent).html(result);
			Form.init();
		}
	});
}
function load_notifi(parent, url, params) {
	$.ajax({
		url: url,
		type: 'POST',
		data: params,
		success: function(result) {
			$(parent).html(result);
			Form.init();
		}
	});
}

/**
 * Author: NamNV
 * Desciption: Load Ajax Link Report
 * 
 * @parent: Nơi hiển thị nội dung
 * @url: Link
 * @options: params
 */
function load_report(parent, url, options) {
	var ajaxUrl	= url;
	var data = null;
	if(options != null) {
		data = $.parseJSON(options.replace(/'/gi, '"'));
	}
	
	$.ajax({
		url: ajaxUrl,
		type: 'GET',
		data: data,
		beforeSend: function() {
		},
		success: function(result) {
			if(result == 'no-access') {
				result = '<div class="alert alert-danger">Bạn không có quyền truy cập vào mục này</div>';
			}
			if(result == 'not-found') {
				result = '<div class="alert alert-danger">Không tìm thấy dữ liệu hoặc đường dẫn không tồn tại</div>';
			}
			$(parent).html(result);
		}
	});
}

/**
 * Author: NamNV
 * Desciption: Load Ajax Page
 * 
 * @parent: Nơi hiển thị nội dung
 * @url: Link
 * @options: params
 */
function ajax_load_page(parent, url, options) {
	var data = null;
	if(options != null) {
		data = $.parseJSON(options.replace(/'/gi, '"'));
	}
	
	$.ajax({
		url: url,
		type: 'POST',
		data: data,
		beforeSend: function() {
			pageLoading('loading', parent);
		},
		success: function(result) {
			$(parent).html(result);
			pageLoading('close', parent);
			Form.init();
		}
	});
}

/**
 * Author: NamNV
 * Desciption: Load Ajax Page Append
 * 
 * @parent: Nơi hiển thị nội dung
 * @url: Link
 * @options: params
 */
function ajax_load_page_append(parent, url, options) {
	var ajaxUrl	= url;
	var data 	= $.parseJSON(options.replace(/'/gi, '"'));
	
	$.ajax({
		url: ajaxUrl,
		type: 'POST',
		data: data,
		beforeSend: function() {
			pageLoading('loading', parent);
		},
		success: function(result) {
			$(parent + ' table tbody').append(result);
			pageLoading('close', parent);
			Form.init();
		}
	});
}

/**
 * Author: NamNV
 * Desciption: Thêm lịch sử chăm sóc bằng ajax
 * 
 * @id: Id liên hệ
 */
function add_contact_history(id) {
	Form.extendedModals('ajax');
		
	var ajaxUrl	= moduleAdmin + '/contact/add-history';
    var $modal 	= $('#ajax-modal');
    
    $('body').modalmanager('loading');
	$modal.load(ajaxUrl, {id: id}, function(){
		if($modal.html() == 'no-access') {
			modalMessage($modal, '<div class="alert alert-danger">Bạn không có quyền truy cập vào mục này</div>');
		}
		if($modal.html() == 'not-found') {
			modalMessage($modal, '<div class="alert alert-danger">Không tìm thấy dữ liệu hoặc đường dẫn không tồn tại</div>');
		}
  		$modal.modal();
  	});
    
    $modal.on('click', '.save-close', function(){
    	$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: $modal.find('form').serialize(),
			beforeSend: function() {
				$modal.modal('loading');
				$modal.find('.btn').addClass('disabled');
			},
			success: function(result) {
				if(result == 'success') {
					$modal.modal('hide');
					location.reload();
				} else {
					$modal.modal('loading');
					$modal.find('.btn').removeClass('disabled');
					$modal.find('.modal-body').html(result);
					reloadScript();
				}
			},
			error: function (request, status, error) {
				console.log(error);
			}
		});
	});
    
    $modal.on('shown.bs.modal', function (e) {
		reloadScript();
	});
	$modal.on('hidden.bs.modal', function (e) {
		$modal.html('');
	});
}

/**
 * Author: NamNV
 * Desciption: Thay đổi trạng thái
 * 
 * @type: Phân loại thực hiện là nhiều hay một phần tử: item/multi
 * @option: Là Id nếu type = item, load trạng thái 0/1 nếu type = multi
 */
function changeStatus(type, option) {
	var itemStatus = option;
	
	if(type == 'item') {
		jQuery('#tr_' + option + ' .checkboxes').attr('checked', true);
		jQuery('#tr_' + option + ' .checkboxes').parents('tr').addClass("active");
		
		itemStatus = jQuery('#tr_' + option + ' .btn-status').attr('data-status');
	}
	
	var itemId = [];
	$(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
        if (checked) {
        	itemId.push(jQuery(this).val());
        }
    });
	
	if(itemId.length > 0) {
		var actionForm  = $(formAdmin).attr('action').split('/');
    	var ajaxUrl 	= '/' + actionForm[1] + '/' + actionForm[2] + '/status';
    	if(actionForm[0] != '') {
    		ajaxUrl 	= '/' + actionForm[0] + '/' + actionForm[1] + '/status';
    	}
		var classRemove = (itemStatus == 0) ? 'default' : 'green';
		var classAdd 	= (itemStatus == 0) ? 'green' : 'default';
		var statusNew 	= (itemStatus == 0) ? 1 : 0;
		
		jQuery.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				cid: itemId,
				status: itemStatus
			},
			beforeSend: function() {
				pageLoading('loading', '.table-scrollable');
			},
			success: function(result) {
				if(result == 'no-access') {
					xToastr('error', xMessage['no-access'], '');
				} else {
					for(var i = 0; i < itemId.length; i++) {
						jQuery('#tr_' + itemId[i] + ' .btn-status').removeClass(classRemove).addClass(classAdd);
						jQuery('#tr_' + itemId[i] + ' .btn-status').attr({'onclick': 'javascript:changeStatus(\'item\', \''+ itemId[i] +'\');', 'data-status': statusNew});
						jQuery('#tr_' + itemId[i] + ' .checkboxes').attr('checked', false);
						jQuery('#tr_' + itemId[i] + ' .checkboxes').parents('tr').removeClass("active");
					}
					
					$(formAdmin + ' .group-checkable').attr('checked', false);
				}
				
				pageLoading('close', '.table-scrollable');
			},
			complete: function(){
			}
		});
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/**
 * Author: NamNV
 * Desciption: Thay đổi trạng thái
 */
function updateStatus(element) {
	var action		= $(element).attr('action') ? $(element).attr('action') : 'status';
	var actionForm  = $(formAdmin).attr('action').split('/');
	var ajaxUrl 	= '/' + actionForm[1] + '/' + actionForm[2] + '/' + action;
	if(actionForm[0] != '') {
		ajaxUrl 	= '/' + actionForm[0] + '/' + actionForm[1] + '/' + action;
	}
	var itemStatus	= parseInt($(element).attr('data-status'));
	var classRemove = (itemStatus == 0) ? 'default' : 'green';
	var classAdd 	= (itemStatus == 0) ? 'green' : 'default';
	var statusNew 	= (itemStatus == 0) ? 1 : 0;
	
	$.ajax({
		url: ajaxUrl,
		type: 'POST',
		data: {
			id: $(element).attr('data-id'),
			fields: $(element).attr('data-fields'),
			value: itemStatus
		},
		beforeSend: function() {
			pageLoading('loading', '.table-scrollable');
		},
		success: function(result) {
			if(result == 'no-access') {
				xToastr('error', xMessage['no-access'], '');
			} else {
					jQuery(element).removeClass(classRemove).addClass(classAdd);
					jQuery(element).attr({'onclick': 'javascript:updateStatus(this);', 'data-status': statusNew});
			}
			
			pageLoading('close', '.table-scrollable');
		},
		complete: function(){
		}
	});
}

/**
 * Author: NamNV
 * Desciption: Duyệt hóa đơn
 * 
 * @ajaxUrl: Đường dẫn đến action duyệt
 * @trId: Id của tr cần xử lý
 */
function confirmCheckBill(ajaxUrl, trId) {
	Form.extendedModals('ajax');
	
    var $modal 	= $('#ajax-modal');
    
    $('body').modalmanager('loading');
	$modal.load(ajaxUrl, null, function(){
  		$modal.modal();
  	});
    
    $modal.on('click', '.save-close', function(){
    	$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: $modal.find('form').serialize(),
			beforeSend: function() {
				$modal.modal('loading');
				$modal.find('.btn').addClass('disabled');
			},
			success: function(result) {
				var data = JSON.parse(result);
				
				$(trId + ' .checked').html(data['checked_format']);
				$(trId + ' .checked_id').html(data['checked_user']);
				if($(trId + ' .btn-check').hasClass('default')) {
					$(trId + ' .btn-check').removeClass('default').addClass('green').removeAttr('onclick').attr('title', 'Hóa đơn này đã được duyệt');
				}
				
				$modal.modal('hide');
			},
			error: function (request, status, error) {
				console.log(error);
			}
		});
	});
    
    $modal.on('shown.bs.modal', function (e) {
		reloadScript();
	});
	$modal.on('hidden.bs.modal', function (e) {
		$modal.html('');
	});
}

/**
 * Author: NamNV
 * Desciption: Thêm quyền truy cập
 * 
 * @row: Phần tử muốn thêm
 */
function insertPermission(row) {
	var ajaxUrl = moduleAdmin + '/' + controllerName + '/add';
	var id 		= jQuery(row).attr('data-id');
	
	jQuery.ajax({
		url: ajaxUrl,
		type: 'POST',
		data: {
			name: $('#row_' + id + ' #name').val(),
			module: $('#row_' + id + ' #module').val(),
			controller: $('#row_' + id + ' #controller').val(),
			action: $('#row_' + id + ' #action').val(),
			status: 1,
			ordering: 255
		},
		beforeSend: function() {
			pageLoading('loading', '.page-container');
		},
		success: function(result) {
			if(result == 'no-access') {
				xToastr('error', xMessage['no-access'], '');
			} else if(result == 'record-exists'){
				xToastr('error', xMessage['record-exists'], '');
			} else {
				$('#row_' + id).remove(),
				xToastr('success', xMessage['success'], '');
			}
			
			pageLoading('close', '.page-container');
		},
		complete: function(){
		}
	});
}

/**
 * Author: NamNV
 * Desciption: Xóa phần tử
 * 
 * @type: Phân loại thực hiện là nhiều hay một phần tử: item/multi
 * @option: Là Id nếu type = item, ngược lại all - xóa tất cả các phần tử được chọn
 */
function deleteItem(type, option) {
	var itemId = [];
	
	if(type == 'item') {
		jQuery('#tr_' + option + ' .checkboxes').attr('checked', true);
		jQuery('#tr_' + option + ' .checkboxes').parents('tr').addClass("active");
		
		itemId.push(option);
	} else {
		jQuery(formAdmin + ' .checkboxes').each(function () {
			checked = jQuery(this).is(":checked");
	        if (checked) {
	        	itemId.push(jQuery(this).val());
	        }
	    });
	}
	
	if(itemId.length > 0) {
		Form.extendedModals('confirm');
		var $modal 	= $('#confirm-modal');
		
		$modal.modal();
		$modal.find('.modal-body').html("Nếu xóa phần tử sẽ không thể khôi phục lại. Bạn có chắc chắn muốn xóa?");
	    $modal.on('click', '.confirm', function(){
	    	var actionForm  = $(formAdmin).attr('action').split('/');
	    	var ajaxUrl 	= '/' + actionForm[1] + '/' + actionForm[2] + '/delete';
	    	if(actionForm[0] != '') {
	    		ajaxUrl 	= '/' + actionForm[0] + '/' + actionForm[1] + '/delete';
	    	}
			submitForm(ajaxUrl);
		});
	    
		$modal.on('hidden.bs.modal', function (e) {
			$modal.html('');
		});
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}
/**
 * Author: NhanTD
 * Desciption: Cập nhật item
 *
 * @action: Là Tên action thực hiện update item.
 * @notify_text: Nội dung thông báo khi tiến hành cập nhật.
 */
function updateItem(action, notify_text) {
	var itemId = [];
	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});

	if(itemId.length > 0) {
		Form.extendedModals('confirm');
		var $modal 	= $('#confirm-modal');

		$modal.modal();
		$modal.find('.modal-body').html(notify_text);
	    $modal.on('click', '.confirm', function(){
	    	var actionForm  = $(formAdmin).attr('action').split('/');
	    	var ajaxUrl 	= '/' + actionForm[1] + '/' + actionForm[2] + '/' + action;
			submitForm(ajaxUrl);
		});

		$modal.on('hidden.bs.modal', function (e) {
			$modal.html('');
		});
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/**
 * Author: NamNV
 * Desciption: Sắp xếp list
 */
function changeOrdering() {
	var itemId = [];
	
	$(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
        if (checked) {
        	itemId.push(jQuery(this).val());
        }
    });
	
	if(itemId.length > 0) {
		var ajaxUrl = $(formAdmin).attr('action').replace('/filter', '/ordering');
		submitForm(ajaxUrl);
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/**
 * Author: NamNV
 * Desciption: Chuyển quyền liên hệ
 */
function contactChangeUser() {
	var itemId = [];
	
	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});
	
	if(itemId.length > 0) {
		var ajaxUrl = jQuery(formAdmin).attr('action').replace('/filter', '/change-user');
		submitForm(ajaxUrl);
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/**
 * Author: NamNV
 * Desciption: Chuyển quyền liên hệ
 */
function changeUser(action) {
	var itemId = [];

	jQuery(formAdmin + ' .checkboxes').each(function () {
		checked = jQuery(this).is(":checked");
		if (checked) {
			itemId.push(jQuery(this).val());
		}
	});

	if(itemId.length > 0) {
		var ajaxUrl = jQuery(formAdmin).attr('action').replace('/filter', '/'+action);
		submitForm(ajaxUrl);
	} else {
		xToastr('error', xMessage['no-checked'], '');
	}
}

/**
 * Author: NamNV
 * Desciption: Di chuyển Node
 */
function moveNode(id, type) {
	var ajaxUrl = moduleAdmin + '/' + controllerName + '/move';
	
	jQuery.ajax({
		url: ajaxUrl,
		type: 'POST',
		data: {
			'move-id': id,
			'move-type': type
		},
		beforeSend: function() {
			pageLoading('loading', '.page-container');
		},
		success: function(result) {
			if(result == 'no-access') {
				xToastr('error', xMessage['no-access'], '');
			} else if(result == 'record-exists'){
				xToastr('error', xMessage['record-exists'], '');
			} else {
				$('#row_' + id).remove(),
				xToastr('success', xMessage['success'], '');
			}
			
			pageLoading('close', '.page-container');
			location.reload();
		},
		complete: function(){
		}
	});
}

/**
 * Author: NamNV
 * Desciption: Submit form
 * 
 * @type: Loại: Lưu, Lưu & Mới,Lưu & Đóng
 */
function controlSubmitForm(type) {
	var itemId = [];
	if(type != undefined) {
		if(jQuery('input[name="control-action"]').size() > 0) {
			jQuery('input[name="control-action"]').val(type);
		} else {
			jQuery(formAdmin).append('<input type="hidden" name="control-action" value="'+ type +'">');
		}
	}
	
	var ajaxUrl = $(formAdmin).attr('action');
	console.log(ajaxUrl);
	pageLoading('loading', 'body');
	submitForm(ajaxUrl);
}

/**
 * Author: NamNV
 * Desciption: Submit form & Confirm
 * 
 * @type: Loại: Lưu, Lưu & Mới,Lưu & Đóng
 * @message: Nội dung thông báo xác nhận comfirm
 */
function controlSubmitFormConfirm(type, message) {
	Form.extendedModals('confirm');
	var $modal 	= $('#confirm-modal');
	
	$modal.modal();
	$modal.find('.modal-body').html(message);
    $modal.on('click', '.confirm', function(){
    	var itemId = [];
    	if(type != undefined) {
    		if(jQuery('input[name="control-action"]').size() > 0) {
    			jQuery('input[name="control-action"]').val(type);
    		} else {
    			jQuery(formAdmin).append('<input type="hidden" name="control-action" value="'+ type +'">');
    		}
    	}
    	
    	var ajaxUrl = $(formAdmin).attr('action');
    	submitForm(ajaxUrl);
	});
    
	$modal.on('hidden.bs.modal', function (e) {
		$modal.html('');
	});
}

/**
 * Author: NamNV
 * Desciption: Submit form & Stack
 * 
 * @type: Loại: Lưu, Lưu & Mới,Lưu & Đóng
 * @message: Nội dung thông báo xác nhận comfirm
 */
function controlSubmitFormStack(type, message) {
	Form.extendedModals('stack');
	var $modal 	= $('#stack-modal');
	
	$modal.modal();
	$modal.find('.control-label').html(message);
	$modal.on('click', '.save', function(){
		if($modal.find('input[name="input-stack"]').val() && $modal.find('input[name="input-stack"]').val() != '') {
			var itemId = [];
			if(type != undefined) {
				if(jQuery('input[name="control-action"]').size() > 0) {
					jQuery('input[name="control-action"]').val(type);
				} else {
					jQuery(formAdmin).append('<input type="hidden" name="control-action" value="'+ type +'">');
				}
			}
			
			jQuery(formAdmin).append('<input type="hidden" name="input-stack" value="'+ $modal.find('input[name="input-stack"]').val() +'">');
			
			var ajaxUrl = $(formAdmin).attr('action');
			submitForm(ajaxUrl);
		} else {
			xToastr('error', 'Vui lòng nhập ' + message, '');
		}
	});
	
	$modal.on('hidden.bs.modal', function (e) {
		$modal.html('');
	});
}


/**
 * Author: NamNV
 * Desciption: Check all list table
 */
function checkAll() {
	jQuery(formAdmin + ' .table .group-checkable').change(function () {
        var set = jQuery(this).attr("data-set");
        var checked = jQuery(this).is(":checked");
        jQuery(set).each(function () {
            if (checked) {
                $(this).attr("checked", true);
                $(this).parents('tr').addClass("active");
            } else {
                $(this).attr("checked", false);
                $(this).parents('tr').removeClass("active");
            }                    
        });
    });

    jQuery(formAdmin + ' .table tbody tr .checkboxes').change(function(){
         $(this).parents('tr').toggleClass("active");
    });
}

/**
 * Author: NamNV
 * Desciption: Chọn file upload với textbox
 */
function openFile(field, group) {
    window.KCFinder = {
        callBack: function(url) {
        	jQuery(formAdmin + ' input[name="'+ field +'"]').val(url);
        	jQuery(formAdmin + ' #view_'+ field).attr({'href': url}).css('display', 'inline-block');
        	jQuery(formAdmin + ' #remove_'+ field).css('display', 'inline-block');
            window.KCFinder = null;
        }
    };
    window.open('/public/scripts/kcfinder/browse.php?type?opener=ckeditor&type='+ group +'&langCode=vi', 'kcfinder_textbox',
        'status=0, toolbar=0, location=0, menubar=0, directories=0, ' +
        'resizable=1, scrollbars=0, width=1000, height=600'
    );
}

/**
 * Author: NamNV
 * Desciption: Xóa trắng ô file upload với textbox
 */
function removeFile(field) {
	jQuery(formAdmin + ' input[name="'+ field +'"]').val('');
	jQuery(formAdmin + ' #view_'+ field).attr({'href': ''}).css('display', 'none');
	jQuery(formAdmin + ' #remove_'+ field).css('display', 'none');
}

/**
 * Author: NamNV
 * Desciption: Hide/Show button up/down table-tree
 * Note: Developer
 */
function buttonListTree() {
	if(jQuery(formAdmin + ' .table-tree').size() > 0) {
		var elements = jQuery(formAdmin + ' .table-tree').find('.spinner-input');
		elements.each(function(index, el) {
			var levelCurrent 	= jQuery(this).attr('data-level');
			var valueCurrent 	= jQuery(this).val();
            var levelNext 		= elements.eq(index + 1).attr('data-level');
            var valueNext 		= elements.eq(index + 1).val();
            
            if(valueCurrent == 1) {
            	jQuery(this).parent().addClass('hide-up');
            }
            
            if((levelCurrent != levelNext) && (valueCurrent >= valueNext)) {
            	jQuery(this).parent().addClass('hide-down');
            }
		});
	}
}

/**
 * Author: NamNV
 * Desciption: Page loading
 */
function pageLoading(type, el) {
	if(!el) {
		el = 'body';
	}
	
	if(type == 'loading') {
		App.blockUI(el);
	} else if(type == 'close'){
		App.unblockUI(el);
	}
}

/**
 * Author: NamNV
 * Desciption: Cập nhật nội dung vào modal
 */
function modalMessage(modal, message) {
	var xhtml = '<div class="modal-header">' +
					'<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>' +
					'<h4 class="modal-title">Thông báo từ hệ thống</h4>' +
				'</div>' +
				'<div class="modal-body">'+ message +'</div>' +
				'<div class="modal-footer">' +
					'<button type="button" data-dismiss="modal" class="btn btn-default">Đóng</button>' +
				'</div>'; 
	$(modal).html(xhtml);
}

/**
 * Author: NamNV
 * Desciption: Tạo đường dẫn tính
 */
function createAlias(source, target) {
	var data = jQuery(source).val();
	if(!jQuery(target).val() || jQuery(target).val() == '' || jQuery('input[name="id"]').val() == '') {
		data= data.toLowerCase();
		
		data = data.replace(/à|ả|ã|á|ạ|ă|ằ|ẳ|ẵ|ắ|ặ|â|ầ|ẩ|ẫ|ấ|ậ/g,"a");
		data = data.replace(/è|ẻ|ẽ|é|ẹ|ê|ề|ể|ễ|ế|ệ/g,"e");
		data = data.replace(/ì|ỉ|ĩ|í|ị/g,"i");
		data = data.replace(/ò|ỏ|õ|ó|ọ|ô|ồ|ổ|ỗ|ố|ộ|ơ|ờ|ở|ỡ|ớ|ợ/g,"o");
		data = data.replace(/ù|ủ|ũ|ú|ụ|ư|ừ|ử|ữ|ứ|ự/g,"u");
		data = data.replace(/ỳ|ỷ|ỹ|ý/g,"y");
		data = data.replace(/đ/g,"d");
		data = data.replace(/!|@|\$|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'| |\"|\“|\”|\&|\#|\[|\]|~/g,"-");
		data = data.replace(/-+-/g,"-");
		data = data.replace(/^\-+|\-+$/g,"");
		
		jQuery(target).val(data);
	}
}


/**
 * Author: NamNV
 * Desciption: Toastr
 */
function xToastr(type, msg, title) {
	toastr.options = {
	  "closeButton": true,
	  "debug": false,
	  "positionClass": "toast-top-right",
	  "onclick": null,
	  "showDuration": 300,
	  "hideDuration": 300,
	  "timeOut": 4000,
	  "extendedTimeOut": 1000,
	  "showEasing": "swing",
	  "hideEasing": "linear",
	  "showMethod": "fadeIn",
	  "hideMethod": "fadeOut"
	}

    toastr[type](msg, title); // Wire up an event handler to a button in the toast, if it exists
}

/**
 * Author: NamNV
 * Desciption: Popup Preview Image/Video/Iframe
 * Source: fancyapps.com
 */
function fancyboxPreview(className) {
	jQuery("." + className).fancybox({
		openEffect	: 'elastic',
		closeEffect	: 'elastic'
	});
}

/**
 * Author: NamNV
 * Desciption: Chặn copy nội dung
 */
function noCopy() {
	$('body').css({
		'-webkit-touch-callout': 'none',
		'-webkit-user-select': 'none',
		'-moz-user-select': 'none',
		'-ms-user-select': 'none',
		'-o-user-select': 'none',
		'user-select': 'none'
	});
	document.onselectstart = new Function ("return false");
    if (window.sidebar){
        document.onmousedown = false;
        document.onclick = true;
    }
}

/**
 * Author: NamNV
 * Desciption: Chặn chuột phải
 */
function noRightMouse() {
	$(document).bind("contextmenu",function(e){
		e.preventDefault();
	});
}

/**
 * Author: NamNV
 * Desciption: sự kiện click tr vào table
 */
function trClick() {
	jQuery(".table-hover tr").click(function(){
		if (window.event.ctrlKey) {
			if(jQuery(this).hasClass('active')) {
				jQuery(this).removeClass('active');
			} else {
				jQuery(this).addClass('active');
			}
	    } else {
			jQuery(".table-hover tr").removeClass('active');
			jQuery(this).addClass('active');
	    }
	});
}

/**
 * @typeChart: Kiểu biểu đồ: column, line, bar
 */
function reportChart(arrChart, nameChart, typeChart, colorChart){
	Highcharts.setOptions({
		lang: {
			thousandsSep: ','
		}
	});
	var dataChart = arrChart;
	var w_window = $(window).width();
	if(colorChart == undefined) {
		colorChart = ['#2f7ed8', '#0d233a', '#d64e00', '#910000', '#1aadce', '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'];
	}
	switch (typeChart){
		case "column":
		case "bar":
			if(w_window < 900) {
				typeChart = "bar";
			}
			var myChart = Highcharts.chart(nameChart, {
				colors: colorChart,
		        chart: {
		            type: typeChart
		        },
		        title: { text: '' },
		        yAxis: { title: { text: '' } },
		        legend: {
		            enabled: true
		        },
		        plotOptions: {
		            series: {
		                borderWidth: 0,
		                dataLabels: {
		                    enabled: true,
	                    	formatter: function () {
	                    		return Highcharts.numberFormat(this.y,0);
	                    	}
		                },
	                    maxPointWidth: 50,
	                    groupPadding: typeChart == 'bar' ? 0.1 : 0.35
		            }
		        },
		        tooltip: {
		            pointFormat: '<b>{point.y}</b>'
		        },
		        xAxis: {
		            categories: dataChart.categories,
		        },
		        series: dataChart.series,
		    });
			if(typeChart == 'column') {
				myChart.setSize(null, 450, false);
			}
			if(typeChart == 'bar') {
				var heightColumn = (dataChart.series.length) * 40;
				if(dataChart.categories.length == 1) {
					heightColumn = (dataChart.series.length) * 60;
				}
				var heightChart = (dataChart.categories.length * heightColumn) > 300 ? dataChart.categories.length * heightColumn : 300;
				myChart.setSize(null, heightChart, false);
			}
			break;
		// case "pie":
		// 	var myChartPie =  Highcharts.chart(nameChart, {
		//         chart: {
		//             type: typeChart
		//         },
		//         title: {
		//             text: ''
		//         },
		//         tooltip: {
		//             pointFormat: '<b>{point.y:,.1f}</b>'
		//         },
		//         plotOptions: {
		//             pie: {
		//                 allowPointSelect: true,
		//                 cursor: 'pointer',
		//                 dataLabels: {
		//                     enabled: true,
		//                     format: '<b>{point.name}</b>: {point.percentage:.1f} %',
		//                     style: {
		//                         color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
		//                     }
		//                 }
		//             }
		//         },
		//         series: [{data : dataChart}]
		//     });
		// 	break;
		case "pie":
			// Highcharts.setOptions({
			//           colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
			//               return {
			//                   radialGradient: {
			//                       cx: 0.5,
			//                       cy: 0.3,
			//                       r: 0.7
			//                   },
			//                   stops: [
			//                       [0, color],
			//                       [1, Highcharts.Color(color).brighten(-0.3).get('rgba')] // darken
			//                   ]
			//               };
			//           })
			//       });

			// Build the chart
			Highcharts.chart(nameChart, {
				chart: {
					type: 'pie'
				},
				title: {
					text: ''
				},
				tooltip: {
					pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
				},
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: true,
							format: '<b>{point.name}</b>: {point.percentage:.1f} %',
							connectorColor: 'silver'
						}
					}
				},
				series: dataChart.series
			});
			break;
	}
}

/**
 * Author: NamNV
 * Desciption: Nạp lại các thư viện script khi sử dụng ajax, modal
 */
function reloadScript() {
	App.init();
  	Form.init();
}

/**
 * Author: NamNV
 * Desciption: Sự kiện chung toàn hệ thống
 */
function init() {
	$('input.date-picker').attr('autocomplete', 'off');
}

/**
 * Author: NhanTD
 * Desciption: Cố định thead của bảng
 */
function resize_col_table(target, height = 0){
	var h_table = $('.table-fixed-head').height();
	var h_thead = $('.table-fixed-head thead').height();
	var h_content = h_table - height - h_thead -17;
	$(target+' .table-fixed-head tbody').css({'height': h_content + 'px'});

	$( window ).resize(function() {
		var h_table = $('.table-fixed-head').height();
		var h_thead = $('.table-fixed-head thead').height();
		var h_content = h_table - height - h_thead - 17;
		$(target+' .table-fixed-head tbody').css({'height': h_content + 'px'});
	});

	$(target+' table thead tr th').each(function(index){
		var width_col = $(this).attr('width');
		$(this).attr('style',"min-width:"+width_col+"px; max-width: "+width_col+"px;");
		$(target+' table tbody tr td:nth-child('+(index+1)+')').attr('style',"min-width:"+width_col+"px; max-width: "+width_col+"px;");
	})
}

function show_menu_report(){
	$('#show_menu_report').click(function(){
		$(".page-sidebar-wrapper").slideToggle();
	})
}

/**
 * Author: NhanTD
 * Desciption: chuyển từ text sang định dạng tiền
 */
function format_to_money(){
	$('input.money').on('keyup',function(e){
		var value = this.value.replace(/[^0-9,]/g, '').replace(/(\..*)\./g, '$1');
		$(this).val(value)

		var value = $(this).val().replace(/[.,*+?^${}()|[\]\\]/g, '');
		var formatted = '';
		var length = value.length;
		if(length > 3){
			var number_sep = parseInt(length / 3);
			for(var i = 0; i <= number_sep; i++){
				if(i == 0){
					var str = value.substring(0, length % 3);
					if(str != '') formatted += str + ',';
				}
				else{
					formatted += value.substring((length % 3) + (i-1)*3, (length % 3) + (i*3)) + ',';
				}
			}
			formatted = formatted.substring(0, formatted.length-1)
			$(this).val(formatted)
		}
	});
}
/**
 * Author: NhanTD
 * Desciption: xóa bỏ ký tự đặc biệt trong chuỗi
 */
function format_number_to_data(param){
	return param.replace(/[.,*+?^${}()|[\]\\]/g, '');
}



/**
 * Author: NamNV
 * Desciption: Nạp tất cả các function cần khởi tạo khi chạy ứng dụng
 */
jQuery(document).ready(function() {
	init();
	//noCopy();
	//noRightMouse();
	trClick();
	keywordEnter();
	checkAll();
	show_menu_report();
	format_to_money();

	$('.guarantee input[type="checkbox"]').on('click', function() {
		$('.code-old').toggle(this.checked);
	})
});


/**
 * Author: KhaiNQ
 * Desciption: Format định dạng tiền tệ
 */
function formatNumber(num) {
	return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

/**
 * Author: KhaiNQ
 * Desciption: Bỏ format định dạng tiền tệ
 */
function unFormatNumber(num) {
	return num.toString().replace(/,/g, '');
}

// function showHiddenToggle(name,time){
// 	$(name).slideToggle(time);
// }
