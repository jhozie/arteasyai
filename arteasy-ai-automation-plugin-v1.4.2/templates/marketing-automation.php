<?php
/**
 * Arteasy AI Marketing Automation Dashboard
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include WooCommerce integration
if (file_exists(ARTEASY_AI_PLUGIN_PATH . "includes/woocommerce-integration.php")) {
    require_once ARTEASY_AI_PLUGIN_PATH . "includes/woocommerce-integration.php";
}

$woo_data = new ArteasyWooCommerceData();
$smart_campaigns = $woo_data->generate_smart_campaigns('30_days');
$advanced_cart_data = $woo_data->get_advanced_cart_abandonment('30_days');
$bundle_recommendations = $woo_data->generate_bundle_recommendations('30_days');
$dashboard_metrics = $woo_data->get_marketing_dashboard_metrics('30_days');

// Check if we have real data or need to show demo data
$has_real_data = $woo_data->is_woocommerce_active() && $smart_campaigns !== false;
?>

<div class="wrap arteasy-marketing-automation">
    <h1>🤖 AI Marketing Automation</h1>
    
    <div class="marketing-overview">
        <h2>📊 Marketing Automation Overview</h2>
        <div class="overview-cards" id="marketing-overview-cards">
            <div class="overview-card">
                <h3>Active Campaigns</h3>
                <div class="card-value" id="active-campaigns-count"><?php echo $dashboard_metrics['active_campaigns']; ?></div>
                <div class="card-label">Smart campaigns running</div>
            </div>
            <div class="overview-card">
                <h3>Cart Recovery Rate</h3>
                <div class="card-value" id="cart-recovery-rate"><?php echo $dashboard_metrics['cart_recovery_rate']; ?>%</div>
                <div class="card-label">Average recovery rate</div>
            </div>
            <div class="overview-card">
                <h3>Bundle Opportunities</h3>
                <div class="card-value" id="bundle-opportunities"><?php echo $dashboard_metrics['bundle_opportunities']; ?></div>
                <div class="card-label">Recommended bundles</div>
            </div>
            <div class="overview-card">
                <h3>Automation Level</h3>
                <div class="card-value" id="automation-level"><?php echo $dashboard_metrics['automation_level']; ?>%</div>
                <div class="card-label">Automated processes</div>
            </div>
        </div>
        
        <!-- Real-Time Status Section -->
        <div class="activation-status-section">
            <h3>🎯 Activation Status</h3>
            <div class="status-cards" id="activation-status-cards">
                <div class="status-card" id="campaigns-status">
                    <div class="status-icon">📧</div>
                    <div class="status-info">
                        <div class="status-title">Campaigns</div>
                        <div class="status-count">0 Active</div>
                    </div>
                </div>
                <div class="status-card" id="bundles-status">
                    <div class="status-icon">📦</div>
                    <div class="status-info">
                        <div class="status-title">Bundles</div>
                        <div class="status-count">0 Created</div>
                    </div>
                </div>
                <div class="status-card" id="cart-recovery-status">
                    <div class="status-icon">🛒</div>
                    <div class="status-info">
                        <div class="status-title">Cart Recovery</div>
                        <div class="status-count">Inactive</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="recent-activity-section">
            <h3>📈 Recent Activity</h3>
            <div class="activity-list" id="recent-activity-list">
                <div class="activity-item">
                    <div class="activity-icon">⏳</div>
                    <div class="activity-text">Loading recent activity...</div>
                    <div class="activity-time">Just now</div>
                </div>
            </div>
        </div>
    </div>

    <div class="marketing-sections">
        <div class="marketing-section">
            <h2>🎯 Smart Campaign Generator</h2>
            <div class="campaigns-container">
                <?php if ($has_real_data && $smart_campaigns): ?>
                    <?php foreach ($smart_campaigns as $campaign_key => $campaign): ?>
                    <div class="campaign-card">
                        <div class="campaign-header">
                            <h3><?php echo esc_html($campaign['name']); ?></h3>
                            <span class="campaign-type"><?php echo esc_html($campaign['type']); ?></span>
                        </div>
                        <div class="campaign-content">
                            <p class="campaign-description"><?php echo esc_html($campaign['description']); ?></p>
                            <div class="campaign-details">
                                <div class="detail-item">
                                    <strong>Target:</strong> <?php echo esc_html($campaign['target_audience']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Duration:</strong> <?php echo esc_html($campaign['duration']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Impact:</strong> <?php echo esc_html($campaign['expected_impact']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Automation:</strong> <?php echo esc_html($campaign['automation_level']); ?>
                                </div>
                            </div>
                            <div class="campaign-strategy">
                                <h4>Strategy:</h4>
                                <ul>
                                    <?php foreach ($campaign['strategy'] as $strategy_key => $strategy_value): ?>
                                    <li><strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $strategy_key))); ?>:</strong> <?php echo esc_html($strategy_value); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="campaign-actions">
                                <button class="button button-primary" onclick="openCampaignSettings('<?php echo esc_js($campaign_key); ?>', <?php echo esc_js(json_encode($campaign)); ?>)">Configure & Activate</button>
                                <button class="button" onclick="viewCampaignDetails('<?php echo esc_js($campaign_key); ?>')">View Details</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="demo-campaigns">
                        <p><strong>Demo Mode:</strong> Smart campaigns require WooCommerce data.</p>
                        <div class="campaign-card">
                            <div class="campaign-header">
                                <h3>Weekend Art Sale Campaign</h3>
                                <span class="campaign-type">weekend_boost</span>
                            </div>
                            <div class="campaign-content">
                                <p class="campaign-description">Boost sales on weekends when your performance is 40% higher</p>
                                <div class="campaign-details">
                                    <div class="detail-item"><strong>Target:</strong> All customers</div>
                                    <div class="detail-item"><strong>Duration:</strong> Every weekend</div>
                                    <div class="detail-item"><strong>Impact:</strong> Increase weekend sales by 25-40%</div>
                                    <div class="detail-item"><strong>Automation:</strong> High - Can be fully automated</div>
                                </div>
                                <div class="campaign-strategy">
                                    <h4>Strategy:</h4>
                                    <ul>
                                        <li><strong>Social Media Posts:</strong> Post art tutorials and behind-the-scenes content</li>
                                        <li><strong>Email Marketing:</strong> Send weekend sale announcements on Friday</li>
                                        <li><strong>Discount Offers:</strong> Offer 15% off on all art supplies</li>
                                        <li><strong>Content Focus:</strong> Feature popular products and customer success stories</li>
                                    </ul>
                                </div>
                                <div class="campaign-actions">
                                    <button class="button button-primary">Activate Campaign</button>
                                    <button class="button">View Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="marketing-section">
            <h2>🛒 Advanced Cart Recovery</h2>
            <div class="cart-recovery-setup">
                <button class="button button-primary" onclick="setupCartRecovery()">Activate Cart Recovery System</button>
                <p>This will start tracking abandoned carts and sending automated recovery emails.</p>
            </div>
            <div class="cart-recovery-container">
                <?php if ($has_real_data && $advanced_cart_data): ?>
                    <div class="recovery-strategies">
                        <h3>Recovery Strategies</h3>
                        <?php foreach ($advanced_cart_data['recovery_strategies'] as $strategy_key => $strategy): ?>
                        <div class="strategy-card">
                            <div class="strategy-header">
                                <h4><?php echo esc_html($strategy['strategy']); ?></h4>
                                <span class="strategy-timeframe"><?php echo esc_html($strategy['timeframe']); ?></span>
                            </div>
                            <div class="strategy-content">
                                <p><?php echo esc_html($strategy['message']); ?></p>
                                <div class="strategy-effectiveness"><?php echo esc_html($strategy['effectiveness']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="recovery-timing">
                        <h3>Optimal Recovery Timing</h3>
                        <div class="timing-grid">
                            <div class="timing-card">
                                <h4>Best Hours</h4>
                                <ul>
                                    <?php foreach ($advanced_cart_data['optimal_timing']['best_hours'] as $hour): ?>
                                    <li><?php echo esc_html($hour); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="timing-card">
                                <h4>Best Days</h4>
                                <ul>
                                    <?php foreach ($advanced_cart_data['optimal_timing']['best_days'] as $day): ?>
                                    <li><?php echo esc_html($day); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="demo-cart-recovery">
                        <p><strong>Demo Mode:</strong> Advanced cart recovery requires WooCommerce data.</p>
                        <div class="recovery-strategies">
                            <h3>Sample Recovery Strategies</h3>
                            <div class="strategy-card">
                                <div class="strategy-header">
                                    <h4>Exit-intent popup with 10% discount</h4>
                                    <span class="strategy-timeframe">Within 1 hour</span>
                                </div>
                                <div class="strategy-content">
                                    <p>Wait! Don't miss out on these amazing art supplies. Get 10% off your order now!</p>
                                    <div class="strategy-effectiveness">High - 15-25% recovery rate</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="marketing-section">
            <h2>📦 Product Bundle Intelligence</h2>
            <div class="bundles-container">
                <?php if ($has_real_data && $bundle_recommendations): ?>
                    <?php foreach ($bundle_recommendations as $bundle_key => $bundle): ?>
                    <div class="bundle-card">
                        <div class="bundle-header">
                            <h3><?php echo esc_html($bundle['name']); ?></h3>
                            <span class="bundle-discount"><?php echo esc_html($bundle['discount']); ?> OFF</span>
                        </div>
                        <div class="bundle-content">
                            <p><?php echo esc_html($bundle['description']); ?></p>
                            <div class="bundle-details">
                                <div class="detail-item">
                                    <strong>Target:</strong> <?php echo esc_html($bundle['target_audience']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Savings:</strong> <?php echo esc_html($bundle['price_savings']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Potential:</strong> <?php echo esc_html($bundle['conversion_potential']); ?>
                                </div>
                            </div>
                            <div class="bundle-products">
                                <h4>Included Products:</h4>
                                <ul>
                                    <?php foreach ($bundle['products'] as $product): ?>
                                    <li><?php echo esc_html($product['name']); ?> - ₦<?php echo number_format($product['revenue']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="bundle-actions">
                                <button class="button button-primary" onclick="createRealBundle('<?php echo esc_js($bundle_key); ?>', <?php echo esc_js(json_encode($bundle)); ?>)">Create Bundle</button>
                                <button class="button" onclick="previewBundle('<?php echo esc_js($bundle_key); ?>')">Preview Bundle</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="demo-bundles">
                        <p><strong>Demo Mode:</strong> Bundle recommendations require WooCommerce data.</p>
                        <div class="bundle-card">
                            <div class="bundle-header">
                                <h3>Art Starter Bundle</h3>
                                <span class="bundle-discount">20% OFF</span>
                            </div>
                            <div class="bundle-content">
                                <p>Perfect for beginners - includes all essentials</p>
                                <div class="bundle-details">
                                    <div class="detail-item"><strong>Target:</strong> New customers</div>
                                    <div class="detail-item"><strong>Savings:</strong> Save ₦2,500</div>
                                    <div class="detail-item"><strong>Potential:</strong> High</div>
                                </div>
                                <div class="bundle-actions">
                                    <button class="button button-primary">Create Bundle</button>
                                    <button class="button">Preview Bundle</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Campaign Settings Modal -->
<div id="campaign-settings-modal" class="campaign-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Configure Campaign Settings</h2>
            <span class="close-modal" onclick="closeCampaignSettings()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="campaign-settings-form">
                <div class="settings-section">
                    <h3>Coupon Settings</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="coupon-code">Coupon Code Prefix:</label>
                            <input type="text" id="coupon-code" name="coupon_code" placeholder="WEEKEND" required>
                            <small>Will be combined with date: WEEKEND_20251023</small>
                        </div>
                        <div class="form-group">
                            <label for="discount-type">Discount Type:</label>
                            <select id="discount-type" name="discount_type" required>
                                <option value="percent">Percentage</option>
                                <option value="fixed_cart">Fixed Amount</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="discount-amount">Discount Amount:</label>
                            <input type="number" id="discount-amount" name="discount_amount" min="1" max="100" value="15" required>
                            <small id="discount-help">Percentage (%) or Fixed Amount (₦)</small>
                        </div>
                        <div class="form-group">
                            <label for="usage-limit">Usage Limit:</label>
                            <input type="number" id="usage-limit" name="usage_limit" min="1" value="1000" required>
                            <small>Maximum number of uses</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry-days">Expiry (Days):</label>
                            <input type="number" id="expiry-days" name="expiry_days" min="1" max="365" value="7" required>
                            <small>Days from today</small>
                        </div>
                        <div class="form-group">
                            <label for="minimum-amount">Minimum Order Amount:</label>
                            <input type="number" id="minimum-amount" name="minimum_amount" min="0" value="5000" required>
                            <small>Minimum cart value (₦)</small>
                        </div>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h3>Targeting Settings</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="target-customers">Target Customer Type:</label>
                            <select id="target-customers" name="target_customers">
                                <option value="all">All Customers</option>
                                <option value="new">New Customers Only</option>
                                <option value="returning">Returning Customers Only</option>
                                <option value="vip">VIP Customers Only</option>
                                <option value="inactive">Inactive Customers</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product-categories">Product Categories:</label>
                            <select id="product-categories" name="product_categories[]" multiple>
                                <option value="all">All Categories</option>
                                <!-- Categories will be loaded dynamically -->
                            </select>
                            <small>Hold Ctrl/Cmd to select multiple</small>
                        </div>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h3>Email Settings</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="send-email">Send Announcement Email:</label>
                            <input type="checkbox" id="send-email" name="send_email" checked>
                            <small>Send email to target customers</small>
                        </div>
                        <div class="form-group">
                            <label for="email-delay">Email Delay (Hours):</label>
                            <input type="number" id="email-delay" name="email_delay" min="0" max="168" value="2">
                            <small>Hours after activation</small>
                        </div>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h3>Campaign Preview</h3>
                    <div id="campaign-preview" class="campaign-preview">
                        <div class="preview-coupon">
                            <h4>Coupon Preview:</h4>
                            <div class="coupon-preview">
                                <span class="coupon-code">WEEKEND_20251023</span>
                                <span class="coupon-discount">15% OFF</span>
                                <span class="coupon-expiry">Expires: Oct 30, 2025</span>
                            </div>
                        </div>
                        <div class="preview-stats">
                            <h4>Campaign Stats:</h4>
                            <ul>
                                <li><strong>Target:</strong> All Customers</li>
                                <li><strong>Usage Limit:</strong> 1,000 uses</li>
                                <li><strong>Minimum Order:</strong> ₦5,000</li>
                                <li><strong>Duration:</strong> 7 days</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="button" onclick="closeCampaignSettings()">Cancel</button>
            <button type="button" class="button button-primary" onclick="activateCampaignWithSettings()">Activate Campaign</button>
        </div>
    </div>
</div>

<style>
/* Marketing Automation Styles */
.arteasy-marketing-automation {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.marketing-overview {
    margin-bottom: 30px;
}

.overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.overview-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.overview-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}

