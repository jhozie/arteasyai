<?php
/**
 * Google Gemini API Integration
 * Handles all AI-related API calls using Google Gemini (FREE!)
 */

if (!defined("ABSPATH")) {
    exit;
}

class ArteasyGemini {
    
    private $api_key;
    private $api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-latest:generateContent";
    private $model = "gemini-pro-latest"; // Working model confirmed by diagnostic
    
    public function __construct() {
        $this->api_key = get_option("arteasy_gemini_api_key", "");
    }
    
    /**
     * Generate product description using Gemini
     */
    public function generate_product_description($product_name, $category, $price, $sku, $features = "") {
        // Check API key first
        if (empty($this->api_key)) {
            error_log("Arteasy: API key not set for product: {$product_name}");
            return array('success' => false, 'error' => 'API key not configured. Please set your Gemini API key in Settings.');
        }
        
        $prompt = $this->build_product_description_prompt($product_name, $category, $price, $sku, $features);
        
        $response = $this->make_api_call($prompt);
        
        // Check if response has error
        if (isset($response['error'])) {
            error_log("Arteasy: API error for product {$product_name}: " . $response['error']);
            return array('success' => false, 'error' => $response['error']);
        }
        
        if ($response && isset($response["candidates"][0]["content"]["parts"][0]["text"])) {
            $description = $response["candidates"][0]["content"]["parts"][0]["text"];
            
            // Clean the description - remove unwanted prefixes and markdown
            $description = $this->clean_generated_description($description);
            
            error_log("Arteasy: Successfully generated description for product: {$product_name}");
            return array('success' => true, 'description' => $description);
        }
        
        error_log("Arteasy: Failed to generate description for product: {$product_name}. Response: " . json_encode($response));
        return array('success' => false, 'error' => 'Failed to generate description. Please check your API key and try again.');
    }
    
    /**
     * Generate cart recovery message
     */
    public function generate_cart_recovery_message($customer_name, $cart_items, $template_style = "artistic") {
        $prompt = $this->build_cart_recovery_prompt($customer_name, $cart_items, $template_style);
        
        $response = $this->make_api_call($prompt);
        
        if ($response && isset($response["candidates"][0]["content"]["parts"][0]["text"])) {
            return $response["candidates"][0]["content"]["parts"][0]["text"];
        }
        
        return false;
    }
    
    /**
     * Generate analytics insights
     */
    public function generate_analytics_insights($sales_data, $time_period) {
        $prompt = $this->build_analytics_prompt($sales_data, $time_period);
        
        $response = $this->make_api_call($prompt);
        
        if ($response && isset($response["candidates"][0]["content"]["parts"][0]["text"])) {
            return json_decode($response["candidates"][0]["content"]["parts"][0]["text"], true);
        }
        
        return false;
    }
    
    /**
     * Generate chatbot response
     */
    public function generate_chatbot_response($message, $context = "") {
        $prompt = $this->build_chatbot_prompt($message, $context);
        
        $response = $this->make_api_call($prompt);
        
        if ($response && isset($response["candidates"][0]["content"]["parts"][0]["text"])) {
            return $response["candidates"][0]["content"]["parts"][0]["text"];
        }
        
        return false;
    }
    
    /**
     * Build product description prompt
     */
    private function build_product_description_prompt($product_name, $category, $price, $sku, $features) {
        $prompt = "Write a short product description for an art supply item. 

Product: {$product_name}
Category: {$category}";

        if (!empty($features)) {
            $prompt .= "\nFeatures: {$features}";
        }

        $prompt .= "\n\nInstructions:
- Write ONLY a short, concise description (maximum 150 words)
- NO introduction phrases like 'Of course!', 'Here is...', or 'This is...'
- NO markdown formatting (no **, ###, or other symbols)
- NO SKU, price, or category information in the text
- Focus on benefits and quality
- Appeal to Nigerian artists
- Write naturally, directly describing the product
- Start immediately with the product description (no preamble)

Write the description now:";

        return $prompt;
    }
    
    /**
     * Build cart recovery prompt
     */
    private function build_cart_recovery_prompt($customer_name, $cart_items, $template_style) {
        $items_text = "";
        foreach ($cart_items as $item) {
            $items_text .= "- {$item["name"]} (?" . number_format($item["price"]) . ")\n";
        }
        
        $style_instructions = $this->get_template_style_instructions($template_style);
        
        $prompt = "You are writing a personalized cart recovery email for Arteasy, an art supplies store in Nigeria. 

Customer: {$customer_name}
Abandoned Items:
{$items_text}

Style: {$template_style}

{$style_instructions}

Requirements:
1. Keep it personal and friendly
2. Mention the specific products they were interested in
3. Create urgency without being pushy
4. Appeal to their creative side
5. Include a call-to-action
6. Keep it under 150 words
7. Use Nigerian context where appropriate

Generate a compelling cart recovery message:";

        return $prompt;
    }
    
    /**
     * Build analytics prompt
     */
    private function build_analytics_prompt($sales_data, $time_period) {
        $prompt = "You are an AI business analyst specializing in ecommerce for art supplies stores. Analyze the following sales data and provide actionable insights:

Time Period: {$time_period}
Sales Data: " . json_encode($sales_data) . "

Provide insights in the following JSON format:
{
    \"top_products\": [
        {\"name\": \"Product Name\", \"growth\": 25}
    ],
    \"best_times\": [
        {\"period\": \"Weekends\", \"performance\": \"40% higher sales\"}
    ],
    \"customer_behavior\": {
        \"repeat_customers\": 35,
        \"average_order_value\": 15000,
        \"cart_abandonment_rate\": 45
    },
    \"traffic_sources\": [
        {\"source\": \"Instagram\", \"conversion\": \"3x better conversion\"}
    ],
    \"recommendations\": [
        {\"title\": \"Boost Weekend Sales\", \"description\": \"Your sales are 40% higher on weekends...\"}
    ]
}

Focus on actionable insights that can help grow the art supplies business in Nigeria:";

        return $prompt;
    }
    
