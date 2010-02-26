<?php
/**
 * Wrap everything in a function for now so we can display subpages recursively
 * Kind of a hack... There are *much* better ways to do this but I'm under deadline...
 * @todo Fix this garbage and replace with view helpers and proper decorators, etc.
 */
function renderNavigation($pages) {
	if(!$pages || count($pages) == 0) {
		return;
	}
?>
<ul>
<?php foreach($pages as $page): ?>
	<li>
	<?php echo $page->title; ?>
	<?php
		$children = $page->children;
		if($children && count($children) > 0):
			$tFunc = __FUNCTION__;
			echo $tFunc($children);
		endif;
	?>
	</li>
<?php endforeach; ?>
</ul>
<?php
}

// Render navigation
renderNavigation($this->pages);
?>