<?php

/**
 * Adds a box to the main column on the Post and Page or Custom Page edit screens.
 */
function amz_add_meta_box() {

	//$screens = array( 'post', 'page', 'reviews' );
	$screens = array( 'reviews', 'product' );

	foreach ( $screens as $screen ) {
		if($screen == 'reviews'){
			add_meta_box(
				'amz_sectionid',
				__( 'Reviews Custom Amazon Fields', 'amz_textdomain' ),
				'amz_meta_box_callback',
				$screen,
				'advanced',
				'high'
			);
		}elseif($screen == 'product'){
			add_meta_box(
				'amz_sectionid',
				__( 'WooCommerce Product Custom Amazon Fields', 'amz_textdomain' ),
				'amz_meta_box_callback',
				$screen,
				'advanced',
				'high'
			);
		}
	}
}
add_action( 'add_meta_boxes', 'amz_add_meta_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function amz_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'amz_save_meta_box_data', 'amz_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */

echo "<div><b>Product Information</b></div>";

	$value = get_post_meta( $post->ID, 'amz_asin', true );
	echo '<div align="right"><label for="field0a">';
	_e( 'ASIN: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field0a" name="field0a" value="' . esc_attr( $value ) . '" size="70" /></div>';

	$value = get_post_meta( $post->ID, 'amz_ean', true );
	echo '<div align="right"><label for="field0b">';
	_e( 'EAN: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field0b" name="field0b" value="' . esc_attr( $value ) . '" size="70" /></div>';

	$value = get_post_meta( $post->ID, 'amz_manufacturer', true );
	echo '<div align="right"><label for="field0">';
	_e( 'Manufacturer: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field0" name="field0" value="' . esc_attr( $value ) . '" size="70" /></div>';

	$value = get_post_meta( $post->ID, 'amz_brand', true );
	echo '<div align="right"><label for="field1">';
	_e( 'Brand: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field1" name="field1" value="' . esc_attr( $value ) . '" size="70" /></div>';
	
	$value = get_post_meta( $post->ID, 'amz_model', true );
	echo '<div align="right"><label for="field2">';
	_e( 'Make/Model: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field2" name="field2" value="' . esc_attr( $value ) . '" size="70" /></div>';
	
	$value = get_post_meta( $post->ID, 'amz_rating', true );
	echo '<div align="right"><label for="field3">';
	_e( 'Rating: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field3" name="field3" value="' . esc_attr( $value ) . '" size="70" /></div>';
	
	$value = get_post_meta( $post->ID, 'amz_rating_expert', true );
	echo '<div align="right"><label for="field3a">';
	_e( 'Expert Rating: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field3a" name="field3a" value="' . esc_attr( $value ) . '" size="70" /></div>';
	
	$value = get_post_meta( $post->ID, 'amz_url', true );
	echo '<div align="right"><label for="field4">';
	_e( 'Amazon URL: ', 'amz_textdomain' );
	echo '</label><textarea id="field4" name="field4" cols="68" rows="1">' . esc_attr( $value ) . '</textarea></div>';
	
	$value = get_post_meta( $post->ID, 'amz_categories', true );
	echo '<div align="right"><label for="field5">';
	_e( 'Categories: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field5" name="field5" value="' . esc_attr( $value ) . '" size="70" /></div>';
	
	
	$value = get_post_meta( $post->ID, 'amz_offer', true );
	echo '<div align="right"><label for="field6">';
	_e( 'Sale Price: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field6" name="field6" value="' . esc_attr( $value ) . '" size="70" /></div>';
	
	$value = get_post_meta( $post->ID, 'amz_offer_orig', true );
	echo '<div align="right"><label for="field7">';
	_e( 'Original Price: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field7" name="field7" value="' . esc_attr( $value ) . '" size="70" /></div>';
	
	$value = get_post_meta( $post->ID, 'amz_offer_save', true );
	echo '<div align="right"><label for="field8">';
	_e( 'Amount Saved: ', 'amz_textdomain' );
	echo '</label><input type="text" id="field8" name="field8" value="' . esc_attr( $value ) . '" size="70" /></div>';
	
echo "<hr>";
echo "<div><b>Product Features</b></div>";
	$value = get_post_meta( $post->ID, 'amz_bullet');
		for($n=0;$n<10;$n++){
			echo '<div align="right"><label for="field9">';
			_e( 'Feature Point: ', 'amz_textdomain' );
			if(empty($value[$n])) $value[$n]='';
			echo '</label><textarea id="field9" name="field9[]" cols="68" rows="1">'.esc_attr($value[$n]).'</textarea></div>';
		}
	
echo "<hr>";
echo "<div><b>Product Specifications</b></div>";

	$value = get_post_meta( $post->ID, 'amz_specs');
	echo "<div>";
		for($n=0;$n<20;$n=$n+2){
			echo '<div style="width:48%; float:left;" align="right"><label for="field9a">';
			_e( 'Spec Name: ', 'amz_textdomain' );
			if(empty($value[$n])) $value[$n]='';
			echo '</label><input type="text" id="field9a" name="field9a[]" value="'.esc_attr($value[$n]).'" size="20" /></div>';
			echo '<div style="width:48%; float:left;" align="right"><label for="field9a">';
			_e( 'Spec Value: ', 'amz_textdomain' );
			if(empty($value[$n+1])) $value[$n+1]='';
			echo '</label><input type="text" id="field9a" name="field9a[]" value="'.esc_attr($value[$n+1]).'" size="20" /></div>';
		}
echo '<div style="clear:both;"></div></div>';		

echo "<hr>";

    $value = get_post_meta($post->ID, 'amz_pros' , true ) ;
	echo '<label for="field9b"><b>Product Pros</b></label>';
    wp_editor( esc_textarea($value), 'field9b', array('media_buttons'=>false, 'textarea_rows'=>4, 'wpautop'=>false) );

    $value = get_post_meta($post->ID, 'amz_cons' , true ) ;
	echo '<label for="field9c"><b>Product Cons</b></label>';
    wp_editor( esc_textarea($value), 'field9c', array('media_buttons'=>false, 'textarea_rows'=>4, 'wpautop'=>false) );

echo "<hr>";

    $value = get_post_meta($post->ID, 'amz_opinions' , true ) ;
	echo '<label for="field10"><b>Customer Reviews & Opinions</b></label>';
    wp_editor( esc_textarea($value), 'field10', array('media_buttons'=>false, 'textarea_rows'=>4, 'wpautop'=>false) );

    $value = get_post_meta($post->ID, 'amz_questions' , true ) ;
	echo '<label for="field11"><b>Your Questions Answered</b></label>';
    wp_editor( esc_textarea($value), 'field11', array('media_buttons'=>false, 'textarea_rows'=>4, 'wpautop'=>false) );
}



/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function amz_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['amz_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['amz_meta_box_nonce'], 'amz_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	/* OK, it's safe for us to save the data now. */
	
	// Sanitize user input & update database.
	$amz_data = sanitize_text_field( $_POST['field0a'] );
	update_post_meta( $post_id, 'amz_asin', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field0b'] );
	update_post_meta( $post_id, 'amz_ean', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field0'] );
	update_post_meta( $post_id, 'amz_manufacturer', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field1'] );
	update_post_meta( $post_id, 'amz_brand', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field2'] );
	update_post_meta( $post_id, 'amz_model', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field3'] );
	update_post_meta( $post_id, 'amz_rating', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field3a'] );
	update_post_meta( $post_id, 'amz_rating_expert', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field4'] );
	update_post_meta( $post_id, 'amz_url', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field5'] );
	update_post_meta( $post_id, 'amz_categories', $amz_data );
	
	
	$amz_data = sanitize_text_field( $_POST['field6'] );
	update_post_meta( $post_id, 'amz_offer', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field7'] );
	update_post_meta( $post_id, 'amz_offer_orig', $amz_data );
	
	$amz_data = sanitize_text_field( $_POST['field8'] );
	update_post_meta( $post_id, 'amz_offer_save', $amz_data );
	
	delete_post_meta($post_id, 'amz_bullet');
	foreach($_POST['field9'] as $bullet){
		if(!empty($bullet)){
			add_post_meta($post_id, 'amz_bullet', sanitize_text_field($bullet));
		}
	}
	
	delete_post_meta($post_id, 'amz_specs');
	foreach($_POST['field9a'] as $specs){
		if(!empty($specs)){
			add_post_meta($post_id, 'amz_specs', sanitize_text_field($specs));
		}
	}

	$amz_data = htmlspecialchars( $_POST['field9b'] );
	update_post_meta( $post_id, 'amz_pros', $amz_data );
	
	$amz_data = htmlspecialchars( $_POST['field9c'] );
	update_post_meta( $post_id, 'amz_cons', $amz_data );

	$amz_data = htmlspecialchars( $_POST['field10'] );
	update_post_meta( $post_id, 'amz_opinions', $amz_data );
	
	$amz_data = htmlspecialchars( $_POST['field11'] );
	update_post_meta( $post_id, 'amz_questions', $amz_data );
	
}
add_action( 'save_post', 'amz_save_meta_box_data' );
?>