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
$activation_status = method_exists($woo_data, 'get_activation_status') ? $woo_data->get_activation_status() : array();
$active_campaigns = isset($activation_status['campaigns']) ? $activation_status['campaigns'] : array();

// Check if we have real data or need to show demo data
$has_real_data = $woo_data->is_woocommerce_active() && $smart_campaigns !== false;
?>

<div class="wrap arteasy-marketing-automation">
    <h1>🎯 Marketing Tools</h1>

    <div style="background: #f8fafc; padding: 16px 20px; border-radius: 10px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
        <h2 style="margin-top: 0; margin-bottom: 8px;">How Smart Campaigning Works</h2>
        <ol style="margin: 0 0 10px 18px;">
            <li>Pick a campaign card and click <strong>Configure & Activate</strong>.</li>
            <li>We enforce safe defaults: <strong>5% discount</strong> (max 15%), <strong>₦5,000 min order</strong>, <strong>single-use</strong>, <strong>no stacking</strong>.</li>
            <li>Refine targeting: customer type, exclude recent buyers, include/exclude categories, schedule start/end.</li>
            <li>Click <strong>Simulate Audience</strong> to see how many customers match, then <strong>Activate</strong>.</li>
            <li>We create governed coupons and (optionally) send announcement emails.</li>
        </ol>
        <a href="<?php echo admin_url('admin.php?page=arteasy-cart'); ?>" class="button">Open Cart Recovery Dashboard →</a>
    </div>

    <div class="marketing-sections">
        <div class="marketing-section">
            <h2>🎯 Smart Campaign Generator</h2>
            <div class="campaigns-container">
                <?php if ($has_real_data && $smart_campaigns): ?>
                    <?php foreach ($smart_campaigns as $campaign_key => $campaign): 
                        $is_active = isset($active_campaigns[$campaign_key]) && isset($active_campaigns[$campaign_key]['status']) && $active_campaigns[$campaign_key]['status'] === 'active';
                        $campaign_status = $is_active ? 'Active' : 'Inactive';
                        $status_class = $is_active ? 'status-active' : 'status-inactive';
                    ?>
                    <div class="campaign-card <?php echo $status_class; ?>">
                        <div class="campaign-header">
                            <h3><?php echo esc_html($campaign['name']); ?></h3>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="campaign-type"><?php echo esc_html($campaign['type']); ?></span>
                                <span class="campaign-status-badge" style="background: <?php echo $is_active ? '#10b981' : '#6b7280'; ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    <?php echo $campaign_status; ?>
                                </span>
                            </div>
                        </div>
                        <div class="campaign-content">
                            <p class="campaign-description"><?php echo esc_html($campaign['description']); ?></p>
                            <div class="campaign-details">
                                <div class="detail-item">
                                    <strong>Target:</strong> 
                                    <?php 
                                    if ($is_active && isset($active_campaigns[$campaign_key]['target_customers'])) {
                                        $target_map = array(
                                            'all' => 'All Customers',
                                            'new' => 'New Customers Only',
                                            'returning' => 'Returning Customers Only',
                                            'vip' => 'VIP Customers Only',
                                            'inactive' => 'Inactive Customers'
                                        );
                                        $target = $active_campaigns[$campaign_key]['target_customers'];
                                        echo esc_html($target_map[$target] ?? ucfirst($target));
                                    } else {
                                        echo esc_html($campaign['target_audience']);
                                    }
                                    ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Duration:</strong> 
                                    <?php 
                                    if ($is_active && isset($active_campaigns[$campaign_key]['expiry_days'])) {
                                        $days = intval($active_campaigns[$campaign_key]['expiry_days']);
                                        echo esc_html($days . ' day' . ($days != 1 ? 's' : ''));
                                    } else {
                                        echo esc_html($campaign['duration']);
                                    }
                                    ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Impact:</strong> <?php echo esc_html($campaign['expected_impact']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Status:</strong> <span style="color: <?php echo $is_active ? '#10b981' : '#6b7280'; ?>;"><?php echo $campaign_status; ?></span>
                                </div>
                            </div>
                            <?php if ($is_active && isset($active_campaigns[$campaign_key]['coupons_created'])): ?>
                            <div style="background: #f0fdf4; padding: 10px; border-radius: 6px; margin: 10px 0; border-left: 3px solid #10b981;">
                                <strong style="color: #166534;">Active Campaign Details:</strong><br>
                                <small style="color: #166534;">
                                    Discount: <?php echo esc_html($active_campaigns[$campaign_key]['discount_amount'] ?? 0); ?><?php echo ($active_campaigns[$campaign_key]['discount_type'] ?? 'percent') === 'percent' ? '%' : '₦'; ?> off<br>
                                    Coupons Created: <?php echo intval($active_campaigns[$campaign_key]['coupons_created']); ?><br>
                                    Expires: <?php 
                                    if (isset($active_campaigns[$campaign_key]['expiry_days']) && isset($active_campaigns[$campaign_key]['activated_at'])) {
                                        $expiry = date('M j, Y', strtotime($active_campaigns[$campaign_key]['activated_at'] . ' +' . intval($active_campaigns[$campaign_key]['expiry_days']) . ' days'));
                                        echo esc_html($expiry);
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?><br>
                                    Activated: <?php echo isset($active_campaigns[$campaign_key]['activated_at']) ? date('M j, Y g:i A', strtotime($active_campaigns[$campaign_key]['activated_at'])) : 'Unknown'; ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            <div class="campaign-strategy">
                                <h4>Strategy:</h4>
                                <ul>
                                    <?php foreach ($campaign['strategy'] as $strategy_key => $strategy_value): ?>
                                    <li><strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $strategy_key))); ?>:</strong> <?php echo esc_html($strategy_value); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="campaign-actions">
                                <?php if ($is_active): ?>
                                    <button class="button button-secondary" onclick="deactivateCampaign('<?php echo esc_js($campaign_key); ?>')" style="background: #ef4444; color: white; border-color: #ef4444;">
                                        ⛔ Deactivate Campaign
                                    </button>
                                    <button class="button" onclick="openCampaignSettings('<?php echo esc_js($campaign_key); ?>', <?php echo esc_js(json_encode($campaign)); ?>, <?php echo esc_js(json_encode($active_campaigns[$campaign_key] ?? null)); ?>)">Edit Settings</button>
                                <?php else: ?>
                                    <button class="button button-primary" onclick="openCampaignSettings('<?php echo esc_js($campaign_key); ?>', <?php echo esc_js(json_encode($campaign)); ?>, null)">Configure & Activate</button>
                                <?php endif; ?>
                                <button class="button" onclick="viewCampaignDetails('<?php echo esc_js($campaign_key); ?>', <?php echo esc_js(json_encode($campaign)); ?>, <?php echo esc_js(json_encode($active_campaigns[$campaign_key] ?? null)); ?>)">View Details</button>
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

        <!-- Bundles and extra overviews removed for a cleaner Smart Campaigns focus -->
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
                            <input type="number" id="discount-amount" name="discount_amount" min="1" max="15" value="5" required>
                            <small id="discount-help">Percentage (%) - Default: 5%, Max: 15% (Governed)</small>
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
                            <input type="number" id="minimum-amount" name="minimum_amount" min="5000" value="5000" required>
                            <small>Minimum cart value (₦) - Default: ₦5,000</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="individual-use" name="individual_use" checked disabled>
                                <strong>Individual Use Only (No Stacking)</strong> - Always enabled
                            </label>
                            <small>This coupon cannot be combined with other coupons</small>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="single-use-per-customer" name="usage_limit_per_user" checked disabled>
                                <strong>Single-Use Per Customer</strong> - Always enabled
                            </label>
                            <small>Each customer can only use this coupon once</small>
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
                            <label for="exclude-recent-days">Exclude Recent Purchasers (Days):</label>
                            <input type="number" id="exclude-recent-days" name="exclude_recent_days" min="0" max="365" value="0">
                            <small>Exclude customers who purchased in the last X days (0 = no exclusion)</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product-categories">Product Categories (Include):</label>
                            <select id="product-categories" name="product_categories[]" multiple>
                                <option value="all">All Categories</option>
                                <!-- Categories will be loaded dynamically -->
                            </select>
                            <small>Hold Ctrl/Cmd to select multiple. Leave empty for all categories.</small>
                        </div>
                        <div class="form-group">
                            <label for="exclude-categories">Exclude Categories:</label>
                            <select id="exclude-categories" name="exclude_categories[]" multiple>
                                <!-- Categories will be loaded dynamically -->
                            </select>
                            <small>Exclude these categories from coupon eligibility</small>
                        </div>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h3>Schedule Settings</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="campaign-start-date">Start Date:</label>
                            <input type="datetime-local" id="campaign-start-date" name="campaign_start_date">
                            <small>When campaign becomes active (leave empty for immediate start)</small>
                        </div>
                        <div class="form-group">
                            <label for="campaign-end-date">End Date:</label>
                            <input type="datetime-local" id="campaign-end-date" name="campaign_end_date">
                            <small>When campaign expires (auto-disables coupon)</small>
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
                    <div id="launch-plan-preview" style="margin-top: 16px; background:#f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                        <h4 style="margin:0 0 10px 0; color:#1f2937;">Launch Plan</h4>
                        <div id="launch-plan-content" style="font-size:13px; color:#374151;">
                            <em>Click "Preview Launch Plan" to see audience, dates, and safeguards.</em>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="button" onclick="simulateCampaignAudience()">Simulate Audience</button>
            <button type="button" class="button" onclick="previewLaunchPlan()">Preview Launch Plan</button>
            <button type="button" class="button" onclick="exportCampaignAudienceCsv()">Export Audience CSV</button>
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

