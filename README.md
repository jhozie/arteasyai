# Arteasy AI Automation Plugin

An advanced, enterprise-grade WordPress plugin designed to seamlessly integrate Google's Gemini AI into automated e-commerce and content workflows. Built natively in PHP, it provides a robust bridge between WordPress core logic and modern Large Language Models (LLMs).

## Core Architecture
* **Language:** PHP 8+ (WordPress Native Integration)
* **AI Engine:** Google Gemini API
* **Deployment:** Standalone WordPress Plugin structure

## Key Features
1. **Intelligent Automation:** Automates marketing workflows, content generation, and customer engagement directly within the WordPress ecosystem.
2. **Cart Abandonment Recovery:** Uses AI to analyze cart abandonment data and generate dynamic, high-converting recovery campaigns (refer to `CART-RECOVERY-REVIEW.md`).
3. **Diagnostic Toolkit:** Includes built-in diagnostic and optimization tools (`gemini-diagnostic.php`) to ensure API uptime and monitor generation latency.
4. **Seamless API Integration:** Securely handles API keys and data transmission between WordPress servers and Google's cloud infrastructure without compromising site speed.

## Technical Highlights
This project demonstrates advanced knowledge of WordPress plugin development, secure REST API integrations, and the ability to leverage Generative AI for measurable business outcomes (e.g., cart recovery and marketing automation).

## Setup Instructions
1. Upload the `arteasy-ai-automation-plugin` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin settings and input your Google Gemini API Key.
4. (Optional) Run the diagnostic tests in the settings panel to verify the connection.
