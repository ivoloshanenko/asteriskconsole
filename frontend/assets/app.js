var app = {};

app.initilize = function () {
	app.$users_list = $('#users-list');
	app.$create_form = $('#create-form');
	app.list();
	app.initialize_create();
};

app.list = function () {
	$.ajax({
		url: 'list',
		success: function(data) {
			if (!data.success) return alert('error loading list');
			_.each(data.list, function (user) {
				app.initialize_row(user);
			});
		}
	});
};

app.initialize_row = function (user) {

	var $row,
		$name,
		$secret,
		$callerid,
		$context,
		$pickupgroup,
		$permit,
		$nat,
		$ipaddr,
		$useragent,
		$action,
		$edit,
		$remove;

	app.$users_list.append(
		$row = $('<tr>')
			.attr('id', user.id)
			.append($name = $('<td>').text(user.name))
			.append($secret = $('<td>').text(user.secret))
			.append($callerid = $('<td>').text(user.callerid))
			.append($context = $('<td>').text(user.context))
			.append($pickupgroup = $('<td>').text(user.pickupgroup))
			.append($permit = $('<td>').text(user.permit))
			.append($nat = $('<td>').text(user.nat))
			.append($ipaddr = $('<td>').text(user.ipaddr))
			.append($useragent = $('<td>').text(user.useragent))
			.append($action = $('<td>')
				.append(
					$edit = $('<button>').addClass('btn')
					.append($('<i>').addClass('icon-edit')))
				.append(
					$remove = $('<button>').addClass('btn btn-danger')
					.append($('<i>').addClass('icon-remove icon-white')))
			)
	);

	$remove.click(function(){
		$.ajax({
			url: 'remove',
			type: 'post',
			success: function(data) {
				if (!data.success) return alert('error removing');
				$row.fadeOut(500);
			}
		});
		return false;
	});

};

app.initialize_create = function () {

	app.$create_form.submit(function(){
		$.ajax({
			url: 'create',
			type: 'post',
			data: app.$create_form.serialize(),
			success: function (data) {
				if (data.errors) {
					_.each(data.errors, function (error) {
						alert(error.field + ': ' + error.text);
					});
					return;
				}
				if (!data.success) return alert('error creating');

				app.initialize_row(data.user);

				$('#create').modal('hide');
			}
		});

		return false;
	});

};

$(document).ready(function(){

	app.initilize();

});