.campaign-card.status-active {
    border-left: 4px solid #10b981;
}

.campaign-card.status-inactive {
    border-left: 4px solid #e5e7eb;
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
// Define showNotice globally FIRST (before any functions that use it)
window.showNotice = function(message, type) {
    const noticeClass = type === 'success' || type === 'info' ? 'notice-success' : 'notice-error';
    const notice = jQuery('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
    jQuery('.wrap').prepend(notice);
    
    setTimeout(function() {
        notice.fadeOut(function() {
            notice.remove();
        });
    }, 5000);
};

// Also define as a function for compatibility
function showNotice(message, type) {
    window.showNotice(message, type);
}

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

function viewCampaignDetails(campaignId, campaignData, activeData) {
    let html = '<div style="max-width: 600px; text-align: left;"><h3>' + (campaignData.name || campaignId) + '</h3>';
    html += '<p><strong>Description:</strong> ' + (campaignData.description || 'N/A') + '</p>';
    html += '<p><strong>Target Audience:</strong> ' + (campaignData.target_audience || 'N/A') + '</p>';
    html += '<p><strong>Duration:</strong> ' + (campaignData.duration || 'N/A') + '</p>';
    html += '<p><strong>Expected Impact:</strong> ' + (campaignData.expected_impact || 'N/A') + '</p>';
    
    if (activeData && activeData.status === 'active') {
        html += '<hr><h4>Active Campaign Settings:</h4>';
        html += '<p><strong>Discount:</strong> ' + (activeData.discount_amount || 0) + (activeData.discount_type === 'percent' ? '%' : '₦') + ' off</p>';
        html += '<p><strong>Target:</strong> ' + (activeData.target_customers || 'all') + '</p>';
        html += '<p><strong>Expiry:</strong> ' + (activeData.expiry_days || 0) + ' days</p>';
        html += '<p><strong>Coupons Created:</strong> ' + (activeData.coupons_created || 0) + '</p>';
        html += '<p><strong>Activated:</strong> ' + (activeData.activated_at || 'Unknown') + '</p>';
    } else {
        html += '<hr><p style="color: #6b7280;"><em>Campaign is not active. Click "Configure & Activate" to start.</em></p>';
    }
    
    html += '<hr><h4>Strategy:</h4><ul>';
    if (campaignData.strategy) {
        Object.entries(campaignData.strategy).forEach(([key, value]) => {
            html += '<li><strong>' + key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + ':</strong> ' + value + '</li>';
        });
    }
    html += '</ul></div>';
    
    // Create modal-style popup
    const modal = document.createElement('div');
    modal.style.cssText = 'position:fixed; z-index:100001; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center;';
    modal.innerHTML = '<div style="background:white; padding:30px; border-radius:10px; max-width:700px; max-height:90vh; overflow-y:auto; position:relative;">' +
        '<span onclick="this.parentElement.parentElement.remove()" style="position:absolute; top:10px; right:15px; font-size:28px; cursor:pointer; color:#666;">&times;</span>' +
        html + '</div>';
    document.body.appendChild(modal);
    
    // Close on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.remove();
    });
}

