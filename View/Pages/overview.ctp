<h1 class="page_title">
	<?php echo $title_for_layout; ?>
</h1>

<?php $this->Html->css('overview', array('inline' => false)); ?>

<?php if (empty($repositories)): ?>
	<p>
		No <a href="https://github.com/BallStateCBER">BallStateCBER GitHub repositories</a> found.
	</p>
<?php else: ?>
	<table class="table">
		<thead>
			<tr>
				<th>
					Website / Software
				</td>
				<th>
					Open
					<br />
					issues
				</th>
				<th>
					Master
					<br />Branch
				</th>
				<th>
					Last
					<br />
					Push
				</th>
				<?php if ($is_localhost): ?>
					<th>
						Status
						<br />
						(Dev)
					</th>
					<th>
						Status
						<br />
						(Production)
					</th>
				<?php else: ?>
					<th>
						Status
					</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($repositories as $repo): ?>
				<tr>
					<td>
						<?php echo isset($sites[$repo['name']]['title']) ? $sites[$repo['name']]['title'] : $repo['name']; ?>
						<br />
						<ul class="links">
							<li>
								<a href="<?php echo $repo['html_url']; ?>">
									Repo
								</a>
							</li>
							<?php foreach (array('development', 'production') as $server): ?>
								<?php if (isset($sites[$repo['name']][$server])): ?>
									<li>
										<a href="<?php echo $sites[$repo['name']][$server]; ?>">
											<?php echo ucwords($server); ?>
										</a>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</td>
					<td>
						<a href="<?php echo $repo['html_url']; ?>/issues" class="issues" data-repo="<?php echo $repo['name']; ?>">
							<?php echo $repo['open_issues']; ?>
						</a>
					</td>
					<td>
						<?php echo $repo['master_status']; ?>
					</td>
					<td>
						<?php
							$time_ago = $this->Time->timeAgoInWords($repo['pushed_at'], array(
								'end' => '+10 year'
							));
							$time_ago_split = explode(', ', $time_ago);
							$time_ago = $time_ago_split[0];
							echo str_replace(' ago', '', $time_ago);
						?>
					</td>
					<?php foreach ($servers as $server): ?>
						<?php
							$url = isset($sites[$repo['name']][$server]) ? $sites[$repo['name']][$server] : null;
						?>
						<?php if ($url): ?>
							<td class="check_status" data-url="<?php echo $url; ?>" data-server="<?php echo $server; ?>">

							</td>
						<?php else: ?>
							<td>
								<span class="na">N/A</a>
							</td>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php
	$this->Js->buffer("
		$('td.check_status').each(function () {
			var cell = $(this);
			var is_localhost = cell.data('server') == 'development';
			if (is_localhost) {
				var url = cell.data('url');
			} else {
				var url = 'http://whateverorigin.org/get?url=' + encodeURIComponent(cell.data('url')) + '&callback=?';
			}
			var dataType = is_localhost ? 'html' : 'json';
			$.ajax({
				dataType: dataType,
				url: url,
				crossDomain: true,
				beforeSend: function () {
					cell.html('<img src=\"/data_center/img/loading_small.gif\" alt=\"Loading...\" />');
				},
				success: function (data) {
					cell.html('<span class=\"glyphicon glyphicon-ok-sign\" title=\"200 OK\"></span>');
					var result = is_localhost ? data : data.contents;
					if (result.search('debug-kit-toolbar') > -1) {
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
	");
?>