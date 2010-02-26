<h1><?php echo $this->title; ?></h1>
<?php if($this->errorMessage): ?>
	<p><?php echo $this->errorMessage; ?></p>
<?php else: ?>
	<p>Oops! An error occured that has prevented this page from displaying properly.</p>
<?php endif; ?>