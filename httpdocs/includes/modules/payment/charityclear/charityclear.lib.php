<?php

class charityclear_lib
{

    public $form_url = "https://gateway.charityclear.com/paymentform/";
    public $direct_url = "https://gateway.charityclear.com/direct/";

    public $version = 'ZenCart-1';
    private $secret;



    private $card = array();

    function __construct($secret = 'Circle4Take40Idea'){
        $this->secret = $secret;
    }

    function module_title(){
        // Set the title and description based on the mode the module is in: Admin or Catalog
        if ((defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG === true) || (!isset($_GET['main_page']) || $_GET['main_page'] == '')) {
            // In Admin mode
           return  MODULE_PAYMENT_CHARITYCLEAR_ADMIN_TITLE;
        } else {
            // In Catalog mode
            return MODULE_PAYMENT_CHARITYCLEAR_CATALOG_TEXT_TITLE;
        }
    }

    /**
     * performs checks to make sure we have everything needed for payments
     *
     * @return bool
     */
    function valid_setup()
    {

        /**
         * check merchant password has been set
         */
        if (MODULE_PAYMENT_CHARITYCLEAR_MERCHANT_PASSWORD !== 'TEST' || strlen(MODULE_PAYMENT_CHARITYCLEAR_MERCHANT_PASSWORD) == 0) {
            return false;
        }

        /**
         * check if the user has disabled the payment method
         */
        if (MODULE_PAYMENT_CHARITYCLEAR_STATUS != 'True') {
            return false;
        }


        if (MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_TYPE === 'Direct' && (!defined('ENABLE_SSL') || ENABLE_SSL != 'true')){

          return false;
        }

        return true;
    }

    function form_url()
    {
        if (MODULE_PAYMENT_CHARITYCLEAR_CAPTURE_TYPE == 'Hosted') {
            return $this->form_url;
        } else {
            //return $this->direct_form_url;
        }
    }