function deactivateCampaign(campaignId) {
    if (!confirm('Are you sure you want to deactivate this campaign? All associated coupons will be disabled.')) {
        return;
    }
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_deactivate_campaign',
            campaign_id: campaignId,
            nonce: '<?php echo wp_create_nonce("arteasy_activate_campaign"); ?>'
        },
        success: function(response) {
            if (response.success) {
                showEnhancedSuccess('✅ Campaign deactivated successfully! All coupons have been disabled.');
                updateDashboardStatus();
                // Reload page after 2 seconds to show updated status
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            } else {
                showNotice('❌ Failed to deactivate: ' + (response.data || 'Unknown error'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Deactivate error:', error, xhr.responseText);
            showNotice('❌ Error deactivating campaign: ' + error, 'error');
        }
    });
}

function previewBundle(bundleId) {
    showNotice('Bundle preview for: ' + bundleId, 'info');
}

// Campaign Settings Modal Functions
let currentCampaignId = '';
let currentCampaignData = {};

function openCampaignSettings(campaignId, campaignData, activeData) {
    currentCampaignId = campaignId;
    currentCampaignData = campaignData;
    
    // If campaign is active, use current settings; otherwise use defaults
    let settings = {};
    if (activeData && activeData.status === 'active') {
        // Extract coupon code from results if available
        let couponCode = '';
        if (activeData.results) {
            for (let key in activeData.results) {
                if (activeData.results[key].code) {
                    couponCode = activeData.results[key].code;
                    // Extract prefix (before last underscore + date)
                    const parts = couponCode.split('_');
                    if (parts.length > 1) {
                        parts.pop(); // Remove date
                        couponCode = parts.join('_');
                    }
                    break;
                }
            }
        }
        
        // Load from active campaign settings
        settings = {
            coupon_code: couponCode || activeData.coupon_code || getCampaignDefaults(campaignData.type).coupon_code,
            discount_type: activeData.discount_type || 'percent',
            discount_amount: activeData.discount_amount || 5,
            usage_limit: activeData.usage_limit || 1000,
            expiry_days: activeData.expiry_days || 7,
            minimum_amount: activeData.minimum_amount || 5000,
            target_customers: activeData.target_customers || 'all',
            exclude_recent_days: activeData.exclude_recent_days || 0,
            send_email: activeData.email_sent || false,
            email_delay: activeData.email_delay || 2,
            product_categories: activeData.product_categories || [],
            exclude_categories: activeData.exclude_categories || [],
            campaign_start_date: activeData.start_date || '',
            campaign_end_date: activeData.end_date || ''
        };
    } else {
        // Use defaults based on campaign type
        const defaults = getCampaignDefaults(campaignData.type);
        settings = {
            coupon_code: defaults.coupon_code,
            discount_type: defaults.discount_type,
            discount_amount: defaults.discount_amount || 5,
            usage_limit: defaults.usage_limit || 1000,
            expiry_days: defaults.expiry_days || 7,
            minimum_amount: defaults.minimum_amount || 5000,
            target_customers: defaults.target_customers || 'all',
            exclude_recent_days: defaults.exclude_recent_days || 0,
            send_email: defaults.send_email !== false,
            email_delay: defaults.email_delay || 2,
            product_categories: [],
            exclude_categories: [],
            campaign_start_date: '',
            campaign_end_date: ''
        };
    }
    
    // Populate form fields
    document.getElementById('coupon-code').value = settings.coupon_code || '';
    document.getElementById('discount-type').value = settings.discount_type || 'percent';
    document.getElementById('discount-amount').value = settings.discount_amount || 5;
    document.getElementById('usage-limit').value = settings.usage_limit || 1000;
    document.getElementById('expiry-days').value = settings.expiry_days || 7;
    document.getElementById('minimum-amount').value = settings.minimum_amount || 5000;
    document.getElementById('target-customers').value = settings.target_customers || 'all';
    document.getElementById('exclude-recent-days').value = settings.exclude_recent_days || 0;
    document.getElementById('send-email').checked = settings.send_email || false;
    document.getElementById('email-delay').value = settings.email_delay || 2;
    
    // Handle dates
    if (settings.campaign_start_date) {
        const startDate = new Date(settings.campaign_start_date);
        document.getElementById('campaign-start-date').value = startDate.toISOString().slice(0, 16);
    } else {
        document.getElementById('campaign-start-date').value = '';
    }
    
    if (settings.campaign_end_date) {
        const endDate = new Date(settings.campaign_end_date);
        document.getElementById('campaign-end-date').value = endDate.toISOString().slice(0, 16);
    } else {
        document.getElementById('campaign-end-date').value = '';
    }
    
    // Load categories dynamically
    loadCampaignCategories();
    
    // Update preview
    updateCampaignPreview();
    
    // Show modal
    document.getElementById('campaign-settings-modal').style.display = 'flex';
}

