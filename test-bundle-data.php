<?php
/**
 * Test Bundle Data Structure
 * This script shows what bundle data looks like before and after the fix
 */

// Include WordPress
require_once('../../../../wp-config.php');

// Include WooCommerce integration
if (file_exists('includes/woocommerce-integration.php')) {
    require_once('includes/woocommerce-integration.php');
}

echo "<h1>Bundle Data Test</h1>";

$woo_data = new ArteasyWooCommerceData();

echo "<h2>1. Top Products Data:</h2>";
$top_products = $woo_data->get_top_products(5, '30_days');
echo "<pre>";
print_r($top_products);
echo "</pre>";

echo "<h2>2. Bundle Recommendations:</h2>";
$bundle_recommendations = $woo_data->generate_bundle_recommendations('30_days');
echo "<pre>";
print_r($bundle_recommendations);
echo "</pre>";

echo "<h2>3. Sample Bundle Product Data:</h2>";
if (!empty($bundle_recommendations)) {
    $first_bundle = array_values($bundle_recommendations)[0];
    echo "<h3>Bundle: " . $first_bundle['name'] . "</h3>";
    echo "<pre>";
    print_r($first_bundle['products']);
    echo "</pre>";
    
    echo "<h3>Product IDs Check:</h3>";
    foreach ($first_bundle['products'] as $index => $product) {
        echo "Product " . ($index + 1) . ": ";
        if (isset($product['id']) && $product['id'] > 0) {
            echo "✅ ID = " . $product['id'] . " (" . $product['name'] . ")<br>";
        } else {
            echo "❌ NO ID (" . $product['name'] . ")<br>";
        }
    }
}

echo "<h2>4. WooCommerce Product Check:</h2>";
if (!empty($bundle_recommendations)) {
    $first_bundle = array_values($bundle_recommendations)[0];
    foreach ($first_bundle['products'] as $product) {
        if (isset($product['id']) && $product['id'] > 0) {
            $wc_product = wc_get_product($product['id']);
            if ($wc_product) {
                echo "✅ Product ID " . $product['id'] . " exists: " . $wc_product->get_name() . "<br>";
            } else {
                echo "❌ Product ID " . $product['id'] . " does NOT exist in WooCommerce<br>";
            }
        }
    }
}
?>



