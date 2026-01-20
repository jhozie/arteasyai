<?php
/**
 * WooCommerce Data Integration
 * Pulls real data from WooCommerce for analytics and cart recovery
 */

if (!defined("ABSPATH")) {
    exit;
}

class ArteasyWooCommerceData {
    
    public function __construct() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_not_active_notice'));
            return;
        }
    }
    
    /**
     * Get real sales data for analytics
     */
    public function get_sales_data($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $date_range = $this->get_date_range($period);
        
        // Get orders in date range
        $orders = wc_get_orders(array(
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        $total_sales = 0;
        $order_count = 0;
        $customer_ids = array();
        
        foreach ($orders as $order) {
            $total_sales += $order->get_total();
            $order_count++;
            $customer_ids[] = $order->get_customer_id();
        }
        
        $unique_customers = count(array_unique($customer_ids));
        
        // Calculate conversion rate (simplified)
        $conversion_rate = $this->calculate_conversion_rate($period);
        
        return array(
            'total_sales' => $total_sales,
            'order_count' => $order_count,
            'customer_count' => $unique_customers,
            'conversion_rate' => $conversion_rate,
            'period' => $period
        );
    }
    
    /**
     * Get top selling products
     */
    public function get_top_products($limit = 5, $period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $date_range = $this->get_date_range($period);
        
        $orders = wc_get_orders(array(
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        $product_sales = array();
        
        foreach ($orders as $order) {
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                
                if (!isset($product_sales[$product_id])) {
                    $product_sales[$product_id] = array(
                        'id' => $product_id,        // ✅ ADD PRODUCT ID!
                        'name' => $product_name,
                        'quantity' => 0,
                        'revenue' => 0
                    );
                }
                
                $product_sales[$product_id]['quantity'] += $quantity;
                $product_sales[$product_id]['revenue'] += $item->get_total();
            }
        }
        
        // Sort by quantity sold
        uasort($product_sales, function($a, $b) {
            return $b['quantity'] - $a['quantity'];
        });
        
        return array_slice($product_sales, 0, $limit, true);
    }
    
    /**
     * Get cart abandonment data
     */
    public function get_cart_abandonment_data($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $date_range = $this->get_date_range($period);
        
        // Get abandoned carts (carts created but not completed)
        $abandoned_carts = $this->get_abandoned_carts($date_range);
        
        // Get recovered carts (carts that were abandoned but later completed)
        $recovered_carts = $this->get_recovered_carts($date_range);
        
        $abandonment_rate = 0;
        $recovery_rate = 0;
        $recovered_revenue = 0;
        
        if (count($abandoned_carts) > 0) {
            $abandonment_rate = (count($abandoned_carts) / (count($abandoned_carts) + count($recovered_carts))) * 100;
            $recovery_rate = (count($recovered_carts) / count($abandoned_carts)) * 100;
        }
        
        foreach ($recovered_carts as $cart) {
            // $recovered_carts is a wpdb result set (objects)
            $recovered_revenue += isset($cart->total) ? floatval($cart->total) : 0;
        }
        
        // Include tracked carts stored via frontend tracking within date range
        $tracked = get_option('arteasy_abandoned_carts', array());
        $tracked_count = 0;
        if (!empty($tracked)) {
            foreach ($tracked as $entry) {
                $t = isset($entry['timestamp']) ? strtotime($entry['timestamp']) : 0;
                if ($t && $t >= strtotime($date_range['start']) && $t <= strtotime($date_range['end'])) {
                    $tracked_count++;
                }
            }
        }

        return array(
            'abandoned_count' => count($abandoned_carts) + $tracked_count,
            'recovered_count' => count($recovered_carts),
            'abandonment_rate' => round($abandonment_rate, 1),
            'recovery_rate' => round($recovery_rate, 1),
            'recovered_revenue' => $recovered_revenue
        );
    }
    
    /**
     * Get customer behavior insights
     */
    public function get_customer_insights($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $date_range = $this->get_date_range($period);
        
        $orders = wc_get_orders(array(
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        $customer_orders = array();
        $total_revenue = 0;
        
        foreach ($orders as $order) {
            $customer_id = $order->get_customer_id();
            $total_revenue += $order->get_total();
            
            if (!isset($customer_orders[$customer_id])) {
                $customer_orders[$customer_id] = 0;
            }
            $customer_orders[$customer_id]++;
        }
        
        $repeat_customers = 0;
        foreach ($customer_orders as $order_count) {
            if ($order_count > 1) {
                $repeat_customers++;
            }
        }
        
        $total_customers = count($customer_orders);
        $repeat_customer_rate = $total_customers > 0 ? ($repeat_customers / $total_customers) * 100 : 0;
        $average_order_value = count($orders) > 0 ? $total_revenue / count($orders) : 0;
        
        return array(
            'repeat_customer_rate' => round($repeat_customer_rate, 1),
            'average_order_value' => round($average_order_value, 2),
            'total_customers' => $total_customers,
            'total_orders' => count($orders)
        );
    }
    
    /**
     * Get abandoned carts
     */
    private function get_abandoned_carts($date_range) {
        // Prefer WooCommerce API, fallback to direct SQL if API returns empty
        $results = array();

        if (class_exists('WooCommerce')) {
            $orders = wc_get_orders(array(
                'status' => array('pending', 'on-hold', 'wc-pending', 'wc-on-hold'),
                'date_created' => $date_range['start'] . '...' . $date_range['end'],
                'limit' => -1
            ));

            foreach ($orders as $order) {
                $results[] = (object) array(
                    'ID' => $order->get_id(),
                    'post_date' => $order->get_date_created()->date('Y-m-d H:i:s'),
                    'cart_data' => ''
                );
            }
        }

        // Fallback: direct SQL in case API returns nothing (e.g., HPOS/settings)
        if (empty($results)) {
            global $wpdb;
            $query = $wpdb->prepare("
                SELECT p.ID, p.post_date
                FROM {$wpdb->posts} p
                WHERE p.post_type = 'shop_order'
                AND p.post_status IN ('wc-pending', 'wc-on-hold')
                AND p.post_date >= %s
                AND p.post_date <= %s
            ", $date_range['start'], $date_range['end']);

            $rows = $wpdb->get_results($query);
            foreach ($rows as $row) {
                $results[] = (object) array(
                    'ID' => intval($row->ID),
                    'post_date' => $row->post_date,
                    'cart_data' => ''
                );
            }
        }

        return $results;
    }
    
    /**
     * Get recovered carts
     */
    private function get_recovered_carts($date_range) {
        global $wpdb;
        
        $query = $wpdb->prepare("
            SELECT p.ID, p.post_date, pm.meta_value as total
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND p.post_date >= %s
            AND p.post_date <= %s
            AND pm.meta_key = '_order_total'
        ", $date_range['start'], $date_range['end']);
        
        return $wpdb->get_results($query);
    }

    /**
     * Public: List abandoned orders (pending/on-hold) with details for UI
     */
    public function get_abandoned_orders_list($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return array();
        }
        
        $date_range = $this->get_date_range($period);
        
        // Use WooCommerce API instead of raw SQL - more reliable
        $orders = wc_get_orders(array(
            'status' => array('pending', 'on-hold', 'wc-pending', 'wc-on-hold'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        $list = array();
        foreach ($orders as $order) {
            $list[] = array(
                'id' => $order->get_id(),
                'date' => $order->get_date_created()->date('Y-m-d H:i:s'),
                'total' => floatval($order->get_total()),
                'status' => $order->get_status(),
                'email' => $order->get_billing_email(),
            );
        }
        
        return $list;
    }

    /**
     * Public: List tracked carts saved via frontend tracker
     */
    public function get_tracked_carts_list($period = '30_days') {
        $date_range = $this->get_date_range($period);
        $tracked = get_option('arteasy_abandoned_carts', array());
        $list = array();
        if (empty($tracked)) return $list;
        foreach ($tracked as $key => $entry) {
            $t = isset($entry['timestamp']) ? strtotime($entry['timestamp']) : 0;
            if ($t && $t >= strtotime($date_range['start']) && $t <= strtotime($date_range['end'])) {
                $list[] = array(
                    'key' => $key,
                    'timestamp' => $entry['timestamp'] ?? '',
                    'total' => isset($entry['cart_total']) ? floatval($entry['cart_total']) : 0,
                    'email' => $entry['email'] ?? '',
                    'items' => is_array($entry['cart_items'] ?? null) ? count($entry['cart_items']) : 0,
                );
            }
        }
        // Sort newest first
        usort($list, function($a, $b){ return strcmp($b['timestamp'], $a['timestamp']); });
        return $list;
    }
    
    /**
     * Calculate conversion rate
     */
    private function calculate_conversion_rate($period) {
        // This is a simplified calculation
        // In a real implementation, you'd track visitors vs orders
        $date_range = $this->get_date_range($period);
        
        $orders = wc_get_orders(array(
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        // Estimate based on orders (simplified)
        $estimated_visitors = count($orders) * 30; // Rough estimate
        $conversion_rate = $estimated_visitors > 0 ? (count($orders) / $estimated_visitors) * 100 : 0;
        
        return round($conversion_rate, 1);
    }
    
    /**
     * Get date range for period
     */
    private function get_date_range($period) {
        $end_date = current_time('Y-m-d H:i:s');
        
        switch ($period) {
            case '7_days':
                $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30_days':
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90_days':
                $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            default:
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        }
        
        return array(
            'start' => $start_date,
            'end' => $end_date
        );
    }
    
    /**
     * Show notice if WooCommerce is not active
     */
    public function woocommerce_not_active_notice() {
        echo '<div class="notice notice-warning"><p><strong>Arteasy AI:</strong> WooCommerce is not active. Real data integration requires WooCommerce to be installed and activated.</p></div>';
    }
    
    /**
     * Get best selling times analysis
     */
    public function get_best_selling_times($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $date_range = $this->get_date_range($period);
        
        $orders = wc_get_orders(array(
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        $hourly_sales = array();
        $daily_sales = array();
        $monthly_sales = array();
        
        // Initialize arrays
        for ($i = 0; $i < 24; $i++) {
            $hourly_sales[$i] = array('orders' => 0, 'revenue' => 0);
        }
        
        $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        foreach ($days as $day) {
            $daily_sales[$day] = array('orders' => 0, 'revenue' => 0);
        }
        
        for ($i = 1; $i <= 12; $i++) {
            $monthly_sales[$i] = array('orders' => 0, 'revenue' => 0);
        }
        
        // Analyze each order
        foreach ($orders as $order) {
            $order_date = $order->get_date_created();
            $hour = (int) $order_date->format('H');
            $day_name = $order_date->format('l');
            $month = (int) $order_date->format('n');
            $revenue = $order->get_total();
            
            // Hourly analysis
            $hourly_sales[$hour]['orders']++;
            $hourly_sales[$hour]['revenue'] += $revenue;
            
            // Daily analysis
            $daily_sales[$day_name]['orders']++;
            $daily_sales[$day_name]['revenue'] += $revenue;
            
            // Monthly analysis
            $monthly_sales[$month]['orders']++;
            $monthly_sales[$month]['revenue'] += $revenue;
        }
        
        // Find best times
        $best_hour = $this->find_best_time($hourly_sales, 'hour');
        $best_day = $this->find_best_time($daily_sales, 'day');
        $best_month = $this->find_best_time($monthly_sales, 'month');
        
        return array(
            'hourly_data' => $hourly_sales,
            'daily_data' => $daily_sales,
            'monthly_data' => $monthly_sales,
            'best_hour' => $best_hour,
            'best_day' => $best_day,
            'best_month' => $best_month,
            'total_orders' => count($orders),
            'period' => $period
        );
    }
    
    /**
     * Find the best selling time from data
     */
    private function find_best_time($data, $type) {
        $best_time = null;
        $max_orders = 0;
        $max_revenue = 0;
        
        foreach ($data as $key => $stats) {
            if ($stats['orders'] > $max_orders) {
                $max_orders = $stats['orders'];
                $max_revenue = $stats['revenue'];
                $best_time = $key;
            }
        }
        
        if ($type === 'hour') {
            return array(
                'hour' => $best_time,
                'hour_formatted' => sprintf('%02d:00', $best_time),
                'orders' => $max_orders,
                'revenue' => $max_revenue
            );
        } elseif ($type === 'day') {
            return array(
                'day' => $best_time,
                'orders' => $max_orders,
                'revenue' => $max_revenue
            );
        } elseif ($type === 'month') {
            $month_names = array(
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            );
            return array(
                'month' => $best_time,
                'month_name' => $month_names[$best_time],
                'orders' => $max_orders,
                'revenue' => $max_revenue
            );
        }
        
        return null;
    }
    
    /**
     * Get peak selling hours (top 3)
     */
    public function get_peak_hours($period = '30_days') {
        $selling_times = $this->get_best_selling_times($period);
        
        if (!$selling_times) {
            return false;
        }
        
        $hourly_data = $selling_times['hourly_data'];
        
        // Sort by order count
        uasort($hourly_data, function($a, $b) {
            return $b['orders'] - $a['orders'];
        });
        
        $peak_hours = array();
        $count = 0;
        foreach ($hourly_data as $hour => $data) {
            if ($count >= 3) break;
            if ($data['orders'] > 0) {
                $peak_hours[] = array(
                    'hour' => $hour,
                    'hour_formatted' => sprintf('%02d:00', $hour),
                    'orders' => $data['orders'],
                    'revenue' => $data['revenue']
                );
                $count++;
            }
        }
        
        return $peak_hours;
    }
    
    /**
     * Refresh analytics data and update timestamp
     */
    public function refresh_analytics_data() {
        // Update last analysis timestamp
        update_option('arteasy_last_analysis', current_time('Y-m-d H:i:s'));
        
        // Clear any cached data
        delete_transient('arteasy_sales_data_30_days');
        delete_transient('arteasy_top_products_30_days');
        delete_transient('arteasy_selling_times_30_days');
        
        return array(
            'success' => true,
            'message' => 'Analytics data refreshed successfully',
            'timestamp' => current_time('Y-m-d H:i:s')
        );
    }
    
    /**
     * Export analytics data to CSV format
     */
    public function export_analytics_data($period = '30_days') {
        $sales_data = $this->get_sales_data($period);
        $top_products = $this->get_top_products(10, $period);
        $selling_times = $this->get_best_selling_times($period);
        $customer_insights = $this->get_customer_insights($period);
        
        $csv_data = array();
        
        // Sales overview
        $csv_data[] = array('Metric', 'Value');
        $csv_data[] = array('Total Sales', $sales_data['total_sales']);
        $csv_data[] = array('Order Count', $sales_data['order_count']);
        $csv_data[] = array('Customer Count', $sales_data['customer_count']);
        $csv_data[] = array('Conversion Rate', $sales_data['conversion_rate'] . '%');
        $csv_data[] = array('', ''); // Empty row
        
        // Top products
        $csv_data[] = array('Top Products', '');
        $csv_data[] = array('Product Name', 'Quantity Sold', 'Revenue');
        foreach ($top_products as $product) {
            $csv_data[] = array(
                $product['name'],
                $product['quantity'],
                $product['revenue']
            );
        }
        $csv_data[] = array('', '', ''); // Empty row
        
        // Selling times
        $csv_data[] = array('Selling Times Analysis', '');
        $csv_data[] = array('Hour', 'Orders', 'Revenue');
        for ($hour = 0; $hour < 24; $hour++) {
            $csv_data[] = array(
                sprintf('%02d:00', $hour),
                $selling_times['hourly_data'][$hour]['orders'],
                $selling_times['hourly_data'][$hour]['revenue']
            );
        }
        
        return $csv_data;
    }
    
    /**
     * Get marketing automation dashboard metrics
     */
    public function get_marketing_dashboard_metrics($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return array(
                'active_campaigns' => 0,
                'cart_recovery_rate' => 0,
                'bundle_opportunities' => 0,
                'automation_level' => 0
            );
        }
        
        $smart_campaigns = $this->generate_smart_campaigns($period);
        $advanced_cart_data = $this->get_advanced_cart_abandonment($period);
        $bundle_recommendations = $this->generate_bundle_recommendations($period);
        
        // Calculate active campaigns (from stored active campaigns)
        $active_campaigns = get_option('arteasy_active_campaigns', array());
        $active_campaigns_count = count($active_campaigns);
        
        // Calculate cart recovery rate
        $cart_recovery_rate = 0;
        if ($advanced_cart_data && isset($advanced_cart_data['basic_stats']['recovery_rate'])) {
            $cart_recovery_rate = $advanced_cart_data['basic_stats']['recovery_rate'];
        } else {
            // Calculate from cart abandonment data
            $cart_data = $this->get_cart_abandonment_data($period);
            if ($cart_data && $cart_data['total_carts'] > 0) {
                $cart_recovery_rate = round(($cart_data['recovered_carts'] / $cart_data['total_carts']) * 100, 1);
            }
        }
        
        // Calculate bundle opportunities
        $bundle_opportunities = 0;
        if ($bundle_recommendations) {
            $bundle_opportunities = count($bundle_recommendations);
        }
        
        // Calculate automation level based on active features
        $automation_features = 0;
        $total_features = 4; // campaigns, bundles, cart recovery, analytics
        
        if ($active_campaigns_count > 0) $automation_features++;
        if ($bundle_opportunities > 0) $automation_features++;
        if (get_option('arteasy_cart_recovery_active', false)) $automation_features++;
        if ($this->is_woocommerce_active()) $automation_features++;
        
        $automation_level = round(($automation_features / $total_features) * 100);
        
        return array(
            'active_campaigns' => $active_campaigns_count,
            'cart_recovery_rate' => $cart_recovery_rate,
            'bundle_opportunities' => $bundle_opportunities,
            'automation_level' => $automation_level
        );
    }

    /**
     * Get AI system status
     */
    public function get_ai_status() {
        $last_analysis = get_option('arteasy_last_analysis', 'Never');
        $next_analysis = date('Y-m-d H:i:s', strtotime('+4 hours'));
        
        // Calculate time since last analysis
        $time_since = 'Never';
        if ($last_analysis !== 'Never') {
            $last_time = strtotime($last_analysis);
            $current_time = current_time('timestamp');
            $diff = $current_time - $last_time;
            
            if ($diff < 3600) { // Less than 1 hour
                $time_since = floor($diff / 60) . ' minutes ago';
            } elseif ($diff < 86400) { // Less than 1 day
                $time_since = floor($diff / 3600) . ' hours ago';
            } else {
                $time_since = floor($diff / 86400) . ' days ago';
            }
        }
        
        return array(
            'analysis_engine' => 'Active',
            'last_update' => $time_since,
            'next_analysis' => 'In ' . $this->get_time_until_next_analysis(),
            'gemini_api_status' => $this->test_gemini_connection()
        );
    }
    
    /**
     * Test Gemini API connection
     */
    private function test_gemini_connection() {
        if (class_exists('ArteasyGemini')) {
            $gemini = new ArteasyGemini();
            return $gemini->test_connection() ? 'Connected' : 'Disconnected';
        }
        return 'Not Available';
    }
    
    /**
     * Get time until next analysis
     */
    private function get_time_until_next_analysis() {
        $next_analysis = strtotime('+4 hours');
        $current_time = current_time('timestamp');
        $diff = $next_analysis - $current_time;
        
        if ($diff < 3600) {
            return floor($diff / 60) . ' minutes';
        } else {
            return floor($diff / 3600) . ' hours';
        }
    }
    
    /**
     * Get revenue trends analysis
     */
    public function get_revenue_trends($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $date_range = $this->get_date_range($period);
        
        $orders = wc_get_orders(array(
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        $daily_revenue = array();
        $weekly_revenue = array();
        
        // Initialize arrays
        $days_back = $period === '7_days' ? 7 : ($period === '90_days' ? 90 : 30);
        for ($i = 0; $i < $days_back; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $daily_revenue[$date] = array('revenue' => 0, 'orders' => 0);
        }
        
        // Analyze each order
        foreach ($orders as $order) {
            $order_date = $order->get_date_created();
            $date_key = $order_date->format('Y-m-d');
            $revenue = $order->get_total();
            
            if (isset($daily_revenue[$date_key])) {
                $daily_revenue[$date_key]['revenue'] += $revenue;
                $daily_revenue[$date_key]['orders']++;
            }
        }
        
        // Calculate weekly totals
        $weeks = array();
        foreach ($daily_revenue as $date => $data) {
            $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
            if (!isset($weeks[$week_start])) {
                $weeks[$week_start] = array('revenue' => 0, 'orders' => 0);
            }
            $weeks[$week_start]['revenue'] += $data['revenue'];
            $weeks[$week_start]['orders'] += $data['orders'];
        }
        
        // Calculate growth rates
        $growth_rates = $this->calculate_growth_rates($daily_revenue, $weeks);
        
        // Get revenue forecasting
        $forecast = $this->get_revenue_forecast($daily_revenue);
        
        return array(
            'daily_revenue' => $daily_revenue,
            'weekly_revenue' => $weeks,
            'growth_rates' => $growth_rates,
            'forecast' => $forecast,
            'total_revenue' => array_sum(array_column($daily_revenue, 'revenue')),
            'total_orders' => array_sum(array_column($daily_revenue, 'orders')),
            'period' => $period
        );
    }
    
    /**
     * Calculate growth rates
     */
    private function calculate_growth_rates($daily_revenue, $weekly_revenue) {
        $growth_rates = array();
        
        // Daily growth rate (last 7 days vs previous 7 days)
        $last_7_days = array_slice($daily_revenue, 0, 7, true);
        $previous_7_days = array_slice($daily_revenue, 7, 7, true);
        
        $last_7_revenue = array_sum(array_column($last_7_days, 'revenue'));
        $previous_7_revenue = array_sum(array_column($previous_7_days, 'revenue'));
        
        $daily_growth = $previous_7_revenue > 0 ? (($last_7_revenue - $previous_7_revenue) / $previous_7_revenue) * 100 : 0;
        
        // Weekly growth rate
        $weeks_array = array_values($weekly_revenue);
        if (count($weeks_array) >= 2) {
            $current_week = $weeks_array[0]['revenue'];
            $previous_week = $weeks_array[1]['revenue'];
            $weekly_growth = $previous_week > 0 ? (($current_week - $previous_week) / $previous_week) * 100 : 0;
        } else {
            $weekly_growth = 0;
        }
        
        return array(
            'daily_growth_rate' => round($daily_growth, 1),
            'weekly_growth_rate' => round($weekly_growth, 1),
            'trend' => $daily_growth > 0 ? 'up' : ($daily_growth < 0 ? 'down' : 'stable')
        );
    }
    
    /**
     * Get revenue forecast
     */
    private function get_revenue_forecast($daily_revenue) {
        $revenue_values = array_values(array_column($daily_revenue, 'revenue'));
        
        if (count($revenue_values) < 7) {
            return array(
                'next_7_days' => 0,
                'confidence' => 'low'
            );
        }
        
        // Simple moving average forecast
        $recent_avg = array_sum(array_slice($revenue_values, 0, 7)) / 7;
        $next_7_days = $recent_avg * 7;
        
        // Calculate confidence based on variance
        $variance = 0;
        foreach (array_slice($revenue_values, 0, 7) as $value) {
            $variance += pow($value - $recent_avg, 2);
        }
        $variance = $variance / 7;
        $std_dev = sqrt($variance);
        
        $confidence = 'medium';
        if ($std_dev < $recent_avg * 0.2) {
            $confidence = 'high';
        } elseif ($std_dev > $recent_avg * 0.5) {
            $confidence = 'low';
        }
        
        return array(
            'next_7_days' => round($next_7_days, 2),
            'confidence' => $confidence,
            'daily_average' => round($recent_avg, 2)
        );
    }
    
    /**
     * Get customer segmentation analysis
     */
    public function get_customer_segmentation($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $date_range = $this->get_date_range($period);
        
        $orders = wc_get_orders(array(
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        $customers = array();
        $total_revenue = 0;
        
        // Analyze customer data
        foreach ($orders as $order) {
            $customer_id = $order->get_customer_id();
            $revenue = $order->get_total();
            $total_revenue += $revenue;
            
            if ($customer_id) {
                if (!isset($customers[$customer_id])) {
                    $customers[$customer_id] = array(
                        'orders' => 0,
                        'revenue' => 0,
                        'first_order' => $order->get_date_created(),
                        'last_order' => $order->get_date_created()
                    );
                }
                
                $customers[$customer_id]['orders']++;
                $customers[$customer_id]['revenue'] += $revenue;
                $customers[$customer_id]['last_order'] = $order->get_date_created();
            }
        }
        
        // Segment customers
        $segments = array(
            'new_customers' => 0,
            'returning_customers' => 0,
            'high_value_customers' => 0,
            'vip_customers' => 0
        );
        
        $customer_lifetime_values = array();
        $avg_order_value = count($orders) > 0 ? $total_revenue / count($orders) : 0;
        
        foreach ($customers as $customer_id => $data) {
            $customer_lifetime_values[] = $data['revenue'];
            
            // New vs returning
            if ($data['orders'] == 1) {
                $segments['new_customers']++;
            } else {
                $segments['returning_customers']++;
            }
            
            // High value customers (top 20% by revenue)
            if ($data['revenue'] > ($total_revenue * 0.8 / count($customers))) {
                $segments['high_value_customers']++;
            }
            
            // VIP customers (multiple orders + high AOV)
            if ($data['orders'] >= 3 && $data['revenue'] > ($avg_order_value * 2)) {
                $segments['vip_customers']++;
            }
        }
        
        // Calculate metrics
        $total_customers = count($customers);
        $avg_clv = $total_customers > 0 ? array_sum($customer_lifetime_values) / $total_customers : 0;
        
        return array(
            'total_customers' => $total_customers,
            'segments' => $segments,
            'average_clv' => round($avg_clv, 2),
            'new_customer_rate' => $total_customers > 0 ? round(($segments['new_customers'] / $total_customers) * 100, 1) : 0,
            'returning_customer_rate' => $total_customers > 0 ? round(($segments['returning_customers'] / $total_customers) * 100, 1) : 0,
            'high_value_rate' => $total_customers > 0 ? round(($segments['high_value_customers'] / $total_customers) * 100, 1) : 0,
            'vip_rate' => $total_customers > 0 ? round(($segments['vip_customers'] / $total_customers) * 100, 1) : 0
        );
    }
    
    /**
     * Get product category performance
     */
    public function get_product_category_performance($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $date_range = $this->get_date_range($period);
        
        $orders = wc_get_orders(array(
            'status' => array('wc-completed', 'wc-processing'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        $categories = array();
        
        foreach ($orders as $order) {
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $product = wc_get_product($product_id);
                
                if ($product) {
                    $product_categories = wp_get_post_terms($product_id, 'product_cat');
                    $category_name = !empty($product_categories) ? $product_categories[0]->name : 'Uncategorized';
                    
                    if (!isset($categories[$category_name])) {
                        $categories[$category_name] = array(
                            'quantity' => 0,
                            'revenue' => 0,
                            'orders' => 0,
                            'products' => 0
                        );
                    }
                    
                    $categories[$category_name]['quantity'] += $item->get_quantity();
                    $categories[$category_name]['revenue'] += $item->get_total();
                    $categories[$category_name]['products']++;
                }
            }
        }
        
        // Sort by revenue
        uasort($categories, function($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });
        
        return $categories;
    }
    
    /**
     * Generate smart marketing campaigns based on analytics
     */
    public function generate_smart_campaigns($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $analytics = array(
            'revenue_trends' => $this->get_revenue_trends($period),
            'selling_times' => $this->get_best_selling_times($period),
            'customer_segmentation' => $this->get_customer_segmentation($period),
            'product_categories' => $this->get_product_category_performance($period)
        );
        
        $campaigns = array();
        
        // Weekend Campaign (if weekends perform better)
        if (isset($analytics['selling_times']['best_day']) && 
            in_array($analytics['selling_times']['best_day'], ['Saturday', 'Sunday'])) {
            $campaigns['weekend_campaign'] = $this->generate_weekend_campaign($analytics);
        }
        
        // VIP Customer Campaign
        if (isset($analytics['customer_segmentation']['segments']['vip_customers']) && 
            $analytics['customer_segmentation']['segments']['vip_customers'] > 0) {
            $campaigns['vip_campaign'] = $this->generate_vip_campaign($analytics);
        }
        
        // Category Boost Campaign
        if (isset($analytics['product_categories']) && !empty($analytics['product_categories'])) {
            $campaigns['category_boost'] = $this->generate_category_boost_campaign($analytics);
        }
        
        // Revenue Recovery Campaign (if growth is negative)
        if (isset($analytics['revenue_trends']['growth_rates']['trend']) && 
            $analytics['revenue_trends']['growth_rates']['trend'] === 'down') {
            $campaigns['revenue_recovery'] = $this->generate_revenue_recovery_campaign($analytics);
        }
        
        return $campaigns;
    }
    
    /**
     * Get campaign data with real analytics
     */
    public function get_campaign_data($campaign_id, $period = '30_days') {
        $campaigns = $this->generate_smart_campaigns($period);
        
        if (isset($campaigns[$campaign_id])) {
            $campaign = $campaigns[$campaign_id];
            $campaign['type'] = $campaign_id;
            $campaign['analytics'] = array(
                'revenue_trends' => $this->get_revenue_trends($period),
                'selling_times' => $this->get_best_selling_times($period),
                'customer_segmentation' => $this->get_customer_segmentation($period),
                'product_categories' => $this->get_product_category_performance($period)
            );
            return $campaign;
        }
        
        return false;
    }
    
    /**
     * Generate weekend campaign
     */
    private function generate_weekend_campaign($analytics) {
        $best_day = $analytics['selling_times']['best_day'];
        $weekend_performance = $analytics['selling_times']['weekend_performance'] ?? 0;
        
        return array(
            'name' => 'Weekend Art Sale Campaign',
            'type' => 'weekend_boost',
            'target_audience' => 'All customers',
            'duration' => 'Every weekend',
            'description' => "Boost sales on {$best_day}s when your performance is {$weekend_performance}% higher",
            'strategy' => array(
                'social_media_posts' => 'Post art tutorials and behind-the-scenes content',
                'email_marketing' => 'Send weekend sale announcements on Friday',
                'discount_offers' => 'Offer 15% off on all art supplies',
                'content_focus' => 'Feature popular products and customer success stories'
            ),
            'expected_impact' => 'Increase weekend sales by 25-40%',
            'automation_level' => 'High - Can be fully automated'
        );
    }
    
    /**
     * Generate VIP customer campaign
     */
    private function generate_vip_campaign($analytics) {
        $vip_count = $analytics['customer_segmentation']['segments']['vip_customers'];
        $avg_clv = $analytics['customer_segmentation']['average_clv'];
        
        return array(
            'name' => 'VIP Customer Retention Program',
            'type' => 'vip_retention',
            'target_audience' => 'VIP customers (' . $vip_count . ' customers)',
            'duration' => 'Ongoing',
            'description' => "Retain and upsell your highest-value customers with average CLV of ₦" . number_format($avg_clv),
            'strategy' => array(
                'exclusive_access' => 'Early access to new products and limited editions',
                'personalized_offers' => 'Custom discount codes based on purchase history',
                'priority_support' => 'Dedicated customer service line',
                'loyalty_rewards' => 'Points system for repeat purchases'
            ),
            'expected_impact' => 'Increase VIP customer retention by 30%',
            'automation_level' => 'Medium - Requires some manual setup'
        );
    }
    
    /**
     * Generate category boost campaign
     */
    private function generate_category_boost_campaign($analytics) {
        $top_category = array_keys($analytics['product_categories'])[0];
        $category_revenue = $analytics['product_categories'][$top_category]['revenue'];
        
        return array(
            'name' => 'Top Category Amplification',
            'type' => 'category_boost',
            'target_audience' => 'All customers',
            'duration' => '2-4 weeks',
            'description' => "Amplify sales of your top-performing category: {$top_category} (₦" . number_format($category_revenue) . " revenue)",
            'strategy' => array(
                'bundle_offers' => 'Create bundles featuring top category products',
                'cross_selling' => 'Recommend related products at checkout',
                'content_marketing' => 'Create tutorials using products from this category',
                'influencer_collaboration' => 'Partner with art influencers to showcase products'
            ),
            'expected_impact' => 'Increase category sales by 20-35%',
            'automation_level' => 'Medium - Requires content creation'
        );
    }
    
    /**
     * Generate revenue recovery campaign
     */
    private function generate_revenue_recovery_campaign($analytics) {
        $growth_rate = $analytics['revenue_trends']['growth_rates']['daily_growth_rate'];
        
        return array(
            'name' => 'Revenue Recovery Initiative',
            'type' => 'revenue_recovery',
            'target_audience' => 'All customer segments',
            'duration' => '2-3 weeks',
            'description' => "Recover declining revenue trend (currently {$growth_rate}% growth)",
            'strategy' => array(
                'flash_sales' => 'Time-limited offers on popular products',
                'email_reactivation' => 'Re-engage inactive customers with special offers',
                'social_media_boost' => 'Increase social media advertising spend',
                'customer_feedback' => 'Survey customers to identify improvement areas'
            ),
            'expected_impact' => 'Reverse negative trend and achieve 10-15% growth',
            'automation_level' => 'High - Can be automated with monitoring'
        );
    }
    
    /**
     * Get advanced cart abandonment data with recovery strategies
     */
    public function get_advanced_cart_abandonment($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        // Get basic cart abandonment data
        $basic_data = $this->get_cart_abandonment_data($period);
        
        if (!$basic_data) {
            return false;
        }
        
        // Enhanced analysis
        $advanced_data = array(
            'basic_stats' => $basic_data,
            'recovery_strategies' => $this->get_cart_recovery_strategies(),
            'optimal_timing' => $this->get_optimal_recovery_timing(),
            'personalized_messages' => $this->get_personalized_recovery_messages()
        );
        
        return $advanced_data;
    }
    
    /**
     * Get cart recovery strategies
     */
    private function get_cart_recovery_strategies() {
        return array(
            'immediate' => array(
                'timeframe' => 'Within 1 hour',
                'strategy' => 'Exit-intent popup with 10% discount',
                'message' => 'Wait! Don\'t miss out on these amazing art supplies. Get 10% off your order now!',
                'effectiveness' => 'High - 15-25% recovery rate'
            ),
            'email_1' => array(
                'timeframe' => '2-4 hours later',
                'strategy' => 'First recovery email',
                'message' => 'Your art supplies are waiting! Complete your purchase and start creating.',
                'effectiveness' => 'Medium - 8-12% recovery rate'
            ),
            'email_2' => array(
                'timeframe' => '24 hours later',
                'strategy' => 'Second recovery email with urgency',
                'message' => 'Limited time offer: 15% off your cart + free shipping!',
                'effectiveness' => 'Medium - 6-10% recovery rate'
            ),
            'email_3' => array(
                'timeframe' => '3-7 days later',
                'strategy' => 'Final recovery email',
                'message' => 'Last chance! Your cart expires soon. Don\'t miss out on these art supplies.',
                'effectiveness' => 'Low - 3-5% recovery rate'
            )
        );
    }
    
    /**
     * Get optimal recovery timing
     */
    private function get_optimal_recovery_timing() {
        return array(
            'best_hours' => array('10:00 AM', '2:00 PM', '7:00 PM'),
            'best_days' => array('Tuesday', 'Wednesday', 'Thursday'),
            'avoid_times' => array('Early morning (6-8 AM)', 'Late night (10 PM+)'),
            'reasoning' => 'Based on customer engagement patterns and email open rates'
        );
    }
    
    /**
     * Get personalized recovery messages
     */
    private function get_personalized_recovery_messages() {
        return array(
            'new_customer' => 'Welcome to our art community! Complete your first order and get 15% off.',
            'returning_customer' => 'We missed you! Complete your order and enjoy our loyalty discount.',
            'vip_customer' => 'Your VIP status gives you exclusive access. Don\'t miss this opportunity.',
            'high_value_cart' => 'Your cart value qualifies for free shipping! Complete your order now.',
            'weekend_abandonment' => 'Weekend artists, don\'t let your inspiration wait! Complete your order.'
        );
    }
    
    /**
     * Generate product bundle recommendations
     */
    public function generate_bundle_recommendations($period = '30_days') {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $top_products = $this->get_top_products(10, $period);
        $categories = $this->get_product_category_performance($period);
        
        $bundles = array();
        
        // Create bundles based on popular combinations
        if (count($top_products) >= 3) {
            // Get first 3 products with their IDs preserved
            $starter_products = array();
            $count = 0;
            foreach ($top_products as $product_id => $product_data) {
                if ($count >= 3) break;
                $starter_products[] = array_merge($product_data, array('id' => $product_id));
                $count++;
            }
            
            $bundles['starter_bundle'] = array(
                'name' => 'Art Starter Bundle',
                'description' => 'Perfect for beginners - includes all essentials',
                'products' => $starter_products,
                'discount' => '20%',
                'price_savings' => 'Save ₦2,500',
                'target_audience' => 'New customers',
                'conversion_potential' => 'High'
            );
            
            if (count($top_products) >= 5) {
                // Get products 3-5 with their IDs preserved
                $professional_products = array();
                $count = 0;
                $skip = 2; // Skip first 2 products
                foreach ($top_products as $product_id => $product_data) {
                    if ($count < $skip) {
                        $count++;
                        continue;
                    }
                    if ($count >= $skip + 3) break;
                    $professional_products[] = array_merge($product_data, array('id' => $product_id));
                    $count++;
                }
                
                $bundles['professional_bundle'] = array(
                    'name' => 'Professional Artist Bundle',
                    'description' => 'For serious artists - premium quality supplies',
                    'products' => $professional_products,
                    'discount' => '15%',
                    'price_savings' => 'Save ₦4,000',
                    'target_audience' => 'Professional artists',
                    'conversion_potential' => 'Medium'
                );
            }
        }
        
        // Category-based bundles
        if (!empty($categories)) {
            $top_category = array_keys($categories)[0];
            
            // Get first 4 products with their IDs preserved
            $category_products = array();
            $count = 0;
            foreach ($top_products as $product_id => $product_data) {
                if ($count >= 4) break;
                $category_products[] = array_merge($product_data, array('id' => $product_id));
                $count++;
            }
            
            $bundles['category_special'] = array(
                'name' => $top_category . ' Special Bundle',
                'description' => 'Everything you need for ' . strtolower($top_category),
                'products' => $category_products,
                'discount' => '25%',
                'price_savings' => 'Save ₦3,000',
                'target_audience' => 'Category enthusiasts',
                'conversion_potential' => 'High'
            );
        }
        
        return $bundles;
    }
    
    /**
     * Actually activate a marketing campaign
     */
    public function activate_real_campaign($campaign_id, $campaign_data) {
        if (!class_exists('WooCommerce')) {
            return array('success' => false, 'message' => 'WooCommerce not active');
        }
        
        $results = array();
        
        switch ($campaign_data['type']) {
            case 'weekend_boost':
                $results = $this->activate_weekend_campaign($campaign_data);
                break;
            case 'vip_retention':
                $results = $this->activate_vip_campaign($campaign_data);
                break;
            case 'category_boost':
                $results = $this->activate_category_campaign($campaign_data);
                break;
            case 'revenue_recovery':
                $results = $this->activate_recovery_campaign($campaign_data);
                break;
            default:
                return array('success' => false, 'message' => 'Unknown campaign type');
        }
        
        // Store campaign activation with detailed status
        $active_campaigns = get_option('arteasy_active_campaigns', array());
        $active_campaigns[$campaign_id] = array(
            'activated_at' => current_time('mysql'),
            'status' => 'active',
            'type' => $campaign_data['type'],
            'results' => $results,
            'campaign_name' => $campaign_data['name'] ?? ucfirst(str_replace('_', ' ', $campaign_id)),
            'target_customers' => $campaign_data['settings']['target_customers'] ?? 'all',
            'discount_amount' => $campaign_data['settings']['discount_amount'] ?? 0,
            'discount_type' => $campaign_data['settings']['discount_type'] ?? 'percent',
            'expiry_days' => $campaign_data['settings']['expiry_days'] ?? 7,
            'usage_limit' => $campaign_data['settings']['usage_limit'] ?? 100,
            'email_sent' => $campaign_data['settings']['send_email'] ?? false,
            'coupons_created' => count($results),
            'last_updated' => current_time('mysql')
        );
        update_option('arteasy_active_campaigns', $active_campaigns);
        
        // Update activation history
        $this->update_activation_history('campaign', $campaign_id, $active_campaigns[$campaign_id]);
        
        return array('success' => true, 'message' => 'Campaign activated successfully', 'results' => $results);
    }
    
    /**
     * Activate weekend campaign - creates real coupons
     */
    private function activate_weekend_campaign($campaign_data) {
        $results = array();
        $settings = $campaign_data['settings'] ?? array();
        
        // Use custom settings or defaults
        $coupon_prefix = $settings['coupon_code'] ?? 'WEEKEND';
        $discount_type = $settings['discount_type'] ?? 'percent';
        $discount_amount = $settings['discount_amount'] ?? 15;
        $usage_limit = $settings['usage_limit'] ?? 1000;
        $expiry_days = $settings['expiry_days'] ?? 7;
        $minimum_amount = $settings['minimum_amount'] ?? 5000;
        $target_customers = $settings['target_customers'] ?? 'all';
        
        // Create weekend discount coupon
        $coupon_code = $coupon_prefix . '_' . date('Ymd');
        $coupon_id = $this->create_coupon($coupon_code, array(
            'discount_type' => $discount_type,
            'coupon_amount' => $discount_amount,
            'description' => 'Weekend Art Sale - ' . $discount_amount . ($discount_type === 'percent' ? '%' : '₦') . ' off all art supplies',
            'usage_limit' => $usage_limit,
            'expiry_date' => date('Y-m-d', strtotime('+' . $expiry_days . ' days')),
            'minimum_amount' => $minimum_amount,
            'product_categories' => $this->get_art_categories(),
            'customer_email' => $this->get_target_customer_emails($target_customers)
        ));
        
        if ($coupon_id) {
            $results['coupon_created'] = array(
                'code' => $coupon_code,
                'id' => $coupon_id,
                'discount' => '15% off',
                'expires' => date('Y-m-d', strtotime('+7 days'))
            );
        }
        
        // Schedule weekend email
        $this->schedule_weekend_email($coupon_code);
        
        return $results;
    }
    
    /**
     * Activate VIP campaign - creates exclusive coupons
     */
    private function activate_vip_campaign($campaign_data) {
        $results = array();
        
        // Create VIP exclusive coupon
        $coupon_code = 'VIP20_' . date('Ymd');
        $coupon_id = $this->create_coupon($coupon_code, array(
            'discount_type' => 'percent',
            'coupon_amount' => 20,
            'description' => 'VIP Customer Exclusive - 20% off',
            'usage_limit' => 100,
            'expiry_date' => date('Y-m-d', strtotime('+30 days')),
            'minimum_amount' => 10000,
            'customer_email' => $this->get_vip_customer_emails()
        ));
        
        if ($coupon_id) {
            $results['vip_coupon'] = array(
                'code' => $coupon_code,
                'id' => $coupon_id,
                'discount' => '20% off',
                'target' => 'VIP customers only'
            );
        }
        
        return $results;
    }
    
    /**
     * Activate category boost campaign
     */
    private function activate_category_campaign($campaign_data) {
        $results = array();
        
        // Get top category from analytics
        $categories = $this->get_product_category_performance('30_days');
        if (!empty($categories)) {
            $top_category = array_keys($categories)[0];
            
            // Create category-specific coupon
            $coupon_code = 'CATEGORY25_' . strtoupper(substr($top_category, 0, 3)) . '_' . date('Ymd');
            $coupon_id = $this->create_coupon($coupon_code, array(
                'discount_type' => 'percent',
                'coupon_amount' => 25,
                'description' => "{$top_category} Special - 25% off",
                'usage_limit' => 500,
                'expiry_date' => date('Y-m-d', strtotime('+14 days')),
                'product_categories' => array($top_category)
            ));
            
            if ($coupon_id) {
                $results['category_coupon'] = array(
                    'code' => $coupon_code,
                    'id' => $coupon_id,
                    'discount' => '25% off',
                    'category' => $top_category
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Activate revenue recovery campaign
     */
    private function activate_recovery_campaign($campaign_data) {
        $results = array();
        
        // Create flash sale coupon
        $coupon_code = 'FLASH30_' . date('Ymd');
        $coupon_id = $this->create_coupon($coupon_code, array(
            'discount_type' => 'percent',
            'coupon_amount' => 30,
            'description' => 'Flash Sale - 30% off everything',
            'usage_limit' => 200,
            'expiry_date' => date('Y-m-d', strtotime('+3 days')),
            'minimum_amount' => 3000
        ));
        
        if ($coupon_id) {
            $results['flash_sale'] = array(
                'code' => $coupon_code,
                'id' => $coupon_id,
                'discount' => '30% off',
                'duration' => '3 days only'
            );
        }
        
        // Send reactivation emails to inactive customers
        $this->send_reactivation_emails($coupon_code);
        
        return $results;
    }
    
    /**
     * Create a WooCommerce coupon
     */
    private function create_coupon($code, $args = array()) {
        $coupon = new WC_Coupon();
        $coupon->set_code($code);
        $coupon->set_discount_type($args['discount_type'] ?? 'percent');
        $coupon->set_amount($args['coupon_amount'] ?? 10);
        $coupon->set_description($args['description'] ?? '');
        $coupon->set_usage_limit($args['usage_limit'] ?? 100);
        $coupon->set_usage_limit_per_user(1);
        $coupon->set_limit_usage_to_x_items(0);
        $coupon->set_free_shipping(false);
        $coupon->set_minimum_amount($args['minimum_amount'] ?? 0);
        
        if (isset($args['expiry_date'])) {
            $coupon->set_date_expires(strtotime($args['expiry_date']));
        }
        
        if (isset($args['product_categories']) && is_array($args['product_categories'])) {
            $coupon->set_product_categories($args['product_categories']);
        }
        
        if (isset($args['customer_email']) && is_array($args['customer_email'])) {
            $coupon->set_email_restrictions($args['customer_email']);
        }
        
        $coupon_id = $coupon->save();
        
        return $coupon_id ? $coupon_id : false;
    }
    
    /**
     * Actually create a product bundle
     */
    public function create_real_bundle($bundle_id, $bundle_data) {
        if (!class_exists('WooCommerce')) {
            return array('success' => false, 'message' => 'WooCommerce not active');
        }
        
        $results = array();
        
        // Create bundle product
        $bundle_product = new WC_Product_Simple();
        $bundle_product->set_name($bundle_data['name']);
        $bundle_product->set_description($bundle_data['description']);
        $bundle_product->set_short_description($bundle_data['description']);
        $bundle_product->set_status('publish');
        $bundle_product->set_catalog_visibility('visible');
        $bundle_product->set_featured(false);
        $bundle_product->set_virtual(false);
        $bundle_product->set_downloadable(false);
        $bundle_product->set_sold_individually(false);
        $bundle_product->set_tax_status('taxable');
        $bundle_product->set_tax_class('');
        $bundle_product->set_manage_stock(true);
        $bundle_product->set_stock_quantity(50);
        $bundle_product->set_backorders('no');
        $bundle_product->set_weight('');
        $bundle_product->set_length('');
        $bundle_product->set_width('');
        $bundle_product->set_height('');
        $bundle_product->set_shipping_class_id(0);
        $bundle_product->set_reviews_allowed(true);
        $bundle_product->set_purchase_note('');
        $bundle_product->set_menu_order(0);
        
        // Calculate bundle price
        $total_price = 0;
        $discount_amount = 0;
        
        if (isset($bundle_data['products']) && is_array($bundle_data['products'])) {
            foreach ($bundle_data['products'] as $product) {
                $total_price += $product['revenue'] ?? 0;
            }
            
            // Apply discount
            $discount_percent = intval(str_replace('%', '', $bundle_data['discount'] ?? '20'));
            $discount_amount = ($total_price * $discount_percent) / 100;
            $bundle_price = $total_price - $discount_amount;
            
            $bundle_product->set_regular_price($bundle_price);
            $bundle_product->set_sale_price($bundle_price);
        }
        
        // Save the product FIRST to get the ID
        $bundle_product_id = $bundle_product->save();
        
        // Now create bundle relationships AFTER we have the product ID
        if ($bundle_product_id && isset($bundle_data['products']) && is_array($bundle_data['products'])) {
            // Create bundle relationships (if WooCommerce Product Bundles plugin is active)
            if (class_exists('WC_Product_Bundle')) {
                $this->create_bundle_relationships($bundle_product_id, $bundle_data['products']);
            } else {
                // Fallback: Create a grouped product or add products to description
                $this->create_fallback_bundle($bundle_product_id, $bundle_data['products']);
            }
        }
        
        if ($bundle_product_id) {
            $results['bundle_created'] = array(
                'id' => $bundle_product_id,
                'name' => $bundle_data['name'],
                'price' => $bundle_price,
                'original_price' => $total_price,
                'savings' => $discount_amount,
                'discount_percent' => $discount_percent
            );
            
            // Store bundle creation
            $created_bundles = get_option('arteasy_created_bundles', array());
            $created_bundles[$bundle_id] = array(
                'created_at' => current_time('mysql'),
                'status' => 'created',
                'product_id' => $bundle_product_id,
                'bundle_data' => $bundle_data
            );
            update_option('arteasy_created_bundles', $created_bundles);
        }
        
        return array('success' => true, 'message' => 'Bundle created successfully', 'results' => $results);
    }
    
    /**
     * Update activation history for dashboard tracking
     */
    private function update_activation_history($type, $id, $data) {
        $history = get_option('arteasy_activation_history', array());
        
        $history_entry = array(
            'type' => $type,
            'id' => $id,
            'timestamp' => current_time('mysql'),
            'data' => $data,
            'status' => 'success'
        );
        
        // Keep only last 50 entries
        array_unshift($history, $history_entry);
        $history = array_slice($history, 0, 50);
        
        update_option('arteasy_activation_history', $history);
    }
    
    /**
     * Get activation status for dashboard
     */
    public function get_activation_status() {
        $active_campaigns = get_option('arteasy_active_campaigns', array());
        $created_bundles = get_option('arteasy_created_bundles', array());
        $cart_recovery_active = get_option('arteasy_cart_recovery_active', false);
        
        return array(
            'active_campaigns' => count($active_campaigns),
            'created_bundles' => count($created_bundles),
            'cart_recovery_active' => $cart_recovery_active,
            'campaigns' => $active_campaigns,
            'bundles' => $created_bundles,
            'last_updated' => current_time('mysql')
        );
    }
    
    /**
     * Create bundle relationships using WooCommerce Product Bundles plugin
     */
    private function create_bundle_relationships($bundle_product_id, $products) {
        if (!class_exists('WC_Product_Bundle')) {
            return false;
        }
        
        $bundle_product = wc_get_product($bundle_product_id);
        if (!$bundle_product) {
            return false;
        }
        
        // Convert to bundle product type if possible
        wp_set_object_terms($bundle_product_id, 'bundle', 'product_type');
        
        // Reload the product as bundle type
        $bundle_product = wc_get_product($bundle_product_id);
        if (!$bundle_product || !$bundle_product->is_type('bundle')) {
            // If conversion failed, fall back to grouped product
            return $this->create_fallback_bundle($bundle_product_id, $products);
        }
        
        $bundle_items = array();
        foreach ($products as $index => $product) {
            $bundle_items[] = array(
                'product_id' => $product['id'] ?? 0,
                'quantity' => 1,
                'title' => $product['name'] ?? 'Product ' . ($index + 1),
                'description' => $product['description'] ?? '',
                'optional' => false,
                'discount' => 0
            );
        }
        
        $bundle_product->set_bundled_data_items($bundle_items);
        $bundle_product->save();
        
        return true;
    }
    
    /**
     * Create fallback bundle (grouped product or enhanced description)
     */
    private function create_fallback_bundle($bundle_product_id, $products) {
        $bundle_product = wc_get_product($bundle_product_id);
        if (!$bundle_product) {
            return false;
        }
        
        // Create enhanced description with included products
        $description = $bundle_product->get_description();
        $description .= "\n\n<b>This bundle includes:</b>\n";
        
        $valid_product_ids = array();
        foreach ($products as $product) {
            $product_name = $product['name'] ?? 'Product';
            $product_price = $product['revenue'] ?? 0;
            $description .= "• " . $product_name . " - ₦" . number_format($product_price) . "\n";
            
            // Only add valid product IDs
            if (isset($product['id']) && $product['id'] > 0) {
                $valid_product_ids[] = $product['id'];
            }
        }
        
        $bundle_product->set_description($description);
        
        // Try to create as grouped product if we have valid product IDs
        if (!empty($valid_product_ids) && class_exists('WC_Product_Grouped')) {
            $this->convert_to_grouped_product($bundle_product_id, $valid_product_ids);
        }
        
        $bundle_product->save();
        
        return true;
    }
    
    /**
     * Convert bundle to grouped product
     */
    private function convert_to_grouped_product($bundle_product_id, $product_ids) {
        if (empty($product_ids)) {
            return false;
        }
        
        // Update the bundle product to be a grouped product
        wp_set_object_terms($bundle_product_id, 'grouped', 'product_type');
        
        // Set grouped product data
        $grouped_product = wc_get_product($bundle_product_id);
        if ($grouped_product) {
            $grouped_product->set_children($product_ids);
            $grouped_product->save();
            return true;
        }
        
        return false;
    }
    
    /**
     * Set up real cart recovery system
     */
    public function setup_real_cart_recovery() {
        // Add cart abandonment tracking
        add_action('wp_footer', array($this, 'add_cart_abandonment_tracking'));
        add_action('wp_ajax_arteasy_track_cart_abandonment', array($this, 'track_cart_abandonment'));
        add_action('wp_ajax_nopriv_arteasy_track_cart_abandonment', array($this, 'track_cart_abandonment'));
        
        // Schedule recovery emails
        $this->schedule_recovery_emails();
        
        // Mark cart recovery as active
        update_option('arteasy_cart_recovery_active', true);
        
        // Update activation history
        $this->update_activation_history('cart_recovery', 'system', array(
            'activated_at' => current_time('mysql'),
            'status' => 'active',
            'tracking_enabled' => true,
            'email_automation' => true
        ));
        
        return array('success' => true, 'message' => 'Cart recovery system activated');
    }

    /**
     * Inject simple frontend tracker to record abandoned carts (guests + logged-in)
     */
    public function add_cart_abandonment_tracking() {
        if (!function_exists('wc')) {
            return;
        }
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('arteasy_track_cart');
        echo "\n<script>\n(function(){\n  if (window.arteasyCartTrackerLoaded) return;\n  window.arteasyCartTrackerLoaded = true;\n  var lastSent = 0;\n  function sendCart(status){\n    var now = Date.now();\n    if (now - lastSent < 30000) return;\n    lastSent = now;\n    var data = new FormData();\n    data.append('action','arteasy_track_cart_abandonment');\n    data.append('nonce','" . esc_js($nonce) . "');\n    data.append('status', status||'active');\n    fetch('" . esc_url($ajax_url) . "',{method:'POST',credentials:'same-origin',body:data});\n  }\n  var idleTimer;\n  function resetIdle(){\n    clearTimeout(idleTimer);\n    idleTimer = setTimeout(function(){ sendCart('idle'); }, 60000);\n  }\n  ['mousemove','keydown','scroll','touchstart'].forEach(function(ev){ window.addEventListener(ev, resetIdle, {passive:true}); });\n  resetIdle();\n  window.addEventListener('beforeunload', function(){ sendCart('exit'); });\n})();\n</script>\n";
    }
    
    /**
     * Track cart abandonment
     */
    public function track_cart_abandonment() {
        // Allow both logged-in and guests
        $user_id = get_current_user_id();
        $is_guest = $user_id === 0;
        $guest_id = isset($_COOKIE['arteasy_guest_id']) ? sanitize_text_field($_COOKIE['arteasy_guest_id']) : '';
        if ($is_guest && empty($guest_id)) {
            $guest_id = wp_generate_uuid4();
            setcookie('arteasy_guest_id', $guest_id, time()+3600*24*7, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
        }

        $email = '';
        if (!$is_guest) {
            $email = wp_get_current_user()->user_email;
        } else if (isset($_POST['email'])) {
            $email = sanitize_email(wp_unslash($_POST['email']));
        }

        $cart = function_exists('WC') && WC()->cart ? WC()->cart->get_cart() : array();
        $total = function_exists('WC') && WC()->cart ? WC()->cart->get_total('raw') : 0;

        $cart_data = array(
            'user_id' => $user_id,
            'guest_id' => $guest_id,
            'cart_items' => $cart,
            'cart_total' => floatval($total),
            'timestamp' => current_time('mysql'),
            'email' => $email,
            'last_email' => '',
        );

        $key = $is_guest ? ('guest:' . $guest_id) : ('user:' . $user_id);
        $abandoned_carts = get_option('arteasy_abandoned_carts', array());
        $abandoned_carts[$key] = $cart_data;
        update_option('arteasy_abandoned_carts', $abandoned_carts);

        wp_send_json_success('Cart tracked');
    }
    
    /**
     * Send real recovery emails
     */
    public function send_recovery_email($user_id, $email_type = 'first') {
        $abandoned_carts = get_option('arteasy_abandoned_carts', array());
        
        if (!isset($abandoned_carts[$user_id])) {
            return false;
        }
        
        $cart_data = $abandoned_carts[$user_id];
        $user = get_user_by('id', $user_id);
        
        $subject = '';
        $message = '';
        $coupon_code = '';
        
        switch ($email_type) {
            case 'first':
                $subject = 'Your Art Supplies Are Waiting!';
                $coupon_code = 'RECOVER10_' . date('Ymd');
                $message = "Hi {$user->display_name},\n\nYour art supplies are waiting! Complete your purchase and start creating.\n\nUse code {$coupon_code} for 10% off your order.";
                break;
            case 'second':
                $subject = 'Limited Time Offer: 15% Off + Free Shipping!';
                $coupon_code = 'RECOVER15_' . date('Ymd');
                $message = "Hi {$user->display_name},\n\nDon't miss out! Complete your order now and get 15% off plus free shipping.\n\nUse code {$coupon_code} - expires in 24 hours.";
                break;
            case 'final':
                $subject = 'Last Chance - Your Cart Expires Soon!';
                $coupon_code = 'RECOVER20_' . date('Ymd');
                $message = "Hi {$user->display_name},\n\nThis is your final chance! Your cart expires soon.\n\nUse code {$coupon_code} for 20% off - don't miss out on these art supplies.";
                break;
        }
        
        // Create recovery coupon
        if ($coupon_code) {
            $this->create_coupon($coupon_code, array(
                'discount_type' => 'percent',
                'coupon_amount' => intval(str_replace('RECOVER', '', explode('_', $coupon_code)[0])),
                'description' => "Cart Recovery - {$email_type} email",
                'usage_limit' => 1,
                'expiry_date' => date('Y-m-d', strtotime('+7 days')),
                'customer_email' => array($user->user_email)
            ));
        }
        
        // Send email with detailed error logging
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        error_log("Arteasy Recovery: Attempting to send email to user {$user_id} ({$user->user_email}) - Subject: {$subject}");
        
        if (!function_exists('wp_mail')) {
            error_log("Arteasy Recovery: wp_mail() function not available!");
            return false;
        }
        
        $email_error = null;
        add_filter('wp_mail_failed', function($wp_error) use (&$email_error) {
            $email_error = $wp_error->get_error_message();
            error_log("Arteasy Recovery: wp_mail failed - " . $wp_error->get_error_message());
            return $wp_error;
        });
        
        $sent = wp_mail($user->user_email, $subject, $message, $headers);
        
        if (!$sent) {
            error_log("Arteasy Recovery: wp_mail returned FALSE for {$user->user_email}");
            if ($email_error) {
                error_log("Arteasy Recovery: Email error captured: {$email_error}");
            }
        } else {
            error_log("Arteasy Recovery: ✅ Email sent successfully to {$user->user_email}");
        }
        
        return $sent;
    }

    /**
     * Process abandoned carts (cron handler)
     * @param bool $force_immediate If true, send emails immediately regardless of elapsed time (for manual trigger)
     */
    public function process_abandoned_carts($force_immediate = false) {
        $abandoned_carts = get_option('arteasy_abandoned_carts', array());
        $emails_sent = 0;
        $errors = array();

        // Check if test mode is enabled (5 mins instead of 1 hour)
        $test_mode = get_option('arteasy_cart_recovery_test_mode', false);
        
        // Timing: test mode = 5 mins, production = 1 hour
        // If force_immediate, use 0 delay (send immediately)
        $first_email_delay = $force_immediate ? 0 : ($test_mode ? (5 * 60) : (1 * 3600));
        $second_email_delay = $force_immediate ? 0 : ($test_mode ? (10 * 60) : (24 * 3600));
        $final_email_delay = $force_immediate ? 0 : ($test_mode ? (15 * 60) : (48 * 3600));

        error_log("Arteasy Recovery: Processing carts. Force: " . ($force_immediate ? 'YES' : 'NO') . ", Test Mode: " . ($test_mode ? 'YES' : 'NO'));
        error_log("Arteasy Recovery: Tracked carts count: " . count($abandoned_carts));

        foreach ($abandoned_carts as $key => $data) {
            $ts = isset($data['timestamp']) ? strtotime($data['timestamp']) : time();
            $elapsed = time() - $ts;
            $last_email = $data['last_email'] ?? '';

            error_log("Arteasy Recovery: Cart {$key} - Elapsed: {$elapsed}s, Last email: {$last_email}");

            // Send emails based on elapsed time (or force immediate)
            if ($elapsed >= $final_email_delay && $last_email !== 'final') {
                if (!empty($data['user_id'])) { 
                    $sent = $this->send_recovery_email(intval($data['user_id']), 'final');
                    if ($sent) $emails_sent++;
                    else $errors[] = "Failed to send final email to user {$data['user_id']}";
                } elseif (!empty($data['email'])) {
                    $sent = $this->send_recovery_email_to_guest($data['email'], 'final', $data);
                    if ($sent) $emails_sent++;
                    else $errors[] = "Failed to send final email to {$data['email']}";
                }
                $data['last_email'] = 'final';
            } elseif ($elapsed >= $second_email_delay && $last_email !== 'second') {
                if (!empty($data['user_id'])) { 
                    $sent = $this->send_recovery_email(intval($data['user_id']), 'second');
                    if ($sent) $emails_sent++;
                    else $errors[] = "Failed to send second email to user {$data['user_id']}";
                } elseif (!empty($data['email'])) {
                    $sent = $this->send_recovery_email_to_guest($data['email'], 'second', $data);
                    if ($sent) $emails_sent++;
                    else $errors[] = "Failed to send second email to {$data['email']}";
                }
                $data['last_email'] = 'second';
            } elseif ($elapsed >= $first_email_delay && $last_email !== 'first') {
                if (!empty($data['user_id'])) { 
                    $sent = $this->send_recovery_email(intval($data['user_id']), 'first');
                    if ($sent) $emails_sent++;
                    else $errors[] = "Failed to send first email to user {$data['user_id']}";
                } elseif (!empty($data['email'])) {
                    $sent = $this->send_recovery_email_to_guest($data['email'], 'first', $data);
                    if ($sent) $emails_sent++;
                    else $errors[] = "Failed to send first email to {$data['email']}";
                }
                $data['last_email'] = 'first';
            }

            $abandoned_carts[$key] = $data;
        }

        update_option('arteasy_abandoned_carts', $abandoned_carts);
        
        // Also process abandoned WooCommerce orders (this is where the 3 orders are!)
        $order_results = $this->process_abandoned_orders($force_immediate);
        $emails_sent += $order_results['sent'];
        $errors = array_merge($errors, $order_results['errors']);
        
        error_log("Arteasy Recovery: Total emails sent: {$emails_sent}, Errors: " . count($errors));
        
        return array(
            'success' => true,
            'emails_sent' => $emails_sent,
            'errors' => $errors,
            'message' => "Processed recovery. Sent {$emails_sent} email(s)."
        );
    }
    
    /**
     * Send recovery email to guest (by email address)
     */
    private function send_recovery_email_to_guest($email, $email_type, $cart_data) {
        if (empty($email)) return false;
        
        $customer_name = !empty($cart_data['email']) ? explode('@', $email)[0] : 'Customer';
        
        $subject = '';
        $message = '';
        $coupon_code = '';
        
        switch ($email_type) {
            case 'first':
                $subject = 'Your Art Supplies Are Waiting!';
                $coupon_code = 'RECOVER10_' . date('Ymd');
                $message = "Hi {$customer_name},\n\nYour art supplies are waiting! Complete your purchase and start creating.\n\nUse code {$coupon_code} for 10% off your order.";
                break;
            case 'second':
                $subject = 'Limited Time Offer: 15% Off + Free Shipping!';
                $coupon_code = 'RECOVER15_' . date('Ymd');
                $message = "Hi {$customer_name},\n\nDon't miss out! Complete your order now and get 15% off plus free shipping.\n\nUse code {$coupon_code} - expires in 24 hours.";
                break;
            case 'final':
                $subject = 'Last Chance - Your Cart Expires Soon!';
                $coupon_code = 'RECOVER20_' . date('Ymd');
                $message = "Hi {$customer_name},\n\nThis is your final chance! Your cart expires soon.\n\nUse code {$coupon_code} for 20% off - don't miss out on these art supplies.";
                break;
        }
        
        // Create recovery coupon
        if ($coupon_code) {
            $this->create_coupon($coupon_code, array(
                'discount_type' => 'percent',
                'coupon_amount' => intval(str_replace('RECOVER', '', explode('_', $coupon_code)[0])),
                'description' => "Cart Recovery - {$email_type} email (Guest)",
                'usage_limit' => 1,
                'expiry_date' => date('Y-m-d', strtotime('+7 days')),
                'customer_email' => array($email)
            ));
        }
        
        // Send email with detailed error logging
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $attachments = array();
        
        error_log("Arteasy Recovery: Attempting to send email to {$email} - Subject: {$subject}");
        
        // Check if wp_mail function exists
        if (!function_exists('wp_mail')) {
            error_log("Arteasy Recovery: wp_mail() function not available!");
            return false;
        }
        
        // Add filter to capture email errors
        $email_error = null;
        add_filter('wp_mail_failed', function($wp_error) use (&$email_error) {
            $email_error = $wp_error->get_error_message();
            error_log("Arteasy Recovery: wp_mail failed - " . $wp_error->get_error_message());
            return $wp_error;
        });
        
        $sent = wp_mail($email, $subject, $message, $headers, $attachments);
        
        if (!$sent) {
            error_log("Arteasy Recovery: wp_mail returned FALSE for {$email}");
            if ($email_error) {
                error_log("Arteasy Recovery: Email error captured: {$email_error}");
            }
        } else {
            error_log("Arteasy Recovery: ✅ Email sent successfully to {$email}");
        }
        
        return $sent;
    }
    
    /**
     * Process abandoned WooCommerce orders (pending/on-hold)
     * @param bool $force_immediate If true, send emails immediately regardless of elapsed time
     */
    private function process_abandoned_orders($force_immediate = false) {
        if (!class_exists('WooCommerce')) {
            return array('sent' => 0, 'errors' => array());
        }
        
        $date_range = $this->get_date_range('30_days');
        $orders = wc_get_orders(array(
            'status' => array('pending', 'on-hold'),
            'date_created' => $date_range['start'] . '...' . $date_range['end'],
            'limit' => -1
        ));
        
        error_log("Arteasy Recovery: Found " . count($orders) . " abandoned WooCommerce orders");
        
        $test_mode = get_option('arteasy_cart_recovery_test_mode', false);
        $first_email_delay = $force_immediate ? 0 : ($test_mode ? (5 * 60) : (1 * 3600));
        
        $emails_sent = 0;
        $errors = array();
        
        foreach ($orders as $order) {
            $order_date = $order->get_date_created();
            $elapsed = time() - $order_date->getTimestamp();
            $email = $order->get_billing_email();
            $order_id = $order->get_id();
            
            error_log("Arteasy Recovery: Order #{$order_id} - Email: {$email}, Elapsed: {$elapsed}s");
            
            // Skip if no email
            if (empty($email)) {
                error_log("Arteasy Recovery: Order #{$order_id} skipped - no email");
                continue;
            }
            
            // Check if already sent recovery email (stored in order meta)
            $recovery_sent = get_post_meta($order_id, '_arteasy_recovery_sent', true);
            
            error_log("Arteasy Recovery: Order #{$order_id} - Recovery sent: {$recovery_sent}, Elapsed: {$elapsed}s, Delay needed: {$first_email_delay}s");
            
            // Send first email if enough time passed (or forced) and not sent yet
            if ($elapsed >= $first_email_delay && $recovery_sent !== 'first') {
                $customer_name = $order->get_billing_first_name() ?: explode('@', $email)[0];
                
                // Test email configuration first
                error_log("Arteasy Recovery: Testing email configuration for Order #{$order_id}...");
                error_log("Arteasy Recovery: PHP mail() function exists: " . (function_exists('mail') ? 'YES' : 'NO'));
                error_log("Arteasy Recovery: wp_mail() function exists: " . (function_exists('wp_mail') ? 'YES' : 'NO'));
                
                // Check if SMTP is configured
                if (defined('SMTP_HOST')) {
                    error_log("Arteasy Recovery: SMTP configured - Host: " . SMTP_HOST);
                } else {
                    error_log("Arteasy Recovery: No SMTP configuration found - using PHP mail()");
                }
                
                $sent = $this->send_recovery_email_to_guest($email, 'first', array('email' => $email));
                
                if ($sent) {
                    update_post_meta($order_id, '_arteasy_recovery_sent', 'first');
                    $emails_sent++;
                    error_log("Arteasy Recovery: ✅ Sent recovery email to Order #{$order_id} ({$email})");
                } else {
                    // Try to capture PHPMailer error
                    global $phpmailer;
                    $mail_error = '';
                    if (isset($phpmailer) && is_object($phpmailer) && isset($phpmailer->ErrorInfo)) {
                        $mail_error = $phpmailer->ErrorInfo;
                    }
                    
                    $error_msg = "Failed to send email to Order #{$order_id} ({$email})";
                    if (!empty($mail_error)) {
                        $error_msg .= " - " . $mail_error;
                        error_log("Arteasy Recovery: PHPMailer error: " . $mail_error);
                    } else {
                        error_log("Arteasy Recovery: wp_mail returned false but no error captured. Check WordPress email configuration in wp-config.php or use an SMTP plugin.");
                    }
                    $errors[] = $error_msg;
                }
            } else {
                if ($recovery_sent === 'first') {
                    error_log("Arteasy Recovery: Order #{$order_id} already sent recovery email");
                } else {
                    error_log("Arteasy Recovery: Order #{$order_id} - Not enough time passed ({$elapsed}s < {$first_email_delay}s)");
                }
            }
        }
        
        return array('sent' => $emails_sent, 'errors' => $errors);
    }
    
    /**
     * Helper methods
     */
    private function get_art_categories() {
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));
        
        return array_map(function($cat) { return $cat->term_id; }, $categories);
    }
    
    private function get_vip_customer_emails() {
        // Get customers with high CLV
        $orders = wc_get_orders(array(
            'status' => array('wc-completed'),
            'limit' => -1,
            'date_created' => date('Y-m-d', strtotime('-90 days')) . '...' . date('Y-m-d')
        ));
        
        $customer_totals = array();
        foreach ($orders as $order) {
            $email = $order->get_billing_email();
            if ($email) {
                $customer_totals[$email] = ($customer_totals[$email] ?? 0) + $order->get_total();
            }
        }
        
        // Get top 20% of customers
        arsort($customer_totals);
        $top_customers = array_slice($customer_totals, 0, max(1, floor(count($customer_totals) * 0.2)), true);
        
        return array_keys($top_customers);
    }
    
    /**
     * Get customer emails based on target type
     */
    private function get_target_customer_emails($target_type) {
        switch ($target_type) {
            case 'vip':
                return $this->get_vip_customer_emails();
            case 'new':
                return $this->get_new_customer_emails();
            case 'returning':
                return $this->get_returning_customer_emails();
            case 'inactive':
                return $this->get_inactive_customers();
            case 'all':
            default:
                return array(); // No email restrictions
        }
    }
    
    private function get_new_customer_emails() {
        // Get customers who made their first order in the last 30 days
        $orders = wc_get_orders(array(
            'status' => array('wc-completed'),
            'limit' => -1,
            'date_created' => date('Y-m-d', strtotime('-30 days')) . '...' . date('Y-m-d')
        ));
        
        $new_customers = array();
        foreach ($orders as $order) {
            $email = $order->get_billing_email();
            if ($email) {
                // Check if this is their first order
                $customer_orders = wc_get_orders(array(
                    'status' => array('wc-completed'),
                    'billing_email' => $email,
                    'limit' => 1,
                    'orderby' => 'date',
                    'order' => 'ASC'
                ));
                
                if (!empty($customer_orders) && $customer_orders[0]->get_id() === $order->get_id()) {
                    $new_customers[] = $email;
                }
            }
        }
        
        return array_unique($new_customers);
    }
    
    private function get_returning_customer_emails() {
        // Get customers who have made multiple orders
        $orders = wc_get_orders(array(
            'status' => array('wc-completed'),
            'limit' => -1,
            'date_created' => date('Y-m-d', strtotime('-90 days')) . '...' . date('Y-m-d')
        ));
        
        $customer_counts = array();
        foreach ($orders as $order) {
            $email = $order->get_billing_email();
            if ($email) {
                $customer_counts[$email] = ($customer_counts[$email] ?? 0) + 1;
            }
        }
        
        // Return customers with 2+ orders
        $returning_customers = array();
        foreach ($customer_counts as $email => $count) {
            if ($count >= 2) {
                $returning_customers[] = $email;
            }
        }
        
        return $returning_customers;
    }
    
    private function schedule_weekend_email($coupon_code) {
        // Schedule email for Friday
        wp_schedule_single_event(strtotime('next Friday 10:00'), 'arteasy_send_weekend_email', array($coupon_code));
    }
    
    private function schedule_recovery_emails() {
        // Clear any existing schedule first
        wp_clear_scheduled_hook('arteasy_process_abandoned_carts');
        
        $test_mode = get_option('arteasy_cart_recovery_test_mode', false);
        
        // Schedule based on mode: test mode = every 5 min, production = hourly
        $schedule = $test_mode ? 'arteasy_every_5min' : 'hourly';
        
        if (!wp_next_scheduled('arteasy_process_abandoned_carts')) {
            wp_schedule_event(time(), $schedule, 'arteasy_process_abandoned_carts');
        }
    }
    
    /**
     * Set test mode for cart recovery (5 min emails instead of 1 hour)
     */
    public function set_test_mode($enabled = true) {
        update_option('arteasy_cart_recovery_test_mode', $enabled);
        
        // Reschedule with new timing (only if cart recovery is already active)
        if (get_option('arteasy_cart_recovery_active', false)) {
            $this->schedule_recovery_emails();
        }
        
        return array('success' => true, 'message' => $enabled ? 'Test mode enabled (5 min intervals)' : 'Production mode enabled (1 hour intervals)');
    }
    
    private function send_reactivation_emails($coupon_code) {
        // Send emails to inactive customers
        $inactive_customers = $this->get_inactive_customers();
        
        foreach ($inactive_customers as $customer_email) {
            $subject = 'We Miss You - Special Reactivation Offer!';
            $message = "Hi there,\n\nWe noticed you haven't shopped with us recently. We miss you!\n\nUse code {$coupon_code} for 30% off your next order.\n\nCome back and discover our latest art supplies!";
            
            wp_mail($customer_email, $subject, $message);
        }
    }
    
    private function get_inactive_customers() {
        // Get customers who haven't ordered in 60+ days
        $orders = wc_get_orders(array(
            'status' => array('wc-completed'),
            'limit' => -1,
            'date_created' => date('Y-m-d', strtotime('-60 days')) . '...' . date('Y-m-d')
        ));
        
        $recent_customers = array();
        foreach ($orders as $order) {
            $recent_customers[] = $order->get_billing_email();
        }
        
        // Get all customers
        $all_customers = get_users(array('role' => 'customer'));
        $inactive_customers = array();
        
        foreach ($all_customers as $customer) {
            if (!in_array($customer->user_email, $recent_customers)) {
                $inactive_customers[] = $customer->user_email;
            }
        }
        
        return array_slice($inactive_customers, 0, 50); // Limit to 50 emails
    }
    
    /**
     * Check if WooCommerce is active
     */
    public function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Get all products that need description updates
     */
    public function get_products_needing_descriptions($limit = 50, $offset = 0) {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        // Get a larger pool of products, then filter by actual content with offset handling
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 500, // Pool size to filter from
            'fields' => 'ids'
        );
        
        $products = get_posts($args);
        $product_data = array();
        $skipped = 0;
        
        foreach ($products as $product_id) {
            $wc_product = wc_get_product($product_id);
            if ($wc_product) {
                $description = $wc_product->get_description();
                $short_description = $wc_product->get_short_description();
                
                // Only include products that actually have no description OR short description
                // AND haven't been marked as AI-generated
                $is_ai_generated = get_post_meta($product_id, '_product_description_generated', true) === '1';
                
                // Only products missing SHORT description qualify
                if (!$is_ai_generated && empty($short_description)) {
                    // Apply offset across filtered results
                    if ($skipped < $offset) {
                        $skipped++;
                        continue;
                    }

                    $product_data[] = array(
                        'id' => $product_id,
                        'name' => $wc_product->get_name(),
                        'description' => $description,
                        'short_description' => $short_description,
                        'price' => $wc_product->get_price(),
                        'sku' => $wc_product->get_sku(),
                        'categories' => wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names')),
                        'tags' => wp_get_post_terms($product_id, 'product_tag', array('fields' => 'names')),
                        'image_url' => wp_get_attachment_image_url($wc_product->get_image_id(), 'medium'),
                        'stock_status' => $wc_product->get_stock_status(),
                        'featured' => $wc_product->get_featured()
                    );
                    
                    // Limit results
                    if (count($product_data) >= $limit) {
                        break;
                    }
                }
            }
        }
        
        return $product_data;
    }
    
    /**
     * Generate AI description for a single product
     */
    public function generate_product_description_ai($product_data) {
        if (!class_exists('ArteasyGemini')) {
            error_log("Arteasy: ArteasyGemini class not found");
            return array('success' => false, 'error' => 'Gemini integration not loaded');
        }
        
        $gemini = new ArteasyGemini();
        
        // Extract features from existing data
        $features = array();
        if (!empty($product_data['categories'])) {
            $features[] = 'Categories: ' . implode(', ', $product_data['categories']);
        }
        if (!empty($product_data['tags'])) {
            $features[] = 'Tags: ' . implode(', ', $product_data['tags']);
        }
        if ($product_data['featured']) {
            $features[] = 'Featured product';
        }
        if ($product_data['stock_status'] === 'instock') {
            $features[] = 'In stock';
        }
        
        $features_text = implode('; ', $features);
        
        // Generate description using AI
        $result = $gemini->generate_product_description(
            $product_data['name'],
            !empty($product_data['categories']) ? $product_data['categories'][0] : 'Art Supplies',
            $product_data['price'],
            $product_data['sku'],
            $features_text
        );
        
        // Handle new return format (array with success/error)
        if (is_array($result)) {
            return $result;
        }
        
        // Legacy format support (string description)
        if (is_string($result) && !empty($result)) {
            return array('success' => true, 'description' => $result);
        }
        
        return array('success' => false, 'error' => 'Failed to generate description');
    }
    
    /**
     * Update product description in WooCommerce
     */
    public function update_product_description($product_id, $description) {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }
        
        // ONLY update the SHORT description field
        // Do NOT update the main description field
        $product->set_short_description($description);
        
        // Leave the main description field unchanged
        // $product->set_description(''); // Don't set main description
        
        $product->save();
        
        // Clear WooCommerce cache for this product
        wc_delete_product_transients($product_id);
        clean_post_cache($product_id);
        
        // Mark as AI-generated
        update_post_meta($product_id, '_product_description_generated', '1');
        update_post_meta($product_id, '_product_description_generated_date', current_time('mysql'));
        update_post_meta($product_id, '_product_description_generated_by', 'arteasy-ai');
        
        return true;
    }
    
    /**
     * Bulk generate descriptions for multiple products
     */
    public function bulk_generate_descriptions($product_ids = array(), $batch_size = 5) {
        if (!class_exists('WooCommerce') || !class_exists('ArteasyGemini')) {
            return array('success' => false, 'message' => 'Required plugins not active');
        }
        
        $results = array(
            'success' => true,
            'processed' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => array(),
            'products' => array()
        );
        
        // If no product IDs provided, get products needing descriptions
        if (empty($product_ids)) {
            $products_data = $this->get_products_needing_descriptions($batch_size);
            $product_ids = array_column($products_data, 'id');
        }
        
        foreach ($product_ids as $product_id) {
            try {
                error_log("Arteasy: Processing product ID: {$product_id}");
                
                $product_data = $this->get_product_data_for_ai($product_id);
                if (!$product_data) {
                    $error_msg = "Product ID $product_id not found";
                    error_log("Arteasy: {$error_msg}");
                    $results['failed']++;
                    $results['errors'][] = $error_msg;
                    continue;
                }
                
                error_log("Arteasy: Generating description for: {$product_data['name']}");
                $generation_result = $this->generate_product_description_ai($product_data);
                
                // Handle new array format
                if (is_array($generation_result)) {
                    if ($generation_result['success'] && isset($generation_result['description'])) {
                        $description = $generation_result['description'];
                        $update_result = $this->update_product_description($product_id, $description);
                        
                        if ($update_result) {
                            error_log("Arteasy: Successfully updated product {$product_id}: {$product_data['name']}");
                            $results['updated']++;
                            $results['products'][] = array(
                                'id' => $product_id,
                                'name' => $product_data['name'],
                                'status' => 'updated'
                            );
                        } else {
                            $error_msg = "Failed to update product: {$product_data['name']}";
                            error_log("Arteasy: {$error_msg}");
                            $results['failed']++;
                            $results['errors'][] = $error_msg;
                        }
                    } else {
                        $error_msg = isset($generation_result['error']) 
                            ? "{$product_data['name']}: {$generation_result['error']}" 
                            : "Failed to generate description for {$product_data['name']}";
                        error_log("Arteasy: {$error_msg}");
                        $results['failed']++;
                        $results['errors'][] = $error_msg;
                    }
                } else {
                    // Legacy format support
                    if ($generation_result) {
                        $update_result = $this->update_product_description($product_id, $generation_result);
                        if ($update_result) {
                            $results['updated']++;
                            $results['products'][] = array(
                                'id' => $product_id,
                                'name' => $product_data['name'],
                                'status' => 'updated'
                            );
                        } else {
                            $results['failed']++;
                            $results['errors'][] = "Failed to update product: {$product_data['name']}";
                        }
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Failed to generate description for {$product_data['name']}";
                    }
                }
                
                $results['processed']++;
                
                // Add small delay to avoid API rate limits
                sleep(1);
                
            } catch (Exception $e) {
                $error_msg = "Error processing product $product_id: " . $e->getMessage();
                error_log("Arteasy Exception: {$error_msg}");
                $results['failed']++;
                $results['errors'][] = $error_msg;
            }
        }
        
        return $results;
    }
    
    /**
     * Get product data formatted for AI generation
     */
    private function get_product_data_for_ai($product_id) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }
        
        return array(
            'id' => $product_id,
            'name' => $product->get_name(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'price' => $product->get_price(),
            'sku' => $product->get_sku(),
            'categories' => wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names')),
            'tags' => wp_get_post_terms($product_id, 'product_tag', array('fields' => 'names')),
            'image_url' => wp_get_attachment_image_url($product->get_image_id(), 'medium'),
            'stock_status' => $product->get_stock_status(),
            'featured' => $product->get_featured()
        );
    }
    
    /**
     * Get automation status and statistics
     */
    public function get_automation_status() {
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        // Count total products
        $total_products = wp_count_posts('product');
        $total_count = $total_products->publish;
        
        // Count products with AI-generated descriptions (tracked by meta)
        $ai_generated_query = new WP_Query(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_product_description_generated',
                    'value' => '1'
                )
            ),
            'posts_per_page' => -1
        ));
        $ai_generated_count = $ai_generated_query->found_posts;

        // Determine products that actually have SHORT description content
        $all_products = get_posts(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));

        $with_short_count = 0;
        foreach ($all_products as $pid) {
            $p = wc_get_product($pid);
            if (!$p) { continue; }
            $has_short = trim((string) $p->get_short_description()) !== '';
            if ($has_short) { $with_short_count++; }
        }
        // Products needing description are those without SHORT description
        $needing_count = max(0, $total_count - $with_short_count);
        
        return array(
            'total_products' => $total_count,
            'ai_generated' => $ai_generated_count,
            'needing_descriptions' => $needing_count,
            'completion_percentage' => $total_count > 0 ? round(($ai_generated_count / $total_count) * 100, 1) : 0,
            'last_automation_run' => get_option('arteasy_last_automation_run', 'Never'),
            'automation_enabled' => get_option('arteasy_automation_enabled', false)
        );
    }
    
    /**
     * Enable/disable automation
     */
    public function set_automation_status($enabled) {
        update_option('arteasy_automation_enabled', $enabled);
        update_option('arteasy_last_automation_run', current_time('mysql'));
        
        if ($enabled) {
            // Schedule automatic runs
            if (!wp_next_scheduled('arteasy_auto_generate_descriptions')) {
                wp_schedule_event(time(), 'hourly', 'arteasy_auto_generate_descriptions');
            }
        } else {
            // Remove scheduled runs
            wp_clear_scheduled_hook('arteasy_auto_generate_descriptions');
        }
        
        return true;
    }
}
