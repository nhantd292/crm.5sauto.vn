<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/respond.min.js';?>"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/excanvas.min.js';?>"></script> 
<![endif]-->
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/jquery-1.12.4.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/bootstrap/js/bootstrap.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/jquery-migrate-1.2.1.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/jquery-ui/jquery-ui-1.10.3.custom.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/bootstrap-hover-dropdown/twitter-bootstrap-hover-dropdown.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/jquery.blockui.min.js';?>" type="text/javascript"></script>
<!-- END CORE PLUGINS -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/select2/select2.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/select2/select2_locale_vi.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/simplebar/simplebar.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/bootstrap-toastr/toastr.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/bootbox/bootbox.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/fancybox/source/jquery.fancybox.pack.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/jquery-inputmask/jquery.inputmask.bundle.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/bootstrap-datepicker/js/bootstrap-datepicker.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/bootstrap-colorpicker/js/bootstrap-colorpicker.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/numeric.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/bootstrap-modal/js/bootstrap-modalmanager.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/bootstrap-modal/js/bootstrap-modal.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/highcharts/highcharts.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/jquery.form.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlPlugin'] . '/moment.min.js';?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlJs'] . '/kendo/kendo.ui.core.min.js';?>" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->

<!-- BEGIN FORM PLUGINS -->
<script src="<?php echo URL_SCRIPTS . '/ckeditor/ckeditor.js';?>" type="text/javascript"></script>
<!-- END FORM PLUGINS -->

<script src="<?php echo $this->arrParams['template']['urlJs'] . '/define.js?v='. date('YmdHi');?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlJs'] . '/app.js?v='. date('YmdHi');?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlJs'] . '/form.js?v='. date('YmdHi');?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlJs'] . '/me.js?v='. date('YmdHi');?>" type="text/javascript"></script>
<script src="<?php echo $this->arrParams['template']['urlJs'] . '/notify.js?v='. date('YmdHi');?>" type="text/javascript"></script>

<script type="text/javascript">
    jQuery(document).ready(function() {
       App.init();
       Form.init();
    });

    // Tìm kiếm
    $('#adminFormFilter .select2').change(function() {
		$('#adminFormFilter').submit();
    });
    $('#adminFormFilter input[name="filter_reset"]').click(function() {
		$('#adminFormFilter .form-control').val('');
    });
    $('#pagination_option').change(function () {
    	$('#adminFormFilter').submit();
	});
    
    /* Xử lý active menu */
    var current_url = '<?php echo $_SERVER['REQUEST_URI'];?>';
    $('.hor-menu .nav li a').each(function() {
        var url = $(this).attr('href');
        if(current_url != '/') {
            if(url.indexOf(current_url) !== -1) {
                $(this).parents('li').addClass('active');
            }
        } else {
        	$('a[href="<?php echo $this->url('routeAdmin/default', array('controller' => 'index', 'action' => 'index'));?>"]').parents('li').addClass('active');
        }
    })
    
	//Tính khoảng cách chiều cao phần nội dung
	function resizeDocument() {
        var h_window = $(window).height();
        var h_content = h_window - $('.header.navbar').outerHeight() - $('.page-content-wrapper .page-control').outerHeight() - $('.page-content-wrapper .alert').outerHeight() - $('.page-content-wrapper .page-filter').outerHeight() - $('.paginations').outerHeight() - 10;
        $('#table-manager .table-scrollable').css({'height': h_content + 'px'});
        $( window ).resize(function() {
        	var h_window = $(window).height();
        	var h_content = h_window - $('.header.navbar').outerHeight() - $('.page-control').outerHeight() - $('.page-filter').outerHeight() - $('.paginations').outerHeight() - 10;
        	$('#table-manager .table-scrollable').css({'height': h_content + 'px'});
        });
    }
    if($(window).width() > 900) {
        resizeDocument();
        $(window).resize(function() {
            resizeDocument()
        });
    }
    
    /* Kiểm tra liên hệ cần chăm sóc lại trong ngày hôm nay */
    $.ajax({
		url: '<?php echo $this->url('routeAdmin/default', array('controller' => 'api', 'action' => 'contact-history-return'));?>',
		type: 'POST',
		data: {},
		beforeSend: function() {
		},
		success: function(result) {
			if(result > 0) {
			    $('#notification_history_return .badge').text(result);
			    $('#notification_history_return').removeClass('hidden');
			}
		}
	});

    /* Kiểm tra liên hệ chưa được chăm sóc */
    $.ajax({
		url: '<?php echo $this->url('routeAdmin/default', array('controller' => 'api', 'action' => 'contact-history-status'));?>',
		type: 'POST',
		data: {},
		beforeSend: function() {
		},
		success: function(result) {
			if(result > 0) {
			    $('#notification_history_status .badge').text(result);
			    $('#notification_history_status').removeClass('hidden');
			}
		}
	});

    load_notifi('#notification_contract_false', '<?php echo $this->url('routeAdmin/default', array('controller' => 'api', 'action' => 'contract-notifi-false'));?>');


    /* Kiểm tra có đơn hàng phải duyệt */
    $.ajax({
		url: '<?php echo $this->url('routeAdmin/default', array('controller' => 'api', 'action' => 'pending'));?>',
		type: 'POST',
		data: {},
		beforeSend: function() {
		},
		success: function(result) {
			if(result > 0) {
			    $('.notification_pending').text(result);
			    $('.notification_pending').removeClass('hidden');
			}
		}
	});
</script>
<!-- END JAVASCRIPTS -->

<?php echo $this->headScript();?>