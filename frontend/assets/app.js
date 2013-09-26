$(document).ready(function(){

	var $users_list = $('#users-list');

	$.ajax({
		url: 'list',
		success: function(data) {
			if (!data.success) return alert('error loading list');

			_.each(data.list, function (user) {

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

				$users_list.append(
					$row = $('<tr>')
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

			});
		}
	});

});