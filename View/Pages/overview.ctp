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
					Status
				</th>
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
							}
						?>
					</td>
					<td>
						<?php
							$time_ago = $this->Time->timeAgoInWords($repo['updated_at'], array(
								'end' => '+1 year'
							));
							$time_ago_split = explode(', ', $time_ago);
							$time_ago = $time_ago_split[0];
							if (stripos($time_ago, ' ago') === false && stripos($time_ago, 'on ') === false) {
								$time_ago = $time_ago.' ago';
							}
							echo $time_ago;
						?>
					</td>
					<?php if (isset($sites[$repo['name']])): ?>
						<td class="check_status" data-url="<?php echo $sites[$repo['name']]; ?>">

						</td>
					<?php else: ?>
						<td>
							n/a
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php
	$pos = stripos(env('SERVER_NAME'), 'localhost');
	$sn_len = strlen(env('SERVER_NAME'));
	$lh_len = strlen('localhost');
	$is_localhost = ($pos !== false && $pos == ($sn_len - $lh_len));

	if ($is_localhost) {
		$this->Js->buffer("
			$('td.check_status').each(function () {
				$(this).html('Can\'t check (on localhost)');
			});
		");
	} else {
		$this->Js->buffer("
			$('td.check_status').each(function () {
				var cell = $(this);
				$.ajax({
					url: cell.data('url'),
					crossDomain: true,
					beforeSend: function () {
						cell.html('<img src=\"/data_center/img/loading_small.gif\" alt=\"Loading...\" />');
					},
					success: function (data, textStatus, jqXHR) {
						cell.html('Okay');
					},
					error: function () {
						cell.html('Error');
					}
				});
			});
		");
	}
?>