function loadCampaignCategories() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'GET',
        data: {
            action: 'arteasy_get_campaign_categories',
            nonce: '<?php echo wp_create_nonce("arteasy_activate_campaign"); ?>'
        },
        success: function(response) {
            if (response.success && response.data) {
                const includeSelect = document.getElementById('product-categories');
                const excludeSelect = document.getElementById('exclude-categories');
                
                // Clear existing options (except "All")
                while (includeSelect.options.length > 1) includeSelect.remove(1);
                while (excludeSelect.options.length > 0) excludeSelect.remove(0);
                
                // Populate include categories
                response.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    includeSelect.appendChild(option);
                });
                
                // Populate exclude categories (copy)
                response.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    excludeSelect.appendChild(option);
                });
            }
        },
        error: function() {
            console.error('Failed to load categories');
        }
    });
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
    console.log('🔵 [DEBUG] activateCampaignWithSettings called');
    
    try {
        const form = document.getElementById('campaign-settings-form');
        if (!form) {
            console.error('❌ [DEBUG] Form not found!');
            showNotice('❌ Form not found. Please refresh the page.', 'error');
            return;
        }
        console.log('✅ [DEBUG] Form found');
        
        const formData = new FormData(form);
        console.log('✅ [DEBUG] FormData created');
        
        const settings = {
            coupon_code: formData.get('coupon_code'),
            discount_type: formData.get('discount_type'),
            discount_amount: parseInt(formData.get('discount_amount')) || 5,
            usage_limit: parseInt(formData.get('usage_limit')) || 1000,
            expiry_days: parseInt(formData.get('expiry_days')) || 7,
            minimum_amount: parseInt(formData.get('minimum_amount')) || 5000,
            target_customers: formData.get('target_customers') || 'all',
            exclude_recent_days: parseInt(formData.get('exclude_recent_days') || '0') || 0,
            product_categories: formData.getAll('product_categories[]'),
            exclude_categories: formData.getAll('exclude_categories[]'),
            campaign_start_date: formData.get('campaign_start_date') || '',
            campaign_end_date: formData.get('campaign_end_date') || '',
            send_email: formData.get('send_email') === 'on',
            email_delay: parseInt(formData.get('email_delay')) || 2,
            individual_use: true,
            usage_limit_per_user: 1
        };
        
        console.log('✅ [DEBUG] Settings object:', settings);
        console.log('✅ [DEBUG] currentCampaignId:', currentCampaignId);
        console.log('✅ [DEBUG] currentCampaignData:', currentCampaignData);
        
        if (!currentCampaignId) {
            console.error('❌ [DEBUG] currentCampaignId is empty!');
            showNotice('❌ Campaign ID missing. Please close and reopen the settings.', 'error');
            return;
        }
        
        if (!currentCampaignData || !currentCampaignData.type) {
            console.error('❌ [DEBUG] currentCampaignData invalid:', currentCampaignData);
            showNotice('❌ Campaign data missing. Please close and reopen the settings.', 'error');
            return;
        }
        
        // Merge campaign data with settings
        const campaignData = {
            ...currentCampaignData,
            settings: settings
        };
        
        console.log('✅ [DEBUG] Final campaignData:', campaignData);
        
        // Close modal
        closeCampaignSettings();
        console.log('✅ [DEBUG] Modal closed');
        
        // Activate campaign with custom settings
        console.log('🔵 [DEBUG] Calling activateRealCampaignWithCustomSettings...');
        activateRealCampaignWithCustomSettings(currentCampaignId, campaignData);
        
    } catch (error) {
        console.error('❌ [DEBUG] Error in activateCampaignWithSettings:', error);
        showNotice('❌ Error: ' + error.message, 'error');
    }
}