.card-value {
    font-size: 28px;
    font-weight: bold;
    margin: 10px 0;
}

.card-label {
    font-size: 12px;
    opacity: 0.8;
}

/* Activation Status Styles */
.activation-status-section {
    margin-top: 30px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.activation-status-section h3 {
    margin: 0 0 20px 0;
    color: #1f2937;
    font-size: 18px;
}

.status-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.status-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s ease;
}

.status-card.active {
    border-color: #10b981;
    background: #f0fdf4;
}

.status-card.inactive {
    border-color: #ef4444;
    background: #fef2f2;
}

.status-icon {
    font-size: 24px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 8px;
}

.status-info {
    flex: 1;
}

.status-title {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
    margin-bottom: 4px;
}

.status-count {
    font-size: 12px;
    color: #6b7280;
}

/* Recent Activity Styles */
.recent-activity-section {
    margin-top: 30px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.recent-activity-section h3 {
    margin: 0 0 20px 0;
    color: #1f2937;
    font-size: 18px;
}

.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 6px;
    margin-bottom: 8px;
    border: 1px solid #e5e7eb;
}

.activity-icon {
    font-size: 16px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 6px;
}

.activity-text {
    flex: 1;
    font-size: 14px;
    color: #374151;
}

.activity-time {
    font-size: 12px;
    color: #6b7280;
}

