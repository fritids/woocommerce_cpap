<?php 
// [hotspot]
function ux_hotspot($atts, $content = null) {
	extract(shortcode_atts(array(
		'product_id'  => '8',
		'color' => '',
		'lightbox' => 'true',
	    'show_text' => 'false',
	    'style' => '1',
	    'font_size' => ''
	), $atts));
	ob_start();
	?>
    
    <?php 
	/**
	* Check if WooCommerce is active
	**/
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	global $woocommerce, $product;
	$product = get_product($product_id);
	?>
	
	<?php if($style == '1'){ ?>
	<span class="ux_hotspot <?php if($lightbox == 'true') {echo 'open-quickview';} ?> tip-top" data-tip="<?php echo esc_html($product->get_title());
?> / <b><?php echo esc_html($product->get_price_html());
?></b>" data-prod="<?php echo $product_id;?>" style="<?php if($color){ echo 'background-color:'.$color; } ?>">
<?php if($lightbox == 'false')  {echo '<a href="'.$product->get_permalink().'">';} ?>
	<span class="icon-plus"></span>
	<?php if($lightbox == 'false')  {echo '</a>';} ?>
	</span>


	<?php } elseif($style == '2'){ ?>
	<?php if($lightbox == 'false')  {echo '<a href="'.$product->get_permalink().'">';} ?>
	<span class="ux_hotspot_text <?php if($lightbox == 'true') {echo 'open-quickview';} ?>" data-prod="<?php echo $product_id;?>"  style="<?php if($color){ echo 'color:'.$color; } ?> 	<?php if($font_size )  {echo 'font-size:'.$font_size.';';} ?>">
		<span class="prod-title"><?php echo $product->get_title();?></span>
		<span class="prod-price"><?php echo $product->get_price_html();?></span>
	</span>
	<?php if($lightbox == 'false')  {echo '</a>';} ?>
	<?php } ?>

	
    <?php } else {echo 'WooCommerce not installed';} ?>

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

add_shortcode("hotspot", "ux_hotspot");
