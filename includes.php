<?php
	/*
	 * Register the reviews post type.
	 */
	add_action( 'init', 'amz_reviews_init' );
	function amz_reviews_init() {
		$labels = array(
			'name'               => _x( 'Reviews', 'post type general name', 'amz-plugin' ),
			'singular_name'      => _x( 'Review', 'post type singular name', 'amz-plugin' ),
			'menu_name'          => _x( 'Reviews', 'admin menu', 'amz-plugin' ),
			'name_admin_bar'     => _x( 'Review', 'add new on admin bar', 'amz-plugin' ),
			'add_new'            => _x( 'Add New', 'review', 'amz-plugin' ),
			'add_new_item'       => __( 'Add New Review', 'amz-plugin' ),
			'new_item'           => __( 'New Review', 'amz-plugin' ),
			'edit_item'          => __( 'Edit Review', 'amz-plugin' ),
			'view_item'          => __( 'View Review', 'amz-plugin' ),
			'all_items'          => __( 'All Reviews', 'amz-plugin' ),
			'search_items'       => __( 'Search Reviews', 'amz-plugin' ),
			'parent_item_colon'  => __( 'Parent Reviews:', 'amz-plugin' ),
			'not_found'          => __( 'No reviews found.', 'amz-plugin' ),
			'not_found_in_trash' => __( 'No reviews found in Trash.', 'amz-plugin' )
		);
	
		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'amz-plugin' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'reviews' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'taxonomies'		 => array('category', 'post_tag'),
			'supports'           => array('title', 'editor', 'author', 'comments', 'revisions', 'thumbnail'),
			'menu_icon'			 => 'dashicons-star-filled'
		);
	
		if (!post_type_exists('reviews')) {
			register_post_type( 'reviews', $args );
			add_action('init', 'amz_reviews_add_default_boxes');
			 
			function amz_reviews_add_default_boxes() {
				register_taxonomy_for_object_type('category', 'reviews');
				register_taxonomy_for_object_type('post_tag', 'reviews');
			}
		}
		if (post_type_exists('reviews')) {
			register_post_type( 'reviews', $args );
			require_once('reviewcustomfields.php');	
		}
	}
	
/**
 * Include CSS file for MyPlugin.
 */
function amz_plugin_scripts() {
    wp_register_style( 'amz-styles',  plugin_dir_url( __FILE__ ) . 'style.css' );
    wp_enqueue_style( 'amz-styles' );
}
add_action( 'wp_enqueue_scripts', 'amz_plugin_scripts' );

// Recent Reviews Sidebar Widget
function amz_widget_reviews_display($args) {
   echo $args['before_widget'];
   echo $args['before_title'] . 'Recent Reviews' .  $args['after_title'];
?>
		<!-- BEGIN WIDGET -->
			<ul style="list-style:none;">
				<?php
				$treviews = new WP_Query('post_type=reviews&posts_per_page=5');
				if($treviews->have_posts()): ?>
					<?php while($treviews->have_posts()): $treviews->the_post(); ?>						
					<li class="wrevwidget">
						<div style="float:left; margin-right:2px;">
							<a href='<?php the_permalink(); ?>' title='<?php the_title(); ?>' rel="nofollow">
							<?php if(has_post_thumbnail()): ?>
							<?php the_post_thumbnail(array(80, 80)); ?>
							<?php else: ?>
							<img src="<?php echo plugins_url('images/smallthumb.png',__FILE__) ?>" alt="<?php the_title(); ?>" />
							<?php endif; ?>
							</a>
						</div>
						<a href='<?php the_permalink(); ?>' title='<?php the_title(); ?>' class="wrevtitle"><?php
						$bits=explode(" ",get_the_title(),7);
						unset($bits[6]);
						echo implode(" ",$bits);
						?></a>
						<div><?php if(get_post_meta(get_the_ID(), 'amz_rating', true)): ?>
							<span><img src="<?php echo plugins_url( 'images/stars/'.get_post_meta(get_the_ID(), 'amz_rating', true).'.png', __FILE__ ); ?>" style="height:20px; float:right;"/></span>
						<?php endif; ?>
						</div>
						<div style="clear:both;"></div>
					</li>
					<?php endwhile; ?>
				<?php endif; ?>
			<ul>
		<!-- END WIDGET -->

<?php
   echo $args['after_widget'];
}
class AmzWidget extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct( false, 'Amz Widget' );
	}

	function widget( $args, $instance ) {
		// Widget output
		amz_widget_reviews_display($args);
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}
function amz_widget_register_widgets() {
	register_widget( 'AmzWidget' );
}
add_action( 'widgets_init', 'amz_widget_register_widgets' );