    /**
     * Build chatbot prompt
     */
    private function build_chatbot_prompt($message, $context) {
        $prompt = "You are an AI assistant for Arteasy, an art supplies store in Nigeria. You help customers with:

1. Product information and recommendations
2. Shipping and delivery questions
3. Order status and returns
4. Art technique advice
5. General store information

Customer Question: {$message}
Context: {$context}

Guidelines:
1. Be helpful and friendly
2. Provide accurate information
3. Suggest relevant products when appropriate
4. Use Nigerian context (shipping to Lagos, etc.)
5. Keep responses concise but informative
6. If you do not know something, admit it and offer to help them contact support

Respond as a helpful art supplies expert:";

        return $prompt;
    }
    
    /**
     * Clean generated description - remove unwanted prefixes, markdown, and boilerplate
     */
    private function clean_generated_description($text) {
        // Remove common AI prefixes
        $prefixes = array(
            "/^Of course!?\s*/i",
            "/^Here is\s+/i",
            "/^This is\s+/i",
            "/^Here's\s+/i",
            "/^Sure!?\s*/i",
            "/^Absolutely!?\s*/i",
            "/^Certainly!?\s*/i",
        );
        
        foreach ($prefixes as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }
        
        // Remove markdown formatting
        $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text); // Bold **text**
        $text = preg_replace('/\*([^*]+)\*/', '$1', $text); // Italic *text*
        $text = preg_replace('/###+\s*/', '', $text); // Headers ###
        $text = preg_replace('/##+\s*/', '', $text); // Headers ##
        $text = preg_replace('/#+\s*/', '', $text); // Headers #
        $text = preg_replace('/\*\s+/', '', $text); // Bullet points *
        