.marketing-sections {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.marketing-section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.marketing-section h2 {
    margin: 0 0 20px 0;
    color: #1f2937;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}

/* Campaign Cards */
.campaigns-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.campaign-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.2s ease;
}

.campaign-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.campaign-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.campaign-header h3 {
    margin: 0;
    font-size: 18px;
}

.campaign-type {
    background: rgba(255,255,255,0.2);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    text-transform: uppercase;
}

.campaign-content {
    padding: 20px;
}

.campaign-description {
    margin: 0 0 15px 0;
    color: #374151;
    font-size: 14px;
}

.campaign-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.detail-item {
    font-size: 13px;
    color: #6b7280;
}

.detail-item strong {
    color: #374151;
}

.campaign-strategy h4 {
    margin: 0 0 10px 0;
    color: #1f2937;
    font-size: 14px;
}

.campaign-strategy ul {
    margin: 0 0 20px 0;
    padding-left: 20px;
}

.campaign-strategy li {
    margin-bottom: 5px;
    font-size: 13px;
    color: #374151;
}

.campaign-actions {
    display: flex;
    gap: 10px;
}

/* Cart Recovery Styles */
.cart-recovery-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.recovery-strategies h3,
.recovery-timing h3 {
    margin: 0 0 20px 0;
    color: #1f2937;
}

.strategy-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.strategy-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.strategy-header h4 {
    margin: 0;
    font-size: 14px;
    color: #1f2937;
}

.strategy-timeframe {
    background: #dbeafe;
    color: #1e40af;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
}

.strategy-content p {
    margin: 0 0 10px 0;
    font-size: 13px;
    color: #374151;
}

.strategy-effectiveness {
    background: #dcfce7;
    color: #166534;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    display: inline-block;
}

.timing-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.timing-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
}