//
// Shortcodes
//
//[amz-main-infobox]
add_shortcode( 'amz-main-infobox', 'amz_main_infobox' );	
function amz_main_infobox(){
	global $post;
	$ptype=get_post_type($post);
	if(!in_array($ptype, array('reviews', 'product'))) return;

$url=esc_url(get_post_meta( get_the_ID(), 'amz_url', true ));
$asin=esc_html(get_post_meta( get_the_ID(), 'amz_asin',true ));

if(!empty($url)){
$inf='<div class="amz-single-meta">
	<p class="amz-title"><strong>'.esc_html($post->post_title).' - Review</strong></p><hr />';
	
	$rating=sprintf("%.1f",esc_html(get_post_meta( get_the_ID(), 'amz_rating', true )));
	$expertrating=sprintf("%.1f",esc_html(get_post_meta( get_the_ID(), 'amz_rating_expert', true )));
	if ($rating) {
$inf.='<div>
			<div class="stars" align="left" style="margin-top:6px; width:48%; float:left">Expert Rating<br />
				<img src="'.plugins_url( 'images/stars/'.$expertrating.'.png', __FILE__ ).'" style="height:19px !important;" /> ('.$expertrating.')<br /><a href="#expertreview">Read Expert Review</a></div>
			<div class="stars" align="right" style="margin-top:6px; width:48%; float:right">User Rating<br />
				<img src="'.plugins_url( 'images/stars/'.$rating.'.png', __FILE__ ).'" style="height:19px !important;" /> ('.$rating.')<br />
				<a href="'.$url.'" target="_blank" rel="nofollow">Read User Reviews</a></div></div>';
	}
$inf.='<p align="left">';
			$feat_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
$inf.='<img class="right" src="'.$feat_image.'" style="height:100px !important; float:right; margin:2px;" />';
			$manufacturer=esc_html(get_post_meta( get_the_ID(), 'amz_manufacturer', true ));
			if(!empty($manufacturer)){
$inf.='<strong>Manufacturer: </strong><em>'.$manufacturer.'</em><br />';
			}
			$brand=esc_html(get_post_meta( get_the_ID(), 'amz_brand', true ));
			if(!empty($brand)){
$inf.='<strong>';
			if(empty($manufacturer)) $inf.="Manufacturer/";
$inf.='Brand: </strong><em>'.$brand.'</em><br />';
			}
			$model=esc_html(get_post_meta( get_the_ID(), 'amz_model', true ));
			if(!empty($model)){
$inf.='<strong>Model: </strong><em>'.$model.'</em><br />';
			}
			$asin=esc_html(get_post_meta( get_the_ID(), 'amz_asin', true ));
			if(!empty($asin)){
$inf.='<strong>ASIN: </strong><em>'.$asin.'</em><br />';
			}
			$ean=esc_html(get_post_meta( get_the_ID(), 'amz_ean', true ));
			if(!empty($ean)){
$inf.='<strong>EAN: </strong><em>'.$ean.'</em><br />';
			}
			$cats=esc_html(get_post_meta( get_the_ID(), 'amz_categories', true ));
			if(!empty($cats)){
$inf.='<strong>Available in Categories: </strong><em>'.$cats.'</em><br />';
			}
$inf.='</p><hr style="width:100%;" />';
		$bullets=get_post_meta( get_the_ID(), 'amz_bullet' );
		if(!empty($bullets)){
$inf.='<div align="left"> <strong>Features</strong><div>
			<ul>';
			foreach($bullets as $bullet){
$inf.='<li style="list-style:none;">'.esc_html($bullet).'</li>';
			}
$inf.='</ul></div></div><hr style="width:100%;" />';
		}
$inf.='<div class="buybutton" style="float:left;"> <a href="'.$url.'" class="button right" target="_blank" rel="nofollow" style="color:#fff; min-width:160px;">Get Latest Price &raquo;</a> </div>
		<div class="buybutton" style="float:right;"> <a href="'.$url.'" class="button bbright" target="_blank" rel="nofollow" style="color:#fff; min-width:140px;">Buy It Now &raquo;</a> </div>
		<div style="clear:both;"></div></div>';
}
	return $inf;
}
//
//[amz-specs] - Specifications
add_shortcode( 'amz-specs', 'amz_specs' );	
function amz_specs(){
	global $post;
	$ptype=get_post_type($post);
	if(!in_array($ptype, array('reviews', 'product'))) return;

	$spc=''; $specs=get_post_meta( $post->ID, 'amz_specs' );
	if(!empty($specs)){
		$spc='<div class="amz-single-meta">
		<p class="amz-title"><strong>Specifications</strong></p><hr />
		<ul class="specs-left">';
		for($n=0;$n<count($specs);$n=$n+2){
			$spc.='<li style="list-style:none;">'.esc_html($specs[$n]).'</li>';
		}
		$spc.='</ul><ul class="specs-right">';
		for($n=0;$n<count($specs);$n=$n+2){
			$spc.='<li style="list-style:none;">'.esc_html($specs[$n+1]).'</li>';
		}
		$spc.='</ul><div style="clear:both;"></div></div>';
	}
	return $spc;
}
//
//[amz-bottom-buybox]
add_shortcode( 'amz-bottom-buybox', 'amz_bottom_buybox' );	
function amz_bottom_buybox(){
	global $post;
	$ptype=get_post_type($post);
	if(!in_array($ptype, array('reviews', 'product'))) return;

	$url=esc_url(get_post_meta( $post->ID, 'amz_url', true ));
	$asin=esc_html(get_post_meta( $post->ID, 'amz_asin',true ));
	$feat_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
$ret='<div class="amz-single-meta" style="padding:0;"><div class="bottom-buybox"><div align="left">
		<div align="right"><img src="'.$feat_image.'" style="height:70px !important;" /></div>
		<div align="center"><div align="center" class="title">'.esc_html($post->post_title).'</div></div>
		<div align="left"><div align="center" class="buy-button"><a href="'.$url.'" target="_blank" rel="nofollow">SHOP NOW</a>
	  </div></div></div></div><div style="clear:both;"></div></div>';
return $ret;
}

