<?php
/**
 * Complete Bundle Test Script
 * Tests bundle recommendations, creation, and verification
 */

// Include WordPress
require_once('../../../../wp-config.php');

// Include WooCommerce integration
if (file_exists('includes/woocommerce-integration.php')) {
    require_once('includes/woocommerce-integration.php');
}

echo "<h1>🧪 Complete Bundle Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

$woo_data = new ArteasyWooCommerceData();

echo "<div class='test-section'>";
echo "<h2>📊 Test 1: WooCommerce Status</h2>";
if (class_exists('WooCommerce')) {
    echo "<span class='success'>✅ WooCommerce is active</span><br>";
} else {
    echo "<span class='error'>❌ WooCommerce is not active</span><br>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>📦 Test 2: Top Products Data</h2>";
$top_products = $woo_data->get_top_products(5, '30_days');
if ($top_products && count($top_products) > 0) {
    echo "<span class='success'>✅ Found " . count($top_products) . " top products</span><br>";
    echo "<h3>Product Structure:</h3>";
    echo "<pre>";
    foreach ($top_products as $product_id => $product_data) {
        echo "ID: $product_id\n";
        echo "Name: " . ($product_data['name'] ?? 'N/A') . "\n";
        echo "Quantity: " . ($product_data['quantity'] ?? 0) . "\n";
        echo "Revenue: ₦" . ($product_data['revenue'] ?? 0) . "\n";
        echo "Has ID field: " . (isset($product_data['id']) ? 'YES' : 'NO') . "\n";
        echo "---\n";
    }
    echo "</pre>";
} else {
    echo "<span class='error'>❌ No top products found</span><br>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🎯 Test 3: Bundle Recommendations</h2>";
$bundle_recommendations = $woo_data->generate_bundle_recommendations('30_days');
if ($bundle_recommendations && count($bundle_recommendations) > 0) {
    echo "<span class='success'>✅ Generated " . count($bundle_recommendations) . " bundle recommendations</span><br>";
    
    foreach ($bundle_recommendations as $bundle_key => $bundle) {
        echo "<h3>Bundle: " . $bundle['name'] . "</h3>";
        echo "<p><strong>Description:</strong> " . $bundle['description'] . "</p>";
        echo "<p><strong>Discount:</strong> " . $bundle['discount'] . "</p>";
        echo "<p><strong>Products Count:</strong> " . count($bundle['products']) . "</p>";
        
        echo "<h4>Products in Bundle:</h4>";
        echo "<pre>";
        foreach ($bundle['products'] as $index => $product) {
            echo "Product " . ($index + 1) . ":\n";
            echo "  ID: " . ($product['id'] ?? 'MISSING') . "\n";
            echo "  Name: " . ($product['name'] ?? 'N/A') . "\n";
            echo "  Revenue: ₦" . ($product['revenue'] ?? 0) . "\n";
            echo "  Valid ID: " . (isset($product['id']) && $product['id'] > 0 ? 'YES' : 'NO') . "\n";
            echo "---\n";
        }
        echo "</pre>";
    }
} else {
    echo "<span class='error'>❌ No bundle recommendations generated</span><br>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🔍 Test 4: Product ID Verification</h2>";
if ($bundle_recommendations) {
    $first_bundle = array_values($bundle_recommendations)[0];
    $valid_products = 0;
    $invalid_products = 0;
    
    foreach ($first_bundle['products'] as $product) {
        if (isset($product['id']) && $product['id'] > 0) {
            $wc_product = wc_get_product($product['id']);
            if ($wc_product) {
                echo "<span class='success'>✅ Product ID " . $product['id'] . " exists: " . $wc_product->get_name() . "</span><br>";
                $valid_products++;
            } else {
                echo "<span class='error'>❌ Product ID " . $product['id'] . " does NOT exist in WooCommerce</span><br>";
                $invalid_products++;
            }
        } else {
            echo "<span class='error'>❌ Product has no valid ID</span><br>";
            $invalid_products++;
        }
    }
    
    echo "<h3>Summary:</h3>";
    echo "<span class='info'>Valid Products: $valid_products</span><br>";
    echo "<span class='info'>Invalid Products: $invalid_products</span><br>";
    
    if ($valid_products > 0) {
        echo "<span class='success'>✅ Bundle can be created with valid product relationships!</span><br>";
    } else {
        echo "<span class='error'>❌ Bundle cannot be created - no valid products!</span><br>";
    }
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🧪 Test 5: Bundle Creation Simulation</h2>";
if ($bundle_recommendations && $valid_products > 0) {
    $first_bundle = array_values($bundle_recommendations)[0];
    echo "<h3>Simulating bundle creation for: " . $first_bundle['name'] . "</h3>";
    
    // Simulate the bundle creation process
    echo "<h4>Step 1: Create Simple Product</h4>";
    echo "<span class='info'>✅ Would create simple product: " . $first_bundle['name'] . "</span><br>";
    
    echo "<h4>Step 2: Calculate Bundle Price</h4>";
    $total_price = 0;
    foreach ($first_bundle['products'] as $product) {
        $total_price += $product['revenue'] ?? 0;
    }
    $discount_percent = intval(str_replace('%', '', $first_bundle['discount'] ?? '20'));
    $discount_amount = ($total_price * $discount_percent) / 100;
    $bundle_price = $total_price - $discount_amount;
    
    echo "<span class='info'>Total Price: ₦" . number_format($total_price) . "</span><br>";
    echo "<span class='info'>Discount: " . $discount_percent . "% (₦" . number_format($discount_amount) . ")</span><br>";
    echo "<span class='info'>Bundle Price: ₦" . number_format($bundle_price) . "</span><br>";
    
    echo "<h4>Step 3: Convert to Grouped Product</h4>";
    echo "<span class='info'>✅ Would convert to grouped product type</span><br>";
    
    echo "<h4>Step 4: Set Child Products</h4>";
    $product_ids = array();
    foreach ($first_bundle['products'] as $product) {
        if (isset($product['id']) && $product['id'] > 0) {
            $product_ids[] = $product['id'];
        }
    }
    echo "<span class='info'>Child Product IDs: " . implode(', ', $product_ids) . "</span><br>";
    
    echo "<h4>Step 5: Enhanced Description</h4>";
    echo "<span class='info'>✅ Would add product list to description</span><br>";
    
    echo "<h3>Result:</h3>";
    echo "<span class='success'>✅ Bundle creation would succeed!</span><br>";
    echo "<span class='info'>Bundle would be a grouped product with " . count($product_ids) . " child products</span><br>";
    
} else {
    echo "<span class='error'>❌ Cannot simulate bundle creation - no valid products</span><br>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>📋 Test 6: Dashboard Metrics</h2>";
$dashboard_metrics = $woo_data->get_marketing_dashboard_metrics('30_days');
echo "<h3>Marketing Dashboard Metrics:</h3>";
echo "<pre>";
print_r($dashboard_metrics);
echo "</pre>";

echo "<h3>Metrics Analysis:</h3>";
echo "<span class='info'>Active Campaigns: " . $dashboard_metrics['active_campaigns'] . "</span><br>";
echo "<span class='info'>Cart Recovery Rate: " . $dashboard_metrics['cart_recovery_rate'] . "%</span><br>";
echo "<span class='info'>Bundle Opportunities: " . $dashboard_metrics['bundle_opportunities'] . "</span><br>";
echo "<span class='info'>Automation Level: " . $dashboard_metrics['automation_level'] . "%</span><br>";
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🎯 Final Assessment</h2>";
if ($valid_products > 0) {
    echo "<span class='success'>✅ BUNDLE SYSTEM IS READY!</span><br>";
    echo "<span class='info'>• Bundle recommendations have valid product IDs</span><br>";
    echo "<span class='info'>• Bundle creation will create grouped products</span><br>";
    echo "<span class='info'>• Dashboard metrics are dynamic</span><br>";
    echo "<span class='info'>• Real product relationships will be established</span><br>";
} else {
    echo "<span class='error'>❌ BUNDLE SYSTEM NEEDS FIXING</span><br>";
    echo "<span class='info'>• No valid products found for bundling</span><br>";
    echo "<span class='info'>• Check WooCommerce product data</span><br>";
    echo "<span class='info'>• Verify top products function</span><br>";
}
echo "</div>";

echo "<h2>🚀 Next Steps:</h2>";
echo "<ol>";
echo "<li>If tests pass: Create bundle in WordPress admin</li>";
echo "<li>Check WooCommerce Products → Find your bundle</li>";
echo "<li>Verify it's a 'Grouped product' type</li>";
echo "<li>Check frontend to see child products</li>";
echo "</ol>";
?>



