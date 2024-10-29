<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	global $wp_post_types,$wpreview;

	function amz_info_check_nonce() {
		// Check if our nonce is set.
		if ( ! isset( $_POST['amz_info_box_nonce'] ) ) { return false; }
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['amz_info_box_nonce'], 'amz_info_check_nonce' ) ) { return false; }
		/* OK, it's safe for us to save the data now. */
		return true;
	}

	$amzinf=false;
	//$amzinf=true;

	if(empty($_POST['amz_amazonaws'])) $_POST['amz_amazonaws']='';
	if(empty($_POST['amz_amazonsecret'])) $_POST['amz_amazonsecret']='';

	$safe_amazonaws = $_POST['amz_amazonaws']; if( ! $safe_amazonaws ){$safe_amazonaws = '';}
	if( strlen( $safe_amazonaws ) > 20 ){$safe_amazonaws = substr( $safe_amazonaws, 0, 20 );}
	$safe_amazonsecret = $_POST['amz_amazonsecret']; if( ! $safe_amazonsecret ){$safe_amazonsecret = '';}
	if( strlen( $safe_amazonsecret ) > 40 ){$safe_amazonsecret = substr( $safe_amazonsecret, 0, 40 );}
	$_POST['amz_amazonaws']=$safe_amazonaws; $_POST['amz_amazonsecret']=$safe_amazonsecret;

	$amz_cont=get_option('amz_cont', 'insert');
	$amz_amazonaws=get_option('amz_amazonaws', '');
	$amz_amazonsecret=get_option('amz_amazonsecret', '');
	if($amz_cont!='insert' && $amz_cont!='shortcode') $amz_cont='insert';

	if(!empty($_POST['submit'])){
		if(amz_info_check_nonce()===true){
			update_option('amz_cont', sanitize_text_field($_POST['amz_cont'])); $amz_cont=esc_html($_POST['amz_cont']);
			update_option('amz_amazonaws', sanitize_text_field($_POST['amz_amazonaws'])); $amz_amazonaws=esc_html($_POST['amz_amazonaws']);
			update_option('amz_amazonsecret', sanitize_text_field($_POST['amz_amazonsecret'])); $amz_amazonsecret=esc_html($_POST['amz_amazonsecret']);
		}else{
			echo '<br>nonce error.';
		}
	}else{
		$_POST['amz_cont']=$amz_cont;
		$_POST['amz_amazonaws']=$amz_amazonaws;
		$_POST['amz_amazonsecret']=$amz_amazonsecret;
	}

	$amz['plugin-content']=$amz_cont;
	$amz['active-theme']=wp_get_theme()->get('Name');
	$amz['active-plugins']=get_option('active_plugins');

	if(PHP_OS == 'Linux') {$sep='/'; }else{ $sep='\\';}
	$amzfile=str_replace('amz-free'.$sep.'aminfo.php','',__file__);
	
	foreach($amz['active-plugins'] as $plugin){
		unset($t); $t=get_plugin_data($amzfile.$plugin, false );
		$amz['active-plugin-names'][]=$t['Name'];
	}
	$ul=wp_upload_dir();
	$amz['urls']['blog-url']=site_url();
	$amz['urls']['uploads-url']=$ul['baseurl'];
	$amz['urls']['plugins-url']=plugins_url();
	$amz['urls']['themes-url']=get_theme_root_uri();
	foreach($wp_post_types as $posttypes){
		$amz['post-types'][]=$posttypes->name;
	}
?>
<style type="text/css">
.probox {
	max-width: 300px;
	text-align: center;
	display: inline-block;
	border: #5151C6 solid 3px;
	float: right;
	padding: 0 6px;
	margin: 6px;
}
</style>
<div class="probox">
	<p>This is the free version of the Amazon Product Reviews Pro Plugin <b>Amazon Affiliate Reviews</b> that adds an Amazon product to the Reviews section or WooCommerce Products sections.</p>
	<p>With the Pro version it can also create 3 or 5 column comparason charts and allows you to update the products price to reflect what's on Amazon.</p>
	<hr />
	<p><b>More infomation on the Pro version <a href="https://xeniasites.com/amazon-review-plugin/" target="_blank">here</a></b></p>
