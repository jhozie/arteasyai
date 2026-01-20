/**
 * Arteasy AI Automation - Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Global AJAX setup
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (settings.type === 'POST' && settings.url === ajaxurl) {
                settings.data += '&_wpnonce=' + arteasy_ajax.nonce;
            }
        }
    });
    
    // Test API connection
    $('#test-api').on('click', function() {
        var button = $(this);
        var resultDiv = $('#api-test-result');
        
        button.prop('disabled', true).text('Testing...');
        resultDiv.html('<p class="loading">Testing API connection...</p>');
        
        $.post(ajaxurl, {
            action: 'arteasy_test_api'
        }, function(response) {
            if (response.success) {
                resultDiv.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
            } else {
                resultDiv.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
            }
        }).fail(function() {
            resultDiv.html('<div class="notice notice-error"><p>❌ Connection failed. Please try again.</p></div>');
        }).always(function() {
            button.prop('disabled', false).text('Test Connection');
        });
    });
    
    // Product Generator Form
    $('#product-generator-form').on('submit', function(e) {
        e.preventDefault();
        generateProductDescription();
    });
    
    function generateProductDescription() {
        var form = $('#product-generator-form');
        var resultDiv = $('#generated-description');
        var submitBtn = form.find('button[type="submit"]');
        
        // Validate form
        var productName = $('#product_name').val().trim();
        if (!productName) {
            alert('Please enter a product name');
            return;
        }
        
        var formData = {
            action: 'arteasy_generate_description',
            product_name: productName,
            category: $('#category').val(),
            price: $('#price').val(),
            sku: $('#sku').val(),
            features: $('#features').val()
        };
        
        submitBtn.prop('disabled', true).text('Generating...');
        resultDiv.html('<div class="loading">🤖 AI is generating your product description...</div>');
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                resultDiv.html('<div class="description-text">' + response.data + '</div>');
                $('.result-actions').show();
            } else {
                resultDiv.html('<div class="error">❌ Error: ' + response.data + '</div>');
            }
        }).fail(function() {
            resultDiv.html('<div class="error">❌ Connection failed. Please check your API key and try again.</div>');
        }).always(function() {
            submitBtn.prop('disabled', false).text('Generate Description');
        });
    }
    
    // Copy description to clipboard
    $('#copy-description').on('click', function() {
        var description = $('#generated-description .description-text').text();
        if (description) {
            navigator.clipboard.writeText(description).then(function() {
                showNotification('Description copied to clipboard!', 'success');
            }).catch(function() {
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = description;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showNotification('Description copied to clipboard!', 'success');
            });
        }
    });
    
    // Regenerate description
    $('#regenerate-description').on('click', function() {
        generateProductDescription();
    });
    
    // Cart Recovery Form
    $('#cart-recovery-form').on('submit', function(e) {
        e.preventDefault();
        generateCartRecoveryMessage();
    });
    
    function generateCartRecoveryMessage() {
        var form = $('#cart-recovery-form');
        var resultDiv = $('#generated-message');
        var submitBtn = form.find('button[type="submit"]');
        
        // Validate form
        var customerName = $('#customer_name').val().trim();
        if (!customerName) {
            alert('Please enter customer name');
            return;
        }
        
        var cartItems = [];
        $('.cart-item').each(function() {
            var name = $(this).find('input[name="item_name[]"]').val().trim();
            var price = $(this).find('input[name="item_price[]"]').val();
            if (name && price) {
                cartItems.push({name: name, price: price});
            }
        });
        
        if (cartItems.length === 0) {
            alert('Please add at least one cart item');
            return;
        }
        
        var formData = {
            action: 'arteasy_generate_cart_recovery',
            customer_name: customerName,
            template_style: $('#template_style').val(),
            cart_items: JSON.stringify(cartItems)
        };
        
        submitBtn.prop('disabled', true).text('Generating...');
        resultDiv.html('<div class="loading">🤖 AI is crafting your recovery message...</div>');
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                resultDiv.html('<div class="message-text">' + response.data + '</div>');
                $('.result-actions').show();
            } else {
                resultDiv.html('<div class="error">❌ Error: ' + response.data + '</div>');
            }
        }).fail(function() {
            resultDiv.html('<div class="error">❌ Connection failed. Please check your API key and try again.</div>');
        }).always(function() {
            submitBtn.prop('disabled', false).text('Generate Recovery Message');
        });
    }
    
    // Add cart item
    $('#add-cart-item').on('click', function() {
        var itemHtml = '<div class="cart-item">' +
            '<input type="text" name="item_name[]" placeholder="Product Name" class="regular-text" required />' +
            '<input type="number" name="item_price[]" placeholder="Price (₦)" class="small-text" step="0.01" required />' +
            '<button type="button" class="button remove-item">Remove</button>' +
            '</div>';
        $('#cart-items-container').append(itemHtml);
    });
    
    // Remove cart item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.cart-item').remove();
    });
    
    // Copy message to clipboard
    $('#copy-message').on('click', function() {
        var message = $('#generated-message .message-text').text();
        if (message) {
            navigator.clipboard.writeText(message).then(function() {
                showNotification('Message copied to clipboard!', 'success');
            }).catch(function() {
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = message;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showNotification('Message copied to clipboard!', 'success');
            });
        }
    });
    
    // Regenerate message
    $('#regenerate-message').on('click', function() {
        generateCartRecoveryMessage();
    });
    
    // Send test email
    $('#send-test-email').on('click', function() {
        showNotification('Test email feature coming soon!', 'info');
    });
    
    // Template examples
    $('.tab-button').on('click', function() {
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        
        var template = $(this).data('template');
        $('.example-text').hide();
        $('#' + template + '-example').show();
    });
    
    // Analytics quick actions
    $('.quick-actions .button').on('click', function() {
        var action = $(this).text();
        showNotification(action + ' feature coming soon!', 'info');
    });
    
    // Recommendation actions
    $('.recommendation-actions .button').on('click', function() {
        var action = $(this).text();
        showNotification(action + ' feature coming soon!', 'info');
    });
    
    // Utility function to show notifications
    function showNotification(message, type) {
        var notificationClass = 'notice notice-' + (type === 'success' ? 'success' : 'info');
        var notification = '<div class="' + notificationClass + ' is-dismissible"><p>' + message + '</p></div>';
        
        // Remove existing notifications
        $('.notice').remove();
        
        // Add new notification
        $('.wrap').prepend(notification);
        
        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $('.notice').fadeOut();
        }, 3000);
    }
    
    // Auto-save form data to localStorage
    function saveFormData(formId) {
        var formData = {};
        $('#' + formId + ' input, #' + formId + ' select, #' + formId + ' textarea').each(function() {
            if ($(this).attr('name')) {
                formData[$(this).attr('name')] = $(this).val();
            }
        });
        localStorage.setItem('arteasy_' + formId, JSON.stringify(formData));
    }
    
    // Load form data from localStorage
    function loadFormData(formId) {
        var savedData = localStorage.getItem('arteasy_' + formId);
        if (savedData) {
            var formData = JSON.parse(savedData);
            $.each(formData, function(name, value) {
                $('#' + formId + ' [name="' + name + '"]').val(value);
            });
        }
    }
    
    // Auto-save product generator form
    $('#product-generator-form input, #product-generator-form select, #product-generator-form textarea').on('change', function() {
        saveFormData('product-generator-form');
    });
    
    // Load saved product generator data
    loadFormData('product-generator-form');
    
    // Auto-save cart recovery form
    $('#cart-recovery-form input, #cart-recovery-form select').on('change', function() {
        saveFormData('cart-recovery-form');
    });
    
    // Load saved cart recovery data
    loadFormData('cart-recovery-form');
    
    // Clear form data
    $('.clear-form').on('click', function() {
        var formId = $(this).data('form');
        localStorage.removeItem('arteasy_' + formId);
        $('#' + formId)[0].reset();
        showNotification('Form cleared!', 'info');
    });
    
    // Initialize tooltips
    $('[data-tooltip]').each(function() {
        $(this).attr('title', $(this).data('tooltip'));
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Initialize analytics charts (placeholder)
    if (typeof Chart !== 'undefined') {
        // Chart initialization code would go here
        console.log('Charts initialized');
    }
    
    // Handle responsive menu
    $('.menu-toggle').on('click', function() {
        $('.admin-menu').toggleClass('menu-open');
    });
    
    // Close menu when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.admin-menu, .menu-toggle').length) {
            $('.admin-menu').removeClass('menu-open');
        }
    });
    
    // Initialize date pickers
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    }
    
    // Form validation
    $('form').on('submit', function(e) {
        var form = $(this);
        var requiredFields = form.find('[required]');
        var isValid = true;
        
        requiredFields.each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showNotification('Please fill in all required fields', 'error');
        }
    });
    
    // Remove error class on input
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('error');
    });
    
    console.log('Arteasy AI Admin JavaScript loaded successfully!');
});



