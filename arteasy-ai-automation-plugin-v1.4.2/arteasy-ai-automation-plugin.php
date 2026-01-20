<?php
/**
 * Plugin Name: Arteasy AI Automation - Debug Version
 * Description: AI automation for art stores using Google Gemini (Debug Version)
 * Version: 1.4.2
 * Author: AI Developer
 * Text Domain: arteasy-ai
 */

if (!defined("ABSPATH")) {
    exit;
}

// Define plugin constants
define("ARTEASY_AI_VERSION", "1.4.2");
define("ARTEASY_AI_PLUGIN_URL", plugin_dir_url(__FILE__));
define("ARTEASY_AI_PLUGIN_PATH", plugin_dir_path(__FILE__));

// Include required files
if (file_exists(ARTEASY_AI_PLUGIN_PATH . "includes/gemini-integration.php")) {
    require_once ARTEASY_AI_PLUGIN_PATH . "includes/gemini-integration.php";
}

if (file_exists(ARTEASY_AI_PLUGIN_PATH . "includes/woocommerce-integration.php")) {
    require_once ARTEASY_AI_PLUGIN_PATH . "includes/woocommerce-integration.php";
}

// Enqueue admin styles and scripts
add_action("admin_enqueue_scripts", "arteasy_admin_scripts");

