<?php
/**
 * Plugin Name: Arteasy AI Automation
 * Description: AI automation for art stores using Google Gemini
 * Version: 1.0.0
 * Author: AI Developer
 * Text Domain: arteasy-ai
 */

if (!defined("ABSPATH")) {
    exit;
}

// Define plugin constants
define("ARTEASY_AI_VERSION", "1.0.0");
define("ARTEASY_AI_PLUGIN_URL", plugin_dir_url(__FILE__));
define("ARTEASY_AI_PLUGIN_PATH", plugin_dir_path(__FILE__));

// Include required files
if (file_exists(ARTEASY_AI_PLUGIN_PATH . "includes/gemini-integration.php")) {
    require_once ARTEASY_AI_PLUGIN_PATH . "includes/gemini-integration.php";
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
        "Settings",
        "Settings",
        "manage_options",
        "arteasy-settings",
        "arteasy_settings_page"
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

// AJAX handlers
add_action("wp_ajax_arteasy_test_api", "arteasy_test_api_ajax");
add_action("wp_ajax_arteasy_generate_description", "arteasy_generate_description_ajax");
add_action("wp_ajax_arteasy_generate_cart_recovery", "arteasy_generate_cart_recovery_ajax");

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

function arteasy_generate_description_ajax() {
    if (class_exists("ArteasyGemini")) {
        $product_name = sanitize_text_field($_POST["product_name"]);
        $category = sanitize_text_field($_POST["category"]);
        $price = floatval($_POST["price"]);
        $sku = sanitize_text_field($_POST["sku"]);
        $features = sanitize_textarea_field($_POST["features"]);
        
        $gemini = new ArteasyGemini();
        $description = $gemini->generate_product_description($product_name, $category, $price, $sku, $features);
        
        if ($description) {
            wp_send_json_success($description);
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

// Plugin activation hook
register_activation_hook(__FILE__, "arteasy_activate");

function arteasy_activate() {
    // Set default options
    add_option("arteasy_gemini_api_key", "");
    add_option("arteasy_plugin_version", ARTEASY_AI_VERSION);
}
