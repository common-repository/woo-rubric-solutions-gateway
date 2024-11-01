<?php if (!defined('ABSPATH')) {exit;}

class RubricApi
{
    protected $id;
    protected $password;
    protected $endpoint;
    protected $errors = array();
    protected $codes = array();

    public function __construct($apiId, $apiPwd, $endpoint)
    {
        $this->id = $apiId;
        $this->password = $apiPwd;
        $this->endpoint = $endpoint;
    }

    public function processPayment($cardData, $posted, $order)
    {
        $data = $this->processData($cardData, $posted, $order->get_total(), $order->get_currency());
        $payload = json_encode(array('Transaction' => $data));
        $result = wp_remote_post($this->endpoint, array('body' => $payload, 'timeout' => 120));

        if ($result instanceof WP_Error) {
            $this->errors[] = $result->get_error_message();
            return false;
        }
        if (empty($result['response'])) {
            $this->codes[] = 600;
            $this->errors[] = __('Your credit card could not be processed', 'rubric_gateway');
            return false;
        }
        if (200 !== $result['response']['code']) {
            $this->codes[] = $result['response']['code'];
            $this->errors[] = isset($result['response']['message']) ? sprintf(__('Error: %s', 'rubric_gateway'), $result['response']['message']) : sprintf(__('Invalid Response [%s]', 'rubric_gateway'), $result['response']['code']);
            return false;
        }
        $this->codes[] = 200;
        // fix Control character error, possibly incorrectly encoded json error from api call
        if (false === ($response = json_decode(preg_replace('~[[:cntrl:]]~', '', $result['body']), true)) || empty($response['Transaction'])) {
            $this->errors[] = __('Your credit card could not be processed', 'rubric_gateway');
            return false;
        }
        if (empty($response['Transaction']['SuccessOrFail']) || 'Success' !== $response['Transaction']['SuccessOrFail']) {
             $this->errors[] = empty($response['Transaction']['FailureReason']) ? __('Your credit card could not be processed', 'rubric_gateway') : $response['Transaction']['FailureReason'];
             return false;
        }
        return empty($response['Transaction']['ConfirmationCode']) ? true : $response['Transaction']['ConfirmationCode'];
    }

    protected function processData($cardData, $posted, $amount, $currency)
    {
        $data = array(
            'RubricMerchantID'       => $this->id,
            'RubricMerchantPassword' => $this->password,
            'Card_Number'            => $cardData['number'], // for testing use RubricTest
            'CardHoldersName'        => $posted['billing_first_name'] . ' ' . $posted['billing_last_name'],
            'PurchaserFirstName'     => $posted['billing_first_name'],
            'PurchaserLastName'      => $posted['billing_last_name'],
            'Transaction_Type'       => 'Authorization', // Always Authorization at this time
            'Expiration_month'       => $cardData['exp']['month'], // one or two digits, whichever you want
            'Expiration_year'        => $cardData['exp']['year'],
            'TransactionAmount'      => $amount, // punctuation is not needed 
            'CVV'                    => $cardData['cvv'],
            'Currency'               => $currency,
            'PurchaserAgreesToTermsAndConditions' => 'Yes',
            'PurchaserEmail'         => $posted['billing_email'],
            'PurchaserAddress'       => $posted['billing_address_1'] . (empty($posted['billing_address_2']) ? '' : ' ' .  $posted['billing_address_2']),
            'PurchaserCity'          => $posted['billing_city'],
            'PurchaserState'         => $posted['billing_state'],
            'PurchaserZipCode'       => $posted['billing_postcode'],
            'PurchaserCountry'       => $posted['billing_country'],
        );
        if (!empty($posted['shipping_address_1'])) {
            $data['ShipToAddress'] = $posted['shipping_address_1'];
            if (!empty($posted['shipping_address_2'])) {
                $data['ShipToAddress'] .= ' ' . $posted['shipping_address_2'];
            }
            $data['ShipToCity'] = $posted['shipping_city'];
            $data['ShipToState'] = $posted['shipping_state'];
            $data['ShipToZipCode'] = $posted['shipping_postcode'];
            $data['ShipToCountry'] = $posted['shipping_country'];
        }
        return $data;
    }

    public function testData()
    {
        return array(
            'RubricMerchantID'       => $this->id,
            'RubricMerchantPassword' => $this->password,
            'Card_Number'            => '4242424242424242', // for testing use RubricTest
            'CardHoldersName'        => 'Michael B Smith',
            'Transaction_Type'       => 'Authorization', // Always Authorization at this time
            'Expiration_month'       => '01', // one or two digits, whichever you want
            'Expiration_year'        => '2019',
            'TransactionAmount'      => '1255.87', // punctuation is not needed 
            'CVV'                    => '879',
            'Currency'               => 'USD', // always USD at this time
            'PurchaserAgreesToTermsAndConditions' => 'Yes',
            'PurchaserEmail'         => 'JohnSmith@asdf.com',
            'PurchaserAddress'       => '123 Main St',
            'PurchaserCity'          => 'Vail',
            'PurchaserState'         => 'CO',
            'PurchaserZipCode'       => '42015',
        );
    }

    public function getLastError()
    {
        return empty($this->errors) ? '' : end($this->errors);
    }

    public function getLastResponseCode()
    {
        return empty($this->codes) ? 500 : end($this->codes);
    }
}