function arteasy_admin_scripts($hook) {
    if (strpos($hook, "arteasy") !== false) {
        wp_enqueue_style("arteasy-admin-css", ARTEASY_AI_PLUGIN_URL . "assets/css/admin.css", array(), ARTEASY_AI_VERSION);
        wp_enqueue_script("arteasy-admin-js", ARTEASY_AI_PLUGIN_URL . "assets/js/admin.js", array("jquery"), ARTEASY_AI_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script("arteasy-admin-js", "arteasy_ajax", array(
            "ajax_url" => admin_url("admin-ajax.php"),
            "nonce" => wp_create_nonce("arteasy_ajax_nonce")
        ));
    }
}

// Create admin menu
add_action("admin_menu", "arteasy_menu");

function arteasy_menu() {
    add_menu_page(
        "Arteasy AI Dashboard",
        "AI Automation",
        "manage_options",
        "arteasy",
        "arteasy_dashboard_page",
        "dashicons-art",
        30
    );
    
    add_submenu_page(
        "arteasy",
        "Product Generator",
        "Product Generator",
        "manage_options",
        "arteasy-generator",
        "arteasy_generator_page"
    );
    
    add_submenu_page(
        "arteasy",
        "Cart Recovery",
        "Cart Recovery",
        "manage_options",
        "arteasy-cart",
        "arteasy_cart_page"
    );
    
    add_submenu_page(
        "arteasy",
        "Analytics",
        "Analytics",
        "manage_options",
        "arteasy-analytics",
        "arteasy_analytics_page"
    );
    
    add_submenu_page(
        "arteasy",
        "Marketing Automation",
        "Marketing Automation",
        "manage_options",
        "arteasy-marketing-automation",
        "arteasy_marketing_automation_page"
    );
    
    add_submenu_page(
        "arteasy",
        "Settings",
        "Settings",
        "manage_options",
        "arteasy-settings",
        "arteasy_settings_page"
    );
    
    add_submenu_page(
        "arteasy",
        "API Debug",
        "API Debug",
        "manage_options",
        "arteasy-debug",
        "arteasy_debug_page"
    );
    
    add_submenu_page(
        "arteasy",
        "Bundle Test",
        "Bundle Test",
        "manage_options",
        "arteasy-bundle-test",
        "arteasy_bundle_test_page"
    );
    
    add_submenu_page(
        "arteasy",
        "Product Automation",
        "Product Automation",
        "manage_options",
        "arteasy-product-automation",
        "arteasy_product_automation_page"
    );
}

// Dashboard page
function arteasy_dashboard_page() {
    if (file_exists(ARTEASY_AI_PLUGIN_PATH . "templates/admin-dashboard.php")) {
        include ARTEASY_AI_PLUGIN_PATH . "templates/admin-dashboard.php";
    } else {
        echo "<h1>Arteasy AI Automation</h1>";
        echo "<p>Plugin is working! Dashboard template not found.</p>";
    }
}

// Product generator page
function arteasy_generator_page() {
    if (file_exists(ARTEASY_AI_PLUGIN_PATH . "templates/product-generator.php")) {
        include ARTEASY_AI_PLUGIN_PATH . "templates/product-generator.php";
    } else {
        echo "<h1>Product Generator</h1>";
        echo "<p>Template not found.</p>";
    }
}

// Cart recovery page
function arteasy_cart_page() {
    if (file_exists(ARTEASY_AI_PLUGIN_PATH . "templates/cart-recovery.php")) {
        include ARTEASY_AI_PLUGIN_PATH . "templates/cart-recovery.php";
    } else {
        echo "<h1>Cart Recovery</h1>";
        echo "<p>Template not found.</p>";
    }
}

// Analytics page
function arteasy_analytics_page() {
    if (file_exists(ARTEASY_AI_PLUGIN_PATH . "templates/analytics.php")) {
        include ARTEASY_AI_PLUGIN_PATH . "templates/analytics.php";
    } else {
        echo "<h1>Analytics</h1>";
        echo "<p>Template not found.</p>";
    }
}

function arteasy_marketing_automation_page() {
    if (file_exists(ARTEASY_AI_PLUGIN_PATH . "templates/marketing-automation.php")) {
        include ARTEASY_AI_PLUGIN_PATH . "templates/marketing-automation.php";
    } else {
        echo "<h1>Marketing Automation</h1>";
        echo "<p>Template not found.</p>";
    }
}

// Settings page
function arteasy_settings_page() {
    if (isset($_POST["submit"])) {
        update_option("arteasy_gemini_api_key", sanitize_text_field($_POST["gemini_api_key"]));
        echo "<div class='notice notice-success'><p>Settings saved successfully!</p></div>";
    }
    
    $api_key = get_option("arteasy_gemini_api_key", "");
    ?>
    <div class="wrap">
        <h1>Arteasy AI Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">Google Gemini API Key</th>
                    <td>
                        <input type="text" name="gemini_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                        <p class="description">Get your FREE API key from <a href="https://aistudio.google.com/" target="_blank">Google AI Studio</a></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <?php if (!empty($api_key)): ?>
        <div class="card">
            <h2>Test API Connection</h2>
            <button type="button" id="test-api" class="button">Test Connection</button>
            <div id="api-test-result"></div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// Bundle Test page
function arteasy_bundle_test_page() {
    echo '<div class="wrap">';
    echo '<h1>🧪 Bundle Test</h1>';
    echo '<p>This test will verify that bundle recommendations and creation are working properly.</p>';
    
    // Include the test script
    if (file_exists(ARTEASY_AI_PLUGIN_PATH . "test-bundle-complete.php")) {
        include ARTEASY_AI_PLUGIN_PATH . "test-bundle-complete.php";
    } else {
        echo '<div class="notice notice-error"><p>Test script not found. Please ensure test-bundle-complete.php is in the plugin directory.</p></div>';
    }
    
    echo '</div>';
}

// Product Automation page
function arteasy_product_automation_page() {
    if (file_exists(ARTEASY_AI_PLUGIN_PATH . "templates/product-automation.php")) {
        include ARTEASY_AI_PLUGIN_PATH . "templates/product-automation.php";
    } else {
        echo '<h1>Product Automation</h1>';
        echo '<p>Template not found.</p>';
    }
}

// Debug page
function arteasy_debug_page() {
    $api_key = get_option("arteasy_gemini_api_key", "");
    ?>
    <div class="wrap">
        <h1>API Debug Information</h1>
        
        <div class="card">
            <h2>API Key Information</h2>
            <p><strong>API Key Set:</strong> <?php echo !empty($api_key) ? 'Yes' : 'No'; ?></p>
            <p><strong>API Key Length:</strong> <?php echo strlen($api_key); ?> characters</p>
            <p><strong>API Key Format:</strong> <?php echo (strlen($api_key) === 39 && substr($api_key, 0, 4) === 'AIza') ? 'Correct' : 'Incorrect'; ?></p>
            <p><strong>API Key Preview:</strong> <?php echo substr($api_key, 0, 10) . '...'; ?></p>
        </div>
        
        <div class="card">
            <h2>Server Information</h2>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>cURL Available:</strong> <?php echo function_exists('curl_init') ? 'Yes' : 'No'; ?></p>
            <p><strong>OpenSSL Available:</strong> <?php echo extension_loaded('openssl') ? 'Yes' : 'No'; ?></p>
            <p><strong>JSON Available:</strong> <?php echo function_exists('json_encode') ? 'Yes' : 'No'; ?></p>
        </div>
        
        <div class="card">
            <h2>WordPress Information</h2>
            <p><strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?></p>
            <p><strong>Site URL:</strong> <?php echo get_site_url(); ?></p>
            <p><strong>Admin URL:</strong> <?php echo admin_url(); ?></p>
        </div>
        
        <div class="card">
            <h2>Direct API Test</h2>
            <button type="button" id="direct-test" class="button button-primary">Run Direct API Test</button>
            <div id="direct-test-result"></div>
        </div>
        
        <div class="card">
            <h2>Error Logs</h2>
            <p>Check your WordPress error logs for detailed error messages:</p>
            <ul>
                <li>WordPress Debug Log: <code>/wp-content/debug.log</code></li>
                <li>Server Error Logs: Check your hosting control panel</li>
                <li>PHP Error Logs: Check your hosting control panel</li>
            </ul>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#direct-test').on('click', function() {
            var button = $(this);
            var resultDiv = $('#direct-test-result');
            
            button.prop('disabled', true).text('Testing...');
            resultDiv.html('<p>Running direct API test...</p>');
            
            $.post(ajaxurl, {
                action: 'arteasy_direct_api_test'
            }, function(response) {
                if (response.success) {
                    resultDiv.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
                } else {
                    resultDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
                }
            }).fail(function() {
                resultDiv.html('<div class="notice notice-error"><p>❌ Connection failed. Please try again.</p></div>');
            }).always(function() {
                button.prop('disabled', false).text('Run Direct API Test');
            });
        });
    });
    </script>
    <?php
}

