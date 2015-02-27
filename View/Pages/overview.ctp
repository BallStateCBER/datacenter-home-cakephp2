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
	$this->Html->script('overview', array('inline' => false));
	$this->Js->buffer("dataCenterOverview.init();");