        // Remove lines with SKU, Categories, Price patterns
        $lines = explode("\n", $text);
        $cleaned_lines = array();
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip lines that contain SKU, Categories, Price patterns
            if (preg_match('/^(SKU|Categories?|Category|Price|**SKU|**Price):?\s*/i', $line)) {
                continue;
            }
            // Skip lines that are just separators or empty
            if (empty($line) || preg_match('/^[-=]+$/', $line)) {
                continue;
            }
            // Skip lines that look like metadata
            if (preg_match('/^(Present|Whether|An essential|Designed|Perfect|Ideal)/i', $line) && strlen($line) < 50) {
                // These might be incomplete lines - check if next line continues
                continue;
            }
            $cleaned_lines[] = $line;
        }
        
        $text = implode(' ', $cleaned_lines);
        
        // Clean up extra spaces
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Limit to reasonable length for short description (250 words max, ~1500 chars)
        if (strlen($text) > 1500) {
            $text = wp_trim_words($text, 250, '...');
        }
        
        return $text;
    }
    
    /**
     * Get template style instructions
     */
    private function get_template_style_instructions($style) {
        $instructions = array(
            "artistic" => "Use creative, inspiring language that appeals to artists. Focus on artistic vision and creative potential.",
            "professional" => "Use professional, technical language. Focus on quality, reliability, and professional results.",
            "nigerian" => "Use Nigerian context and references. Mention local shipping, Nigerian artists, and local creative community.",
            "friendly" => "Use warm, personal language. Focus on building relationships and understanding customer needs."
        );
        
        return isset($instructions[$style]) ? $instructions[$style] : $instructions["artistic"];
    }
    
    /**
     * Make API call to Google Gemini
     */
    private function make_api_call($prompt) {
        if (empty($this->api_key)) {
            error_log("Arteasy: No API key provided");
            return array('error' => 'API key not configured');
        }
        
        $url = $this->api_url . "?key=" . $this->api_key;
        
        $data = array(
            "contents" => array(
                array(
                    "parts" => array(
                        array(
                            "text" => $prompt
                        )
                    )
                )
            ),
            "generationConfig" => array(
                "temperature" => 0.7,
                "maxOutputTokens" => 2048,  // Increased significantly to prevent MAX_TOKENS truncation
                "topP" => 0.8,
                "topK" => 40
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
        
        error_log("Arteasy: Making Gemini API call for product description");
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            $error_msg = $response->get_error_message();
            error_log("Arteasy: WordPress error - " . $error_msg);
            return array('error' => 'Connection error: ' . $error_msg);
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        error_log("Arteasy: API Response Code: " . $response_code);
        
        if ($response_code !== 200) {
            error_log("Arteasy: API HTTP Error " . $response_code . " - " . substr($body, 0, 200));
            return array('error' => 'API returned HTTP ' . $response_code . '. Please check your API key.');
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Arteasy: JSON decode error - " . json_last_error_msg());
            return array('error' => 'Invalid response from API');
        }
        
        if (isset($decoded["error"])) {
            $error_message = $decoded["error"]["message"] ?? 'Unknown API error';
            $error_code = $decoded["error"]["code"] ?? 'UNKNOWN';
            error_log("Arteasy: Gemini API Error ({$error_code}): " . $error_message);
            return array('error' => "API Error ({$error_code}): " . $error_message);
        }
        
        // Check if candidates exist
        if (!isset($decoded["candidates"]) || empty($decoded["candidates"])) {
            error_log("Arteasy: No candidates in API response");
            error_log("Arteasy: Full response: " . substr($body, 0, 1000));
            return array('error' => 'API returned no candidates. Please check your API key.');
        }
        
        $candidate = $decoded["candidates"][0];
        $finish_reason = $candidate["finishReason"] ?? '';
        
        // Check for finish reason (might indicate truncation, but we'll still try to get text)
        if ($finish_reason === 'MAX_TOKENS') {
            error_log("Arteasy: Response truncated due to MAX_TOKENS limit - will attempt to use partial content");
        }
        
        // Check if we have the text content - try multiple paths
        $text_content = null;
        
        // Try standard path: candidates[0].content.parts[0].text
        if (isset($candidate["content"]["parts"][0]["text"])) {
            $text_content = $candidate["content"]["parts"][0]["text"];
        }
        
        // If not found, try searching through all parts
        if (!$text_content && isset($candidate["content"]["parts"])) {
            foreach ($candidate["content"]["parts"] as $part) {
                if (isset($part["text"]) && !empty(trim($part["text"]))) {
                    $text_content = $part["text"];
                    break;
                }
            }
        }
        
        // If still not found, check if content exists but parts might be missing
        if (!$text_content) {
            if (!isset($candidate["content"])) {
                error_log("Arteasy: No content in API response");
                error_log("Arteasy: Finish reason: {$finish_reason}");
                error_log("Arteasy: Full response: " . substr($body, 0, 1000));
                return array('error' => 'API returned empty content. Response may have been truncated.');
            }
            
            // Check if parts array exists or is empty
            if (!isset($candidate["content"]["parts"]) || empty($candidate["content"]["parts"])) {
                if ($finish_reason === 'MAX_TOKENS') {
                    error_log("Arteasy: Response truncated due to MAX_TOKENS - no text content available");
                    error_log("Arteasy: Full response structure: " . json_encode($candidate, JSON_PRETTY_PRINT));
                    // This is a model limitation - the response was truncated before any text was generated
                    return array('error' => 'API response was truncated before content was generated. This may be due to model limitations. Please try again.');
                }
                error_log("Arteasy: No parts in content. Finish reason: {$finish_reason}");
                error_log("Arteasy: Full response: " . substr($body, 0, 1000));
                return array('error' => 'API response missing text content. Please check your API key and try again.');
            }
            
            error_log("Arteasy: Parts exist but no text found in any part");
            error_log("Arteasy: Finish reason: {$finish_reason}");
            error_log("Arteasy: Full response: " . substr($body, 0, 1000));
            return array('error' => 'Unexpected response format from API - no text content found');
        }
        
        // If we found text content, use it even if truncated
        if ($finish_reason === 'MAX_TOKENS' && $text_content) {
            error_log("Arteasy: Response was truncated but using partial content (length: " . strlen($text_content) . " chars)");
        }
        
        // Store the text in the expected location for compatibility
        $decoded["candidates"][0]["content"]["parts"][0]["text"] = $text_content;
        
        return $decoded;
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            error_log("Gemini API Error: No API key provided");
            return false;
        }
        
        $test_prompt = "Say \"Hello, Arteasy AI is working with Gemini!\"";
        $response = $this->make_api_call($test_prompt);
        
        if ($response && isset($response["candidates"][0]["content"]["parts"][0]["text"])) {
            return true;
        }
        
        // Log the full response for debugging
        error_log("Gemini API Test Response: " . json_encode($response));
        return false;
    }
    
    /**
     * Get API usage stats
     */
    public function get_usage_stats() {
        // Gemini free tier has generous limits
        return array(
            "requests_per_day" => "1500 (free tier)",
            "cost" => "FREE",
            "requests_made" => 0
        );
    }
    
    /**
     * Get detailed error information
     */
    public function get_last_error() {
        return array(
            "api_key_set" => !empty($this->api_key),
            "api_key_length" => strlen($this->api_key),
            "api_url" => $this->api_url,
            "model" => $this->model
        );
    }
}

// Initialize the Gemini class
$arteasy_gemini = new ArteasyGemini();
