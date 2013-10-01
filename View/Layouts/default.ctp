<?php $this->extend('DataCenter.default'); ?>
<?php $this->start('subsite_title'); ?>
	<h1 id="subsite_title" class="max_width_padded">
		<a href="/">
			Collect | Analyze | Display
		</a>
	</h1>
<?php $this->end(); ?>
<?php $this->assign('sidebar', $this->element('sidebar')); ?>
<div id="content">
	<?php echo $this->fetch('content'); ?>
</div>