<?php 
// [featured_box]
function featured_box($atts, $content = null) {
	$sliderrandomid = rand();
	extract(shortcode_atts(array(
		'title' => '',
		'img'  => '',
        'pos' => '',
        'link' => '',
 	), $atts));
	ob_start();
	?>

	<div class="featured-box <?php if($pos) echo 'pos-'.$pos; ?>">
	<div class="box-inner">
	<?php if($link) { echo '<a href="'.$link.'">'; } ?>
	<img class="featured-img" src="<?php echo $img; ?>" alt="<?php echo $title; ?>">
	<?php if($link) { echo '</a>'; } ?>
	<?php if($link) { echo '<a href="'.$link.'">'; } ?>
    <h4><span><?php echo $title; ?></span></h4>
    <?php if($link) { echo '</a>'; } ?>
    <p><?php echo $content; ?></p>
	</div>
	</div>

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}


add_shortcode("featured_box", "featured_box");