</div>
<p>This is the free version of the Amazon Product Reviews Plugin <b>Amazon Affiliate Reviews</b> that adds an Amazon product to the Reviews section or WooCommerce Products section (if WooCommerce is installed).</p>
<p>Current Active Theme <b><?php echo $amz['active-theme']; ?></b></p>
<p><b>Shortcodes available:</b>
<blockquote>
	<p style="color:#060"> <b>[amz-main-infobox]</b> - This displays title & some product info as well as buy now box & a link to [amz-expert]<br />
		<b>[amz-specs]</b> - This is for various specifications ie: (HD, yes), (screen size, 720p) etc.<br />
		<b>[amz-expert]</b> - This provides a link anchor to Expert Review for [amz-main-infobox], place this just above the review but after shortcodes here.<br />
		<b>[amz-reviews-questions]</b> - This provides 'Customer Reviews & Opinions' and 'Your Questions Answered' sections.<br />
		<b>[amz-proscons]</b> - This provides the products pros & cons<br />
		<b>[amz-bottom-buybox]</b> - This provides a buy now box with a small picture & title along with buy button. </p>
	<p>When <b>Auto Insert</b> is selected the above order is the display order and the main review content is inbetween <b>[amz-expert]</b> and <b>[amz-reviews-quesitions]</b>.<br />
		When <b>Shortcode</b> is selected you can place any of the shortcodes above where you would like them to appear in the main review content.</p>
	<form action="" method="post">
<?php
	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'amz_info_check_nonce', 'amz_info_box_nonce' );
?>
		<table width="100%" border="0" cellspacing="1" cellpadding="1">
			<tr>
				<td width="100"><input type="radio" name="amz_cont" value="insert"<?php if($_POST['amz_cont']=='insert') echo ' checked' ?>>
					Auto Insert </td>
				<td>Allow this plugin to auto insert its content into your reviews/products.</td>
			</tr>
			<tr>
				<td width="100"><input type="radio" name="amz_cont" value="shortcode"<?php if($_POST['amz_cont']=='shortcode') echo ' checked' ?>>
					Shortcode </td>
				<td>manually add this plugins content using the shortcodes above in your reviews/products</td>
			</tr>
			<tr>
				<td colspan="2" height="30" valign="bottom"><b>Your Amazon AWS Access Key Pair (Required):</b></td>
			</tr>
			<tr>
				<td colspan="2">AWSAccessKeyId:
					<input type="text" name="amz_amazonaws" value="<?php if($_POST['amz_amazonaws']) echo esc_attr($_POST['amz_amazonaws']); ?>" maxlength="20" size="40"></td>
			</tr>
			<tr>
				<td colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;AWSSecretKey:
					<input type="text" name="amz_amazonsecret" value="<?php if($_POST['amz_amazonaws']) echo esc_attr($_POST['amz_amazonsecret']); ?>" maxlength="40" size="40"></td>
			</tr>
			<tr>
				<td width="100">&nbsp;</td>
				<td><input name="submit" type="submit"></td>
			</tr>
		</table>
	</form>
</blockquote>
<p><b>Sections available:</b>
<blockquote>
	<p style="color:#060"> <b>Reviews</b> - Add an Amazon Product to the Reviews section<br />
		<b>WooCommerce</b> - Add an Amazon Product to WooCommerce section </p>
	<p><b>Additional Sections available with Pro version:</b></p>
	<p style="color:#900"> <b>Comparison Tables</b> - Pro version - Create a 3 or 5 column comparason chart<br />
		<b>Update Prices</b> - Pro version - Update prices to reflect what's on Amazon </p>
</blockquote>
</p>
<?php
	if($amzinf===true){
		echo '<pre>';
		print_r($amz);
		echo '</pre>';
	}	
?>
