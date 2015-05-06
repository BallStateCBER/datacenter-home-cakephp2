var dataCenterOverview = {
	init: function () {
		$('td.check_status').each(function () {
			var cell = $(this);
			var is_localhost = cell.data('server') == 'development';
			var url = '/pages/statuscheck?url=' + encodeURIComponent(cell.data('url'));
			$.ajax({
				dataType: 'json',
				url: url,
				crossDomain: true,
				beforeSend: function () {
					cell.html('<img src=\"/data_center/img/loading_small.gif\" alt=\"Loading...\" />');
				},
				success: function (data) {
					if (data.status.search('200 OK') > -1) {
    					cell.html('<span class=\"glyphicon glyphicon-ok-sign\" title=\"200 OK\"></span>');
					} else {
						cell.html('<span class=\"glyphicon glyphicon-remove-sign\" title=\"'+data.status+'\"></span>');
					}
					if (data.debug) {
						cell.append(' <span class=\"debug\">debug</span>');
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					cell.html('<span class=\"glyphicon glyphicon-remove-sign\" title=\"'+errorThrown+'\"></span>');
				}
			});
		});
		$('a.issues').click(function (event) {
			event.preventDefault();
			var link = $(this);
			var repo = link.data('repo');
			var issues_row = $('#'+repo+'_issues');
	
			// Already loaded?
			if (issues_row.length > 0) {
				if (issues_row.is(':visible')) {
					issues_row.find('ul').slideUp(300, function () {
						issues_row.hide();
					});
				} else {
					issues_row.show();
					issues_row.find('ul').slideDown();
				}
				return;
			}
	
			// Query GitHub
			$.ajax({
				type: 'GET',
				url: 'https://api.github.com/repos/BallStateCBER/'+repo+'/issues',
				success: function (data) {
					var tr = link.closest('tr');
					var colspan = tr.children().length - 1;
					var new_row = $('<tr id=\"'+repo+'_issues\" class=\"issues\" style=\"display: none;\"><td></td><td colspan=\"'+colspan+'\"><ul style=\"display: none;\"></ul></td></tr>').hide();
					tr.after(new_row);
					var ul = new_row.find('ul');
					if (data.length > 0) {
						for (i = 0; i < data.length; i++) {
							ul.append('<li><a href=\"'+data[i]['html_url']+'\">'+data[i]['title']+'</a></li>');
						}
					}
					ul.append('<li><a href=\"https://github.com/BallStateCBER/'+repo+'/issues/new\">Add a new issue</a></li>');
					new_row.show();
					ul.slideDown();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					link.after(' error');
				}
			});
		});
	}
};