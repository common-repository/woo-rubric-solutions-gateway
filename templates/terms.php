<?php
/**
 * Checkout terms and conditions checkbox
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.1.1
 */
if ( ! defined( 'ABSPATH' ) ) {exit;}?>

	<p class="form-row terms wc-terms-and-conditions">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="rubric_terms" value="1" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['rubric_terms'] ) ), true ); ?> id="terms" /> <span><?php printf( __( 'I&rsquo;ve read and accept the Rubric Solutions <a href="%s" target="_blank" class="woocommerce-terms-and-conditions-link">terms &amp; conditions</a>', 'rubric_gateway' ), WC_Gateway_Rubric::TERMS_URL ); ?></span> <span class="required">*</span>
		</label>
	</p>
