<?php
/**
 * Product Generator Template
 */

if (!defined("ABSPATH")) {
    exit;
}
?>

<div class="wrap arteasy-product-generator">
    <h1>AI Product Description Generator</h1>
    
    <div class="arteasy-generator-container">
        <div class="generator-form">
            <h2>📝 Generate Product Description</h2>
            <p class="description" style="margin-bottom: 20px;">Enter product details below to generate a clean, SEO-optimized short description. The description will be ready to use (no markdown, no prefixes, no metadata).</p>
            <form id="product-generator-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="product_name">Product Name <span class="required">*</span></label></th>
                        <td>
                            <input type="text" id="product_name" name="product_name" class="regular-text" required placeholder="e.g., Professional Art Brush Set" />
                            <p class="description">Enter the name of your art product (required)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="category">Category</label></th>
                        <td>
                            <select id="category" name="category" class="regular-text">
                                <option value="Art Supplies" selected>Art Supplies</option>
                                <option value="Paint Brushes">Paint Brushes</option>
                                <option value="Canvas">Canvas</option>
                                <option value="Paints">Paints</option>
                                <option value="Drawing Tools">Drawing Tools</option>
                                <option value="Craft Materials">Craft Materials</option>
                                <option value="Sculpting Tools">Sculpting Tools</option>
                                <option value="Digital Art">Digital Art</option>
                            </select>
                            <p class="description">Select the product category</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price">Price (₦)</label></th>
                        <td>
                            <input type="number" id="price" name="price" class="regular-text" step="0.01" min="0" placeholder="0.00" />
                            <p class="description">Optional: Enter price in Nigerian Naira</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sku">SKU</label></th>
                        <td>
                            <input type="text" id="sku" name="sku" class="regular-text" placeholder="Optional product SKU" />
                            <p class="description">Optional: Product SKU or code</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="features">Features</label></th>
                        <td>
                            <textarea id="features" name="features" rows="4" class="large-text" placeholder="Optional: Key features, materials, or specifications (e.g., Professional quality, Synthetic bristles, Set of 12 brushes)"></textarea>
                            <p class="description">Optional: Key features, materials, or specifications to include in the description</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-edit" style="vertical-align: middle; margin-right: 5px;"></span>
                        Generate Description
                    </button>
                    <button type="button" class="button" onclick="$('#product-generator-form')[0].reset(); $('#generated-description').html('<p class=\"placeholder\">Your AI-generated product description will appear here...</p>'); $('.result-actions').hide();" style="margin-left: 10px;">
                        Clear Form
                    </button>
                </p>
            </form>
        </div>
        
        <div class="generator-result">
            <h2>✨ Generated Description</h2>
            <p class="description" style="margin-bottom: 15px;">The description below is cleaned and ready to use - no markdown, no prefixes, no metadata.</p>
            <div id="generated-description" class="description-output">
                <p class="placeholder">Your AI-generated product description will appear here...</p>
            </div>
            
            <div class="result-actions" style="display: none; margin-top: 15px;">
                <button type="button" id="copy-description" class="button button-primary">
                    <span class="dashicons dashicons-clipboard" style="vertical-align: middle; margin-right: 5px;"></span>
                    Copy Description
                </button>
                <button type="button" id="regenerate-description" class="button">
                    <span class="dashicons dashicons-update" style="vertical-align: middle; margin-right: 5px;"></span>
                    Regenerate
                </button>
                <p class="description" style="margin-top: 10px; font-style: italic; color: #666;">💡 Tip: This description will be saved to the <strong>short description</strong> field when you use it in a product.</p>
            </div>
        </div>
    </div>
    
    <div class="generator-info">
        <h3>📖 How it works:</h3>
        <ul>
            <li>Enter your product details above</li>
            <li>AI analyzes your product information</li>
            <li>Generates clean, SEO-optimized short description (no markdown, no prefixes, no metadata)</li>
            <li>Copy and paste directly into your WooCommerce product's <strong>short description</strong> field</li>
        </ul>
        
        <div class="api-status">
            <h3>🔌 API Status:</h3>
            <span id="api-status-indicator" class="status-indicator">Checking...</span>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
            <p style="margin: 0;"><strong>✨ New:</strong> Descriptions are now automatically cleaned - no "Of course!", no markdown (**, ###), and no SKU/Price/Categories in the text!</p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Check API status on page load
    checkApiStatus();
    
    // Handle form submission
    $('#product-generator-form').on('submit', function(e) {
        e.preventDefault();
        generateDescription();
    });
    
    // Copy description
    $('#copy-description').on('click', function() {
        // Get text content, preserving line breaks
        var description = $('#generated-description .description-text').text() || $('#generated-description').text();
        
        if (!description || description.trim() === '') {
            alert('No description to copy. Please generate one first.');
            return;
        }
        
        // Use modern clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(description).then(function() {
                // Show visual feedback
                const button = $('#copy-description');
                const originalText = button.text();
                button.text('✓ Copied!').css('background', '#00a32a').css('color', 'white');
                setTimeout(function() {
                    button.text(originalText).css('background', '').css('color', '');
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy:', err);
                alert('Failed to copy to clipboard. Please select and copy manually.');
            });
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = description;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                alert('Description copied to clipboard!');
            } catch (err) {
                alert('Failed to copy. Please select and copy manually.');
            }
            document.body.removeChild(textarea);
        }
    });
    
    // Regenerate description
    $('#regenerate-description').on('click', function() {
        generateDescription();
    });
    
    function generateDescription() {
        const formData = {
            action: 'arteasy_generate_description',
            product_name: $('#product_name').val().trim(),
            category: $('#category').val() || 'Art Supplies',
            price: $('#price').val() || 0,
            sku: $('#sku').val().trim() || '',
            features: $('#features').val().trim() || ''
        };
        
        // Validate required fields
        if (!formData.product_name) {
            alert('Please enter a product name.');
            $('#product_name').focus();
            return;
        }
        
        // Disable form during generation
        $('#product-generator-form button[type="submit"]').prop('disabled', true).text('Generating...');
        $('#generated-description').html('<p class="loading">🔄 Generating clean product description...</p>');
        $('.result-actions').hide();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            timeout: 30000, // 30 second timeout
            success: function(response) {
                console.log('Generator Response:', response);
                
                if (response.success) {
                    // Display the cleaned description (already cleaned by backend)
                    const description = response.data || '';
                    if (description) {
                        $('#generated-description').html('<div class="description-text">' + description.replace(/\n/g, '<br>') + '</div>');
                        $('.result-actions').show();
                        
                        // Show success notification
                        $('#generated-description').prepend('<div class="notice notice-success notice-inline"><p>✅ Description generated successfully! Ready to copy.</p></div>');
                        setTimeout(function() {
                            $('.notice-inline').fadeOut();
                        }, 3000);
                    } else {
                        $('#generated-description').html('<p class="error">⚠️ Generated description is empty. Please try again.</p>');
                    }
                } else {
                    const errorMsg = response.data || 'Unknown error occurred';
                    $('#generated-description').html('<div class="error"><p>❌ Error generating description:</p><p>' + errorMsg + '</p><p class="description">Please check your API key in Settings and try again.</p></div>');
                    console.error('Generation Error:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error, xhr);
                let errorMessage = 'Network error occurred. ';
                
                if (status === 'timeout') {
                    errorMessage += 'The request took too long. Please check your internet connection and try again.';
                } else if (xhr.status === 0) {
                    errorMessage += 'Could not connect to server. Please refresh the page and try again.';
                } else {
                    errorMessage += error || 'Unknown error';
                }
                
                $('#generated-description').html('<div class="error"><p>❌ AJAX Error:</p><p>' + errorMessage + '</p></div>');
            },
            complete: function() {
                // Re-enable form
                $('#product-generator-form button[type="submit"]').prop('disabled', false).text('Generate Description');
            }
        });
    }
    
    function checkApiStatus() {
        $.post(ajaxurl, {action: 'arteasy_test_api'}, function(response) {
            if (response.success) {
                $('#api-status-indicator').text('Connected').removeClass('error').addClass('success');
            } else {
                $('#api-status-indicator').text('Not Connected').removeClass('success').addClass('error');
            }
        });
    }
});
</script>

