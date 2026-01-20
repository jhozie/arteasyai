<?php
/**
 * Product Description Automation Template
 */

if (!defined("ABSPATH")) {
    exit;
}
?>

<div class="wrap arteasy-automation">
    <h1>🤖 AI Product Description Automation</h1>
    
    <!-- Automation Dashboard -->
    <div class="automation-dashboard">
        <h2>📊 Automation Overview</h2>
        <div class="automation-cards" id="automation-cards">
            <div class="automation-card">
                <h3>Total Products</h3>
                <div class="card-value" id="total-products">-</div>
                <div class="card-label">In your store</div>
            </div>
            <div class="automation-card">
                <h3>AI Generated</h3>
                <div class="card-value" id="ai-generated">-</div>
                <div class="card-label">Descriptions created</div>
            </div>
            <div class="automation-card">
                <h3>Needing Descriptions</h3>
                <div class="card-value" id="needing-descriptions">-</div>
                <div class="card-label">Products pending</div>
            </div>
            <div class="automation-card">
                <h3>Completion</h3>
                <div class="card-value" id="completion-percentage">-</div>
                <div class="card-label">Automation progress</div>
            </div>
        </div>
        
        <!-- Automation Controls -->
        <div class="automation-controls">
            <h3>🎛️ Automation Controls</h3>
            <div class="control-buttons">
                <button id="enable-automation" class="button button-primary">Enable Auto-Generation</button>
                <button id="disable-automation" class="button button-secondary" style="display: none;">Disable Auto-Generation</button>
                <button id="bulk-generate" class="button button-secondary">Bulk Generate Now</button>
                <button id="refresh-status" class="button">Refresh Status</button>
            </div>
            
            <div class="automation-settings">
                <label for="batch-size">Batch Size:</label>
                <select id="batch-size">
                    <option value="5">5 products</option>
                    <option value="10" selected>10 products</option>
                    <option value="20">20 products</option>
                    <option value="50">50 products</option>
                </select>
            </div>
        </div>
        
        <!-- Automation Status -->
        <div class="automation-status">
            <h3>📈 Automation Status</h3>
            <div class="status-info">
                <p><strong>Status:</strong> <span id="automation-status">Checking...</span></p>
                <p><strong>Last Run:</strong> <span id="last-run">Never</span></p>
                <p><strong>Next Run:</strong> <span id="next-run">-</span></p>
            </div>
        </div>
    </div>
    
    <!-- Products Needing Descriptions -->
    <div class="products-pending">
        <h2>📝 Products Needing Descriptions</h2>
        <div id="products-list" class="products-grid">
            <div class="loading">Loading products...</div>
        </div>
        <button id="load-more-products" class="button" style="display: none;">Load More Products</button>
    </div>
    
    <!-- Individual Generator -->
    <div class="arteasy-generator-container">
        <div class="generator-form">
            <h2>📝 Generate Single Product Description</h2>
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
    
    <!-- Progress Modal -->
    <div id="progress-modal" class="progress-modal" style="display: none;">
        <div class="modal-content">
            <h3>🤖 Generating Descriptions</h3>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <div class="progress-text" id="progress-text">Starting...</div>
            <div class="progress-details" id="progress-details"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentProducts = [];
    let currentPage = 0;
    const productsPerPage = 20;
    
    // Initialize automation dashboard
    loadAutomationStatus();
    loadProductsNeedingDescriptions();
    
    // Automation controls
    $('#enable-automation').on('click', function() {
        enableAutomation();
    });
    
    $('#disable-automation').on('click', function() {
        disableAutomation();
    });
    
    $('#bulk-generate').on('click', function() {
        startBulkGeneration();
    });
    
    $('#refresh-status').on('click', function() {
        loadAutomationStatus();
        loadProductsNeedingDescriptions();
    });
    
    $('#load-more-products').on('click', function() {
        loadMoreProducts();
    });
    
    // Individual generator
    $('#product-generator-form').on('submit', function(e) {
        e.preventDefault();
        generateSingleDescription();
    });
    
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
    
    $('#regenerate-description').on('click', function() {
        generateSingleDescription();
    });
    
    function loadAutomationStatus() {
        $.post(ajaxurl, {action: 'arteasy_get_automation_status'}, function(response) {
            if (response.success) {
                const data = response.data;
                $('#total-products').text(data.total_products);
                $('#ai-generated').text(data.ai_generated);
                $('#needing-descriptions').text(data.needing_descriptions);
                $('#completion-percentage').text(data.completion_percentage + '%');
                $('#last-run').text(data.last_automation_run);
                
                if (data.automation_enabled) {
                    $('#automation-status').text('Enabled').removeClass('disabled').addClass('enabled');
                    $('#enable-automation').hide();
                    $('#disable-automation').show();
                } else {
                    $('#automation-status').text('Disabled').removeClass('enabled').addClass('disabled');
                    $('#enable-automation').show();
                    $('#disable-automation').hide();
                }
            }
        });
    }
    
    function loadProductsNeedingDescriptions() {
        $.post(ajaxurl, {
            action: 'arteasy_get_products_needing_descriptions',
            limit: productsPerPage
        }, function(response) {
            if (response.success) {
                currentProducts = response.data;
                displayProducts(currentProducts);
                if (currentProducts.length >= productsPerPage) {
                    $('#load-more-products').show();
                }
            }
        });
    }
    
    function loadMoreProducts() {
        currentPage++;
        $.post(ajaxurl, {
            action: 'arteasy_get_products_needing_descriptions',
            limit: productsPerPage,
            offset: currentPage * productsPerPage
        }, function(response) {
            if (response.success) {
                currentProducts = currentProducts.concat(response.data);
                displayProducts(response.data, true);
                if (response.data.length < productsPerPage) {
                    $('#load-more-products').hide();
                }
            }
        });
    }
    
    function displayProducts(products, append = false) {
        let html = '';
        
        if (!append) {
            html = '<div class="products-grid">';
        }
        
        if (products.length === 0) {
            html += '<div class="no-products">All products have descriptions! 🎉</div>';
        } else {
            products.forEach(function(product) {
                html += `
                    <div class="product-card" data-id="${product.id}">
                        <div class="product-image">
                            ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}">` : '<div class="no-image">📦</div>'}
                        </div>
                        <div class="product-info">
                            <h4>${product.name}</h4>
                            <p class="product-price">₦${product.price || '0'}</p>
                            <p class="product-sku">SKU: ${product.sku || 'N/A'}</p>
                            <div class="product-categories">
                                ${product.categories ? product.categories.join(', ') : 'No categories'}
                            </div>
                            <button class="button button-small generate-single" data-id="${product.id}">Generate Description</button>
                        </div>
                    </div>
                `;
            });
        }
        
        if (!append) {
            html += '</div>';
            $('#products-list').html(html);
        } else {
            $('#products-list .products-grid').append(html);
        }
        
        // Bind generate buttons
        $('.generate-single').on('click', function() {
            const productId = $(this).data('id');
            generateDescriptionForProduct(productId);
        });
    }
    
    function enableAutomation() {
        $.post(ajaxurl, {
            action: 'arteasy_set_automation_status',
            enabled: 'true',
            nonce: '<?php echo wp_create_nonce('arteasy_set_automation_status'); ?>'
        }, function(response) {
            if (response.success) {
                alert('Automation enabled! Descriptions will be generated automatically.');
                loadAutomationStatus();
            } else {
                alert('Failed to enable automation: ' + response.data);
            }
        });
    }
    
    function disableAutomation() {
        $.post(ajaxurl, {
            action: 'arteasy_set_automation_status',
            enabled: 'false',
            nonce: '<?php echo wp_create_nonce('arteasy_set_automation_status'); ?>'
        }, function(response) {
            if (response.success) {
                alert('Automation disabled.');
                loadAutomationStatus();
            } else {
                alert('Failed to disable automation: ' + response.data);
            }
        });
    }
    
    function startBulkGeneration() {
        const batchSize = $('#batch-size').val();
        
        $('#progress-modal').show();
        $('#progress-fill').css('width', '0%');
        $('#progress-text').text('Starting bulk generation...');
        
        $.post(ajaxurl, {
            action: 'arteasy_bulk_generate_descriptions',
            batch_size: batchSize,
            nonce: '<?php echo wp_create_nonce('arteasy_bulk_generate_descriptions'); ?>'
        }, function(response) {
            if (response.success) {
                const data = response.data;
                $('#progress-fill').css('width', '100%');
                $('#progress-text').text('Generation complete!');
                $('#progress-details').html(`
                    <div class="generation-results">
                        <p><strong>Processed:</strong> ${data.processed} products</p>
                        <p><strong>Updated:</strong> ${data.updated} products</p>
                        <p><strong>Failed:</strong> ${data.failed} products</p>
                        ${data.errors.length > 0 ? `<p><strong>Errors:</strong> ${data.errors.join(', ')}</p>` : ''}
                    </div>
                `);
                
                setTimeout(function() {
                    $('#progress-modal').hide();
                    loadAutomationStatus();
                    loadProductsNeedingDescriptions();
                }, 3000);
            } else {
                $('#progress-text').text('Generation failed: ' + response.data);
            }
        });
    }
    
    function generateDescriptionForProduct(productId) {
        const button = $(`.generate-single[data-id="${productId}"]`);
        const originalText = button.text();
        button.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'arteasy_bulk_generate_descriptions',
                product_ids: JSON.stringify([productId]),
                batch_size: 1,
                nonce: '<?php echo wp_create_nonce('arteasy_bulk_generate_descriptions'); ?>'
            },
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    button.text('✅ Generated').addClass('success').removeClass('error');
                    alert('Description generated successfully! The product has been updated.');
                    setTimeout(function() {
                        loadAutomationStatus();
                        loadProductsNeedingDescriptions();
                    }, 1000);
                } else {
                    button.text('❌ Failed').addClass('error').removeClass('success');
                    alert('Failed to generate description: ' + (response.data || 'Unknown error'));
                    console.error('Error:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                button.text('❌ Failed').addClass('error').removeClass('success');
                alert('AJAX Error: ' + error);
            },
            complete: function() {
                // Re-enable button after 3 seconds
                setTimeout(function() {
                    button.prop('disabled', false);
                    if (!button.hasClass('success') && !button.hasClass('error')) {
                        button.text(originalText);
                    }
                }, 3000);
            }
        });
    }
    
    function generateSingleDescription() {
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
                console.log('Form Generator Response:', response);
                
                if (response.success) {
                    // Display the cleaned description (already cleaned by backend)
                    const description = response.data || '';
                    if (description) {
                        $('#generated-description').html('<div class="description-text">' + description.replace(/\n/g, '<br>') + '</div>');
                        $('.result-actions').show();
                        
                        // Show success notification
                        $('#generated-description').prepend('<div class="notice notice-success notice-inline"><p>✅ Description generated successfully!</p></div>');
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
});
</script>

<style>
.arteasy-automation {
    max-width: 1400px;
}

.automation-dashboard {
    background: white;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.automation-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.automation-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.automation-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}

.automation-card .card-value {
    font-size: 32px;
    font-weight: bold;
    margin: 10px 0;
}

.automation-card .card-label {
    font-size: 12px;
    opacity: 0.8;
}

.automation-controls {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.control-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.automation-settings {
    display: flex;
    align-items: center;
    gap: 10px;
}

.automation-status {
    background: #f0f8ff;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #0073aa;
}

.status-info p {
    margin: 5px 0;
}

.enabled {
    color: #00a32a;
    font-weight: bold;
}

.disabled {
    color: #d63638;
    font-weight: bold;
}

.products-pending {
    background: white;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.product-card {
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 15px;
    background: white;
    transition: transform 0.2s;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.product-image {
    width: 100%;
    height: 150px;
    background: #f5f5f5;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    font-size: 48px;
    color: #ccc;
}

.product-info h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #333;
}

.product-price {
    font-weight: bold;
    color: #0073aa;
    margin: 5px 0;
}

.product-sku {
    font-size: 12px;
    color: #666;
    margin: 5px 0;
}

.product-categories {
    font-size: 12px;
    color: #888;
    margin: 10px 0;
}

.generate-single {
    width: 100%;
    margin-top: 10px;
}

.generate-single.success {
    background: #00a32a;
    color: white;
}

.generate-single.error {
    background: #d63638;
    color: white;
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

.progress-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    max-width: 500px;
    width: 90%;
    text-align: center;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin: 20px 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0073aa, #00a32a);
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 16px;
    margin: 10px 0;
}

.progress-details {
    margin-top: 15px;
    text-align: left;
}

.generation-results p {
    margin: 5px 0;
}

.no-products {
    text-align: center;
    padding: 40px;
    color: #666;
    font-size: 18px;
}

@media (max-width: 768px) {
    .arteasy-generator-container {
        grid-template-columns: 1fr;
    }
    
    .automation-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .control-buttons {
        flex-direction: column;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>
