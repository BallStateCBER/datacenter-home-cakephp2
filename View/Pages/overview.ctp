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
						<?php echo $repo['open_issues']; ?>
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
							echo $this->Time->timeAgoInWords($repo['updated_at'], array(
								'end' => '+1 year'
							));
							$date = substr($repo['updated_at'], 0, 10);
							$time = substr($repo['updated_at'], 11, 8);
							$timestamp = strtotime("$date $time");
							$timestamp = strtotime($repo['updated_at']);
							//echo date('F j, Y g:ia', $timestamp);
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php

?>