.timing-card h4 {
    margin: 0 0 10px 0;
    color: #1f2937;
    font-size: 14px;
}

.timing-card ul {
    margin: 0;
    padding-left: 20px;
}

.timing-card li {
    font-size: 13px;
    color: #374151;
    margin-bottom: 5px;
}

/* Bundle Cards */
.bundles-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.bundle-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.bundle-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bundle-header h3 {
    margin: 0;
    font-size: 18px;
}

.bundle-discount {
    background: rgba(255,255,255,0.2);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.bundle-content {
    padding: 20px;
}

.bundle-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin: 15px 0;
}

.bundle-products h4 {
    margin: 0 0 10px 0;
    color: #1f2937;
    font-size: 14px;
}

.bundle-products ul {
    margin: 0 0 20px 0;
    padding-left: 20px;
}

.bundle-products li {
    font-size: 13px;
    color: #374151;
    margin-bottom: 5px;
}

.bundle-actions {
    display: flex;
    gap: 10px;
}

/* Demo Mode Styles */
.demo-campaigns,
.demo-cart-recovery,
.demo-bundles {
    text-align: center;
    padding: 40px;
    background: #f8fafc;
    border-radius: 10px;
    border: 2px dashed #cbd5e1;
}

.demo-campaigns p,
.demo-cart-recovery p,
.demo-bundles p {
    margin: 0 0 20px 0;
    color: #64748b;
    font-size: 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .overview-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .campaigns-container,
    .bundles-container {
        grid-template-columns: 1fr;
    }
    
    .cart-recovery-container {
        grid-template-columns: 1fr;
    }
    
    .timing-grid {
        grid-template-columns: 1fr;
    }
    
    .campaign-details,
    .bundle-details {
        grid-template-columns: 1fr;
    }
}

