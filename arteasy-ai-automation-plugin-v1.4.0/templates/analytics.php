<?php
/**
 * Analytics Template - Real Data Version
 */

if (!defined("ABSPATH")) {
    exit;
}

// Include WooCommerce integration
if (file_exists(ARTEASY_AI_PLUGIN_PATH . "includes/woocommerce-integration.php")) {
    require_once ARTEASY_AI_PLUGIN_PATH . "includes/woocommerce-integration.php";
}

$woo_data = new ArteasyWooCommerceData();
$sales_data = $woo_data->get_sales_data('30_days');
$top_products = $woo_data->get_top_products(5, '30_days');
$cart_data = $woo_data->get_cart_abandonment_data('30_days');
$customer_insights = $woo_data->get_customer_insights('30_days');
$selling_times = $woo_data->get_best_selling_times('30_days');
$peak_hours = $woo_data->get_peak_hours('30_days');
$ai_status = $woo_data->get_ai_status();
$revenue_trends = $woo_data->get_revenue_trends('30_days');
$customer_segmentation = $woo_data->get_customer_segmentation('30_days');
$product_categories = $woo_data->get_product_category_performance('30_days');

// Check if we have real data or need to show demo data
$has_real_data = $woo_data->is_woocommerce_active() && $sales_data !== false;
?>