<style>
.arteasy-product-generator {
    max-width: 1200px;
}

.arteasy-generator-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.generator-form, .generator-result {
    background: white;
    padding: 25px;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.description-output {
    min-height: 200px;
    padding: 20px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    background: #f9f9f9;
    font-size: 14px;
    line-height: 1.8;
}

.description-text {
    line-height: 1.8;
    color: #333;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.description-output .placeholder {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 40px 20px;
}

.description-output .notice-inline {
    margin: 0 0 15px 0;
    padding: 10px;
    border-left: 4px solid #00a32a;
    background: #f0f6fc;
}

.description-output .error {
    background: #fcf0f1;
    border-left: 4px solid #d63638;
    padding: 15px;
    border-radius: 4px;
}

.description-output .error p {
    margin: 5px 0;
}

.description-output .description {
    font-size: 12px;
    color: #666;
}

.loading {
    color: #666;
    font-style: italic;
}

.error {
    color: #d63638;
}

.success {
    color: #00a32a;
}

.status-indicator {
    padding: 5px 10px;
    border-radius: 3px;
    font-weight: bold;
}

.status-indicator.success {
    background: #d1edff;
    color: #0073aa;
}

.status-indicator.error {
    background: #fbeaea;
    color: #d63638;
}

.result-actions {
    margin-top: 15px;
}

.result-actions .button {
    margin-right: 10px;
}

.generator-info {
    background: #f0f0f1;
    padding: 20px;
    border-radius: 10px;
}

.generator-info ul {
    margin: 10px 0;
}

.generator-info li {
    margin: 5px 0;
}

.api-status {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}
</style>


