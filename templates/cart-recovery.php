<?php
/**
 * Cart Recovery Template - Real Data Version
 */

if (!defined("ABSPATH")) {
    exit;
}

// Include WooCommerce integration
if (file_exists(ARTEASY_AI_PLUGIN_PATH . "includes/woocommerce-integration.php")) {
    require_once ARTEASY_AI_PLUGIN_PATH . "includes/woocommerce-integration.php";
}

$woo_data = new ArteasyWooCommerceData();
$cart_data = $woo_data->get_cart_abandonment_data('30_days');
$abandoned_orders = method_exists($woo_data, 'get_abandoned_orders_list') ? $woo_data->get_abandoned_orders_list('30_days') : array();
$tracked_carts = method_exists($woo_data, 'get_tracked_carts_list') ? $woo_data->get_tracked_carts_list('30_days') : array();
$has_real_data = $woo_data->is_woocommerce_active() && $cart_data !== false;
?>

<div class="wrap arteasy-cart-recovery">
    <h1>Smart Cart Recovery System</h1>
    
    <?php if (!$has_real_data): ?>
    <div class="notice notice-info">
        <p><strong>Demo Mode:</strong> Install and activate WooCommerce to see real cart abandonment data. Currently showing sample data.</p>
    </div>
    <?php endif; ?>
    
    <div class="cart-recovery-container">
        <div class="recovery-form">
            <h2>Generate Cart Recovery Message</h2>
            <form id="cart-recovery-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">Customer</th>
                        <td>
                            <input type="text" id="customer_search" class="regular-text" placeholder="Search name, email or phone" autocomplete="off" />
                            <div id="customer_results" class="arteasy-search-results" style="display:none;"></div>
                            <div style="margin-top:8px;">
                                <input type="text" id="customer_name" name="customer_name" class="regular-text" placeholder="Customer name" />
                                <input type="email" id="customer_email" class="regular-text" placeholder="Email" style="margin-left:6px;" />
                                <button type="button" class="button" id="use_recent_cart" style="margin-left:6px;">Use recent abandoned cart</button>
                            </div>
                            <p class="description">Search and select a customer to auto-fill details and recent cart</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Template Style</th>
                        <td>
                            <select id="template_style" name="template_style" class="regular-text">
                                <option value="artistic">Artistic - Creative & Inspiring</option>
                                <option value="professional">Professional - Technical & Reliable</option>
                                <option value="nigerian">Nigerian - Local Context</option>
                                <option value="friendly">Friendly - Warm & Personal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Cart Items</th>
                        <td>
                            <div style="margin-bottom:8px;">
                                <input type="text" id="product_search" class="regular-text" placeholder="Search product name or SKU" autocomplete="off" />
                                <div id="product_results" class="arteasy-search-results" style="display:none;"></div>
                            </div>
                            <div id="cart-items-container"></div>
                            <div style="margin-top:8px; font-weight:600;">Subtotal: ₦<span id="items_subtotal">0.00</span></div>
                            <p class="description">Use search to add items. Adjust quantities inline.</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">Generate Recovery Message</button>
                </p>
            </form>
        </div>
        
        <div class="recovery-result">
            <h2>Generated Recovery Message</h2>
            <div id="generated-message" class="message-output">
                <p class="placeholder">Your AI-generated cart recovery message will appear here...</p>
            </div>
            
            <div class="result-actions" style="display: none;">
                <button type="button" id="copy-message" class="button">Copy Message</button>
                <button type="button" id="regenerate-message" class="button">Regenerate</button>
                <button type="button" id="send-test-email" class="button">Send Test Email</button>
            </div>
        </div>
    </div>
    
    <div class="recovery-controls" style="background: white; padding: 20px; border: 1px solid #e1e5e9; border-radius: 10px; margin-bottom: 20px;">
        <h2>Recovery Controls</h2>
        <?php 
        $test_mode = get_option('arteasy_cart_recovery_test_mode', false);
        $recovery_active = get_option('arteasy_cart_recovery_active', false);
        ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 15px;">
            <div>
                <strong>Status:</strong> <span id="recovery-status-display" style="color: <?php echo $recovery_active ? 'green' : 'red'; ?>;">
                    <?php echo $recovery_active ? '✅ Active' : '❌ Inactive'; ?>
                </span>
            </div>
            <div>
                <strong>Test Mode:</strong> 
                <span id="test-mode-status" style="color: <?php echo $test_mode ? 'orange' : 'green'; ?>;">
                    <?php echo $test_mode ? 'ON (5 min intervals)' : 'OFF (1 hour intervals)'; ?>
                </span>
                <button type="button" class="button" id="toggle-test-mode" style="margin-left: 10px;">
                    <?php echo $test_mode ? 'Disable Test Mode' : 'Enable Test Mode'; ?>
                </button>
            </div>
            <div>
                <?php if ($recovery_active): ?>
                    <button type="button" class="button" id="deactivate-cart-recovery" style="background: #ef4444; color: white; border-color: #ef4444; margin-right: 10px;">
                        ⛔ Deactivate Cart Recovery
                    </button>
                    <button type="button" class="button button-primary" id="trigger-recovery-now">
                        ⚡ Trigger Recovery Now (Test)
                    </button>
                <?php else: ?>
                    <button type="button" class="button button-primary" id="activate-cart-recovery">
                        ✅ Activate Cart Recovery
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <p class="description" style="margin-top: 10px;">
            <strong>Test Mode:</strong> Emails send after 5, 10, 15 minutes (instead of 1, 24, 48 hours). 
            Perfect for testing! Trigger manually button runs the process immediately.
        </p>
    </div>

    <div class="recovery-stats">
        <h2>Recovery Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Abandoned Carts</h3>
                <div class="stat-number"><?php echo $has_real_data && isset($cart_data['abandoned_count']) ? $cart_data['abandoned_count'] : ($has_real_data ? '0' : '24'); ?></div>
                <div class="stat-label"><?php echo $has_real_data ? 'Last 30 days' : 'This Week'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Recovery Rate</h3>
                <div class="stat-number"><?php echo $has_real_data && isset($cart_data['recovery_rate']) ? $cart_data['recovery_rate'] . '%' : ($has_real_data ? '0%' : '35%'); ?></div>
                <div class="stat-label"><?php echo $has_real_data ? 'Last 30 days' : 'Average'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Revenue Recovered</h3>
                <div class="stat-number">₦<?php echo $has_real_data && isset($cart_data['recovered_revenue']) ? number_format($cart_data['recovered_revenue'], 2) : ($has_real_data ? '0.00' : '45,000'); ?></div>
                <div class="stat-label"><?php echo $has_real_data ? 'Last 30 days' : 'This Month'; ?></div>
            </div>
        </div>
    </div>

    <div class="recovery-lists">
        <h2>Abandoned Carts Details</h2>
        <div class="lists-grid">
            <div class="list-card">
                <h3>Abandoned Orders (WooCommerce)</h3>
                <?php if (!empty($abandoned_orders)) : ?>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total (₦)</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($abandoned_orders as $o) : ?>
                        <tr>
                            <td>#<?php echo intval($o['id']); ?></td>
                            <td><?php echo esc_html(date('Y-m-d H:i', strtotime($o['date']))); ?></td>
                            <td><?php echo esc_html(str_replace('wc-', '', $o['status'])); ?></td>
                            <td><?php echo number_format($o['total'], 2); ?></td>
                            <td><?php echo esc_html($o['email']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="description">No abandoned orders found in the selected period.</p>
                <?php endif; ?>
            </div>

            <div class="list-card">
                <h3>Tracked Carts (Live Tracking)</h3>
                <?php if (!empty($tracked_carts)) : ?>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Last Active</th>
                            <th>Items</th>
                            <th>Total (₦)</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tracked_carts as $c) : ?>
                        <tr>
                            <td><?php echo esc_html($c['key']); ?></td>
                            <td><?php echo esc_html($c['timestamp']); ?></td>
                            <td><?php echo intval($c['items']); ?></td>
                            <td><?php echo number_format($c['total'], 2); ?></td>
                            <td><?php echo esc_html($c['email']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="description">No tracked carts yet. Add items to cart and wait ~60s (or leave the page) to see live entries.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="recovery-info">
        <h3>How Smart Cart Recovery Works</h3>
        <ul>
            <li>Live tracking saves carts on inactivity or exit (guests + logged-in)</li>
            <li>Abandoned orders are detected from WooCommerce (pending/on-hold)</li>
            <li>Hourly automation sends recovery emails at 1h, 24h, 48h</li>
            <li>AI generates personalized recovery messages referencing cart items</li>
            <li>Dashboard combines WooCommerce orders + tracked carts</li>
        </ul>
        
        <div class="template-examples">
            <h3>Template Examples:</h3>
            <div class="example-tabs">
                <button class="tab-button active" data-template="artistic">Artistic</button>
                <button class="tab-button" data-template="professional">Professional</button>
                <button class="tab-button" data-template="nigerian">Nigerian</button>
                <button class="tab-button" data-template="friendly">Friendly</button>
            </div>
            <div class="example-content">
                <div id="artistic-example" class="example-text">
                    "Hi [Name], your artistic vision is waiting! We noticed you left some beautiful art supplies in your cart. Don't let your creativity be put on hold - complete your order and bring your next masterpiece to life!"
                </div>
                <div id="professional-example" class="example-text" style="display: none;">
                    "Dear [Name], we noticed you have high-quality art supplies in your cart. These professional-grade materials are essential for achieving the results you're looking for. Complete your order today."
                </div>
                <div id="nigerian-example" class="example-text" style="display: none;">
                    "Hello [Name], your art supplies are ready for delivery to Lagos! Don't miss out on these quality materials that Nigerian artists trust. Complete your order and we'll deliver to your doorstep."
                </div>
                <div id="friendly-example" class="example-text" style="display: none;">
                    "Hey [Name], we miss you! Your cart is still here with those lovely art supplies you picked out. We'd love to help you complete your creative journey. What do you say?"
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Simple debounce
    function debounce(fn, wait){ let t; return function(){ clearTimeout(t); const args=arguments; t=setTimeout(()=>fn.apply(this,args), wait); } }

    // Add cart item
    function renderCartItem(p){
        const id = p.id || 0;
        const name = p.name || '';
        const price = parseFloat(p.price || 0);
        const sku = p.sku || '';
        const qty = p.qty ? parseInt(p.qty) : 1;
        const row = $('<div class="cart-item" data-id="'+id+'" data-sku="'+sku+'">'
            + '<span style="display:inline-block; min-width:220px;">'+name+(sku? ' <small>('+sku+')</small>':'')+'</span>'
            + ' x <input type="number" class="small-text item-qty" min="1" step="1" value="'+qty+'" style="width:70px;" />'
            + ' @ ₦<input type="number" class="small-text item-price" step="0.01" min="0" value="'+price.toFixed(2)+'" style="width:100px;" />'
            + ' <button type="button" class="button remove-item">Remove</button>'
            + '</div>');
        $('#cart-items-container').append(row);
        recalcSubtotal();
    }

    function recalcSubtotal(){
        let sum = 0;
        $('#cart-items-container .cart-item').each(function(){
            const qty = parseInt($(this).find('.item-qty').val()||'1');
            const price = parseFloat($(this).find('.item-price').val()||'0');
            sum += (qty * price);
        });
        $('#items_subtotal').text(sum.toFixed(2));
    }

    
    // Remove cart item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.cart-item').remove();
        recalcSubtotal();
    });
    $(document).on('change keyup', '.item-qty,.item-price', recalcSubtotal);
    
    // Handle form submission
    $('#cart-recovery-form').on('submit', function(e) {
        e.preventDefault();
        generateRecoveryMessage();
    });
    
    // Customer search
    $('#customer_search').on('input', debounce(function(){
        const q = $(this).val().trim();
        if (!q){ $('#customer_results').hide().empty(); return; }
        $.post(ajaxurl, {action:'arteasy_search_customers', q:q}, function(res){
            if (!res.success){ $('#customer_results').hide().empty(); return; }
            const list = res.data || [];
            const box = $('#customer_results').empty();
            list.forEach(function(c){
                const item = $('<div class="arteasy-result-item" style="padding:6px; cursor:pointer; border-bottom:1px solid #eee;"></div>');
                item.text(c.name + ' <'+c.email+'> ' + (c.phone? ' '+c.phone:''));
                item.data('customer', c);
                box.append(item);
            });
            box.show();
        });
    }, 250));

    $(document).on('click', '.arteasy-result-item', function(){
        const c = $(this).data('customer');
        $('#customer_name').val(c.name||'');
        $('#customer_email').val(c.email||'');
        $('#customer_results').hide().empty();
    });

    // Use recent abandoned cart
    $('#use_recent_cart').on('click', function(){
        const email = $('#customer_email').val().trim();
        if (!email){ alert('Enter/select customer email first'); return; }
        $(this).prop('disabled', true).text('Loading...');
        $.post(ajaxurl, {action:'arteasy_get_recent_abandoned_cart', email:email}, function(res){
            if (res.success){
                $('#cart-items-container').empty();
                const data = res.data || {items:[]};
                (data.items||[]).forEach(function(it){ renderCartItem({id:it.product_id, name:it.name, price:it.price, qty:it.qty}); });
                recalcSubtotal();
            } else {
                alert('No recent abandoned cart found for this customer');
            }
        }).always(()=>$('#use_recent_cart').prop('disabled', false).text('Use recent abandoned cart'));
    });

    // Product search
    $('#product_search').on('input', debounce(function(){
        const q = $(this).val().trim();
        if (!q){ $('#product_results').hide().empty(); return; }
        $.post(ajaxurl, {action:'arteasy_search_products', q:q}, function(res){
            if (!res.success){ $('#product_results').hide().empty(); return; }
            const list = res.data || [];
            const box = $('#product_results').empty();
            list.forEach(function(p){
                const item = $('<div class="arteasy-result-item" style="padding:6px; cursor:pointer; border-bottom:1px solid #eee;"></div>');
                item.html((p.image? '<img src="'+p.image+'" style="height:20px;vertical-align:middle;margin-right:6px;" />':'') + p.name + (p.sku? ' <small>('+p.sku+')</small>':'') + ' — ₦'+parseFloat(p.price||0).toFixed(2));
                item.data('product', p);
                box.append(item);
            });
            box.show();
        });
    }, 250));

    $(document).on('click', '#product_results .arteasy-result-item', function(){
        const p = $(this).data('product');
        $('#product_results').hide().empty();
        $('#product_search').val('');
        renderCartItem(p);
    });

    // Copy message
    $('#copy-message').on('click', function() {
        var message = $('#generated-message').text();
        navigator.clipboard.writeText(message).then(function() {
            alert('Message copied to clipboard!');
        });
    });
    
    // Regenerate message
    $('#regenerate-message').on('click', function() {
        generateRecoveryMessage();
    });
    
    // Send test email
    $('#send-test-email').on('click', function() {
        alert('Test email feature coming soon!');
    });
    
    // Template examples
    $('.tab-button').on('click', function() {
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        
        var template = $(this).data('template');
        $('.example-text').hide();
        $('#' + template + '-example').show();
    });
    
    // Toggle test mode
    $('#toggle-test-mode').on('click', function() {
        var currentMode = $('#test-mode-status').text().includes('ON');
        var newMode = !currentMode;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'arteasy_toggle_test_mode',
                nonce: '<?php echo wp_create_nonce("arteasy_cart_recovery_action"); ?>',
                test_mode: newMode
            },
            success: function(response) {
                if (response.success) {
                    $('#test-mode-status').text(newMode ? 'ON (5 min intervals)' : 'OFF (1 hour intervals)');
                    $('#test-mode-status').css('color', newMode ? 'orange' : 'green');
                    $('#toggle-test-mode').text(newMode ? 'Disable Test Mode' : 'Enable Test Mode');
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Activate Cart Recovery
    $('#activate-cart-recovery').on('click', function() {
        if (!confirm('Activate Cart Recovery System? This will start tracking abandoned carts and sending recovery emails.')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Activating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'arteasy_setup_cart_recovery',
                nonce: '<?php echo wp_create_nonce("arteasy_setup_cart_recovery"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Cart recovery system activated successfully!');
                    window.location.reload();
                } else {
                    alert('❌ Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('❌ Error activating cart recovery');
            },
            complete: function() {
                $('#activate-cart-recovery').prop('disabled', false).text('✅ Activate Cart Recovery');
            }
        });
    });
    
    // Deactivate Cart Recovery
    $('#deactivate-cart-recovery').on('click', function() {
        if (!confirm('Are you sure you want to deactivate Cart Recovery? All tracking and email automation will stop.')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Deactivating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'arteasy_deactivate_cart_recovery',
                nonce: '<?php echo wp_create_nonce("arteasy_setup_cart_recovery"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Cart recovery system deactivated successfully!');
                    window.location.reload();
                } else {
                    alert('❌ Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('❌ Error deactivating cart recovery');
            },
            complete: function() {
                $('#deactivate-cart-recovery').prop('disabled', false).text('⛔ Deactivate Cart Recovery');
            }
        });
    });
    
    // Trigger recovery manually
    $('#trigger-recovery-now').on('click', function() {
        if (!confirm('This will process all abandoned carts and send emails immediately. Continue?')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'arteasy_trigger_recovery_manually',
                nonce: '<?php echo wp_create_nonce("arteasy_cart_recovery_action"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var msg = '✅ Recovery process completed!\n\n';
                    msg += 'Emails sent: ' + (response.data.emails_sent || 0) + '\n';
                    if (response.data.errors && response.data.errors.length > 0) {
                        msg += 'Errors: ' + response.data.errors.join(', ');
                    }
                    alert(msg);
                } else {
                    alert('❌ Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('❌ Network error occurred');
            },
            complete: function() {
                $('#trigger-recovery-now').prop('disabled', false).text('⚡ Trigger Recovery Now (Test)');
            }
        });
    });
    
    function generateRecoveryMessage() {
        var cartItems = [];
        $('#cart-items-container .cart-item').each(function(){
            const name = $(this).find('span').first().text();
            const price = $(this).find('.item-price').val();
            const qty = $(this).find('.item-qty').val();
            if (name && price) { cartItems.push({name:name, price:price, qty:qty}); }
        });
        
        var formData = {
            action: 'arteasy_generate_cart_recovery',
            customer_name: $('#customer_name').val(),
            template_style: $('#template_style').val(),
            cart_items: JSON.stringify(cartItems)
        };
        
        $('#generated-message').html('<p class="loading">Generating recovery message...</p>');
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                $('#generated-message').html('<div class="message-text">' + response.data + '</div>');
                $('.result-actions').show();
            } else {
                $('#generated-message').html('<p class="error">Error: ' + response.data + '</p>');
            }
        });
    }
});
</script>

