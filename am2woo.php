<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	if(empty($_POST['asin'])) $_POST['asin']='';
	if(empty($_POST['tag'])) $_POST['tag']='';
	if(empty($_POST['cntry'])) $_POST['cntry']='com';
	
$upload_dir = wp_upload_dir();
$uploadsurl=$upload_dir['baseurl'];
$uploadsdir=$upload_dir['basedir'];

	$safe_asin = $_POST['asin']; if( ! $safe_asin ){$safe_asin = '';}
	if( strlen( $safe_asin ) > 10 ){$safe_asin = substr( $safe_asin, 0, 10 );}
	$safe_tag = $_POST['tag']; if( ! $safe_tag ){$safe_asin = '';}
	if( strlen( $safe_tag ) > 20 ){$safe_tag = substr( $safe_tag, 0, 20 );}
	$_POST['asin']=$safe_asin; $_POST['tag']=$safe_tag;
	$tag=$safe_tag; $asin=$safe_asin; $region=$_POST['cntry'];

	function amz_woo_check_nonce() {
		// Check if our nonce is set.
		if ( ! isset( $_POST['amz_woo_box_nonce'] ) ) { return false; }
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['amz_woo_box_nonce'], 'amz_woo_check_nonce' ) ) { return false; }
		/* OK, it's safe for us to save the data now. */
		return true;
	}
?>
<form action="" method="post">
<?php
	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'amz_woo_check_nonce', 'amz_woo_box_nonce' );
?>
<table width="50%" border="0" cellspacing="1" cellpadding="1">
  <tr>
    <td align="right">Amazon's Product ASIN: </td>
    <td><input name="asin" type="text" value="<?php echo esc_attr($_POST['asin']) ?>"  maxlength="10" size="20"></td>
  </tr>
  <tr>
    <td align="right">Your Amazon Affilliate ID: </td>
    <td><input name="tag" type="text" value="<?php echo esc_attr($_POST['tag']) ?>"  maxlength="20" size="20"></td>
  </tr>
  <tr>
    <td align="right">Country: </td>
    <td><input type="radio" name="cntry" value="com"<?php if($_POST['cntry']=='com') echo ' checked' ?>>USA <input type="radio" name="cntry" value="co.uk"<?php if($_POST['cntry']=='co.uk') echo ' checked' ?>>UK</td>
  </tr>
  <tr>
    <td align="right">&nbsp;</td>
    <td><input name="submit" type="submit"></td>
  </tr>