/* Campaign Settings Modal Styles */
.campaign-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 10px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 20px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.close-modal {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    opacity: 0.8;
}

.close-modal:hover {
    opacity: 1;
}

.modal-body {
    padding: 30px;
}

.modal-footer {
    padding: 20px 30px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.settings-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.settings-section:last-child {
    border-bottom: none;
}

.settings-section h3 {
    margin: 0 0 20px 0;
    color: #1f2937;
    font-size: 18px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group select {
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group small {
    margin-top: 5px;
    color: #6b7280;
    font-size: 12px;
}

.campaign-preview {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.preview-coupon h4,
.preview-stats h4 {
    margin: 0 0 15px 0;
    color: #1f2937;
    font-size: 16px;
}

.coupon-preview {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.coupon-code {
    background: #3b82f6;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-family: monospace;
    font-weight: bold;
    text-align: center;
}

.coupon-discount {
    background: #10b981;
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    text-align: center;
    font-weight: bold;
}

.coupon-expiry {
    background: #f59e0b;
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    text-align: center;
    font-size: 12px;
}

.preview-stats ul {
    margin: 0;
    padding-left: 20px;
}

.preview-stats li {
    margin-bottom: 8px;
    color: #374151;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .campaign-preview {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Real Marketing automation functions
function activateRealCampaign(campaignId, campaignData) {
    const btn = event.target;
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = 'Activating...';
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_activate_campaign',
            campaign_id: campaignId,
            campaign_data: JSON.stringify(campaignData),
            nonce: '<?php echo wp_create_nonce("arteasy_activate_campaign"); ?>'
        },
        success: function(response) {
            if (response.success) {
                showNotice('✅ Campaign activated successfully! Created coupons and scheduled emails.', 'success');
                
                // Show campaign results
                if (response.data.results) {
                    let resultsHtml = '<div class="campaign-results"><h4>Campaign Results:</h4><ul>';
                    Object.values(response.data.results).forEach(result => {
                        if (result.code) {
                            resultsHtml += `<li><strong>Coupon Created:</strong> ${result.code} - ${result.discount}</li>`;
                        }
                    });
                    resultsHtml += '</ul></div>';
                    showNotice(resultsHtml, 'success');
                }
            } else {
                showNotice('❌ Failed to activate campaign: ' + response.data, 'error');
            }
        },
        error: function() {
            showNotice('❌ Error activating campaign', 'error');
        },
        complete: function() {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
}

function createRealBundle(bundleId, bundleData) {
    const btn = event.target;
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = 'Creating...';
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_create_bundle',
            bundle_id: bundleId,
            bundle_data: JSON.stringify(bundleData),
            nonce: '<?php echo wp_create_nonce("arteasy_create_bundle"); ?>'
        },
        success: function(response) {
            if (response.success) {
                let detailsHtml = '';
                if (response.data.results && response.data.results.bundle_created) {
                    const bundle = response.data.results.bundle_created;
                    detailsHtml = `
                        <div class="bundle-results">
                            <h4>Bundle Created:</h4>
                            <ul>
                                <li><strong>Product ID:</strong> ${bundle.id}</li>
                                <li><strong>Name:</strong> ${bundle.name}</li>
                                <li><strong>Price:</strong> ₦${bundle.price.toLocaleString()}</li>
                                <li><strong>Savings:</strong> ₦${bundle.savings.toLocaleString()} (${bundle.discount_percent}%)</li>
                            </ul>
                        </div>
                    `;
                }
                showEnhancedSuccess('✅ Bundle created successfully! Check your WooCommerce products.', detailsHtml);
            } else {
                showNotice('❌ Failed to create bundle: ' + response.data, 'error');
            }
        },
        error: function() {
            showNotice('❌ Error creating bundle', 'error');
        },
        complete: function() {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
}

function setupCartRecovery() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_setup_cart_recovery',
            nonce: '<?php echo wp_create_nonce("arteasy_setup_cart_recovery"); ?>'
        },
        success: function(response) {
            if (response.success) {
                showEnhancedSuccess('✅ Cart recovery system activated! Tracking abandoned carts and sending recovery emails.');
            } else {
                showNotice('❌ Failed to setup cart recovery: ' + response.data, 'error');
            }
        },
        error: function() {
            showNotice('❌ Error setting up cart recovery', 'error');
        }
    });
}

function viewCampaignDetails(campaignId) {
    showNotice('Campaign details for: ' + campaignId, 'info');
}

function previewBundle(bundleId) {
    showNotice('Bundle preview for: ' + bundleId, 'info');
}

// Campaign Settings Modal Functions
let currentCampaignId = '';
let currentCampaignData = {};

function openCampaignSettings(campaignId, campaignData) {
    currentCampaignId = campaignId;
    currentCampaignData = campaignData;
    
    // Set default values based on campaign type
    const defaults = getCampaignDefaults(campaignData.type);
    
    document.getElementById('coupon-code').value = defaults.coupon_code;
    document.getElementById('discount-type').value = defaults.discount_type;
    document.getElementById('discount-amount').value = defaults.discount_amount;
    document.getElementById('usage-limit').value = defaults.usage_limit;
    document.getElementById('expiry-days').value = defaults.expiry_days;
    document.getElementById('minimum-amount').value = defaults.minimum_amount;
    document.getElementById('target-customers').value = defaults.target_customers;
    document.getElementById('send-email').checked = defaults.send_email;
    document.getElementById('email-delay').value = defaults.email_delay;
    
    // Update preview
    updateCampaignPreview();
    
    // Show modal
    document.getElementById('campaign-settings-modal').style.display = 'flex';
}

function closeCampaignSettings() {
    document.getElementById('campaign-settings-modal').style.display = 'none';
}

function getCampaignDefaults(campaignType) {
    const defaults = {
        'weekend_boost': {
            coupon_code: 'WEEKEND',
            discount_type: 'percent',
            discount_amount: 15,
            usage_limit: 1000,
            expiry_days: 7,
            minimum_amount: 5000,
            target_customers: 'all',
            send_email: true,
            email_delay: 2
        },
        'vip_retention': {
            coupon_code: 'VIP',
            discount_type: 'percent',
            discount_amount: 20,
            usage_limit: 100,
            expiry_days: 30,
            minimum_amount: 10000,
            target_customers: 'vip',
            send_email: true,
            email_delay: 1
        },
        'category_boost': {
            coupon_code: 'CATEGORY',
            discount_type: 'percent',
            discount_amount: 25,
            usage_limit: 500,
            expiry_days: 14,
            minimum_amount: 3000,
            target_customers: 'all',
            send_email: true,
            email_delay: 3
        },
        'revenue_recovery': {
            coupon_code: 'FLASH',
            discount_type: 'percent',
            discount_amount: 30,
            usage_limit: 200,
            expiry_days: 3,
            minimum_amount: 2000,
            target_customers: 'inactive',
            send_email: true,
            email_delay: 0
        }
    };
    
    return defaults[campaignType] || defaults['weekend_boost'];
}

function updateCampaignPreview() {
    const couponCode = document.getElementById('coupon-code').value;
    const discountType = document.getElementById('discount-type').value;
    const discountAmount = document.getElementById('discount-amount').value;
    const expiryDays = document.getElementById('expiry-days').value;
    const usageLimit = document.getElementById('usage-limit').value;
    const minimumAmount = document.getElementById('minimum-amount').value;
    const targetCustomers = document.getElementById('target-customers').value;
    
    // Generate coupon code with date
    const today = new Date();
    const dateStr = today.getFullYear() + 
                   String(today.getMonth() + 1).padStart(2, '0') + 
                   String(today.getDate()).padStart(2, '0');
    const fullCouponCode = `${couponCode}_${dateStr}`;
    
    // Calculate expiry date
    const expiryDate = new Date();
    expiryDate.setDate(expiryDate.getDate() + parseInt(expiryDays));
    const expiryStr = expiryDate.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
    
    // Update coupon preview
    document.querySelector('.coupon-code').textContent = fullCouponCode;
    document.querySelector('.coupon-discount').textContent = 
        discountType === 'percent' ? `${discountAmount}% OFF` : `₦${discountAmount} OFF`;
    document.querySelector('.coupon-expiry').textContent = `Expires: ${expiryStr}`;
    
    // Update stats preview
    const statsList = document.querySelector('.preview-stats ul');
    statsList.innerHTML = `
        <li><strong>Target:</strong> ${getTargetCustomerText(targetCustomers)}</li>
        <li><strong>Usage Limit:</strong> ${parseInt(usageLimit).toLocaleString()} uses</li>
        <li><strong>Minimum Order:</strong> ₦${parseInt(minimumAmount).toLocaleString()}</li>
        <li><strong>Duration:</strong> ${expiryDays} days</li>
    `;
}

function getTargetCustomerText(targetCustomers) {
    const texts = {
        'all': 'All Customers',
        'new': 'New Customers Only',
        'returning': 'Returning Customers Only',
        'vip': 'VIP Customers Only',
        'inactive': 'Inactive Customers'
    };
    return texts[targetCustomers] || 'All Customers';
}

function activateCampaignWithSettings() {
    const form = document.getElementById('campaign-settings-form');
    const formData = new FormData(form);
    
    const settings = {
        coupon_code: formData.get('coupon_code'),
        discount_type: formData.get('discount_type'),
        discount_amount: parseInt(formData.get('discount_amount')),
        usage_limit: parseInt(formData.get('usage_limit')),
        expiry_days: parseInt(formData.get('expiry_days')),
        minimum_amount: parseInt(formData.get('minimum_amount')),
        target_customers: formData.get('target_customers'),
        product_categories: formData.getAll('product_categories'),
        send_email: formData.get('send_email') === 'on',
        email_delay: parseInt(formData.get('email_delay'))
    };
    
    // Merge campaign data with settings
    const campaignData = {
        ...currentCampaignData,
        settings: settings
    };
    
    // Close modal
    closeCampaignSettings();
    
    // Activate campaign with custom settings
    activateRealCampaignWithCustomSettings(currentCampaignId, campaignData);
}

function activateRealCampaignWithCustomSettings(campaignId, campaignData) {
    const btn = event.target;
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = 'Activating...';
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_activate_campaign',
            campaign_id: campaignId,
            campaign_data: JSON.stringify(campaignData),
            nonce: '<?php echo wp_create_nonce("arteasy_activate_campaign"); ?>'
        },
        success: function(response) {
            if (response.success) {
                let detailsHtml = '';
                if (response.data.results) {
                    detailsHtml = '<div class="campaign-results"><h4>Campaign Results:</h4><ul>';
                    Object.values(response.data.results).forEach(result => {
                        if (result.code) {
                            detailsHtml += `<li><strong>Coupon Created:</strong> ${result.code} - ${result.discount}</li>`;
                        }
                    });
                    detailsHtml += '</ul></div>';
                }
                showEnhancedSuccess('✅ Campaign activated successfully with your custom settings!', detailsHtml);
            } else {
                showNotice('❌ Failed to activate campaign: ' + response.data, 'error');
            }
        },
        error: function() {
            showNotice('❌ Error activating campaign', 'error');
        },
        complete: function() {
            btn.disabled = false;
            btn.textContent = originalText;
            // Update dashboard after successful activation
            updateDashboardStatus();
        }
    });
}

