var Form = function () {

    var handleBootstrapSwitch = function() {
        $('.radio1').on('switch-change', function () {
            $('.radio1').bootstrapSwitch('toggleRadioState');
        });
        
        // or
        $('.radio1').on('switch-change', function () {
            $('.radio1').bootstrapSwitch('toggleRadioStateAllowUncheck');
        });

        // or
        $('.radio1').on('switch-change', function () {
            $('.radio1').bootstrapSwitch('toggleRadioStateAllowUncheck', false);
        });
    }

    var handleBootstrapTouchSpin = function() {

        $("#touchspin_demo1").TouchSpin({
            inputGroupClass: 'input-medium',            
            spinUpClass: 'green',
            spinDownClass: 'green',
            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
            prefix: '$'
        }); 
        
        $("#touchspin_demo2").TouchSpin({
            inputGroupClass: 'input-medium',
            spinUpClass: 'blue',
            spinDownClass: 'blue',
            min: 0,
            max: 100,
            step: 0.1,
            decimals: 2,
            boostat: 5,
            maxboostedstep: 10,
            postfix: '%'
        });         
    }

    var handleBootstrapMaxlength = function() {
        $('#maxlength_defaultconfig').maxlength({
            limitReachedClass: "label label-danger",
        })
    
        $('#maxlength_thresholdconfig').maxlength({
            limitReachedClass: "label label-danger",
            threshold: 20
        });

        $('#maxlength_alloptions').maxlength({
            alwaysShow: true,
            warningClass: "label label-success",
            limitReachedClass: "label label-danger",
            separator: ' out of ',
            preText: 'You typed ',
            postText: ' chars available.',
            validate: true
        });

        $('#maxlength_textarea').maxlength({
            limitReachedClass: "label label-danger",
            alwaysShow: true
        });

        $('#maxlength_placement').maxlength({
            limitReachedClass: "label label-danger",
            alwaysShow: true,
            placement: App.isRTL() ? 'top-right' : 'top-left'
        });
    }

    var handleSpinners = function () {
        $('#spinner1').spinner();
        $('#spinner2').spinner({disabled: true});
        $('#spinner3').spinner({value:0, min: 0, max: 10});
        $('#spinner4').spinner({value:0, step: 5, min: 0, max: 200});
    }

    var handleWysihtml5 = function () {
        if (!jQuery().wysihtml5) {
            return;
        }

        if ($('.wysihtml5').size() > 0) {
            $('.wysihtml5').wysihtml5({
                "stylesheets": ["assets/plugins/bootstrap-wysihtml5/wysiwyg-color.css"]
            });
        }
    }

    var handleTagsInput = function () {
        if (!jQuery().tagsInput) {
            return;
        }
        $('#tags_1').tagsInput({
            width: 'auto',
            'onAddTag': function () {
                //alert(1);
            },
        });
        $('#tags_2').tagsInput({
            width: 300
        });
    }

    var handleDatePickers = function () {
    	$.fn.datepicker.dates.en = {
			days: ["Chủ nhật", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7", "Chủ nhật"],
			daysShort: ["CN", "T2", "T3", "T4", "T5", "T6", "T7", "CN"],
			daysMin: ["CN", "T2", "T3", "T4", "T5", "T6", "T7", "CN"],
			months: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Thang 9", "Tháng 10", "Tháng 11", "Tháng 12"],
			monthsShort: ["Th.1", "Th.2", "Th.3", "Th.4", "Th.5", "Th.6", "Th.7", "Th.8", "Th.9", "Th.10", "Th.11", "Th.12"],
			today: "Hôm nay",
			clear: "Xóa"
		};
    	
        if (jQuery().datepicker) {
            $('.date-picker').datepicker({
                rtl: App.isRTL(),
        		weekStart: 1,
                autoclose: true,
                format: 'dd/mm/yyyy'
            });
            $('.date-picker-default').datepicker({
                rtl: App.isRTL(),
        		weekStart: 1,
                autoclose: true,
                format: 'yyyy-mm-dd'
            });
            $('.date-picker-month').datepicker({
                rtl: App.isRTL(),
                viewMode: "months", 
                minViewMode: "months",
                autoclose: true,
                format: 'mm/yyyy'
            });
            $('body').removeClass("modal-open"); // fix bug when inline picker is used in modal
        }
    }

    var handleTimePickers = function () {

        if (jQuery().timepicker) {
            $('.timepicker-default').timepicker({
                autoclose: true
            });
            $('.timepicker-24').timepicker({
                autoclose: true,
                minuteStep: 1,
                showSeconds: true,
                showMeridian: false
            });
        }
    }

    var handleDateRangePickers = function () {
        if (!jQuery().daterangepicker) {
            return;
        }

        $('#defaultrange').daterangepicker({
                opens: (App.isRTL() ? 'left' : 'right'),
                format: 'MM/DD/YYYY',
                separator: ' to ',
                startDate: moment().subtract('days', 29),
                endDate: moment(),
                minDate: '01/01/2012',
                maxDate: '12/31/2014',
            },
            function (start, end) {
                console.log("Callback has been called!");
                $('#defaultrange input').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }
        );        

        $('#reportrange').daterangepicker({
                opens: (App.isRTL() ? 'left' : 'right'),
                startDate: moment().subtract('days', 29),
                endDate: moment(),
                minDate: '01/01/2012',
                maxDate: '12/31/2014',
                dateLimit: {
                    days: 60
                },
                showDropdowns: true,
                showWeekNumbers: true,
                timePicker: false,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                },
                buttonClasses: ['btn'],
                applyClass: 'green',
                cancelClass: 'default',
                format: 'MM/DD/YYYY',
                separator: ' to ',
                locale: {
                    applyLabel: 'Apply',
                    fromLabel: 'From',
                    toLabel: 'To',
                    customRangeLabel: 'Custom Range',
                    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    firstDay: 1
                }
            },
            function (start, end) {
                console.log("Callback has been called!");
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }
        );
        //Set the initial state of the picker label
        $('#reportrange span').html(moment().subtract('days', 29).format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
    }

    var handleDatetimePicker = function () {

        $(".form_datetime").datetimepicker({
            autoclose: true,
            isRTL: App.isRTL(),
            format: "dd MM yyyy - hh:ii",
            pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left")
        });

        $(".form_advance_datetime").datetimepicker({
            isRTL: App.isRTL(),
            format: "dd MM yyyy - hh:ii",
            autoclose: true,
            todayBtn: true,
            startDate: "2013-02-14 10:00",
            pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left"),
            minuteStep: 10
        });

        $(".form_meridian_datetime").datetimepicker({
            isRTL: App.isRTL(),
            format: "dd MM yyyy - HH:ii P",
            showMeridian: true,
            autoclose: true,
            pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left"),
            todayBtn: true
        });

        $('body').removeClass("modal-open"); // fix bug when inline picker is used in modal
    }

    var handleClockfaceTimePickers = function () {

        if (!jQuery().clockface) {
            return;
        }

        $('.clockface_1').clockface();

        $('#clockface_2').clockface({
            format: 'HH:mm',
            trigger: 'manual'
        });

        $('#clockface_2_toggle').click(function (e) {
            e.stopPropagation();
            $('#clockface_2').clockface('toggle');
        });

        $('#clockface_2_modal').clockface({
            format: 'HH:mm',
            trigger: 'manual'
        });

        $('#clockface_2_modal_toggle').click(function (e) {
            e.stopPropagation();
            $('#clockface_2_modal').clockface('toggle');
        });

        $('.clockface_3').clockface({
            format: 'H:mm'
        }).clockface('show', '14:30');
    }

    var handleColorPicker = function () {
        if (!jQuery().colorpicker) {
            return;
        }
        $('.colorpicker-default').colorpicker({
            format: 'hex'
        });
        $('.colorpicker-rgba').colorpicker();
    }

    var handleSelect2 = function () {

        $('#select2_sample1').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        $('#select2_sample2').select2({
            placeholder: "Select a State",
            allowClear: true
        });

        $("#select2_sample3").select2({
            placeholder: "Select...",
            allowClear: true,
            minimumInputLength: 1,
            query: function (query) {
                var data = {
                    results: []
                }, i, j, s;
                for (i = 1; i < 5; i++) {
                    s = "";
                    for (j = 0; j < i; j++) {
                        s = s + query.term;
                    }
                    data.results.push({
                        id: query.term + i,
                        text: s
                    });
                }
                query.callback(data);
            }
        });

        function format(state) {
            if (!state.id) return state.text; // optgroup
            return "<img class='flag' src='assets/img/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
        }
        $("#select2_sample4").select2({
            placeholder: "Select a Country",
            allowClear: true,
            formatResult: format,
            formatSelection: format,
            escapeMarkup: function (m) {
                return m;
            }
        });

        $("#select2_sample5").select2({
            tags: ["red", "green", "blue", "yellow", "pink"]
        });


        function movieFormatResult(movie) {
            var markup = "<table class='movie-result'><tr>";
            if (movie.posters !== undefined && movie.posters.thumbnail !== undefined) {
                markup += "<td valign='top'><img src='" + movie.posters.thumbnail + "'/></td>";
            }
            markup += "<td valign='top'><h5>" + movie.title + "</h5>";
            if (movie.critics_consensus !== undefined) {
                markup += "<div class='movie-synopsis'>" + movie.critics_consensus + "</div>";
            } else if (movie.synopsis !== undefined) {
                markup += "<div class='movie-synopsis'>" + movie.synopsis + "</div>";
            }
            markup += "</td></tr></table>"
            return markup;
        }

        function movieFormatSelection(movie) {
            return movie.title;
        }

        $("#select2_sample6").select2({
            placeholder: "Search for a movie",
            minimumInputLength: 1,
            ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                url: "http://api.rottentomatoes.com/api/public/v1.0/movies.json",
                dataType: 'jsonp',
                data: function (term, page) {
                    return {
                        q: term, // search term
                        page_limit: 10,
                        apikey: "ju6z9mjyajq2djue3gbvv26t" // please do not use so this example keeps working
                    };
                },
                results: function (data, page) { // parse the results into the format expected by Select2.
                    // since we are using custom formatting functions we do not need to alter remote JSON data
                    return {
                        results: data.movies
                    };
                }
            },
            initSelection: function (element, callback) {
                // the input tag has a value attribute preloaded that points to a preselected movie's id
                // this function resolves that id attribute to an object that select2 can render
                // using its formatResult renderer - that way the movie name is shown preselected
                var id = $(element).val();
                if (id !== "") {
                    $.ajax("http://api.rottentomatoes.com/api/public/v1.0/movies/" + id + ".json", {
                        data: {
                            apikey: "ju6z9mjyajq2djue3gbvv26t"
                        },
                        dataType: "jsonp"
                    }).done(function (data) {
                        callback(data);
                    });
                }
            },
            formatResult: movieFormatResult, // omitted for brevity, see the source of this page
            formatSelection: movieFormatSelection, // omitted for brevity, see the source of this page
            dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
            escapeMarkup: function (m) {
                return m;
            } // we do not want to escape markup since we are displaying html in results
        });
    }

    var handleSelect2Modal = function () {
    	$('#select2-drop-mask').remove();
    	$('.select2-drop-active').remove();
    	$('.select2-hidden-accessible').html('');
        $('select.select2_basic').select2({
            placeholder: "- Chọn -",
            allowClear: true
        });

        $("input.select2_advance").select2({
        	placeholder: "- Chọn -",
        	ajax: {
        		quietMillis: 300,
        		transport: function (params) {
        			var attrData = $(this).data();
        			var dataWhere = {};
        			$.each(attrData, function(key, value) {
        			    var w = key.split('where_');
        			    if(w[0] == '') {
        			    	dataWhere[w[1]] = value;
        			    }
        			});
        			
        			var module = $(this).attr('data-module') ? $(this).attr('data-module') : moduleAdmin;
        			var controller = $(this).attr('data-controller') ? $(this).attr('data-controller') : 'api';
        			var action = $(this).attr('data-action') ? $(this).attr('data-action') : 'select';
        			
        			params['data']['data-where'] = dataWhere ? dataWhere : '';
        			params['data']['data-parent'] = $(this).attr('data-parent') ? $(this).attr('data-parent') : '';
        			params['data']['data-parent-field'] = $(this).attr('data-parent-field') ? $(this).attr('data-parent-field') : '';
        			params['data']['data-table'] = $(this).attr('data-table') ? $(this).attr('data-table') : '';
        			params['data']['data-id'] = $(this).attr('data-id') ? $(this).attr('data-id') : 'id';
        			params['data']['data-text'] = $(this).attr('data-text') ? $(this).attr('data-text') : 'name';
        			params['data']['data-order'] = $(this).attr('data-order') ? $(this).attr('data-order') : '';
        			params['data']['data-db'] = $(this).attr('data-db') ? $(this).attr('data-db') : '';
        			params['url'] = module +'/'+ controller +'/'+ action;

        			return $.ajax(params);
    		    },
        	    type: 'POST',
        	    dataType: 'json',
        	    data: function (term, page) {
        	    	return {
        	    		term: term,
        	    		page: page
        	    	};
        	    },
        	    results: function (data, page) {
        	    	return {
        	    		results: data
        	    	};
        	    },
        	    cache: true
        	},
        	initSelection: function(element, callback) {
                var id = $(element).val();
                var text = $(element).attr('data-text-label');
                var params = {};
                	params['data-parent'] = $(element).attr('data-parent') ? $(element).attr('data-parent') : '';
                	params['data-parent-field'] = $(element).attr('data-parent-field') ? $(element).attr('data-parent-field') : '';
	    			params['data-table'] = $(element).attr('data-table') ? $(element).attr('data-table') : '';
	    			params['data-id'] = $(element).attr('data-id') ? $(element).attr('data-id') : 'id';
	    			params['data-text'] = $(element).attr('data-text') ? $(element).attr('data-text') : 'name';

    			var module = $(element).attr('data-module') ? $(element).attr('data-module') : moduleAdmin;
    			var controller = $(element).attr('data-controller') ? $(element).attr('data-controller') : 'api';
    			var action = $(element).attr('data-action') ? $(element).attr('data-action') : 'select';
	    			
    			if(text) {
    				callback({'id': id, 'text': text});
    			} else if (id !== "") {
                    $.ajax(module +"/"+ controller +"/"+ action +"/id/"+ id, {
                    	type: 'POST',
                        dataType: "json",
                        data: params
                    }).done(function(data) { 
                    	callback(data);
                	});
                }
            },
        	formatResult: function(data) {
        		var markup = "<div>" + data.text + '</div>';
                return markup;
        	},
            formatSelection: function(data) {
            	return data.text;
            },
			escapeMarkup: function (markup) { return markup; },
        });
        
        $(".select2_user").select2({
        	placeholder: "- Chọn -",
        	minimumInputLength: 1,
            query: function (query) {
                var data = {results: []}, i, j, s;
                for (i = 1; i < 5; i++) {
                    s = "";
                    for (j = 0; j < i; j++) {s = s + query.term;}
                    data.results.push({id: query.term + i, text: s});
                }
                query.callback(data);
            },
        	formatResult: function(data) {
        		var markup = "<div>a" + data.text + '</div>';
                return markup;
        	},
            formatSelection: function(data) {
            	return data.text;
            },
			escapeMarkup: function (markup) { return markup; },
        });
    }

    var handleMultiSelect = function () {
        $('#my_multi_select1').multiSelect();
        $('#my_multi_select2').multiSelect({
            selectableOptgroup: true
        });

        $('#my_multi_select3').multiSelect({
            selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
            selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
            afterInit: function (ms) {
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function (e) {
                        if (e.which === 40) {
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function (e) {
                        if (e.which == 40) {
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },
            afterSelect: function () {
                this.qs1.cache();
                this.qs2.cache();
            },
            afterDeselect: function () {
                this.qs1.cache();
                this.qs2.cache();
            }
        });
    }

    var handleInputMasks = function () {
    	$('.mask_percent').autoNumeric("init",{
    		vMin: 0,
        	vMax: 100,
        	mDec: 0,
            aSep: '.',
            aDec: ',', 
        });
        
        $('.mask_currency').autoNumeric("init",{
        	mDec: 0,
            aSep: ',',
            aDec: '.', 
        });
        
        $(".mask_number").inputmask({
            "mask": "9",
            "repeat": 11,
            "greedy": false
        });
        
        $(".mask_integer").inputmask({
            "mask": "9",
            "repeat": 20,
            "greedy": false
        });
        
        $(".mask_integer").keypress(function (e){
        	var value = $(this).val();
			if(value != '') {
				if(value.substr(0, 1) == 0) {
					value = parseInt(value.substr(1));
				}
				$(this).val(value);
			}
    	});
        
        $(".mask_integer").blur(function(){
        	var value = $(this).val();
			if(value != '') {
				if(value.substr(0, 1) == 0) {
					value = parseInt(value.substr(1));
				}
				$(this).val(value);
			}
        });
        
        $(".mask_phone").blur(function(){
        	var value = $(this).val();
        	value = value.replace(/[^0-9]+/g,"");
        	/*if(value.substring(0, 1) != '0') {
        		value = '0' + value;
        	}*/
			$(this).val(value);
        });
        
        $(".mask_phone").keypress(function(e){
        	var charCode = (e.which) ? e.which : e.keyCode;
    		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    			return false;
    	  	}
        });
        
        $(".mask_double").keypress(function (e){
    		var charCode = (e.which) ? e.which : e.keyCode;
    		
    		if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
    			return false;
    	  	}
    	});
        
        $(".mask_bill").blur(function(){
        	var value = $(this).val().trim();
        	if(value.substring(0,1) == '0') {
        		$(this).val(value.substring(1).trim());
        	} else {
        		$(this).val(value);
        	}
        });
    }

    var handleIPAddressInput = function () {
        $('#input_ipv4').ipAddress();
        $('#input_ipv6').ipAddress({
            v: 6
        });
    }

    var handlePasswordStrengthChecker = function () {
        var initialized = false;
        var input = $("#password_strength");

        input.keydown(function () {
            if (initialized === false) {
                // set base options
                input.pwstrength({
                    raisePower: 1.4,
                    minChar: 8,
                    verdicts: ["Weak", "Normal", "Medium", "Strong", "Very Strong"],
                    scores: [17, 26, 40, 50, 60]
                });

                // add your own rule to calculate the password strength
                input.pwstrength("addRule", "demoRule", function (options, word, score) {
                    return word.match(/[a-z].[0-9]/) && score;
                }, 10, true);

                // set as initialized 
                initialized = true;
            }
        });
    }

    var handleUsernameAvailabilityChecker1 = function () {
        var input = $("#username1_input");

        $("#username1_checker").click(function (e) {
            var pop = $(this);

            if (input.val() === "") {
                input.closest('.form-group').removeClass('has-success').addClass('has-error');

                pop.popover('destroy');
                pop.popover({
                    'placement': (App.isRTL() ? 'left' : 'right'),
                    'html': true,
                    'container': 'body',
                    'content': 'Please enter a username to check its availability.',
                });
                // add error class to the popover
                pop.data('bs.popover').tip().addClass('error');
                // set last poped popover to be closed on click(see App.js => handlePopovers function)     
                App.setLastPopedPopover(pop);
                pop.popover('show');
                e.stopPropagation(); // prevent closing the popover

                return;
            }

            var btn = $(this);

            btn.attr('disabled', true);

            input.attr("readonly", true).
            attr("disabled", true).
            addClass("spinner");

            $.post('demo/username_checker.php', {
                username: input.val()
            }, function (res) {
                btn.attr('disabled', false);

                input.attr("readonly", false).
                attr("disabled", false).
                removeClass("spinner");

                if (res.status == 'OK') {
                    input.closest('.form-group').removeClass('has-error').addClass('has-success');

                    pop.popover('destroy');
                    pop.popover({
                        'html': true,
                        'placement': (App.isRTL() ? 'left' : 'right'),
                        'container': 'body',
                        'content': res.message,
                    });
                    pop.popover('show');
                    pop.data('bs.popover').tip().removeClass('error').addClass('success');
                } else {
                    input.closest('.form-group').removeClass('has-success').addClass('has-error');

                    pop.popover('destroy');
                    pop.popover({
                        'html': true,
                        'placement': (App.isRTL() ? 'left' : 'right'),
                        'container': 'body',
                        'content': res.message,
                    });
                    pop.popover('show');
                    pop.data('bs.popover').tip().removeClass('success').addClass('error');
                    App.setLastPopedPopover(pop);
                }

            }, 'json');

        });
    }

    var handleUsernameAvailabilityChecker2 = function () {
        $("#username2_input").change(function () {
            var input = $(this);

            if (input.val() === "") {
                return;
            }

            input.attr("readonly", true).
            attr("disabled", true).
            addClass("spinner");

            $.post('demo/username_checker.php', {
                username: input.val()
            }, function (res) {
                input.attr("readonly", false).
                attr("disabled", false).
                removeClass("spinner");

                // change popover font color based on the result
                if (res.status == 'OK') {
                    input.closest('.form-group').removeClass('has-error').addClass('has-success');
                    $('.icon-exclamation-sign', input.closest('.form-group')).remove();
                    input.before('<i class="icon-ok"></i>');
                    input.data('bs.popover').tip().removeClass('error').addClass('success');
                } else {
                    input.closest('.form-group').removeClass('has-success').addClass('has-error');
                    $('.icon-ok', input.closest('.form-group')).remove();
                    input.before('<i class="icon-exclamation-sign"></i>');

                    input.popover('destroy');
                    input.popover({
                        'html': true,
                        'placement': (App.isRTL() ? 'left' : 'right'),
                        'container': 'body',
                        'content': res.message,
                    });
                    input.popover('show');
                    input.data('bs.popover').tip().removeClass('success').addClass('error');

                    App.setLastPopedPopover(input);
                }

            }, 'json');

        });
    }
    
    var extendedModals = function (option) {
    	// Cấu hình các tham số mặc định
    	$.fn.modal.defaults.spinner = $.fn.modalmanager.defaults.spinner = '<div class="loader"></div>';
        $.fn.modalmanager.defaults.resize = true;
        
    	switch(option) {
	    	case 'alert':
		    	$('#alert-modal').remove();
	        	var xhtml = '<div id="alert-modal" class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="true">' +
			                  	'<div class="modal-header">' +
				                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
				                    '<h4 class="modal-title">Thông báo từ hệ thống</h4>' +
			                    '</div>' +
			                    '<div class="modal-body"></div>' +
			                    '<div class="modal-footer">' +
				                    '<a href="javascript:;" data-dismiss="modal" class="btn btn-default">Đóng</a>' +
			                    '</div>' +
			                '</div>';
	        	$('body').append(xhtml);
		        break;
		    case 'confirm':
		    	$('#confirm-modal').remove();
	        	var xhtml = '<div id="confirm-modal" class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="true">' +
			                  	'<div class="modal-header">' +
				                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
				                    '<h4 class="modal-title">Thông báo từ hệ thống</h4>' +
			                    '</div>' +
			                    '<div class="modal-body"></div>' +
			                    '<div class="modal-footer">' +
				                    '<a href="javascript:;" data-dismiss="modal" class="btn btn-default">Hủy</a>' +
				                    '<a href="javascript:;" class="btn btn-primary confirm">Đồng ý</a>' +
			                    '</div>' +
			                '</div>';
	        	$('body').append(xhtml);
		        break;
		    case 'stack':
		    	$('#stack-modal').remove();
		    	var xhtml = '<div id="stack-modal" class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="true">' +
						    	'<div class="modal-header">' +
							    	'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
							    	'<h4 class="modal-title">Thông báo từ hệ thống</h4>' +
						    	'</div>' +
						    	'<div class="modal-body">' +
							    	'<div class="form-group">' +
							    		'<label class="control-label">Nội dung xác nhận</label>' +
							    		'<input type="text" name="input-stack" class="form-control">' +
		    						'</div>' +
				    			'</div>' +
						    	'<div class="modal-footer">' +
							    	'<a href="javascript:;" data-dismiss="modal" class="btn btn-default">Hủy</a>' +
							    	'<a href="javascript:;" class="btn btn-primary save">Lưu</a>' +
						    	'</div>' +
					    	'</div>';
		    	$('body').append(xhtml);
		    	break;
		    case 'ajax':
		    	$('#ajax-modal').remove();
		    	$('body').append('<div id="ajax-modal" class="modal container custom-modal fade" tabindex="-1" data-backdrop="static" data-keyboard="true"></div>');
		        break;
		}
    };

    return {
        //main function to initiate the module
        init: function () {
        	handleInputMasks();
        	handleSelect2Modal();
        	handleDatePickers();
        	handleColorPicker();
            /*handleBootstrapSwitch();
            handleBootstrapTouchSpin();
            handleBootstrapMaxlength();
            handleSpinners();
            handleWysihtml5();
            handleTagsInput();
            handleTimePickers();
            handleDatetimePicker();
            handleDateRangePickers();
            handleClockfaceTimePickers();
            handleSelect2();
            handleIPAddressInput();
            handleMultiSelect();
            handlePasswordStrengthChecker();
            handleUsernameAvailabilityChecker1();
            handleUsernameAvailabilityChecker2();*/
        },
        extendedModals: function(option) {
        	extendedModals(option);
        }
    };

}();