function activateRealCampaignWithCustomSettings(campaignId, campaignData) {
    console.log('🔵 [DEBUG] activateRealCampaignWithCustomSettings called');
    console.log('🔵 [DEBUG] campaignId:', campaignId);
    console.log('🔵 [DEBUG] campaignData:', campaignData);
    
    if (!campaignId) {
        console.error('❌ [DEBUG] campaignId is missing!');
        showNotice('❌ Campaign ID is missing. Please refresh the page.', 'error');
        return;
    }
    
    if (!campaignData) {
        console.error('❌ [DEBUG] campaignData is missing!');
        showNotice('❌ Campaign data is missing. Please refresh the page.', 'error');
        return;
    }
    
    // Find the activate button (could be from modal or campaign card)
    let btn = null;
    let originalText = 'Activate Campaign';
    
    // Try to find button in modal footer
    const modalFooter = document.querySelector('.modal-footer');
    console.log('🔵 [DEBUG] Looking for modal footer:', modalFooter);
    if (modalFooter) {
        const activateBtn = modalFooter.querySelector('button.button-primary');
        console.log('🔵 [DEBUG] Modal button found:', activateBtn);
        if (activateBtn) {
            btn = activateBtn;
            originalText = btn.textContent;
        }
    }
    
    // If not found, try to find from campaign card
    if (!btn) {
        console.log('🔵 [DEBUG] Button not found in modal, checking campaign cards...');
        const campaignCards = document.querySelectorAll('.campaign-card');
        campaignCards.forEach(card => {
            const cardBtn = card.querySelector('button[onclick*="openCampaignSettings"]');
            if (cardBtn) {
                btn = cardBtn;
                originalText = btn.textContent;
            }
        });
    }
    
    console.log('🔵 [DEBUG] Button found:', btn);
    
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Activating...';
        console.log('✅ [DEBUG] Button disabled and text updated');
    }
    
    // Show loading notice
    showNotice('⏳ Activating campaign...', 'info');
    console.log('✅ [DEBUG] Loading notice shown');
    
    // Prepare AJAX data
    const ajaxData = {
        action: 'arteasy_activate_campaign',
        campaign_id: campaignId,
        campaign_data: JSON.stringify(campaignData),
        nonce: '<?php echo wp_create_nonce("arteasy_activate_campaign"); ?>'
    };
    
    console.log('🔵 [DEBUG] AJAX data prepared:', ajaxData);
    console.log('🔵 [DEBUG] ajaxurl:', typeof ajaxurl !== 'undefined' ? ajaxurl : 'UNDEFINED!');
    
    if (typeof ajaxurl === 'undefined') {
        console.error('❌ [DEBUG] ajaxurl is undefined! WordPress AJAX not available.');
        showNotice('❌ WordPress AJAX not available. Please refresh the page.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText;
        }
        return;
    }
    
    if (typeof jQuery === 'undefined') {
        console.error('❌ [DEBUG] jQuery is undefined!');
        showNotice('❌ jQuery not loaded. Please refresh the page.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText;
        }
        return;
    }
    
    console.log('🔵 [DEBUG] Sending AJAX request...');
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: ajaxData,
        beforeSend: function() {
            console.log('✅ [DEBUG] AJAX beforeSend - Request sent');
        },
        success: function(response) {
            console.log('✅ [DEBUG] AJAX success - Response received:', response);
            
            if (response.success) {
                console.log('✅ [DEBUG] Campaign activation successful!');
                let detailsHtml = '';
                if (response.data && response.data.results) {
                    console.log('✅ [DEBUG] Results found:', response.data.results);
                    detailsHtml = '<div class="campaign-results"><h4>Campaign Results:</h4><ul>';
                    Object.values(response.data.results).forEach(result => {
                        if (result.code) {
                            detailsHtml += `<li><strong>Coupon Created:</strong> ${result.code} - ${result.discount || 'N/A'}</li>`;
                        } else if (result.coupon_created && result.coupon_created.code) {
                            detailsHtml += `<li><strong>Coupon Created:</strong> ${result.coupon_created.code} - ${result.coupon_created.discount || 'N/A'}</li>`;
                        }
                    });
                    detailsHtml += '</ul></div>';
                }
                showEnhancedSuccess('✅ Campaign activated successfully with your custom settings!', detailsHtml);
                
                console.log('✅ [DEBUG] Success message shown, reloading in 2 seconds...');
                // Reload page after 2 seconds to show updated status
                setTimeout(function() {
                    console.log('✅ [DEBUG] Reloading page now...');
                    window.location.reload();
                }, 2000);
            } else {
                console.error('❌ [DEBUG] Campaign activation failed:', response);
                showNotice('❌ Failed to activate campaign: ' + (response.data || 'Unknown error'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ [DEBUG] AJAX error occurred!');
            console.error('❌ [DEBUG] Status:', status);
            console.error('❌ [DEBUG] Error:', error);
            console.error('❌ [DEBUG] XHR:', xhr);
            console.error('❌ [DEBUG] Response Text:', xhr.responseText);
            console.error('❌ [DEBUG] Status Code:', xhr.status);
            
            let errorMsg = 'Error activating campaign: ' + error;
            if (xhr.responseText) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.data) {
                        errorMsg += ' - ' + errorResponse.data;
                    }
                } catch (e) {
                    errorMsg += ' - ' + xhr.responseText.substring(0, 200);
                }
            }
            
            showNotice('❌ ' + errorMsg, 'error');
        },
        complete: function() {
            console.log('✅ [DEBUG] AJAX complete - Request finished');
            if (btn) {
                btn.disabled = false;
                btn.textContent = originalText;
                console.log('✅ [DEBUG] Button re-enabled');
            }
            // Update dashboard after activation attempt
            updateDashboardStatus();
        }
    });
}

