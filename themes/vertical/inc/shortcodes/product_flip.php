<?php
// [ux_product_flip]
function ux_product_flip($atts, $content = null) {

  /* register script */
  wp_register_script( 'flatsome-flip', get_template_directory_uri() .'/js/jquery.mobile.flip.js', array( 'jquery' ), '20120202', true );
  wp_enqueue_script('flatsome-flip');

	global $woocommerce;
	$sliderrandomid = rand();
	extract(shortcode_atts(array(
		'products'  => '8',
    'height' => '510px',
    'cat' => '',

	), $atts));
	ob_start();

 if(!strpos((string) $height,'px') !== false) { $height = $height.'px';}

   $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'product_cat' => $cat,
            'products' => $products
        );
	?>
<div class="flip-container" style="height:<?php echo $height+20;?>px;overflow:hidden;">
  <div class="row">
    <div class="large-12 columns">
      <div id="flipRoot" class="flipContainer">
            <?php if($content) { ?>
            <div class="row-collapse flip-slide">
              <div class="large-12 columns">
                 <?php echo fixShortcode($content); ?>
              </div><!-- large-6 -->
             </div><!-- row -->
             <?php } ?>

               <?php
                  $products = new WP_Query( $args );
                  if ( $products->have_posts() ) : ?>
                      <?php while ( $products->have_posts() ) : $products->the_post(); ?>
                       <div class="row collapse">
                          <?php woocommerce_get_template_part( 'content', 'product-flipbook' ); ?>
                        </div>
                      <?php endwhile; // end of the loop. ?>
                  <?php
                  endif; 
                  wp_reset_query();
              ?>
      </div>
    </div>
  </div>
</div>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
			 $("#flipRoot").flip({
        height: '<?php echo $height;?>',
        forwardDir: 'ltor',
        showPager: true,
        loop: true
      });
	});
	</script>
  <style>
/* -- product flip --*/
  .flipShadow{display: none!important}
  .flipContainer{position:relative;-webkit-perspective:3000px;-moz-perspective:3000px;perspective:3000px;-webkit-user-select:none;-moz-user-select:none;user-select:none}
  .flipContent{position:absolute;top:0;left:0;height:100%;width:100%;display:none;overflow:hidden}
  .flipContent.flipCurrent{display:block}
  .sliding,.slidingBg{position:absolute;overflow:hidden;z-index:1;background-color:inherit}
  .flipping{background-color:inherit;-webkit-backface-visibility:hidden;-webkit-transform-style:flat;-webkit-transform:rotateY(0deg);-moz-backface-visibility:hidden;-moz-transform-style:preserve3d;-moz-transform:rotateY(0deg);backface-visibility:hidden;transform-style:preserve3d;transform:rotateY(0deg)}
  .flipping.firstHalf{-webkit-transform-origin:100% 0;-moz-transform-origin:100% 0;transform-origin:100% 0}
  .flipping.secondHalf{-webkit-transform-origin:0 0;-moz-transform-origin:0 0;transform-origin:0 0}
  .backflipping{display:none;background-color:inherit;-webkit-backface-visibility:hidden;-webkit-transform-style:flat;-webkit-transform:rotateY(180deg);-moz-backface-visibility:hidden;-moz-transform-style:flat;-moz-transform:rotateY(180deg);backface-visibility:hidden;transform-style:flat;transform:rotateY(180deg)}
  .holizontalFlipping.firstHalf{-webkit-transform-origin:100% 0;-moz-transform-origin:100% 0;transform-origin:100% 0}
  .holizontalFlipping.secondHalf{-webkit-transform-origin:0 0;-moz-transform-origin:0 0;transform-origin:0 0}
  .verticalFlipping.firstHalf{-webkit-transform-origin:0 100%;-moz-transform-origin:0 100%;transform-origin:0 100%}
  .verticalFlipping.secondHalf{-webkit-transform-origin:0 0;-moz-transform-origin:0 0;transform-origin:0 0}
  .splitHalf{position:absolute;overflow:hidden}.splitEmpty{background-color:#333}
  .flipContainer .pager{text-align:center;position:absolute;bottom:-10px;width:100%;cursor:pointer;overflow:hidden;}
  .flipContainer .pager span{display: inline-block;}
  .flip-slide{background: #FFF;}
  .flipContainer .callout{top:20px;left:20px;}
  .flipContainer .row-collapse {float:left!important;}
  .flipContainer .row-collapse > .columns{padding:0!important}
  .flipContainer .product-info{font-size: 80%; padding: 30px; overflow-y:auto}
  .flipContainer .star-rating{margin-bottom:15px;}
  .flipContainer .entry-title{padding:15px 50px 0 0;}
  .flipContainer .button{margin-top: 15px;}
</style>
	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

add_shortcode("ux_product_flip", "ux_product_flip");