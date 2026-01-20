<?php
/**
 * Gemini API Model List Diagnostic Tool
 * This will show us what models are actually available
 */

// Your API key
$api_key = "AIzaSyBIkUyU2mTWKWgad6HqKd2VT1m0b4XQytA";

echo "<h1>Gemini API Model List Diagnostic</h1>";
echo "<p>Testing API Key: " . substr($api_key, 0, 10) . "...</p>";

// Test 1: List available models
echo "<h2>Test 1: List Available Models</h2>";

function list_gemini_models($api_key) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $api_key;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    return array(
        'response' => $response,
        'http_code' => $http_code,
        'curl_error' => $curl_error
    );
}

$models_result = list_gemini_models($api_key);

echo "HTTP Code: " . $models_result['http_code'] . "<br>";
if ($models_result['curl_error']) {
    echo "cURL Error: " . $models_result['curl_error'] . "<br>";
}
echo "Response: " . htmlspecialchars($models_result['response']) . "<br>";

// Parse the models response
$json_response = json_decode($models_result['response'], true);
if ($json_response && isset($json_response['models'])) {
    echo "<h3>Available Models:</h3>";
    echo "<ul>";
    foreach ($json_response['models'] as $model) {
        $model_name = $model['name'];
        $display_name = isset($model['displayName']) ? $model['displayName'] : $model_name;
        $supported_methods = isset($model['supportedGenerationMethods']) ? implode(', ', $model['supportedGenerationMethods']) : 'Unknown';
        
        echo "<li><strong>" . htmlspecialchars($display_name) . "</strong><br>";
        echo "Name: " . htmlspecialchars($model_name) . "<br>";
        echo "Supported Methods: " . htmlspecialchars($supported_methods) . "<br></li>";
    }
    echo "</ul>";
    
    // Find models that support generateContent
    echo "<h3>Models Supporting generateContent:</h3>";
    echo "<ul>";
    foreach ($json_response['models'] as $model) {
        if (isset($model['supportedGenerationMethods']) && in_array('generateContent', $model['supportedGenerationMethods'])) {
            $model_name = $model['name'];
            $display_name = isset($model['displayName']) ? $model['displayName'] : $model_name;
            echo "<li><strong>" . htmlspecialchars($display_name) . "</strong> - " . htmlspecialchars($model_name) . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>Could not parse models response or no models found.</p>";
}

// Test 2: Try different model names
echo "<h2>Test 2: Try Different Model Names</h2>";

$test_models = array(
    'gemini-1.5-flash',
    'gemini-1.5-flash-latest',
    'gemini-1.5-pro',
    'gemini-1.5-pro-latest',
    'gemini-pro',
    'gemini-pro-latest',
    'gemini-2.0-flash-exp',
    'gemini-2.0-flash-thinking-exp'
);

foreach ($test_models as $model_name) {
    echo "<h3>Testing Model: " . $model_name . "</h3>";
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $model_name . ":generateContent?key=" . $api_key;
    
    $data = array(
        "contents" => array(
            array(
                "parts" => array(
                    array(
                        "text" => "Say 'Hello, test successful!'"
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
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: " . $http_code . "<br>";
    
    if ($http_code === 200) {
        echo "<strong style='color: green;'>✅ SUCCESS!</strong><br>";
        $json_response = json_decode($response, true);
        if ($json_response && isset($json_response['candidates'][0]['content']['parts'][0]['text'])) {
            echo "Response: " . htmlspecialchars($json_response['candidates'][0]['content']['parts'][0]['text']) . "<br>";
        }
    } else {
        echo "<strong style='color: red;'>❌ FAILED</strong><br>";
        echo "Response: " . htmlspecialchars($response) . "<br>";
    }
    echo "<hr>";
}

?>