function simulateCampaignAudience() {
    const form = document.getElementById('campaign-settings-form');
    if (!form) {
        showNotice('❌ Form not found', 'error');
        return;
    }
    const formData = new FormData(form);
    const excludeRecentEl = document.getElementById('exclude-recent-days');
    const settings = {
        target_customers: formData.get('target_customers') || 'all',
        exclude_recent_days: excludeRecentEl ? parseInt(excludeRecentEl.value || '0') : 0,
        product_categories: formData.getAll('product_categories[]'),
        exclude_categories: formData.getAll('exclude_categories[]')
    };
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_simulate_campaign',
            settings: JSON.stringify(settings),
            nonce: '<?php echo wp_create_nonce("arteasy_activate_campaign"); ?>'
        },
        success: function(response) {
            if (response.success && response.data) {
                const data = response.data;
                const sampleHtml = data.sample && data.sample.length > 0 
                    ? `<div style="max-height:200px; overflow-y:auto; margin-top:10px;"><strong>Sample emails:</strong><br>${data.sample.join('<br>')}</div>` 
                    : '<div style="margin-top:10px;">No sample emails available</div>';
                showEnhancedSuccess(`✅ Audience simulation complete: <strong>${data.audience_count || 0} customers</strong>`, sampleHtml);
            } else {
                showNotice('❌ Simulation failed: ' + (response.data || 'Unknown error'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Simulate error:', error, xhr.responseText);
            showNotice('❌ Simulation error: ' + error, 'error');
        }
    });
}

