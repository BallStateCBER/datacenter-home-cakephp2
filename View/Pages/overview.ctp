<?php if (empty($repositories)): ?>
	<p>
		No <a href="https://github.com/BallStateCBER">BallStateCBER GitHub repositories</a> found.
	</p>
<?php else: ?>
	<table>
		<thead>
			<tr>
				<th>
					Repo
				</th>
				<th>
					Open
					<br />
					issues
				</th>
				<th>
					Master & Dev
					<br />
					branches synced
				</th>
				<th>
					Updated
				</th>
				<th>
					URLs
				</th>
				<?php if ($is_localhost): ?>
					<th>
						Dev Status
					</th>
					<th>
						Production Status
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
						<?php
							switch ($repo['master_synced']) {
								case -1:
									echo 'No';
									break;
								case 1:
									echo 'Yes';
									break;
								case 0:
									echo 'N/A';
							}
						?>
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
								n/a
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
					cell.html('Okay');
					var result = is_localhost ? data : data.contents;
					if (result.search('debug-kit-toolbar') > -1) {
						cell.append(' (debug)');
					}
				},
				error: function () {
					cell.html('Error');
				}
			});
		});
	");
?>