// AJAX handlers
add_action("wp_ajax_arteasy_test_api", "arteasy_test_api_ajax");
add_action("wp_ajax_arteasy_generate_description", "arteasy_generate_description_ajax");
add_action("wp_ajax_arteasy_generate_cart_recovery", "arteasy_generate_cart_recovery_ajax");
add_action("wp_ajax_arteasy_refresh_analytics", "arteasy_refresh_analytics_ajax");
add_action("wp_ajax_arteasy_export_analytics", "arteasy_export_analytics_ajax");
add_action("wp_ajax_arteasy_get_ai_status", "arteasy_get_ai_status_ajax");
add_action("wp_ajax_arteasy_direct_api_test", "arteasy_direct_api_test_ajax");
add_action("wp_ajax_arteasy_get_revenue_trends", "arteasy_get_revenue_trends_ajax");
add_action("wp_ajax_arteasy_get_customer_segmentation", "arteasy_get_customer_segmentation_ajax");
add_action("wp_ajax_arteasy_get_category_performance", "arteasy_get_category_performance_ajax");
add_action("wp_ajax_arteasy_activate_campaign", "arteasy_activate_campaign_ajax");
add_action("wp_ajax_arteasy_create_bundle", "arteasy_create_bundle_ajax");
add_action("wp_ajax_arteasy_get_smart_campaigns", "arteasy_get_smart_campaigns_ajax");
add_action("wp_ajax_arteasy_setup_cart_recovery", "arteasy_setup_cart_recovery_ajax");
add_action("wp_ajax_arteasy_toggle_test_mode", "arteasy_toggle_test_mode_ajax");
add_action("wp_ajax_arteasy_trigger_recovery_manually", "arteasy_trigger_recovery_manually_ajax");
add_action("wp_ajax_arteasy_get_activation_status", "arteasy_get_activation_status_ajax");
// Search customers
add_action("wp_ajax_arteasy_search_customers", function() {
    if (!current_user_can('manage_woocommerce')) { wp_send_json_error('Unauthorized'); }
    $q = sanitize_text_field($_POST['q'] ?? '');
    if (class_exists('ArteasyWooCommerceData')) {
        $woo = new ArteasyWooCommerceData();
        wp_send_json_success($woo->search_customers($q));
    }
    wp_send_json_error('Woo integration missing');
});

