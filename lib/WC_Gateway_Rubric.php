<?php
if (!defined('ABSPATH')) {exit;}

class WC_Gateway_Rubric extends WC_Payment_Gateway
{
    protected $api;
    protected $cardData;
    protected $hasPayment = false;
    protected $confirmationCode;

    const ENDPOINT = 'https://www.rubric-solutions.com/v20180517/';
    const TERMS_URL = 'https://www.rubric-solutions.com/tc20180517/';

    public function __construct()
    {
        $this->id = 'rubric';
        // $this->icon;
        $this->has_fields = true;
        $this->method_title = 'RS Payments';
        $this->method_description = 'Credit card payments with RS Payments';
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        // run the card after validation so customer can retry if failure
        // add_action('woocommerce_after_checkout_validation', array($this, 'afterValidation'), 11, 2);
        // in wc 3.x this is a better place
        add_action('woocommerce_checkout_create_order', array($this, 'beforeCreateOrder'), 11, 2);
        // scripts
        add_action('wp_enqueue_scripts', array($this, 'loadScripts'), 11);
    }

/**
 * beforeCreateOrder
 * 
 * @note hooked on woocommerce_checkout_create_order
 * @note process payment here, so that customer can re-enter payment if it fails
 * @note the hook for this function is in a try catch so throw an exception upon failure
 */ 
    public function beforeCreateOrder($order, $data)
    {
        if (!isset($this->cardData)) {
            return;
        }
        // run the card
        $api = $this->getApi();
        if (false === ($result = $api->processPayment($this->cardData, $data, $order))) {
            // do not show user internal errors
            if (200 !== $api->getLastResponseCode() || '' === ($error = $api->getLastError())) {
                throw new RuntimeException(__('Payment gateway error, please try again.', 'rubric_gateway'));
                return;
            }
            throw new RuntimeException(sprintf(__('%s, please try again.', 'rubric_gateway'), $error));
            return;
        }
        if (true !== $result) { // returns a confirmation code
            $this->confirmationCode = $result;
        }
        $this->hasPayment = true; // if there is a transaction ID returned by gateway it would be saved here
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', 'rubric_gateway'),
                'label'       => __('Enable RS Payments Gateway', 'rubric_gateway'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => __('Title', 'rubric_gateway'),
                'type'        => 'text',
                'description' => __('This contains the title the customer sees during checkout.', 'rubric_gateway'),
                'default'     => __('Credit Card Payment', 'rubric_gateway')
            ),
            'description' => array(
                'title'       => __('Description', 'rubric_gateway'),
                'type'        => 'textarea',
                'description' => __('This is the description the customer will see during checkout.', 'rubric_gateway'),
                'default'     => 'Secure credit card payment.'
            ),
            'merchantId' => array(
                'title'       => __('Merchant ID', 'rubric_gateway'),
                'type'        => 'text',
                'description' => __('Your RS Payments Merchant ID.', 'rubric_gateway'),
                'default'     => ''
            ),
            'merchantPwd' => array(
                'title'       => __('Merchant Password', 'rubric_gateway'),
                'type'        => 'password',
                'description' => __('Your RS Payments Merchant Password.', 'rubric_gateway'),
                'default'     => ''
            ),
            /*'environment' => array(
                'title'       => __('Environment', 'rubric_gateway'),
                'type'        => 'select',
                'description' => __('Run your transactions in sandbox or live mode.', 'rubric_gateway'),
                'default'     => 'sandbox',
                'desc_tip'    => true,
                'options'     => array(
                    'sandbox'    => __('Sandbox', 'rubric_gateway'),
                    'live' => __('Live', 'rubric_gateway'),
                ),
            ),*/
            'enhanced_inputs' => array(
                'title'       => __('Enhanced Credit Card Fields', 'rubric_gateway'),
                'label'       => __('Used enhanced credit card fields that show credit card type hints as entered.', 'rubric_gateway'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'yes'
            ),
            'enhanced_tc' => array(
                'title'       => __('Enhanced Payment Terms', 'rubric_gateway'),
                'label'       => __('Used an in-page lightbox to show payment terms and conditions.', 'rubric_gateway'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'yes'
            ),
        );
    }

    public function payment_fields()
    {
        include RubricGatewayManager::getPluginDir() . 'templates/payment_fields.php';
    }

    public function validate_fields()
    {
        $this->cardData = null;
        // @note there is now in wc 3.x no way to find out if errors until after validation
        /*if (0 !== wc_notice_count('error')) { //there are already errors don't check
            return false;
        }*/
        if (empty($_POST['rubric_card_number']) || '' === ($cardNumber = trim($_POST['rubric_card_number']))) {
            wc_add_notice(__('Error: Invalid Credit Card Number', 'rubric_gateway'), 'error');
            return false;
        }

        if (empty($_POST['rubric_card_expiration']) || '' === trim($cardExp = trim($_POST['rubric_card_expiration'])) || !strstr($cardExp, '/')) {
            wc_add_notice(__('Error: Invalid Expiration Date', 'rubric_gateway'), 'error');
            return false;
        }

        if (empty($_POST['rubric_card_cvv']) || '' === trim($cardCvv = trim($_POST['rubric_card_cvv']))) {
            wc_add_notice(__('Error: Invalid CVV Code', 'rubric_gateway'), 'error');
            return false;
        }
        // extended validations
        if (!class_exists('InachoCreditCard', false)) {
            require_once(__DIR__ . '/InachoCreditCard.php');
        }
        // validate expiration
        list($expMonth, $expYear) = explode('/', $cardExp);
        if (empty($expMonth) || empty($expYear)) {
            wc_add_notice(__('Error: Invalid Expiration Date', 'rubric_gateway'), 'error');
            return false;
        }
        // allow 2-digit year
        if (2 == strlen($expYear)) {
            $expYear = '20' . $expYear;
        }
        if (!InachoCreditCard::validDate($expYear, $expMonth)) {
            wc_add_notice(__('Error: Invalid Expiration Date', 'rubric_gateway'), 'error');
            return false;
        }
        $validate = InachoCreditCard::validCreditCard($cardNumber);
        if (!$validate['valid']) {
            wc_add_notice(__('Error: Invalid Credit Card Number', 'rubric_gateway'), 'error');
            return false;
        }
        if (!InachoCreditCard::validCvc($cardCvv, $validate['type'])) {
            wc_add_notice(__('Error: Invalid CVV Code', 'rubric_gateway'), 'error');
            return false;
        }
        // agree to terms
        if (empty($_POST['rubric_terms'])) {
            wc_add_notice(__('Please agree to the Rubric Solutions Payment terms and conditions', 'rubric_gateway'), 'error');
            return false;
        }
        // if all is well, run the card on the hook, woocommerce_after_checkout_validation, if $errors count is 0;
        $validate['exp'] = array('month' => $expMonth, 'year' => $expYear);
        $validate['cvv'] = $cardCvv;
        $this->cardData = $validate;
        return true;
    }

/**
 * afterValidation
 * 
 * @deprecated for beforeCreateOrder
 */ 
    public function afterValidation($data, $errors)
    {
        if (!empty($errors->errors) || !isset($this->cardData)) {
            return;
        }
        // run the card
        $api = $this->getApi();
        if (false === ($result = $api->processPayment($this->cardData, $data))) {
            // do not show user internal errors
            if (200 !== $api->getLastResponseCode()) {
                wc_add_notice(__('Payment gateway error, please try again.', 'rubric_gateway'), 'error');
                return;
            }
            wc_add_notice(sprintf(__('Payment error: %s, please try again.', 'rubric_gateway'), $api->getLastError()), 'error');
            return;
        }
        // @note good result store for process payment hook
    }

    public function process_payment($orderId)
    {
        if (!$this->hasPayment) {
            // @note this won't happen, it will throw expception when payment fails and will not get to this point
            wc_add_notice(__('Your credit card could not be processed, please try again.', 'rubric_gateway'), 'error');
            return;
        }
        $order = wc_get_order($orderId);
        $order->add_order_note(__('RS Payment Successful', 'rubric_gateway'));
        $order->payment_complete(($hasConfirmation = isset($this->confirmationCode)) ? $this->confirmationCode : '');
        if ($hasConfirmation) {
            $order->add_order_note(sprintf(__('Confirmation code: %s', 'rubric_gateway'), $this->confirmationCode));
        }
        WC()->cart->empty_cart();

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    public function getApi()
    {
        if (!isset($this->api)) {
            if (!class_exists('RubricApi', false)) {
                require_once(__DIR__ . '/RubricApi.php');
            }
            $this->api = new RubricApi($this->settings['merchantId'], $this->settings['merchantPwd'], self::ENDPOINT);
        }
        return $this->api;
    }

    public function loadScripts()
    {
        $version = ($isDebug = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? uniqid() : RubricGatewayManager::VERSION;
        // wp_deregister_style('font-awesome');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', '', null);
        if ($enhancedTc = (!isset($this->settings['enhanced_tc']) || 'yes' === $this->settings['enhanced_tc'])) {
            wp_enqueue_style('magnific-popup', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css', [], null);
            wp_enqueue_script('magnific-popup', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js', ['jquery'], null);
        }
        wp_enqueue_style('rubric-checkout', ($url = RubricGatewayManager::getPluginUrl()) . '/css/checkout.css', '', $version);
        wp_enqueue_script('cleave', $url . '/js/cleave' . ($isDebug ? '.js' : '.min.js'), array(), null, true);
        wp_enqueue_script('rubric-checkout', $url . '/js/checkout' . ($isDebug ? '.js' : '.min.js'), array('jquery'), $version, true);
        wp_localize_script('rubric-checkout', 'rubricParams', array('enhanced' => !isset($this->settings['enhanced_inputs']) || 'yes' === $this->settings['enhanced_inputs'] ? 1 : 0, 'enhanced_tc' => $enhancedTc, 'tc_url' => self::TERMS_URL));
    }

/**
 * addGateway
 * 
 * adds gateway to WooCommerce
 * 
 * @note is a static method, WooCommerce will create instance of class on demand
 */ 
    public static function addGateway($methods)
    {
        // only for USD
        /*if ('USD' !== get_woocommerce_currency()) {
            return $methods;
        }*/
        $methods[] = 'WC_Gateway_Rubric';
        return $methods;
    }
}

add_filter('woocommerce_payment_gateways', 'WC_Gateway_Rubric::addGateway');
