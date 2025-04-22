<?php 
// [section] 
function backgroundShortcode($atts, $content = null) {
extract( shortcode_atts( array(
    'bg' => '',
    'padding' =>'',
    'dark' => 'false',
    'class' => '',
    'video_mp4' => '',
    'video_ogv' => '',
    'video_webm' => '',
    'parallax' => '',
    'parallax_text' => '',
    'margin' => '0px',
    'title' => '',
    'img' => '',
    'img_pos' => 'right',
    'img_width' => '50%',
    'img_margin' => '',
    ), $atts ) );
    
    ob_start();

   $background = "";
   $background_color = "";
   $padding_row = "";
   $dark_text = "";
   if($dark == 'true') $dark_text = " dark";

    if($padding){ $padding_row = 'padding:'.$padding.' 0;';}

    if (str_contains((string) $bg,'http://') || str_contains((string) $bg,'https://')) {
      $background = $bg;
    }
    elseif (str_contains((string) $bg,'#')) {
      $background_color = 'background-color:'.$bg.'!important;';
    }

   $parallax_class = '';
   if($parallax){$parallax_class = ' ux_parallax'; $parallax=' data-velocity="'.(intval($parallax)/10).'"';} 
 
   $parallax_text = '';
   $text_parallax_class = '';
   if($parallax_text){$text_parallax_class = ' parallax_text'; $parallax_text=' data-velocity="'.(intval($parallax_text)/10).'"';} 

  ?>

    <?php if($title){ ?>
     <h3 class="ux-bg-title"><span><?php echo $title; ?></span></h3>
    <?php } ?> 
     <section class="ux-section<?php echo $dark_text; ?><?php if($img){echo ' has-img has-img-'.$img_pos;}?><?php if($class){echo ' '.$class;}?>" style="<?php echo $background_color; ?><?php echo $padding_row; ?><?php if($margin){ echo 'margin-bottom:'.$margin.'!important;';}?>">  
     <?php if($background){ ?> <div class="ux-section-bg banner-bg <?php echo $parallax_class; ?>" <?php echo $parallax; ?> style="background-image:url(<?php echo $background; ?>);"></div><?php } ?> 
     <?php if($img && $img_pos != 'bottom'){ ?><div class="ux-section-img <?php echo $img_pos; ?>" style="width:<?php echo $img_width; ?>; background-image: url('<?php echo $img; ?>');<?php if($img_margin) echo 'margin:'.$img_margin.' 0;';?>"><img src="<?php echo $img; ?>"></div><?php } ?> 
     <div class="ux-section-content<?php echo $text_parallax_class; ?><?php echo $text_parallax_class; ?>"<?php echo $parallax_text; ?>><?php echo fixShortcode($content); ?></div>
     <?php if($img && $img_pos == 'bottom'){ ?><div class="ux-section-img <?php echo $img_pos; ?>" style="width:<?php echo $img_width; ?>; background-image: url('<?php echo $img; ?>');"><img src="<?php echo $img; ?>"></div><?php } ?> 
    <?php if($video_mp4 || $video_webm || $video_ogv){ ?>
     <div class="video-overlay"></div>
     <video class="ux-banner-video hide-for-small" poster="<?php echo $background; ?>" preload="auto" autoplay="" loop="loop" muted="muted">
          <source src="<?php echo $video_mp4; ?>" type="video/mp4">
          <source src="<?php echo $video_webm; ?>" type="video/webm">
          <source src="<?php echo $video_ogg; ?>" type="video/ogg">
      </video>
      <?php } ?>
    </section><!-- .ux-section -->

  <?php
  $content = ob_get_contents();
  ob_end_clean();
  return $content;

} 


// [row]
function rowShortcode($atts, $content = null) {
  extract( shortcode_atts( array(
    'style' => '',
    'custom_width' => '',
    'border_color' => '',
    'width' => '',
    'class' => '',
  ), $atts ) );
  ob_start();
  ?>
	<div class="row container<?php if($style) echo ' '.$style; ?><?php if($width){ ?> custom-width<?php } ?> <?php echo $class; ?>"<?php if($width){ ?>style="max-width:<?php echo $width; ?>"<?php } ?>><?php echo fixShortcode($content); ?></div>
  <?php
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
} 


// [col]
function colShortcode($atts, $content = null) {	
	extract( shortcode_atts( array(
    'span' => '12',
    'animate' => '',
    'small' => '12',
    'padding' => '',
    'tooltip' => '',
    'delay' => '',
    'hover' => '',
    'class' => '',
    'align' => '',
    'parallax' => '',
    'bg' => '',
  	), $atts ) );


  	switch ($span) {
    case "1/1":
        $span = '12'; break;
    case "1/4":
        $span = '3'; break;
    case "2/4":
         $span ='6'; break;
    case "3/4":
        $span = '9'; break;
    case "1/3":
        $span = '4'; break;
    case "2/3":
         $span = '8'; break;
    case "1/2":
        $span = '6'; break;
    case "1/6":
        $span = '2'; break;
    case "2/6":
         $span = '4'; break;
    case "3/6":
        $span = '6'; break;
    case "4/6":
        $span = '8'; break;
    case "5/6":
        $span = '10'; break;
    case "1/12":
        $span = '1'; break;
    case "2/12":
        $span = '2'; break;
    case "3/12":
        $span = '3'; break;
    case "4/12":
        $span = '4'; break;
    case "5/12":
        $span = '5'; break;
    case "6/12":
        $span = '6'; break;
    case "7/12":
        $span = '7'; break;
    case "8/12":
        $span = '8'; break;
    case "9/12":
        $span = '9'; break;
    case "10/12":
        $span = '10'; break;
     case "11/12":
        $span = '11'; break;
	}

  // SCROLL HTML
  $scroll = '';
  $scroll_html = '';
  if($animate) {
    $scroll = 'scroll-animate';
    $scroll_html = 'data-animate="'.$animate.'"';
  }

  if($align) $align = ' text-'.$align.' ';

  // DELAY HTML
  $delay_html = '';
  if($delay) {
    $delay_html = 'style="-webkit-animation-delay: '.$delay.';animation-delay: '.$delay.';-moz-animation-delay: '.$delay.';"';
  }
  
  // HOVER HTML
  if($hover) {
    $hover = 'col_hover_'.$hover;
  }

  // PADDING HTML
  if($padding) $padding = 'style="padding:'.$padding.'"';

  // TOOLTIP
  $tooltip_class = '';
  if($tooltip) {
    $tooltip = 'title="'.$tooltip.'"';
    $tooltip_class = 'tip-top';
  }

  // Background
  $bg_class = '';
  if($bg) { $bg = 'background-color:'.$bg; $bg_class = 'col-bg'; }

  // Parallax
  $parallax_html = '';
  $text_parallax_class = '';
  if($parallax){$text_parallax_class = ' parallax_text'; $parallax_html=' data-velocity="-0.'.$parallax.'"';} 


	$column = '<div class="small-'.$small.''.$align.' '.$bg_class.' '.$tooltip_class.' '.$class.' large-'.$span.' '.$hover.' columns '.$scroll.'" '.$tooltip.' '.$scroll_html.' '.$delay_html.'><div class="column-inner'.$text_parallax_class.'" '.$parallax_html.' '.$padding.'>'.$content.'</div></div>';
	return fixShortcode($column);
}


add_shortcode('col', 'colShortcode');
add_shortcode('row', 'rowShortcode');
add_shortcode('background', 'backgroundShortcode');
add_shortcode('section', 'backgroundShortcode');