function exportCampaignAudienceCsv() {
    const form = document.getElementById('campaign-settings-form');
    if (!form) {
        showNotice('❌ Form not found', 'error');
        return;
    }
    const formData = new FormData(form);
    const excludeRecentEl = document.getElementById('exclude-recent-days');
    const settings = {
        target_customers: formData.get('target_customers') || 'all',
        exclude_recent_days: excludeRecentEl ? parseInt(excludeRecentEl.value || '0') : 0,
        product_categories: formData.getAll('product_categories[]'),
        exclude_categories: formData.getAll('exclude_categories[]')
    };
    
    showNotice('⏳ Building audience list...', 'info');
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_export_campaign_audience',
            settings: JSON.stringify(settings),
            nonce: '<?php echo wp_create_nonce("arteasy_activate_campaign"); ?>'
        },
        success: function(response) {
            if (response.success && response.data && response.data.content_base64) {
                try {
                    const csv = atob(response.data.content_base64);
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const a = document.createElement('a');
                    const url = URL.createObjectURL(blob);
                    a.href = url;
                    a.download = response.data.filename || 'campaign_audience.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    showNotice(`✅ CSV downloaded successfully! (${response.data.count || 0} emails)`, 'success');
                } catch (e) {
                    console.error('Download error:', e);
                    showNotice('❌ Failed to create download: ' + e.message, 'error');
                }
            } else {
                showNotice('❌ Export failed: ' + (response.data || 'Unknown error'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Export error:', error, xhr.responseText);
            showNotice('❌ Export error: ' + error, 'error');
        }
    });
}

