<?php $this->extend('DataCenter.default'); ?>

<?php $this->start('subsite_title'); ?>
	<h1 id="subsite_title" class="max_width_padded">
		<a href="/">
			Collect | Analyze | Display
		</a>
	</h1>
<?php $this->end(); ?>

<?php $this->assign('sidebar', $this->element('sidebar')); ?>

<?php $this->start('footer_about'); ?>
	<h3>
		About the CBER Data Center
	</h3>
	<p>
		The <a href="http://www.cberdata.org/">CBER Data Center</a> offers simple, visual, easily accessible
		economic web tools for economic developers, community leaders, grant writers, policymakers, and the general public.
	</p>
	<p>
		The CBER Data Center is a product of the Center for Business
		and Economic Research at Ball State University.  CBER's mission is to conduct relevant and timely
		public policy research on a wide range of economic issues affecting the state and nation.
		<a href="http://www.bsu.edu/cber">Learn more</a>.
	</p>
<?php $this->end(); ?>

<?php $this->start('flash_messages'); ?>
    <?php echo $this->element('flash_messages', array(), array('plugin' => 'DataCenter')); ?>
<?php $this->end(); ?>

<div id="content">
	<?php echo $this->fetch('content'); ?>
</div>