// Search products
add_action("wp_ajax_arteasy_search_products", function() {
    if (!current_user_can('manage_woocommerce')) { wp_send_json_error('Unauthorized'); }
    $q = sanitize_text_field($_POST['q'] ?? '');
    if (class_exists('ArteasyWooCommerceData')) {
        $woo = new ArteasyWooCommerceData();
        wp_send_json_success($woo->search_products($q));
    }
    wp_send_json_error('Woo integration missing');
});

// Get recent abandoned cart by email
add_action("wp_ajax_arteasy_get_recent_abandoned_cart", function() {
    if (!current_user_can('manage_woocommerce')) { wp_send_json_error('Unauthorized'); }
    $email = sanitize_email($_POST['email'] ?? '');
    if (class_exists('ArteasyWooCommerceData')) {
        $woo = new ArteasyWooCommerceData();
        wp_send_json_success($woo->get_recent_abandoned_cart_by_email($email));
    }
    wp_send_json_error('Woo integration missing');
});
add_action("wp_ajax_arteasy_bulk_generate_descriptions", "arteasy_bulk_generate_descriptions_ajax");
add_action("wp_ajax_arteasy_get_automation_status", "arteasy_get_automation_status_ajax");
add_action("wp_ajax_arteasy_set_automation_status", "arteasy_set_automation_status_ajax");
add_action("wp_ajax_arteasy_get_products_needing_descriptions", "arteasy_get_products_needing_descriptions_ajax");

function arteasy_test_api_ajax() {
    if (class_exists("ArteasyGemini")) {
        $gemini = new ArteasyGemini();
        $result = $gemini->test_connection();
        
        if ($result) {
            wp_send_json_success("API connection successful!");
        } else {
            $error_info = $gemini->get_last_error();
            $error_message = "API connection failed. ";
            
            if (!$error_info["api_key_set"]) {
                $error_message .= "No API key provided.";
            } else {
                $error_message .= "Please check your API key. Key length: " . $error_info["api_key_length"] . " characters.";
            }
            
            wp_send_json_error($error_message);
        }
    } else {
        wp_send_json_error("Gemini integration not loaded.");
    }
}