// Real-time Dashboard Update Functions
function updateDashboardStatus() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_get_activation_status',
            nonce: '<?php echo wp_create_nonce("arteasy_get_activation_status"); ?>'
        },
        success: function(response) {
            if (response.success) {
                updateStatusCards(response.data);
                updateRecentActivity(response.data);
            }
        },
        error: function() {
            console.log('Failed to update dashboard status');
        }
    });
}

function updateStatusCards(statusData) {
    // Update campaigns status
    const campaignsCard = document.getElementById('campaigns-status');
    const campaignsCount = document.querySelector('#campaigns-status .status-count');
    if (campaignsCard && campaignsCount) {
        campaignsCount.textContent = `${statusData.active_campaigns} Active`;
        campaignsCard.className = statusData.active_campaigns > 0 ? 'status-card active' : 'status-card inactive';
    }
    
    // Update bundles status
    const bundlesCard = document.getElementById('bundles-status');
    const bundlesCount = document.querySelector('#bundles-status .status-count');
    if (bundlesCard && bundlesCount) {
        bundlesCount.textContent = `${statusData.created_bundles} Created`;
        bundlesCard.className = statusData.created_bundles > 0 ? 'status-card active' : 'status-card inactive';
    }
    
    // Update cart recovery status
    const cartRecoveryCard = document.getElementById('cart-recovery-status');
    const cartRecoveryCount = document.querySelector('#cart-recovery-status .status-count');
    if (cartRecoveryCard && cartRecoveryCount) {
        cartRecoveryCount.textContent = statusData.cart_recovery_active ? 'Active' : 'Inactive';
        cartRecoveryCard.className = statusData.cart_recovery_active ? 'status-card active' : 'status-card inactive';
    }
}

