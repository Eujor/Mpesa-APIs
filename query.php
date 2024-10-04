<?php
// INCLUDE ACCESS TOKEN FILE 
include 'accessToken.php';
date_default_timezone_set('Africa/Nairobi');
$query_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
$BusinessShortCode = '174379';
$Timestamp = date('YmdHis');
$passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
// ENCRYPT DATA TO GET PASSWORD
$Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);
// THIS IS THE UNIQUE ID THAT WAS GENERATED WHEN STK REQUEST INITIATED SUCCESSFULLY
$CheckoutRequestID = 'ws_CO_04102024092151284743418844';
$queryheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];

# initiating the transaction
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $query_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $queryheader); // setting custom header

$curl_post_data = array(
  'BusinessShortCode' => $BusinessShortCode,
  'Password' => $Password,
  'Timestamp' => $Timestamp,
  'CheckoutRequestID' => $CheckoutRequestID
);

$data_string = json_encode($curl_post_data);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

$curl_response = curl_exec($curl);
curl_close($curl); // Close the cURL session

// Check if the cURL response is false (an error occurred)
if ($curl_response === false) {
    echo "cURL Error: " . curl_error($curl);
    exit;
}

$data_to = json_decode($curl_response);

// Initialize the message variable
$message = '';

if (isset($data_to->ResultCode)) {
    $ResultCode = $data_to->ResultCode;

    // Use $message instead of $massage
    if ($ResultCode == '1037') {
        $message = "result code: 1037 - Timeout in completing transaction";
    } elseif ($ResultCode == '1032') {
        $message = "result code: 1032 - Transaction has been cancelled by user";
    } elseif ($ResultCode == '1') {
        $message = "result code: 1 - The balance is insufficient for the transaction";
    } elseif ($ResultCode == '0') {
        $message = "result code: 0 - The transaction is successful";
    } else {
        // Handle unexpected ResultCodes
        $message = "Unexpected ResultCode: $ResultCode";
    }
} else {
    $message = "No ResultCode in response";
}

// Output the message
echo $message;
?>