function arteasy_direct_api_test_ajax() {
    $api_key = get_option("arteasy_gemini_api_key", "");
    
    if (empty($api_key)) {
        wp_send_json_error("No API key provided");
        return;
    }
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;
    
    $data = array(
        "contents" => array(
            array(
                "parts" => array(
                    array(
                        "text" => "Say 'Hello, direct test successful!'"
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
        wp_send_json_error("WordPress Error: " . $response->get_error_message());
        return;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    if ($response_code !== 200) {
        wp_send_json_error("HTTP Error " . $response_code . ": " . $body);
        return;
    }
    
    $json_response = json_decode($body, true);
    
    if (!$json_response) {
        wp_send_json_error("JSON Parse Error: " . json_last_error_msg());
        return;
    }
    
    if (isset($json_response['candidates'][0]['content']['parts'][0]['text'])) {
        wp_send_json_success("Direct test successful! Response: " . $json_response['candidates'][0]['content']['parts'][0]['text']);
    } elseif (isset($json_response['error'])) {
        wp_send_json_error("API Error: " . $json_response['error']['message']);
    } else {
        wp_send_json_error("Unexpected response format: " . $body);
    }
}

function arteasy_generate_description_ajax() {
    if (class_exists("ArteasyGemini")) {
        $product_name = sanitize_text_field($_POST["product_name"] ?? '');
        $category = sanitize_text_field($_POST["category"] ?? 'Art Supplies');
        $price = floatval($_POST["price"] ?? 0);
        $sku = sanitize_text_field($_POST["sku"] ?? '');
        $features = sanitize_textarea_field($_POST["features"] ?? '');
        
        // Validate required field
        if (empty($product_name)) {
            wp_send_json_error("Product name is required.");
            return;
        }
        
        $gemini = new ArteasyGemini();
        $result = $gemini->generate_product_description($product_name, $category, $price, $sku, $features);
        
        // Handle new array format (with success/error)
        if (is_array($result)) {
            if ($result['success'] && isset($result['description'])) {
                // Description is already cleaned by generate_product_description()
                wp_send_json_success($result['description']);
            } else {
                $error_msg = isset($result['error']) ? $result['error'] : 'Failed to generate description. Please check your API key.';
                wp_send_json_error($error_msg);
            }
        } else if ($result) {
            // Legacy format support (string)
            wp_send_json_success($result);
        } else {
            wp_send_json_error("Failed to generate description. Please check your API key.");
        }
    } else {
        wp_send_json_error("Gemini integration not loaded.");
    }
}

function arteasy_generate_cart_recovery_ajax() {
    if (class_exists("ArteasyGemini")) {
        $customer_name = sanitize_text_field($_POST["customer_name"]);
        $template_style = sanitize_text_field($_POST["template_style"]);
        $cart_items_json = sanitize_text_field($_POST["cart_items"]);
        
        $cart_items = json_decode($cart_items_json, true);
        
        if (!$cart_items || !is_array($cart_items)) {
            wp_send_json_error("Invalid cart items data.");
            return;
        }
        
        $gemini = new ArteasyGemini();
        $message = $gemini->generate_cart_recovery_message($customer_name, $cart_items, $template_style);
        
        if ($message) {
            wp_send_json_success($message);
        } else {
            wp_send_json_error("Failed to generate cart recovery message. Please check your API key.");
        }
    } else {
        wp_send_json_error("Gemini integration not loaded.");
    }
}

// Analytics action handlers
function arteasy_refresh_analytics_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->refresh_analytics_data();
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_export_analytics_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $period = sanitize_text_field($_POST["period"]);
        $woo_data = new ArteasyWooCommerceData();
        $csv_data = $woo_data->export_analytics_data($period);
        
        // Generate CSV content
        $csv_content = '';
        foreach ($csv_data as $row) {
            $csv_content .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="arteasy-analytics-' . date('Y-m-d') . '.csv"');
        header('Content-Length: ' . strlen($csv_content));
        
        echo $csv_content;
        exit;
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_get_ai_status_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $status = $woo_data->get_ai_status();
        
        wp_send_json_success($status);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

// Advanced Analytics AJAX handlers
function arteasy_get_revenue_trends_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $period = sanitize_text_field($_POST["period"] ?? "30_days");
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->get_revenue_trends($period);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_get_customer_segmentation_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $period = sanitize_text_field($_POST["period"] ?? "30_days");
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->get_customer_segmentation($period);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_get_category_performance_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $period = sanitize_text_field($_POST["period"] ?? "30_days");
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->get_product_category_performance($period);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

// Marketing Automation AJAX handlers
function arteasy_activate_campaign_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'arteasy_activate_campaign')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $campaign_id = sanitize_text_field($_POST['campaign_id']);
    $campaign_data = json_decode(stripslashes($_POST['campaign_data']), true);
    
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        
        // Get real campaign data if not provided
        if (empty($campaign_data) || !isset($campaign_data['type'])) {
            $real_campaign_data = $woo_data->get_campaign_data($campaign_id, '30_days');
            if ($real_campaign_data) {
                $campaign_data = $real_campaign_data;
            }
        }
        
        $result = $woo_data->activate_real_campaign($campaign_id, $campaign_data);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_create_bundle_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'arteasy_create_bundle')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $bundle_id = sanitize_text_field($_POST['bundle_id']);
    $bundle_data = json_decode(stripslashes($_POST['bundle_data']), true);
    
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        
        // Get real bundle data if not provided
        if (empty($bundle_data) || !isset($bundle_data['products'])) {
            $bundle_recommendations = $woo_data->generate_bundle_recommendations('30_days');
            if (isset($bundle_recommendations[$bundle_id])) {
                $bundle_data = $bundle_recommendations[$bundle_id];
            }
        }
        
        $result = $woo_data->create_real_bundle($bundle_id, $bundle_data);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_setup_cart_recovery_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'arteasy_setup_cart_recovery')) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->setup_real_cart_recovery();
        
        // Enable test mode by default (5 min emails)
        $woo_data->set_test_mode(true);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_toggle_test_mode_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'arteasy_cart_recovery_action')) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $enabled = isset($_POST['test_mode']) && $_POST['test_mode'] === 'true';
        $result = $woo_data->set_test_mode($enabled);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_trigger_recovery_manually_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'arteasy_cart_recovery_action')) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        
        // Force immediate send (skip time delays)
        $result = $woo_data->process_abandoned_carts(true);
        
        $message = $result['message'];
        if (!empty($result['errors'])) {
            $message .= ' Errors: ' . implode(', ', $result['errors']);
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'emails_sent' => $result['emails_sent'],
            'errors' => $result['errors']
        ));
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