function updateRecentActivity(statusData) {
    const activityList = document.getElementById('recent-activity-list');
    if (!activityList) return;
    
    let activityHtml = '';
    
    // Add campaign activities
    if (statusData.campaigns) {
        Object.entries(statusData.campaigns).forEach(([id, campaign]) => {
            const timeAgo = getTimeAgo(campaign.activated_at);
            activityHtml += `
                <div class="activity-item">
                    <div class="activity-icon">📧</div>
                    <div class="activity-text">Campaign "${campaign.campaign_name}" activated</div>
                    <div class="activity-time">${timeAgo}</div>
                </div>
            `;
        });
    }
    
    // Add bundle activities
    if (statusData.bundles) {
        Object.entries(statusData.bundles).forEach(([id, bundle]) => {
            const timeAgo = getTimeAgo(bundle.created_at);
            activityHtml += `
                <div class="activity-item">
                    <div class="activity-icon">📦</div>
                    <div class="activity-text">Bundle created (Product ID: ${bundle.product_id})</div>
                    <div class="activity-time">${timeAgo}</div>
                </div>
            `;
        });
    }
    
    // If no activities, show default message
    if (!activityHtml) {
        activityHtml = `
            <div class="activity-item">
                <div class="activity-icon">ℹ️</div>
                <div class="activity-text">No recent activity</div>
                <div class="activity-time">-</div>
            </div>
        `;
    }
    
    activityList.innerHTML = activityHtml;
}