//
//[amz-expert]
add_shortcode( 'amz-expert', 'amz_expert' );	
function amz_expert(){
	return '<a name="expertreview" id="expertreview"></a>';
}

// Reviews - Questions
// [amz-reviews-questions]
add_shortcode( 'amz-reviews-questions', 'amz_reviews_questions' );	
function amz_reviews_questions(){
	global $post;
	$ptype=get_post_type($post);
	if(!in_array($ptype, array('reviews', 'product'))) return;

		global $amz_tag, $loc;
		$id = $post->ID;		
		//if($GLOBALS['post']){
			$url=esc_url(get_post_meta( $id, 'amz_url', true ));
      		$asin=esc_html(get_post_meta( $id, 'amz_asin',true ));

$box1 = ''; $box2 = ''; $clear='<div style="clear:both;"></div>';
			$opinions=get_post_meta( $id, 'amz_opinions', true );
			$out='';
			if(!empty($opinions)){$box1='<div class="amz-single-meta" style="background:#EEEEEE; text-align:left;">
			  <strong><font size="4">Customer Reviews & Opinions</font></strong>
			  <hr style="width:100%;" />
			  <div align="left">'.esc_html($opinions).'
			  <div align="right"><a href="'.$url.'" target="_blank" rel="nofollow">Read All Reviews</a></div>
			  </div>
			</div>';}
			
			$questions=get_post_meta( $id, 'amz_questions', true );
			if(!empty($questions)){$box2='<div class="amz-single-meta" style="background:#DDDDDD; text-align:left;">
			  <strong><font size="4">Your Questions Answered</font></strong>
			  <hr style="width:100%;" />
			  <div align="left">'.esc_html($questions).'
			  <div align="right"><a href="'.$url.'" target="_blank" rel="nofollow">Read All Questions</a></div>
			  </div>
			</div>';}
		if(!empty($box1) || !empty($box2)){
			$out=$clear.$box1.$box2.$clear;
		}
			return $out;
		//}
}

// working on
// [amz-proscons]
add_shortcode( 'amz-proscons', 'amz_proscons' );	
function amz_proscons(){
	global $post; $id=$post->ID;
	$pross = get_post_meta( $id, 'amz_pros', true );
	$conss = get_post_meta( $id, 'amz_cons', true );
	$pc='';
	if (!empty($pross) || !empty($conss)) {
		$pc.='<div class="amz-single-meta" style="text-align:left;">';
		$pc.='<b>Pros & Cons</b><hr>';
		if (!empty($pross)) {
			$pc.='<div style="width:50%; float:left;"><b style="color:green">Pros</b>';
			$pc.='<div align="left" style="padding-left:20px; font-size:12px;">'.esc_html($pross).'</div></div>';
		}
		if (!empty($conss)) {
			$pc.='<div style="width:50%; float:left;"><b style="color:red">Cons</b>';
			$pc.='<div align="left" style="padding-left:20px; font-size:12px;">'.esc_html($conss).'</div></div>';
		}
		$pc.='<div style="clear:both;"></div></div>';
	}
	return $pc;
}

// Inserts our content before & after main content
// by executing the shortcode functions & returning
// their content in a string
function amz_content_filter($content) {
	global $post;
	$ptype=get_post_type($post);
	if(!in_array($ptype, array('reviews', 'product'))) return $content;
	if(get_option('amz_cont', 'insert')=='insert'){
		$clr='<div style="clear:both;"></div>';
		$inf=do_shortcode('[amz-main-infobox]');
		$spc=do_shortcode('[amz-specs]');
		$anc=do_shortcode('[amz-expert]');
		$qaa=do_shortcode('[amz-reviews-questions]');
		$con=do_shortcode('[amz-proscons]');
		$buy=do_shortcode('[amz-bottom-buybox]');
		
		$out=$clr.$inf.$spc.$clr.$anc.$content.$clr.$qaa.$con.$buy;
		return $out;
	}else{
		return $content;
	}
}
add_filter( 'the_content', 'amz_content_filter' ,9);
?>