function previewLaunchPlan() {
    const form = document.getElementById('campaign-settings-form');
    if (!form) return;
    const formData = new FormData(form);

    const couponCode = formData.get('coupon_code') || 'COUPON';
    const discountType = formData.get('discount_type') || 'percent';
    const discountAmount = parseInt(formData.get('discount_amount') || '5');
    const expiryDays = parseInt(formData.get('expiry_days') || '7');
    const minAmount = parseInt(formData.get('minimum_amount') || '5000');
    const startDate = formData.get('campaign_start_date') || 'Immediate';
    const endDate = formData.get('campaign_end_date') || `+${expiryDays} days`;
    const targetCustomers = formData.get('target_customers') || 'all';
    const excludeRecent = parseInt(formData.get('exclude_recent_days') || '0');
    const includeCats = formData.getAll('product_categories[]');
    const excludeCats = formData.getAll('exclude_categories[]');

    const settings = {
        target_customers: targetCustomers,
        exclude_recent_days: excludeRecent,
        product_categories: includeCats,
        exclude_categories: excludeCats
    };

    // First, simulate to get audience count then render the plan
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'arteasy_simulate_campaign',
            settings: JSON.stringify(settings),
            nonce: '<?php echo wp_create_nonce("arteasy_activate_campaign"); ?>'
        },
        success: function(response) {
            const data = (response && response.success && response.data) ? response.data : { audience_count: 0 };
            const today = new Date();
            const dateStr = today.getFullYear() + String(today.getMonth()+1).padStart(2,'0') + String(today.getDate()).padStart(2,'0');
            const fullCoupon = `${couponCode}_${dateStr}`;
            const discountText = discountType === 'percent' ? `${discountAmount}%` : `₦${discountAmount}`;

            const safeguards = [
                'Single-use per customer',
                'No stacking (individual use only)',
                `Minimum order ₦${minAmount.toLocaleString()}`,
                'Max 15% discount enforced'
            ];

            const catsText = includeCats && includeCats.length > 0 && !includeCats.includes('all')
                ? `Included categories: ${includeCats.join(', ')}`
                : 'All categories included';
            const excludeText = excludeCats && excludeCats.length > 0
                ? `Excluded categories: ${excludeCats.join(', ')}`
                : 'No category exclusions';

            const planHtml = `
                <ul style="margin:0; padding-left:18px;">
                    <li><strong>Audience:</strong> ${parseInt(data.audience_count || 0).toLocaleString()} customers (${getTargetCustomerText(targetCustomers)})</li>
                    <li><strong>Coupon:</strong> ${fullCoupon} — ${discountText} off, duration ${expiryDays} days</li>
                    <li><strong>Schedule:</strong> Start: ${startDate || 'Immediate'} • End: ${endDate}</li>
                    <li><strong>Eligibility:</strong> ${catsText}; ${excludeText}; Exclude recent: ${excludeRecent} days</li>
                    <li><strong>Safeguards:</strong>
                        <ul style="margin-top:6px; padding-left:18px;">
                            ${safeguards.map(s => `<li>${s}</li>`).join('')}
                        </ul>
                    </li>
                </ul>
            `;
            document.getElementById('launch-plan-content').innerHTML = planHtml;
        },
        error: function() {
            document.getElementById('launch-plan-content').innerHTML = '<em>Could not simulate audience. Please try again.</em>';
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

</script>