</table>
</form>
<?php
if(!empty($_POST['asin'])) $_GET['asin']=$_POST['asin'];
if(!empty($_POST['tag'])) $_GET['tag']=$_POST['tag'];
if(!empty($_POST['cntry'])) $_GET['cntry']=$_POST['cntry'];

	include("amazon.php");
	
	$here=dirname("http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
if(!empty($asin)){
	if(!empty($_GET['tag'])) $tag=$_GET['tag'];
	if(!empty($_GET['asin'])) $asin=$_GET['asin'];
	if(!empty($_GET['cntry'])) $region=$_GET['cntry'];
	if(!empty($_GET['ez'])) $ez=$_GET['ez'];
	if($region=="com") $cntry="US"; else $cntry="UK";

if($region == "com"){$c = "$";}else{$c = "£";}
	
	$res = amzASIN($region, $asin, $tag);
		if(!empty($res->Error)){
			echo '<p><b>'.$res->Error->Code.'</b> - '.$res->Error->Message.'</p>';
			exit;
		}elseif(!empty($res[0]['response']->Request->Errors->Error)){
			echo '<p><b>'.$res[0]['response']->Request->Errors->Error->Code.'</b> - '.$res[0]['response']->Request->Errors->Error->Message.'</p>';
			exit;
		}

$bstyle="style='border:1px solid #0000ff;'";
echo "<img src='".$res[0]['images']->ImageSet[count($res[0]['images']->ImageSet)-1]->TinyImage->URL."' $bstyle />";
	for($n=0;$n<count($res[0]['images']->ImageSet);$n++){
		if($n==0){
			echo "<img src='".$res[0]['images']->ImageSet[$n]->TinyImage->URL."' $bstyle />";
		}else{
			echo "<img src='".$res[0]['images']->ImageSet[$n]->TinyImage->URL."' />";
		}
	}
	echo "<br>";
	

$hi=$res[0]["offer orig"];
$lo=$res[0]["offer"];

$ps=str_replace(basename($_SERVER['PHP_SELF']),"",$_SERVER['PHP_SELF']);
$img=basename(urldecode($res[0]["image"]));

if(!file_exists($uploadsdir.'/amz_images')) mkdir($uploadsdir.'/amz_images');
if(!empty($img)){
	file_put_contents($uploadsdir.'/amz_images/'.$img,file_get_contents($res[0]["image"]));
}
$img1=""; $n=0;
if(!empty($res[0]['images']->ImageSet)){
	foreach($res[0]['images']->ImageSet as $imageset){
		if(!empty($imageset->LargeImage->URL)){
			if($n == 0){
				$img1=basename(urldecode($imageset->LargeImage->URL));
				file_put_contents($uploadsdir.'/amz_images/'.$img1,file_get_contents($imageset->LargeImage->URL));
				break;
			}
		}
		$n++;
	}
}
if(empty($img)) $img = $img1;
echo $img." : ".$img1."<br>";

$desc=$res[0]["description"];
$res[0]["description"]= '<img src="'.$uploadsurl.'/amz_images/'.$img1.'" style="float:left; margin:4px; height: 250px !important;" /><img src="'.$uploadsurl.'/amz_images/'.$img.'" style="float:right; margin:4px; height: 250px !important;" />'.$res[0]["description"];

	$my_post = array(
		'post_title'    => wp_strip_all_tags($res[0]["title"]),
		'post_content'  => $res[0]["description"],
		'post_excerpt'  => substr(sanitize_text_field(strip_tags($desc,'<p><br><span>')),0,300),
		'post_status'   => 'draft',
		'post_type'		=> 'product',
		'post_author'	=> 1
  	);
	if( amz_post_exists(wp_strip_all_tags($res[0]["title"])) == 0){
		if(amz_woo_check_nonce()===true){
			$id=wp_insert_post($my_post);
	
			// 1 Upload image and set it as featured image
			// 2 Upload more images & set them as featured
			amz_upload_wp_image($id, $uploadsdir.'/amz_images/'.$img1, true);
			amz_upload_wp_image($id, $uploadsdir.'/amz_images/'.$img, true);
			
			// Woocommerce
			$c=explode(", ",$res[0]["categories"]);
			wp_set_object_terms($id, 'external', 'product_type');
			//wp_set_object_terms($id, "", 'product_cat');
			//wp_set_object_terms($id, $c, 'product_tag');
			
			// Woocommerce meta
			update_post_meta($id, '_regular_price', sanitize_text_field($res[0]["offer orig"]));
			update_post_meta($id, '_sale_price', sanitize_text_field($res[0]["offer"]));
			update_post_meta($id, '_price', sanitize_text_field($res[0]["offer"]));
			update_post_meta($id, '_product_url', esc_url($res[0]["url"]));
			
			update_post_meta($id, 'amz_asin', sanitize_text_field($res[0]["asin"]));
			update_post_meta($id, 'amz_ean', sanitize_text_field($res[0]["ean"]));
			update_post_meta($id, 'amz_region', sanitize_text_field($res[0]["region"]));
			update_post_meta($id, 'amz_manufacturer', sanitize_text_field($res[0]["manufacturer"]));
			update_post_meta($id, 'amz_brand', sanitize_text_field($res[0]["brand"]));
			update_post_meta($id, 'amz_model', sanitize_text_field($res[0]["model"]));
			update_post_meta($id, 'amz_rating', sanitize_text_field($res[0]["rating"]));
			update_post_meta($id, 'amz_rating_expert', sanitize_text_field($res[0]["rating"]));
			update_post_meta($id, 'amz_url', sanitize_text_field($res[0]["url"]));
			update_post_meta($id, 'amz_categories', sanitize_text_field($res[0]["categories"]));
	
			foreach($res[0]["bullets"] as $bullet){
				add_post_meta($id, 'amz_bullet', sanitize_text_field((string)$bullet));
			}
	
			update_post_meta($id, 'amz_offer', sanitize_text_field($res[0]["offer"]));
			update_post_meta($id, 'amz_offer_orig', sanitize_text_field($res[0]["offer orig"]));
			update_post_meta($id, 'amz_offer_save', sanitize_text_field($res[0]["offer save"]));
			
			update_post_meta($id, 'wp_review_total', sanitize_text_field($res[0]["rating"]));
			update_post_meta($id, 'wp_review_type', 'star');
			echo '<br>Amazon Product successfully added to WooCommerce Products section'.post_exists(wp_strip_all_tags($res[0]["title"]));
		}else{
			echo '<br>nonce error.';
		}
	}else{
		echo '<br>Amazon Product Already exists post ID is '.post_exists(wp_strip_all_tags($res[0]["title"]));
	}
}

function amz_upload_wp_image($post_id, $filename, $featured = false) {
//function set_featured_image($post_id,$filename) {
	$wp_filetype = wp_check_filetype(basename($filename), null );
	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
		'post_content' => '',
		'post_status' => 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );

	if ($featured === true && wp_update_attachment_metadata( $attach_id,  $attach_data )) {
		// set as featured image
		return update_post_meta($post_id, '_thumbnail_id', $attach_id);
	}
}

	function amz_post_exists($title, $content = '', $date = '') {
		global $wpdb;
	 
		$post_title = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
		$post_content = wp_unslash( sanitize_post_field( 'post_content', $content, 0, 'db' ) );
		$post_date = wp_unslash( sanitize_post_field( 'post_date', $date, 0, 'db' ) );
	 
		$query = "SELECT ID FROM $wpdb->posts WHERE ";
		$args = array();
		
		$query .= 'post_type = %s AND ';
		$args[] = 'product';
	 
		if ( !empty ( $date ) ) {
			$query .= 'post_date = %s ';
			$args[] = $post_date;
		}
	 
		if ( !empty ( $title ) ) {
			$query .= 'post_title = %s ';
			$args[] = $post_title;
		}
	 
		if ( !empty ( $content ) ) {
			$query .= 'post_content = %s ';
			$args[] = $post_content;
		}
	 
		if ( !empty ( $args ) )
			return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );
	 
		return 0;
	}

?>
