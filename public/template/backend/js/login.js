var Login = function () {

	var handleLogin = function() {
        $('.login-form input').keypress(function (e) {
            if (e.which == 13) {
            	$('.login-form').submit();
                return false;
            }
        });
	}

	var handleForgetPassword = function () {
        $('.forget-form input').keypress(function (e) {
            if (e.which == 13) {
            	$('.forget-form').submit();
                return false;
            }
        });

        jQuery('#forget-password').click(function () {
            jQuery('.login-form').hide();
            jQuery('.forget-form').show();
        });

        jQuery('#back-btn').click(function () {
            jQuery('.login-form').show();
            jQuery('.forget-form').hide();
        });
	}

	var handleRegister = function () {
		
	}
    
    return {
        //main function to initiate the module
        init: function () {
            handleLogin();
            handleForgetPassword();
            handleRegister();
        }
    };
}();