<div class="wrap arteasy-analytics">
    <h1>AI Analytics Dashboard</h1>
    
    <?php if (!$has_real_data): ?>
    <div class="notice notice-info">
        <p><strong>Demo Mode:</strong> Install and activate WooCommerce to see real business data. Currently showing sample data.</p>
    </div>
    <?php endif; ?>
    
    <div class="analytics-overview">
        <div class="overview-cards">
            <div class="overview-card">
                <h3>Total Sales</h3>
                <div class="card-value">₦<?php echo $has_real_data ? number_format($sales_data['total_sales']) : '125,000'; ?></div>
                <div class="card-change positive"><?php echo $has_real_data ? 'Last 30 days' : '+12% from last month'; ?></div>
            </div>
            <div class="overview-card">
                <h3>Orders</h3>
                <div class="card-value"><?php echo $has_real_data ? $sales_data['order_count'] : '89'; ?></div>
                <div class="card-change positive"><?php echo $has_real_data ? 'Last 30 days' : '+8% from last month'; ?></div>
            </div>
            <div class="overview-card">
                <h3>Customers</h3>
                <div class="card-value"><?php echo $has_real_data ? $sales_data['customer_count'] : '156'; ?></div>
                <div class="card-change positive"><?php echo $has_real_data ? 'Last 30 days' : '+15% from last month'; ?></div>
            </div>
            <div class="overview-card">
                <h3>Conversion Rate</h3>
                <div class="card-value"><?php echo $has_real_data ? $sales_data['conversion_rate'] . '%' : '3.2%'; ?></div>
                <div class="card-change <?php echo $has_real_data ? 'positive' : 'negative'; ?>"><?php echo $has_real_data ? 'Estimated' : '-0.5% from last month'; ?></div>
            </div>
        </div>
    </div>
    
    <div class="analytics-content">
        <div class="analytics-main">
            <div class="analytics-section">
                <h2>AI-Generated Insights</h2>
                <div class="insights-container">
                    <div class="insight-card">
                        <h3>Top Performing Products</h3>
                        <div class="insight-content">
                            <?php if ($has_real_data && $top_products): ?>
                                <?php foreach ($top_products as $product_id => $product): ?>
                                    <div class="product-item">
                                        <span class="product-name"><?php echo esc_html($product['name']); ?></span>
                                        <span class="product-growth"><?php echo $product['quantity']; ?> sold</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="product-item">
                                    <span class="product-name">Professional Paint Brushes Set</span>
                                    <span class="product-growth">+25% growth</span>
                                </div>
                                <div class="product-item">
                                    <span class="product-name">Canvas Boards (Pack of 10)</span>
                                    <span class="product-growth">+18% growth</span>
                                </div>
                                <div class="product-item">
                                    <span class="product-name">Acrylic Paint Set</span>
                                    <span class="product-growth">+22% growth</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="insight-card">
                        <h3>Best Selling Times</h3>
                        <div class="insight-content">
                            <?php if ($has_real_data && $selling_times && $selling_times['total_orders'] > 0): ?>
                                <?php if ($selling_times['best_hour']): ?>
                                <div class="time-item">
                                    <span class="time-period">Peak Hour: <?php echo $selling_times['best_hour']['hour_formatted']; ?></span>
                                    <span class="time-performance"><?php echo $selling_times['best_hour']['orders']; ?> orders (₦<?php echo number_format($selling_times['best_hour']['revenue']); ?>)</span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($selling_times['best_day']): ?>
                                <div class="time-item">
                                    <span class="time-period">Best Day: <?php echo $selling_times['best_day']['day']; ?></span>
                                    <span class="time-performance"><?php echo $selling_times['best_day']['orders']; ?> orders (₦<?php echo number_format($selling_times['best_day']['revenue']); ?>)</span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($peak_hours && count($peak_hours) > 0): ?>
                                <div class="time-item">
                                    <span class="time-period">Top Hours:</span>
                                    <span class="time-performance">
                                        <?php 
                                        $hour_list = array();
                                        foreach ($peak_hours as $hour) {
                                            $hour_list[] = $hour['hour_formatted'] . ' (' . $hour['orders'] . ' orders)';
                                        }
                                        echo implode(', ', $hour_list);
                                        ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="time-item">
                                    <span class="time-period">Peak Hour: 14:00</span>
                                    <span class="time-performance">12 orders (₦18,500)</span>
                                </div>
                                <div class="time-item">
                                    <span class="time-period">Best Day: Saturday</span>
                                    <span class="time-performance">25 orders (₦37,200)</span>
                                </div>
                                <div class="time-item">
                                    <span class="time-period">Top Hours:</span>
                                    <span class="time-performance">14:00 (12 orders), 19:00 (8 orders), 10:00 (7 orders)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="insight-card">
                        <h3>Customer Behavior</h3>
                        <div class="insight-content">
                            <?php if ($has_real_data && $customer_insights): ?>
                                <div class="behavior-item">
                                    <span class="behavior-metric">Repeat Customers</span>
                                    <span class="behavior-value"><?php echo $customer_insights['repeat_customer_rate']; ?>%</span>
                                </div>
                                <div class="behavior-item">
                                    <span class="behavior-metric">Average Order Value</span>
                                    <span class="behavior-value">₦<?php echo number_format($customer_insights['average_order_value']); ?></span>
                                </div>
                                <div class="behavior-item">
                                    <span class="behavior-metric">Total Customers</span>
                                    <span class="behavior-value"><?php echo $customer_insights['total_customers']; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="behavior-item">
                                    <span class="behavior-metric">Repeat Customers</span>
                                    <span class="behavior-value">35%</span>
                                </div>
                                <div class="behavior-item">
                                    <span class="behavior-metric">Average Order Value</span>
                                    <span class="behavior-value">₦1,500</span>
                                </div>
                                <div class="behavior-item">
                                    <span class="behavior-metric">Cart Abandonment Rate</span>
                                    <span class="behavior-value">45%</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="analytics-section">
                <h2>Detailed Selling Times Analysis</h2>
                <div class="selling-times-container">
                    <?php if ($has_real_data && $selling_times && $selling_times['total_orders'] > 0): ?>
                        <div class="times-grid">
                            <div class="time-analysis-card">
                                <h3>Hourly Performance</h3>
                                <div class="time-chart">
                                    <?php 
                                    $hourly_data = $selling_times['hourly_data'];
                                    $max_orders = max(array_column($hourly_data, 'orders'));
                                    for ($hour = 0; $hour < 24; $hour++): 
                                        $orders = $hourly_data[$hour]['orders'];
                                        $height = $max_orders > 0 ? ($orders / $max_orders) * 100 : 0;
                                    ?>
                                    <div class="hour-bar">
                                        <div class="bar" style="height: <?php echo $height; ?>%"></div>
                                        <span class="hour-label"><?php echo sprintf('%02d', $hour); ?></span>
                                        <span class="hour-orders"><?php echo $orders; ?></span>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="time-analysis-card">
                                <h3>Daily Performance</h3>
                                <div class="daily-stats">
                                    <?php 
                                    $daily_data = $selling_times['daily_data'];
                                    $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
                                    foreach ($days as $day): 
                                        $data = $daily_data[$day];
                                    ?>
                                    <div class="day-item">
                                        <span class="day-name"><?php echo substr($day, 0, 3); ?></span>
                                        <div class="day-bar">
                                            <div class="bar-fill" style="width: <?php echo $max_orders > 0 ? ($data['orders'] / $max_orders) * 100 : 0; ?>%"></div>
                                        </div>
                                        <span class="day-orders"><?php echo $data['orders']; ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="insights-summary">
                            <h3>Key Insights</h3>
                            <div class="insights-grid">
                                <?php if ($selling_times['best_hour']): ?>
                                <div class="insight-item">
                                    <strong>Peak Hour:</strong> <?php echo $selling_times['best_hour']['hour_formatted']; ?> with <?php echo $selling_times['best_hour']['orders']; ?> orders
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($selling_times['best_day']): ?>
                                <div class="insight-item">
                                    <strong>Best Day:</strong> <?php echo $selling_times['best_day']['day']; ?> with <?php echo $selling_times['best_day']['orders']; ?> orders
                                </div>
                                <?php endif; ?>
                                
                                <div class="insight-item">
                                    <strong>Total Orders Analyzed:</strong> <?php echo $selling_times['total_orders']; ?> orders in the last 30 days
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="demo-times">
                            <p><strong>Demo Mode:</strong> Install and activate WooCommerce to see real selling times analysis.</p>
                            <div class="demo-chart">
                                <div class="demo-note">
                                    <h4>Sample Data Preview:</h4>
                                    <ul>
                                        <li>Peak selling hour: 14:00 (12 orders)</li>
                                        <li>Best selling day: Saturday (25 orders)</li>
                                        <li>Evening hours (18:00-21:00) show 35% higher conversion</li>
                                        <li>Weekend sales are 40% higher than weekdays</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="analytics-section">
                <h2>Revenue Trends & Forecasting</h2>
                <div class="revenue-trends-container">
                    <?php if ($has_real_data && $revenue_trends): ?>
                        <div class="revenue-overview">
                            <div class="revenue-cards">
                                <div class="revenue-card">
                                    <h3>Total Revenue</h3>
                                    <div class="revenue-value">₦<?php echo number_format($revenue_trends['total_revenue']); ?></div>
                                    <div class="revenue-period">Last 30 days</div>
                                </div>
                                <div class="revenue-card">
                                    <h3>Growth Rate</h3>
                                    <div class="revenue-value <?php echo $revenue_trends['growth_rates']['trend'] === 'up' ? 'positive' : ($revenue_trends['growth_rates']['trend'] === 'down' ? 'negative' : 'neutral'); ?>">
                                        <?php echo $revenue_trends['growth_rates']['daily_growth_rate']; ?>%
                                    </div>
                                    <div class="revenue-period">vs previous week</div>
                                </div>
                                <div class="revenue-card">
                                    <h3>7-Day Forecast</h3>
                                    <div class="revenue-value">₦<?php echo number_format($revenue_trends['forecast']['next_7_days']); ?></div>
                                    <div class="revenue-period"><?php echo ucfirst($revenue_trends['forecast']['confidence']); ?> confidence</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="revenue-chart-container">
                            <h3>Daily Revenue Trend</h3>
                            <div class="revenue-chart">
                                <?php 
                                $daily_data = $revenue_trends['daily_revenue'];
                                $max_revenue = max(array_column($daily_data, 'revenue'));
                                $chart_days = array_slice($daily_data, 0, 14, true); // Show last 14 days
                                ?>
                                <div class="chart-bars">
                                    <?php foreach ($chart_days as $date => $data): 
                                        $height = $max_revenue > 0 ? ($data['revenue'] / $max_revenue) * 100 : 0;
                                        $day_name = date('M j', strtotime($date));
                                    ?>
                                    <div class="chart-bar">
                                        <div class="bar" style="height: <?php echo $height; ?>%"></div>
                                        <span class="bar-label"><?php echo $day_name; ?></span>
                                        <span class="bar-value">₦<?php echo number_format($data['revenue']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="demo-revenue">
                            <p><strong>Demo Mode:</strong> Install and activate WooCommerce to see real revenue trends.</p>
                            <div class="demo-revenue-chart">
                                <h4>Sample Revenue Trend (Last 14 Days)</h4>
                                <div class="chart-bars">
                                    <?php for ($i = 13; $i >= 0; $i--): 
                                        $revenue = rand(5000, 25000);
                                        $height = ($revenue / 25000) * 100;
                                        $day_name = date('M j', strtotime("-{$i} days"));
                                    ?>
                                    <div class="chart-bar">
                                        <div class="bar" style="height: <?php echo $height; ?>%"></div>
                                        <span class="bar-label"><?php echo $day_name; ?></span>
                                        <span class="bar-value">₦<?php echo number_format($revenue); ?></span>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="analytics-section">
                <h2>Customer Segmentation Analysis</h2>
                <div class="customer-segmentation-container">
                    <?php if ($has_real_data && $customer_segmentation): ?>
                        <div class="segmentation-grid">
                            <div class="segment-card">
                                <h3>New Customers</h3>
                                <div class="segment-value"><?php echo $customer_segmentation['segments']['new_customers']; ?></div>
                                <div class="segment-rate"><?php echo $customer_segmentation['new_customer_rate']; ?>%</div>
                            </div>
                            <div class="segment-card">
                                <h3>Returning Customers</h3>
                                <div class="segment-value"><?php echo $customer_segmentation['segments']['returning_customers']; ?></div>
                                <div class="segment-rate"><?php echo $customer_segmentation['returning_customer_rate']; ?>%</div>
                            </div>
                            <div class="segment-card">
                                <h3>High Value</h3>
                                <div class="segment-value"><?php echo $customer_segmentation['segments']['high_value_customers']; ?></div>
                                <div class="segment-rate"><?php echo $customer_segmentation['high_value_rate']; ?>%</div>
                            </div>
                            <div class="segment-card">
                                <h3>VIP Customers</h3>
                                <div class="segment-value"><?php echo $customer_segmentation['segments']['vip_customers']; ?></div>
                                <div class="segment-rate"><?php echo $customer_segmentation['vip_rate']; ?>%</div>
                            </div>
                        </div>
                        <div class="clv-summary">
                            <h3>Customer Lifetime Value</h3>
                            <div class="clv-value">₦<?php echo number_format($customer_segmentation['average_clv']); ?></div>
                            <div class="clv-label">Average CLV per customer</div>
                        </div>
                    <?php else: ?>
                        <div class="demo-segmentation">
                            <p><strong>Demo Mode:</strong> Customer segmentation requires WooCommerce data.</p>
                            <div class="segmentation-grid">
                                <div class="segment-card">
                                    <h3>New Customers</h3>
                                    <div class="segment-value">45</div>
                                    <div class="segment-rate">65%</div>
                                </div>
                                <div class="segment-card">
                                    <h3>Returning Customers</h3>
                                    <div class="segment-value">24</div>
                                    <div class="segment-rate">35%</div>
                                </div>
                                <div class="segment-card">
                                    <h3>High Value</h3>
                                    <div class="segment-value">8</div>
                                    <div class="segment-rate">12%</div>
                                </div>
                                <div class="segment-card">
                                    <h3>VIP Customers</h3>
                                    <div class="segment-value">5</div>
                                    <div class="segment-rate">7%</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="analytics-section">
                <h2>Product Category Performance</h2>
                <div class="category-performance-container">
                    <?php if ($has_real_data && $product_categories): ?>
                        <div class="category-grid">
                            <?php foreach (array_slice($product_categories, 0, 4, true) as $category_name => $data): ?>
                            <div class="category-card">
                                <h3><?php echo esc_html($category_name); ?></h3>
                                <div class="category-metrics">
                                    <div class="metric">
                                        <span class="metric-label">Revenue</span>
                                        <span class="metric-value">₦<?php echo number_format($data['revenue']); ?></span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Quantity</span>
                                        <span class="metric-value"><?php echo $data['quantity']; ?></span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Products</span>
                                        <span class="metric-value"><?php echo $data['products']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="demo-categories">
                            <p><strong>Demo Mode:</strong> Product category analysis requires WooCommerce data.</p>
                            <div class="category-grid">
                                <div class="category-card">
                                    <h3>Art Supplies</h3>
                                    <div class="category-metrics">
                                        <div class="metric">
                                            <span class="metric-label">Revenue</span>
                                            <span class="metric-value">₦45,000</span>
                                        </div>
                                        <div class="metric">
                                            <span class="metric-label">Quantity</span>
                                            <span class="metric-value">125</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="category-card">
                                    <h3>Painting Tools</h3>
                                    <div class="category-metrics">
                                        <div class="metric">
                                            <span class="metric-label">Revenue</span>
                                            <span class="metric-value">₦32,000</span>
                                        </div>
                                        <div class="metric">
                                            <span class="metric-label">Quantity</span>
                                            <span class="metric-value">89</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="analytics-section">
                <h2>AI Recommendations</h2>
                <div class="recommendations-container">
                    <div class="recommendation-card">
                        <h3>Boost Weekend Sales</h3>
                        <p>Your sales are 40% higher on weekends. Consider running weekend-specific promotions and social media campaigns to maximize this opportunity.</p>
                        <div class="recommendation-actions">
                            <button class="button button-primary">Create Weekend Campaign</button>
                            <button class="button">Learn More</button>
                        </div>
                    </div>
                    
                    <div class="recommendation-card">
                        <h3>Reduce Cart Abandonment</h3>
                        <p>45% cart abandonment rate is above industry average. Implement exit-intent popups and cart recovery emails to recover lost sales.</p>
                        <div class="recommendation-actions">
                            <button class="button button-primary">Setup Cart Recovery</button>
                            <button class="button">View Strategy</button>
                        </div>
                    </div>
                    
                    <div class="recommendation-card">
                        <h3>Expand Top Products</h3>
                        <p>Professional Paint Brushes Set shows 25% growth. Consider creating bundles or variations to capitalize on this trend.</p>
                        <div class="recommendation-actions">
                            <button class="button button-primary">Create Bundle</button>
                            <button class="button">Analyze Further</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="analytics-sidebar">
            <div class="sidebar-section">
                <h3>Traffic Sources</h3>
                <div class="traffic-sources">
                    <div class="source-item">
                        <span class="source-name">Instagram</span>
                        <span class="source-performance">3x better conversion</span>
                    </div>
                    <div class="source-item">
                        <span class="source-name">Google Search</span>
                        <span class="source-performance">High volume</span>
                    </div>
                    <div class="source-item">
                        <span class="source-name">Facebook</span>
                        <span class="source-performance">Good engagement</span>
                    </div>
                    <div class="source-item">
                        <span class="source-name">Direct</span>
                        <span class="source-performance">Repeat customers</span>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-section">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <button class="button button-primary" onclick="generateReport()">Generate Report</button>
                    <button class="button" onclick="exportData()">Export Data</button>
                    <button class="button" onclick="refreshInsights()" id="refresh-btn">Refresh Insights</button>
                </div>
            </div>
            
            <div class="sidebar-section">
                <h3>AI Status</h3>
                <div class="ai-status">
                    <div class="status-item">
                        <span class="status-label">Analysis Engine</span>
                        <span class="status-indicator active"><?php echo $ai_status['analysis_engine']; ?></span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Last Update</span>
                        <span class="status-time"><?php echo $ai_status['last_update']; ?></span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Next Analysis</span>
                        <span class="status-time"><?php echo $ai_status['next_analysis']; ?></span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Gemini API</span>
                        <span class="status-indicator <?php echo $ai_status['gemini_api_status'] === 'Connected' ? 'active' : 'inactive'; ?>"><?php echo $ai_status['gemini_api_status']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="analytics-footer">
        <div class="footer-info">
            <h3>About AI Analytics</h3>
            <p>Our AI analyzes your sales data, customer behavior, and market trends to provide actionable insights. The system learns from your business patterns and provides recommendations to help grow your art supplies store.</p>
        </div>
        
        <div class="footer-features">
            <h3>Analytics Features</h3>
            <ul>
                <li>Real-time sales analysis</li>
                <li>Customer behavior insights</li>
                <li>Product performance tracking</li>
                <li>Traffic source analysis</li>
                <li>Automated recommendations</li>
                <li>Nigerian market context</li>
            </ul>
        </div>
    </div>
</div>

<style>
.arteasy-analytics {
    max-width: 1400px;
}

.analytics-overview {
    margin-bottom: 30px;
}

.overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.overview-card {
    background: white;
    padding: 25px;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.overview-card h3 {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-value {
    font-size: 32px;
    font-weight: bold;
    color: #0073aa;
    margin: 10px 0;
}

.card-change {
    font-size: 14px;
    font-weight: 500;
}

.card-change.positive {
    color: #00a32a;
}

.card-change.negative {
    color: #d63638;
}

.analytics-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.analytics-section {
    background: white;
    padding: 25px;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.insights-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.insight-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.insight-card h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
}

.product-item, .time-item, .behavior-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.product-item:last-child, .time-item:last-child, .behavior-item:last-child {
    border-bottom: none;
}

.product-name, .time-period, .behavior-metric {
    font-weight: 500;
    color: #333;
}

.product-growth, .time-performance, .behavior-value {
    color: #0073aa;
    font-weight: 600;
}

.recommendations-container {
    margin-top: 20px;
}

.recommendation-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    margin-bottom: 20px;
}

.recommendation-card h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.recommendation-card p {
    margin: 0 0 15px 0;
    color: #666;
    line-height: 1.5;
}

.recommendation-actions {
    display: flex;
    gap: 10px;
}

.analytics-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-section {
    background: white;
    padding: 20px;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sidebar-section h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
}

.traffic-sources {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.source-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.source-item:last-child {
    border-bottom: none;
}

.source-name {
    font-weight: 500;
    color: #333;
}

.source-performance {
    color: #0073aa;
    font-size: 12px;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ai-status {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.status-item:last-child {
    border-bottom: none;
}

.status-label {
    color: #666;
    font-size: 14px;
}

.status-indicator {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.status-indicator.active {
    background: #d1edff;
    color: #0073aa;
}

.status-time {
    color: #666;
    font-size: 12px;
}

.analytics-footer {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-top: 30px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 10px;
}

.footer-info h3, .footer-features h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.footer-info p {
    margin: 0;
    color: #666;
    line-height: 1.5;
}

.footer-features ul {
    margin: 0;
    padding-left: 20px;
}

.footer-features li {
    margin: 5px 0;
    color: #666;
}

/* Selling Times Analysis Styles */
.selling-times-container {
    margin-top: 20px;
}

.times-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.time-analysis-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.time-analysis-card h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
}

.time-chart {
    display: flex;
    align-items: end;
    gap: 2px;
    height: 200px;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}

.hour-bar {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.hour-bar .bar {
    background: linear-gradient(to top, #0073aa, #00a0d2);
    width: 100%;
    min-height: 2px;
    border-radius: 2px 2px 0 0;
    transition: all 0.3s ease;
}

.hour-bar:hover .bar {
    background: linear-gradient(to top, #005177, #0073aa);
}

.hour-label {
    font-size: 10px;
    color: #666;
    margin-top: 5px;
    font-weight: 500;
}

.hour-orders {
    font-size: 9px;
    color: #999;
    margin-top: 2px;
}

.daily-stats {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.day-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.day-name {
    width: 40px;
    font-size: 12px;
    font-weight: 500;
    color: #333;
}

.day-bar {
    flex: 1;
    height: 20px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.bar-fill {
    height: 100%;
    background: linear-gradient(to right, #0073aa, #00a0d2);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.day-orders {
    width: 30px;
    text-align: right;
    font-size: 12px;
    font-weight: 500;
    color: #333;
}

.insights-summary {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.insights-summary h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.insight-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    font-size: 14px;
    color: #555;
}

.demo-times {
    text-align: center;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #ddd;
}

.demo-times p {
    margin: 0 0 20px 0;
    color: #666;
    font-size: 16px;
}

.demo-chart {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.demo-note h4 {
    margin: 0 0 15px 0;
    color: #333;
}

.demo-note ul {
    margin: 0;
    padding-left: 20px;
    text-align: left;
}

.demo-note li {
    margin: 8px 0;
    color: #666;
}

@media (max-width: 768px) {
    .analytics-content {
        grid-template-columns: 1fr;
    }
    
    .analytics-footer {
        grid-template-columns: 1fr;
    }
    
    .overview-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .insights-container {
        grid-template-columns: 1fr;
    }
    
    .times-grid {
        grid-template-columns: 1fr;
    }
    
    .time-chart {
        height: 150px;
    }
    
    .hour-label {
        font-size: 8px;
    }
    
    .hour-orders {
        font-size: 7px;
    }
}

.status-indicator.inactive {
    color: #d63638;
}

/* Revenue Trends Styles */
.revenue-trends-container {
    margin-top: 20px;
}

.revenue-overview {
    margin-bottom: 30px;
}

.revenue-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.revenue-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.revenue-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}

.revenue-value {
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0;
}

.revenue-value.positive {
    color: #4ade80;
}

.revenue-value.negative {
    color: #f87171;
}

.revenue-value.neutral {
    color: #fbbf24;
}

.revenue-period {
    font-size: 12px;
    opacity: 0.8;
}

.revenue-chart-container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.revenue-chart {
    margin-top: 20px;
}

.chart-bars {
    display: flex;
    align-items: end;
    gap: 8px;
    height: 200px;
    padding: 20px 0;
    border-bottom: 1px solid #e5e7eb;
}

.chart-bar {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.chart-bar .bar {
    background: linear-gradient(to top, #3b82f6, #1d4ed8);
    width: 100%;
    min-height: 2px;
    border-radius: 4px 4px 0 0;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.chart-bar:hover .bar {
    background: linear-gradient(to top, #2563eb, #1e40af);
    transform: scaleY(1.1);
}

.bar-label {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 5px;
}

.bar-value {
    font-size: 10px;
    color: #374151;
    font-weight: 500;
}

/* Customer Segmentation Styles */
.customer-segmentation-container {
    margin-top: 20px;
}

.segmentation-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.segment-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}

.segment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.segment-card h3 {
    margin: 0 0 15px 0;
    color: #374151;
    font-size: 16px;
}

.segment-value {
    font-size: 32px;
    font-weight: bold;
    color: #1f2937;
    margin: 10px 0;
}

.segment-rate {
    font-size: 14px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 5px 10px;
    border-radius: 20px;
    display: inline-block;
}

.clv-summary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.clv-summary h3 {
    margin: 0 0 15px 0;
    font-size: 18px;
}

.clv-value {
    font-size: 36px;
    font-weight: bold;
    margin: 10px 0;
}

.clv-label {
    font-size: 14px;
    opacity: 0.9;
}

/* Product Category Styles */
.category-performance-container {
    margin-top: 20px;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.category-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.category-card h3 {
    margin: 0 0 15px 0;
    color: #1f2937;
    font-size: 18px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}

.category-metrics {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.metric {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.metric-label {
    color: #6b7280;
    font-size: 14px;
}

.metric-value {
    color: #1f2937;
    font-weight: 600;
    font-size: 16px;
}

/* Demo Mode Styles */
.demo-revenue, .demo-segmentation, .demo-categories {
    text-align: center;
    padding: 40px;
    background: #f8fafc;
    border-radius: 10px;
    border: 2px dashed #cbd5e1;
}

.demo-revenue p, .demo-segmentation p, .demo-categories p {
    margin: 0 0 20px 0;
    color: #64748b;
    font-size: 16px;
}

.demo-revenue-chart {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.demo-revenue-chart h4 {
    margin: 0 0 20px 0;
    color: #1e293b;
}

@media (max-width: 768px) {
    .revenue-cards {
        grid-template-columns: 1fr;
    }
    
    .segmentation-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .category-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-bars {
        height: 150px;
        gap: 4px;
    }
    
    .bar-label {
        font-size: 9px;
    }
    
    .bar-value {
        font-size: 8px;
    }
}
</style>

<script>
// Analytics action functions
function refreshInsights() {
    const btn = document.getElementById('refresh-btn');
    const originalText = btn.textContent;
    
    btn.textContent = 'Refreshing...';
    btn.disabled = true;
    
    jQuery.ajax({
        url: arteasy_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'arteasy_refresh_analytics',
            nonce: arteasy_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                // Show success message
                showNotice('Analytics data refreshed successfully!', 'success');
                
                // Reload the page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotice('Failed to refresh analytics data.', 'error');
            }
        },
        error: function() {
            showNotice('Error refreshing analytics data.', 'error');
        },
        complete: function() {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    });
}

function exportData() {
    // Create a form to submit the export request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = arteasy_ajax.ajax_url;
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'arteasy_export_analytics';
    
    const nonceInput = document.createElement('input');
    nonceInput.type = 'hidden';
    nonceInput.name = 'nonce';
    nonceInput.value = arteasy_ajax.nonce;
    
    const periodInput = document.createElement('input');
    periodInput.type = 'hidden';
    periodInput.name = 'period';
    periodInput.value = '30_days';
    
    form.appendChild(actionInput);
    form.appendChild(nonceInput);
    form.appendChild(periodInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function generateReport() {
    showNotice('Report generation feature coming soon!', 'info');
}

function showNotice(message, type) {
    const noticeClass = type === 'success' ? 'notice-success' : 
                       type === 'error' ? 'notice-error' : 'notice-info';
    
    const notice = document.createElement('div');
    notice.className = `notice ${noticeClass} is-dismissible`;
    notice.innerHTML = `<p>${message}</p>`;
    
    // Insert notice at the top of the page
    const wrap = document.querySelector('.arteasy-analytics');
    wrap.insertBefore(notice, wrap.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (notice.parentNode) {
            notice.parentNode.removeChild(notice);
        }
    }, 5000);
}
</script>