    function draw_direct_form(){
        global $order;

        for ($i=1; $i<13; $i++) {
            $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
        }

        $today = getdate();
        for ($i=$today['year']; $i < $today['year']+15; $i++) {
            $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

        $selection = array('id' => 'charityclear',
            'module' => $this->module_title(),
            'fields' => array(array('title' => MODULE_PAYMENT_CHARITYCLEAR_CARD_HOLDER,
                'field' => zen_draw_input_field('charityclear_card_holder', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="charityclear-cc-owner"'. $onFocus . ' autocomplete="off"'),
                'tag' => 'charityclear-cc-owner'),
                array('title' => MODULE_PAYMENT_CHARITYCLEAR_CARD_NUMBER,
                    'field' => zen_draw_input_field('charityclear_card_number', $ccnum, 'id="charityclear-cc-number"' . $onFocus . ' autocomplete="off"'),
                    'tag' => $this->code.'-cc-number'),
                array('title' => MODULE_PAYMENT_CHARITYCLEAR_CARD_EXPIRE,
                    'field' => zen_draw_pull_down_menu('charityclear_card_expires_month', $expires_month, strftime('%m'), 'id="charityclear-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('charityclear_card_expires_year', $expires_year, '', 'id="charityclear-cc-expires-year"' . $onFocus),
                    'tag' => 'charityclear-card-expires-month'),
                array('title' => MODULE_PAYMENT_CHARITYCLEAR_CARD_CVV,
                    'field' => zen_draw_input_field('charityclear_card_cvv', '', 'size="4" maxlength="4"'. ' id="charityclear-cc-cvv"' . $onFocus . ' autocomplete="off"') . ' ' . '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . MODULE_PAYMENT_CHARITYCLEAR_CARD_CVV_HELP . '</a>',
                    'tag' => 'charityclear-card-cvv')));

        return $selection;
    }

    function draw_direct_form_button(){
        // These are hidden fields on the checkout confirmation page
        $process_button_string = zen_draw_hidden_field('charityclear_card_holder', $_POST['charityclear_card_holder']) .
            zen_draw_hidden_field('charityclear_card_expires', $this->card['expiry_month'] . substr($this->card['expiry_year'], -2)) .
            zen_draw_hidden_field('charityclear_card_expires_month', $this->card['expiry_month']) .
            zen_draw_hidden_field('charityclear_card_expires_year', substr($this->card['expiry_year'], -2)) .
            zen_draw_hidden_field('charityclear_card_type', $this->card['card_type']) .
            zen_draw_hidden_field('charityclear_card_number', $this->card['card_number']) .
            zen_draw_hidden_field('charityclear_card_cvv', $_POST['charityclear_card_cvv']);
        $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

        return $process_button_string;
    }

    function draw_hosted_form(){
        global $order, $template, $current_page_base;


        if (substr_count($order->info['total'], ".") == 1) {

            $exploded = explode(".", $order->info['total']);
            if (strlen($exploded[1]) == 1) {
                $amount = str_replace(".", "", $order->info['total']) * 10;
            } else {
                $amount = str_replace(".", "", $order->info['total']);
            }
        } else {
            $amount = $order->info['total'] * 100;
        }

        //We're gonna need to zen_draw_hidden_field for EVERY FIELD.

        $tu = md5(mktime());

        $process_button_string = zen_draw_hidden_field('redirectURL', str_replace('&amp;', '&', zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true)) . '&') .
            zen_draw_hidden_field('transactionUnique', $tu) .
            zen_draw_hidden_field('action', 'SALE') .
            zen_draw_hidden_field('type', '1') .
            zen_draw_hidden_field('amount', $amount) .
            zen_draw_hidden_field('customerName', $order->billing['firstname'] . ' ' . $order->billing['lastname']) .
            zen_draw_hidden_field('customerAddress', $order->billing['street_address'] . "\n" . $order->billing['suburb'] . "\n" . $order->billing['city']) .
            zen_draw_hidden_field('customerPostCode', $order->billing['postcode']) .
            zen_draw_hidden_field('customerEmail', $order->customer['email_address']) .
            zen_draw_hidden_field('customerPhone', $order->customer['telephone']) .
            zen_draw_hidden_field('merchantID', MODULE_PAYMENT_CHARITYCLEAR_MERCHANT_ID) .
            zen_draw_hidden_field('threeDSRequired', MODULE_PAYMENT_CHARITYCLEAR_3DS == 'True' ? 'Y' : 'N') .
            zen_draw_hidden_field('countryCode', MODULE_PAYMENT_CHARITYCLEAR_COUNTRY_ID) .
            zen_draw_hidden_field('currencyCode', MODULE_PAYMENT_CHARITYCLEAR_CURRENCY_ID).
            zen_draw_hidden_field('merchantData', $this->version);

        $req = array(
            'securityToken' =>  $_SESSION['securityToken'],
            'transactionUnique' => $tu,
            'action' => 'SALE',
            'type' => 1,
            'amount' => $amount,
            'customerName' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
            'customerAddress' => $order->billing['street_address'] . "\n" . $order->billing['suburb'] . "\n" . $order->billing['city'],
            'customerPostCode' => $order->billing['postcode'],
            'customerEmail' => $order->customer['email_address'],
            'customerPhone' => $order->customer['telephone'],
            'merchantID' => MODULE_PAYMENT_CHARITYCLEAR_MERCHANT_ID,
            'threeDSRequired' => MODULE_PAYMENT_CHARITYCLEAR_3DS == 'True' ? 'Y' : 'N',
            'countryCode' => MODULE_PAYMENT_CHARITYCLEAR_COUNTRY_ID,
            'currencyCode' => MODULE_PAYMENT_CHARITYCLEAR_CURRENCY_ID,
            'merchantData' => $this->version,
            'btn_submit' => zen_output_string($template->get_template_dir('button_confirm_order.gif', DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $_SESSION['language'] . '/') . 'button_confirm_order.gif')
        );


        $process_button_string .= zen_draw_hidden_field('signature', $this->signRequest($req));

        return $process_button_string;
    }

    function process_hosted(){
        global $messageStack, $order;

        if (substr_count($order->info['total'], ".") == 1) {

            $exploded = explode(".", $order->info['total']);
            if (strlen($exploded[1]) == 1) {
                $amount = str_replace(".", "", $order->info['total']) * 10;
            } else {
                $amount = str_replace(".", "", $order->info['total']);
            }
        } else {
            $amount = $order->info['total'] * 100;
        }

        if (($_POST["responseCode"] != "0") || ($_POST["amountReceived"] != $amount)) {

            $payment_error_return = 'ERROR ' . strtolower($_POST["responseMessage"]);

            $messageStack->add_session('checkout_payment', $payment_error_return, 'error');

            //  die(var_dump($_GET, $_POST));
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        } else {
            return true;
        }
    }

    function process_direct(){

        global $order;



        $req = array(
            "merchantID" => MODULE_PAYMENT_CHARITYCLEAR_MERCHANT_ID,
            "action" => "SALE",
            "type" => 1,
            "transactionUnique" => uniqid(),
            "currencyCode" => MODULE_PAYMENT_CHARITYCLEAR_CURRENCY_ID,
            "amount" => $order->info['total'] * 100,
            "orderRef" => $_POST['orderRef'],
            "cardNumber" => $_POST['charityclear_card_number'],
            "cardExpiryMonth" => $_POST['charityclear_card_expires_month'],
            "cardExpiryYear" => $_POST['charityclear_card_expires_year'],
            "cardCVV" => $_POST['charityclear_card_cvv'],
            "customerName" => $_POST['charityclear_card_holder'],
            "customerEmail" => $order->customer['email_address'],
            "customerPhone" => $order->customer['telephone'],//"+44 (0) 845 00 99 575",
            "customerAddress" => $order->billing['street_address']."\n".$order->billing['suburb']."\n".$order->billing['city']."\n".$order->billing['state'],
            "countryCode" => MODULE_PAYMENT_CHARITYCLEAR_COUNTRY_ID,
            "returnInternalData" => "Y",
            "threeDSRequired" => MODULE_PAYMENT_CHARITYCLEAR_3DS == 'True' ? 'Y' : 'N',
            "customerPostCode" => $order->billing['postcode'],
            "threeDSMD" => (isset($_REQUEST['MD']) ? $_REQUEST['MD'] : null),
            "threeDSPaRes" => (isset($_REQUEST['PaRes']) ? $_REQUEST['PaRes'] : null),
            "threeDSPaReq" => (isset($_REQUEST['PaReq']) ? $_REQUEST['PaReq'] : null)
        );

        $res = $this->makeRequest('https://gateway.charityclear.com/direct/',$req);
       // echo $res['responseCode'];
        if ($res['responseCode'] == 65802) {


            $_SESSION['CHARITYCLEARDATA'] = $res;

            zen_redirect(zen_href_link('charityclear_3ds', '', 'SSL', true, false));

            // Send details to 3D Secure ACS and the return here to repeat request
            $pageUrl = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageUrl .=
                    $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
            } else {
                $pageUrl .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            }
            echo "<p>Your transaction requires 3D Secure Authentication</p>
        <form action=\"" . htmlentities($res['threeDSACSURL']) . "\" method=\"post\">
        <input type=\"hidden\" name=\"MD\" value=\"" . htmlentities($res['threeDSMD']) . "\">
        <input type=\"hidden\" name=\"PaReq\" value=\"" . htmlentities($res['threeDSPaReq']) .
                "\">
                    <input type=\"hidden\" name=\"TermUrl\" value=\"" . htmlentities($pageUrl) . "\">
        <input type=\"submit\" value=\"Continue\">
        </form>";
            //return false;
die();

        } elseif ($res['responseCode'] === "0") {


            $_SESSION['CHARITYCLEARDATA'] = $res;


            print "<pre>";
            print_r($res);
            print "</pre>";
            echo "<p>Thank you for your payment</p>";
            die();
            return true;
        } else {

            echo "<p>Failed to take payment: " . htmlentities($res['responseMessage']) . "</p>";
        }

    }

    function card_data_check(){
        global $db, $messageStack;

        include(DIR_WS_CLASSES . 'cc_validation.php');

       // var_dump($_POST);
        $cc_validation = new cc_validation();
        $result = $cc_validation->validate($_POST['charityclear_card_number'], $_POST['charityclear_card_expires_month'], $_POST['charityclear_card_expires_year']);
        $error = '';
        switch ($result) {
            case -1:
                $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
                break;
            case -2:
            case -3:
            case -4:
                $error = TEXT_CCVAL_ERROR_INVALID_DATE;
                break;
            case false:
                $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
                break;
        }

       // die();
        if ( ($result == false) || ($result < 1) ) {
            $payment_error_return = 'payment_error=charityclear' ;
            $error_info2 = '&error=' . urlencode($error) . '&charityclear_card_holder=' . urlencode($_POST['charityclear_card_holder']) . '&charityclear_card_expires_month=' . $_POST['charityclear_card_expires_month'] . '&charityclear_card_expires_year=' . $_POST['charityclear_card_expires_year'];
            $messageStack->add_session('checkout_payment', $error . '<!-- [charityclear] -->', 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        // if no error, continue with validated data:
        $this->card['card_type'] = $cc_validation->cc_type;
        $this->card['card_number'] = $cc_validation->cc_number;
        $this->card['expiry_month'] = $cc_validation->cc_expiry_month;
        $this->card['expiry_year'] = $cc_validation->cc_expiry_year;


    }

    function makeRequest($url,$params, $verb = 'POST'){


        $cparams = array(
            'http' => array(
                'method'        => $verb,
                'ignore_errors' => true
            )
        );
        if ($params !== null && !empty($params)) {

            if (!isset($params['signature'])) {
                $params['signature'] = $this->signRequest($params);
            }

            $params = http_build_query($params);
            
            $cparams["http"]['header'] = 'Content-Type: application/x-www-form-urlencoded';
            $cparams['http']['content'] = $params;
            
        }
        

        $context = stream_context_create($cparams);
        $fp = fopen($url, 'rb', false, $context);
        if (!$fp) {
            $res = false;
        } else {


            $res = stream_get_contents($fp);
            parse_str($res,$res);
        }

        if ($res === false) {
            return false;
        }

        return $res;

    }

    function signRequest($sig_fields, $secret = null)
    {

        if (is_array($sig_fields)) {
            ksort($sig_fields);
            $sig_fields = http_build_query($sig_fields) . ($secret === null ? $this->secret : $secret);
        } else {
            $sig_fields .= ($secret === null ? $this->secret : $secret);
        }

        return hash('SHA512', $sig_fields);

    }

}
