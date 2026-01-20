<?php
/**
 * Admin Dashboard Template
 */

if (!defined("ABSPATH")) {
    exit;
}
?>

<div class="wrap arteasy-ai-dashboard">
    <h1>Arteasy AI Automation Dashboard</h1>
    
    <div class="arteasy-ai-header">
        <div class="arteasy-ai-logo">
            <h2>?? AI-Powered Art Business Suite</h2>
            <p>Transform your art accessories store with intelligent automation</p>
        </div>
    </div>
    
    <div class="arteasy-ai-features">
        <div class="feature-card">
            <div class="feature-icon">??</div>
            <div class="feature-content">
                <h3>AI Product Description Generator</h3>
                <p>Automatically create compelling, SEO-optimized product descriptions for your art accessories.</p>
                <div class="feature-actions">
                    <a href="<?php echo admin_url("admin.php?page=arteasy-ai-generator"); ?>" class="button button-primary">Configure</a>
                </div>
            </div>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">??</div>
            <div class="feature-content">
                <h3>Smart Cart Recovery</h3>
                <p>Automatically recover abandoned carts with personalized AI-generated messages.</p>
                <div class="feature-actions">
                    <a href="<?php echo admin_url("admin.php?page=arteasy-ai-cart"); ?>" class="button button-primary">Configure</a>
                </div>
            </div>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">??</div>
            <div class="feature-content">
                <h3>AI Analytics Dashboard</h3>
                <p>Get actionable insights about your art business with AI-powered analytics.</p>
                <div class="feature-actions">
                    <a href="<?php echo admin_url("admin.php?page=arteasy-ai-analytics"); ?>" class="button button-primary">View Analytics</a>
                </div>
            </div>
        </div>
    </div>
</div>
