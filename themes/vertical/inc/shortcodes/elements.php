<?php
// [ux_price_table]
function ux_price_table( $atts, $content = null ){
  extract( shortcode_atts( array(
    'title' => 'Title',
    'price' => '$99.99',
    'description' => 'Description',
    'button_style' => 'small alt-button',
    'button_text' => 'Buy Now',
    'button_link' => '',
    'featured' => 'false',
  ), $atts ) );
  ob_start();
?>

<div class="ux_price_table text-center <?php if($featured == 'true'){ ?>featured-table box-shadow<?php } ?>">
<ul class="pricing-table">
  <li class="title"><?php echo $title;?></li>
  <li class="price"><?php echo $price;?></li>
  <li class="description"><?php echo $description;?></li>
  <?php echo fixShortcode($content); ?>
  <?php if($button_style) { ?> 
  <li class="cta-button"><a class="button <?php echo $button_style;?>" href="<?php echo $button_link;?>"><?php echo $button_text;?></a></li>
  <?php } ?>
</ul>
</div>

<?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
add_shortcode('ux_price_table', 'ux_price_table');

// Price bullet 
function bullet_item( $atts, $content = null ){
  extract( shortcode_atts( array(
    'text' => 'Title',
    'tooltip' => '',
  ), $atts ) );

    if($tooltip) $tooltip = '<span class="bullet-more-info tip-top circle" title="'.$tooltip.'">?</span>';
    $content = '<li class="bullet-item">'.$text.''.$tooltip.'</li>';
    return $content;
}
add_shortcode('bullet_item', 'bullet_item');


// Scroll to [scroll_to link="#this" bullet="true" bullet_title="Scroll to This"]
function ux_scroll_to($atts, $content = null) {
  if(!function_exists('scroll_to_js')){
     function scroll_to_js(){
      ?>
       <script>
      jQuery(function($){
      $('body').append('<div class="scroll-to-bullets hide-for-small"/>');
      $('.scroll-to').each(function(){
            var link = $(this).data('link');
            var end = $(this).offset().top;
            var title = $(this).data('title');
            var css_class = '';
            if(title){css_class = 'tip-left';}

            $('.scroll-to-bullets').append('<a href="'+link+'" class="'+css_class+' animated fadeInRight" title="'+title+'"><strong></strong></a>');
            
            $('a[href="'+link+'"]').click(function(){
                $.scrollTo(end,500);
                e.preventDefault();
            });

            $(this).waypoint(function(direction) {
              $('.scroll-to-bullets a').removeClass('active');
              $('.scroll-to-bullets').find('a[href="'+link+'"]').addClass('active');
              if(direction == 'up'){
                $('.scroll-to-bullets').find('a[href="'+link+'"]').removeClass('active').prev().addClass('active');
              }
            });
      });

      $('.tip-left').tooltipster({position: 'left', delay: 50, contentAsHTML: true,touchDevices: false});
      
      });
      </script>
    <?php
  }
  }
  add_action('wp_footer','scroll_to_js');

  extract(shortcode_atts(array(
    'bullet' => 'true',
    'title' => '',
    'link' => '',
  ), $atts));

  return '<span class="scroll-to" data-link="'.$link.'" data-title="'.$title.'"></span>';
}

add_shortcode("scroll_to", "ux_scroll_to");

// [logo img=""]
function ux_logo( $atts, $content = null ){
  extract( shortcode_atts( array(
    'img' => '#',
    'padding' => '15px',
    'title' => '',
    'link' => '#',
    'height' => '50px',
  ), $atts ) );

    if (str_contains((string) $img,'http://') || str_contains((string) $img,'https://')) {
      $img = $img;
    }
    else {
      $img = wp_get_attachment_image_src($img, 'large');
      $img = $img[0];
    }

    $content = '<div class="ux_logo"><a title="'.$title.'" href="'.$link.'" style="padding: '.$padding.';"><img src="'.$img.'" alt="'.$title.'" style="max-height:'.$height.';min-height:'.$height.'" /></a></div>';
    return $content;
}
add_shortcode('logo', 'ux_logo');


// UX IMAGE
function ux_image( $atts, $content = null ){
  extract( shortcode_atts( array(
    'id' => '',
    'title' => '',
    'image_size' => 'large',
    'image_width' => '',
    'image_pull' => '0px',
    'drop_shadow' => '',
    'lightbox' => '',
    'link' => '',
  ), $atts ) );

    $image =  wp_get_attachment_image_src($id, $image_size);
    $image_large =  wp_get_attachment_image_src($id, 'large');


    $link_start = '';
    $link_end = '';

    if($link){
        $link_start = '<a href="'.$link.'">';
        $link_end = '</a>';
    }

    if($lightbox){
       $link_start = '<a class="image-lightbox" href="'.$image_large[0].'">';
       $link_end = '</a>';
    }
   
    if($drop_shadow) $drop_shadow = 'box-shadow';
    $content = '<div class="ux-img-container '.$drop_shadow.'">'.$link_start.'<img src="'.$image[0].'" alt="'.$title.'" title="'.$title.'" style="bottom:-'.$image_pull.'"/>'.$link_end.'</div>';
    return $content;
}
add_shortcode('ux_image', 'ux_image');


// Phone number
function ux_phone( $atts, $content = null ){
  extract( shortcode_atts( array(
    'number' => '+000 000 000',
    'tooltip' => '',
    'border' => '2px',
  ), $atts ) );
    $tooltip_class = '';
    if($tooltip) $tooltip_class = 'tip-top';
    $content = '<div class="ux-header-element element-phone"><a href="tel:'.$number.'" class="circle '.$tooltip_class.'" title="'.$tooltip.'" style="border-width:'.$border.'"><span class="icon-phone"></span> '.$number.'</a></div>';
    return $content;
}
add_shortcode('phone', 'ux_phone');

// Header button
function ux_header_button( $atts, $content = null ){
  extract( shortcode_atts( array(
    'text' => 'Order Now',
    'link' => '',
    'tooltip' => '',
    'border' => '2px',
  ), $atts ) );
    $tooltip_class = '';
    if($tooltip) $tooltip_class = 'tip-top';
    $content = '<div class="ux-header-element header_button"><a href="'.$link.'" class="circle '.$tooltip_class.'" title="'.$tooltip.'" style="border-width:'.$border.'">'.$text.'</a></div>';
    return $content;
}
add_shortcode('header_button', 'ux_header_button');


// UX Texts

// [ux_text] 
function uxTextShortcode( $atts, $content = null ){
  global $flatsome_opt;
  extract( shortcode_atts( array(
    'text_pos' => 'center',
    'height' => 'auto',
    'text_align' => 'left',
    'text_color' => 'light',
    'padding' => '30px',
  ), $atts ) );
   ob_start();
   $textalign = "";
   if($text_align) {$textalign = "text-".$text_align;}
   $color = "light";
   if($text_color == 'light') $color = "dark";
   $fix = array (
                '_____' => '<div class="tx-div large"></div>',
                '____' => '<div class="tx-div medium"></div>',
                '___' => '<div class="tx-div small"></div>',
   );
   $content = strtr($content, $fix);
?>
<div class="ux_text <?php echo $text_pos; ?> <?php echo $color; ?> <?php echo $textalign; ?>" style="height:<?php echo $height; ?>">
    <div class="inner" style="padding:<?php echo $padding; ?>;">
      <?php echo do_shortcode($content); ?>
    </div>
  </div><!-- end .ux_text -->
<?php 
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}
add_shortcode('ux_text', 'uxTextShortcode');
