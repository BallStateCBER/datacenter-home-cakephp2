<?php if (! empty($latest_release['Release'])): ?>
	<section id="latest_release">
		<h1>
			Latest Release
		</h1>
		<?php
			$link_content = '<span class="title">'.$latest_release['Release']['title'].'</span>';
			$link_content .= '<span class="released">Published '.date('F j, Y', strtotime($latest_release['Release']['released'])).'</span>';
			if (! empty($latest_release['Graphic'])) {
				$link_content .= '<img src="'.$latest_release['Graphic'][0]['thumbnail'].'"> ';
			}
			echo $this->Html->link(
				$link_content,
				$latest_release['Release']['url'],
				array('escape' => false)
			);
		?>
		<br />
		<a href="http://projects.cberdata.org">
			View Project Database &rarr;
		</a>
		<br class="clear" />
	</section>
<?php endif; ?>

<section id="twitter">
	<h1>
		Twitter
	</h1>
	<h3>
		@BallStateCBER
	</h3>
	<a class="twitter-timeline"  href="https://twitter.com/BallStateCBER"  data-widget-id="351709426740252672">Tweets by @BallStateCBER</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</section>