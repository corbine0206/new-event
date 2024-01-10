<?php
// Sample data to send to Colab
$data_to_send = '5';  // Convert your data to a string

// URL of your Colab notebook's API endpoint
$colab_api_url = 'https://colab.research.google.com/drive/1tfVofqHrr2RhZcfq_vJIYZhMLkQeVWm5';

// Create a context for the POST request
$options = array(
    'http' => array(
        'header'  => "Content-Type: text/plain\r\n" .
                     "Content-Length: " . strlen($data_to_send) . "\r\n",  // Add Content-Length header
        'method'  => 'POST',
        'content' => $data_to_send
    )
);

$context  = stream_context_create($options);

// Send the POST request to Colab
$response = file_get_contents($colab_api_url, false, $context);

// Parse and use the response from Colab
$result = intval($response);

// Output the result
echo 'Result from Colab: ' . $result;
?>
