/**
 * Author: NTD
 * Desciption: Load url action
 */
function load_notify(parent, url, params) {
	params = params.split(',');
	jQuery.ajax({
		url: url,
		type: 'POST',
		data: {
				type: params[0],
				load: params[1],
			},
		success: function(result) {
			$(parent).html(result);
			Form.init();
		}
	});
}

function init() {
	$('input.date-picker').attr('autocomplete', 'off');
}

function showHiddenToggle(name,time){
	$(name).slideToggle(time);
}

function update_notify() {
	jQuery.ajax({
		url: '/xnotifycation/api/updateNotify/',
		type: 'POST',
		data: {},
		success: function(result) {
			console.log(result);
			load_notify('#load_notifycation','/xnotifycation/api/list-notify/','unread,all');
			update_notify();
		}
	});
}

// function poll() {
// 	var ajax = new XMLHttpRequest();
// 	ajax.onreadystatechange = function() {
// 		if (this.readyState === 4 && this.status === 200) {
// 			if (this.status === 200) {
// 				try {
// 					var json = JSON.parse(this.responseText);
// 				} catch {
// 					poll();return;
// 				}
// 				if (json.status === true) {
// 					// load_notify('#load_notifycation','/xnotifycation/api/list-notify/','unread,all');
// 					console.log('aaa');
// 					return;
// 				}
// 				poll();
// 			} else {
// 				poll();
// 			}
// 		}
// 	}
// 	ajax.open('GET', '/xnotifycation/api/checkNew/', true);
// 	ajax.send();
// }
// poll();


jQuery(document).ready(function() {
	init();

	// update_notify();
	
	$(document).on('click', function(e){
		if(e.target.className != ' subNotify'){
			$('#lists-notify').slideUp(200);
		}
	});

	// lấy dữ liệu thông báo.
	// setInterval(function(){
	//     load_notify('#load_notifycation','/xnotifycation/api/list-notify/','unread,all');
	// }, 15000);

	// load_notify('#load_notifycation','/xnotifycation/api/list-notify/','unread,all');
});