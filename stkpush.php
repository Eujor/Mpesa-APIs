<?php
// Include the access token file
include 'accessToken.php';
date_default_timezone_set('Africa/Nairobi');

// Constants for STK Push
$processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
$callbackurl = 'https://eujor.info.darajaapp.callback.php';
$passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
$BusinessShortCode = '174379';
$Timestamp = date('YmdHis');

// Encrypt data to get password
$Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);
$phone = '254743418844'; //
$money = '01';
$PartyA = $phone;
$PartyB = '254708374149';
$AccountReference = 'Eujor Mpesa stkpush';
$TransactionDesc = 'stkpush test';
$Amount = $money;
$stkpushheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];

// Function to perform the cURL request
function makeCurlRequest($url, $headers, $postData) {
    $curl = curl_init($url);
    if ($curl === false) {
        die("Failed to initialize cURL session.");
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification (not recommended for production)
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // Disable SSL verification (not recommended for production)

    $response = curl_exec($curl);
    if ($response === false) {
        die("cURL Error: " . curl_error($curl));
    }

    curl_close($curl);
    return $response;
}

// Prepare the STK Push request data
$curl_post_data = [
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $Amount,
    'PartyA' => $PartyA,
    'PartyB' => $BusinessShortCode,
    'PhoneNumber' => $PartyA,
    'CallBackURL' => $callbackurl,
    'AccountReference' => $AccountReference,
    'TransactionDesc' => $TransactionDesc
];

// Make the cURL request and handle the response
$curl_response = makeCurlRequest($processrequestUrl, $stkpushheader, $curl_post_data);
$data = json_decode($curl_response);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON Decode Error: " . json_last_error_msg());
}

// Check for response data
if (isset($data->CheckoutRequestID) && $data->ResponseCode == "0") {
    echo "\tThe CheckoutRequestID for this transaction is: " . $data->CheckoutRequestID;
} else {
    echo "Error: " . ($data->ResponseDescription ?? "Unknown error occurred.");
}
?>