<style>
.arteasy-cart-recovery {
    max-width: 1200px;
}

.cart-recovery-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.recovery-form, .recovery-result {
    background: white;
    padding: 25px;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cart-item {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
    align-items: center;
}

.cart-item input {
    flex: 1;
}

.message-output {
    min-height: 200px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #f9f9f9;
}

.message-text {
    line-height: 1.6;
    color: #333;
}

.recovery-stats {
    background: white;
    padding: 25px;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-card {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #0073aa;
    margin: 10px 0;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.recovery-info {
    background: #f0f0f1;
    padding: 20px;
    border-radius: 10px;
}

.template-examples {
    margin-top: 20px;
}

.example-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.tab-button {
    padding: 8px 16px;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
    border-radius: 4px;
}

.tab-button.active {
    background: #0073aa;
    color: white;
}

.example-text {
    padding: 15px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-style: italic;
    color: #666;
}

.loading {
    color: #666;
    font-style: italic;
}

.error {
    color: #d63638;
}

.result-actions {
    margin-top: 15px;
}

.result-actions .button {
    margin-right: 10px;
}

.recovery-lists {
    background: white;
    padding: 25px;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    margin-top: 30px;
}

.recovery-lists h2 {
    margin-top: 0;
}

.lists-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.list-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.list-card h3 {
    margin-top: 0;
    color: #0073aa;
}

.list-card table {
    width: 100%;
    margin-top: 10px;
}

.list-card table th {
    background: #e1e5e9;
    padding: 8px;
    text-align: left;
    font-size: 12px;
}

.list-card table td {
    padding: 8px;
    font-size: 12px;
    border-bottom: 1px solid #ddd;
}

@media (max-width: 1200px) {
    .lists-grid {
        grid-template-columns: 1fr;
    }
}
</style>

