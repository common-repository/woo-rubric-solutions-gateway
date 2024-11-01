<?php if (!defined('ABSPATH')) {exit;} ?>

<div class="form-row form-row-wide cc_row">
    <label for="rubric_card_number">
        <?php _e("Credit Card number", 'rubric_gateway') ?>
        <span class="required">*</span>
    </label>
    <div class="cc-number">
      <input id="rubric_card_number" name="rubric_card_number" autocomplete="off" class="input-text card-number" placeholder="•••• •••• •••• ••••" />
      <div class="card-type-logo"><i class="fa fa-credit-card fa-lg"></i></div>
   </div>
</div>
<div class="form-row cc_exp">
    <div class="form-row-first half-row">
        <label for="rubric_card_expiration">
            <?php _e("Expiration date", 'rubric_gateway') ?>
            <span class="required">*</span>
        </label>
            <input id="rubric_card_expiration" name="rubric_card_expiration" autocomplete="off" class="input-text expiry-date" placeholder="MM / YYYY" />
    </div>
    <div class="form-row-last half-row">
        <label for="rubric_card_cvv">
            <?php _e("Security code", 'rubric_gateway') ?>
            <span class="required">*</span>
        </label>
           <div class="ss-cvv">
            <input id="rubric_card_cvv" name="rubric_card_cvv" maxlength="4" autocomplete="off" class="input-text card-cvc" placeholder="CVV" />
           </div>
        <span class="help rubric_card_csc_description"></span>
    </div>
</div>
<div class="form-row cc_terms">
    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
        <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="rubric_terms" value="1" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['rubric_terms'] ) ), true ); ?> id="rubric_terms" /> <span><?php printf( __( 'I&rsquo;ve read and accept the Rubric Solutions <a href="%s" target="_blank" class="rubric-terms-and-conditions-link">terms &amp; conditions</a>', 'rubric_gateway' ), WC_Gateway_Rubric::TERMS_URL ); ?></span> <span class="required">*</span>
    </label>
</div>
