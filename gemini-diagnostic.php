<?php
/**
 * Gemini API Diagnostic Tool
 * Upload this file to your WordPress root directory and access it via browser
 */

// Your API key
$api_key = "AIzaSyBIkUyU2mTWKWgad6HqKd2VT1m0b4XQytA";

echo "<h1>Gemini API Diagnostic Tool</h1>";
echo "<p>Testing API Key: " . substr($api_key, 0, 10) . "...</p>";

// Test 1: Basic API key format
echo "<h2>Test 1: API Key Format</h2>";
if (strlen($api_key) === 39 && substr($api_key, 0, 4) === 'AIza') {
    echo "✅ API key format is correct (39 characters, starts with AIza)<br>";
} else {
    echo "❌ API key format is incorrect<br>";
}

// Test 2: Test with curl
echo "<h2>Test 2: Direct cURL Test</h2>";
function test_with_curl($api_key) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $api_key;
    
    $data = array(
        "contents" => array(
            array(
                "parts" => array(
                    array(
                        "text" => "Say 'Hello, cURL test successful!'"
                    )
                )
            )
        )
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_info = curl_getinfo($ch);
    curl_close($ch);
    
    return array(
        'response' => $response,
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'curl_info' => $curl_info
    );
}

$curl_result = test_with_curl($api_key);

echo "HTTP Code: " . $curl_result['http_code'] . "<br>";
if ($curl_result['curl_error']) {
    echo "cURL Error: " . $curl_result['curl_error'] . "<br>";
}
echo "Response: " . htmlspecialchars($curl_result['response']) . "<br>";

// Test 3: Test with WordPress functions (if available)
echo "<h2>Test 3: WordPress wp_remote_post Test</h2>";
if (function_exists('wp_remote_post')) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $api_key;
    
    $data = array(
        "contents" => array(
            array(
                "parts" => array(
                    array(
                        "text" => "Say 'Hello, WordPress test successful!'"
                    )
                )
            )
        )
    );
    
    $args = array(
        "headers" => array(
            "Content-Type" => "application/json"
        ),
        "body" => json_encode($data),
        "timeout" => 30,
        "sslverify" => true
    );
    
    $response = wp_remote_post($url, $args);
    
    if (is_wp_error($response)) {
        echo "WordPress Error: " . $response->get_error_message() . "<br>";
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        echo "HTTP Code: " . $response_code . "<br>";
        echo "Response: " . htmlspecialchars($body) . "<br>";
    }
} else {
    echo "WordPress functions not available (not running in WordPress context)<br>";
}

// Test 4: Check server capabilities
echo "<h2>Test 4: Server Capabilities</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "cURL Available: " . (function_exists('curl_init') ? 'Yes' : 'No') . "<br>";
echo "OpenSSL Available: " . (extension_loaded('openssl') ? 'Yes' : 'No') . "<br>";
echo "JSON Available: " . (function_exists('json_encode') ? 'Yes' : 'No') . "<br>";

// Test 5: Check if API key has proper permissions
echo "<h2>Test 5: API Key Permissions Check</h2>";
echo "To check API key permissions, go to: <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Console</a><br>";
echo "Make sure the 'Generative Language API' is enabled for your project.<br>";
echo "Check if your API key has any restrictions that might block your server.<br>";

?>
