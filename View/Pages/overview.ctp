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
					Repository
				</th>
				<th>
					Open
					<br />
					issues
				</th>
				<th>
					Master
					<br />vs. Dev
				</th>
				<th>
					Last
					<br />
					Push
				</th>
				<th>
					URLs
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
						<a href="<?php echo $repo['html_url']; ?>">
							<?php echo $repo['name']; ?>
						</a>
					</td>
					<td>
						<a href="<?php echo $repo['html_url']; ?>/issues">
							<?php echo $repo['open_issues']; ?>
						</a>
					</td>
					<td>
						<?php echo $repo['master_status']; ?>
					</td>
					<td>
						<?php
							$time_ago = $this->Time->timeAgoInWords($repo['pushed_at'], array(
								'end' => '+1 year'
							));
							$time_ago_split = explode(', ', $time_ago);
							$time_ago = $time_ago_split[0];
							echo str_replace(' ago', '', $time_ago);
						?>
					</td>
					<td>
						<?php foreach (array('development', 'production') as $server): ?>
							<?php if (isset($sites[$repo['name']][$server])): ?>
								<a href="<?php echo $sites[$repo['name']][$server]; ?>">
									<?php echo substr($server, 0, 3); ?>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
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
				success: function(data) {
					cell.html('<span class=\"glyphicon glyphicon-ok-sign\"></span>');
					var result = is_localhost ? data : data.contents;
					if (result.search('debug-kit-toolbar') > -1) {
						cell.append(' <span class=\"debug\">debug</span>');
					}
				},
				error: function () {
					cell.html('<span class=\"glyphicon glyphicon-remove-sign\"></span>');
				}
			});
		});
	");
?>