function getTimeAgo(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diffInSeconds = Math.floor((now - time) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
}

// Enhanced success feedback
function showEnhancedSuccess(message, details = null) {
    const noticeClass = 'notice-success';
    let noticeHtml = `<div class="notice ${noticeClass} is-dismissible"><p>${message}</p>`;
    
    if (details) {
        noticeHtml += `<div class="notice-details" style="margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.5); border-radius: 4px;">${details}</div>`;
    }
    
    noticeHtml += '</div>';
    
    const notice = jQuery(noticeHtml);
    jQuery('.wrap').prepend(notice);
    
    // Auto-dismiss after 8 seconds
    setTimeout(function() {
        notice.fadeOut();
    }, 8000);
    
    // Update dashboard after showing success
    setTimeout(updateDashboardStatus, 1000);
}

// Add event listeners for real-time preview updates
document.addEventListener('DOMContentLoaded', function() {
    const previewFields = ['coupon-code', 'discount-type', 'discount-amount', 'expiry-days', 'usage-limit', 'minimum-amount', 'target-customers'];
    
    previewFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updateCampaignPreview);
            field.addEventListener('change', updateCampaignPreview);
        }
    });
    
    // Load initial dashboard status
    updateDashboardStatus();
    
    // Update dashboard every 30 seconds
    setInterval(updateDashboardStatus, 30000);
});

function showNotice(message, type) {
    const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
    const notice = jQuery('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
    jQuery('.wrap').prepend(notice);
    
    setTimeout(function() {
        notice.fadeOut();
    }, 5000);
}
</script>