// Bootstrap cart recovery hooks on frontend if active
// Add custom cron schedule for faster processing (every 5 minutes)
add_filter('cron_schedules', function($schedules) {
    $schedules['arteasy_every_5min'] = array(
        'interval' => 300, // 5 minutes in seconds
        'display' => 'Every 5 Minutes'
    );
    return $schedules;
});

add_action('init', function() {
    if (get_option('arteasy_cart_recovery_active', false) && class_exists('ArteasyWooCommerceData')) {
        $woo_data = new ArteasyWooCommerceData();
        // One-click restore handler (run before template output)
        add_action('template_redirect', array($woo_data, 'handle_restore_request'));
        add_action('wp_footer', array($woo_data, 'add_cart_abandonment_tracking'));
        add_action('wp_ajax_arteasy_track_cart_abandonment', array($woo_data, 'track_cart_abandonment'));
        add_action('wp_ajax_nopriv_arteasy_track_cart_abandonment', array($woo_data, 'track_cart_abandonment'));
        add_action('arteasy_process_abandoned_carts', array($woo_data, 'process_abandoned_carts'));
        
        // Enable test mode by default for faster testing (user can disable later)
        if (get_option('arteasy_cart_recovery_test_mode') === false) {
            update_option('arteasy_cart_recovery_test_mode', true);
            $woo_data->schedule_recovery_emails();
        }
    }
});

function arteasy_get_activation_status_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $status = $woo_data->get_activation_status();
        
        wp_send_json_success($status);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

// Bulk Description Generation AJAX Handler
function arteasy_bulk_generate_descriptions_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'arteasy_bulk_generate_descriptions')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $batch_size = intval($_POST['batch_size'] ?? 5);
    
    // Handle product_ids parameter
    $product_ids = array();
    if (isset($_POST['product_ids']) && !empty($_POST['product_ids'])) {
        $decoded = json_decode(stripslashes($_POST['product_ids']), true);
        $product_ids = is_array($decoded) ? $decoded : array();
    }
    
    error_log('Arteasy Bulk Generate: product_ids=' . print_r($product_ids, true) . ', batch_size=' . $batch_size);
    
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->bulk_generate_descriptions($product_ids, $batch_size);
        
        error_log('Arteasy Bulk Generate Result: ' . print_r($result, true));
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

// Get Automation Status AJAX Handler
function arteasy_get_automation_status_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $status = $woo_data->get_automation_status();
        wp_send_json_success($status);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

// Set Automation Status AJAX Handler
function arteasy_set_automation_status_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'arteasy_set_automation_status')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $enabled = $_POST['enabled'] === 'true';
    
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->set_automation_status($enabled);
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

// Get Products Needing Descriptions AJAX Handler
function arteasy_get_products_needing_descriptions_ajax() {
    $limit = intval($_POST['limit'] ?? 20);
    $offset = intval($_POST['offset'] ?? 0);
    
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $products = $woo_data->get_products_needing_descriptions($limit, $offset);
        wp_send_json_success($products);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

function arteasy_get_smart_campaigns_ajax() {
    if (class_exists("ArteasyWooCommerceData")) {
        $period = sanitize_text_field($_POST["period"] ?? "30_days");
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->generate_smart_campaigns($period);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error("WooCommerce integration not loaded.");
    }
}

// Plugin activation hook
register_activation_hook(__FILE__, "arteasy_activate");

// Register scheduled automation hook
add_action('arteasy_auto_generate_descriptions', 'arteasy_auto_generate_descriptions_cron');

function arteasy_auto_generate_descriptions_cron() {
    if (class_exists("ArteasyWooCommerceData")) {
        $woo_data = new ArteasyWooCommerceData();
        $result = $woo_data->bulk_generate_descriptions(array(), 5); // Process 5 products per hour
        
        // Log the result
        error_log('Arteasy Auto Description Generation: ' . json_encode($result));
        
        // Update last run time
        update_option('arteasy_last_automation_run', current_time('mysql'));
    }
}

function arteasy_activate() {
    // Set default options
    add_option("arteasy_gemini_api_key", "");
    add_option("arteasy_plugin_version", ARTEASY_AI_VERSION);
}

