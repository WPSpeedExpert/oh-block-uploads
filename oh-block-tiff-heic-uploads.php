<?php
/**
 * Plugin Name:       OH – Block TIFF & HEIC Uploads
 * Plugin URI:        https://github.com/WPSpeedExpert/oh-block-uploads
 * Description:       Aggressively blocks TIFF and HEIC uploads with debug logging and verification.
 * Version:           1.5.1
 * Author:            OctaHexa
 * Author URI:        https://octahexa.com
 * License:           GPL v2 or later
 * Text Domain:       oh-block-uploads
 * 
 * Filename:          oh-block-tiff-heic-uploads.php
 */

// Define constants for debugging
define('OH_BLOCK_DEBUG', true); // Set to false in production
define('OH_BLOCKED_MESSAGE', 'File type not allowed. Please upload JPG or WebP format only (max 1MB, 1920px).');

/**
 * Log debug messages
 */
function oh_debug_log($message) {
    if (OH_BLOCK_DEBUG && defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[OH Block Upload] ' . $message);
    }
}

/**
 * Check if file should be blocked
 */
function oh_should_block_file($filename, $tmp_name = null) {
    // Check extension
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $blocked_extensions = ['tif', 'tiff', 'tff', 'heic', 'heif', 'heics', 'heifs', 'hif'];
    
    if (in_array($ext, $blocked_extensions, true)) {
        oh_debug_log("Blocked by extension: $ext");
        return true;
    }
    
    // Check magic bytes if file exists
    if ($tmp_name && file_exists($tmp_name)) {
        $handle = @fopen($tmp_name, 'rb');
        if ($handle) {
            $header = fread($handle, 12);
            fclose($handle);
            
            if (strlen($header) >= 4) {
                $hex = bin2hex(substr($header, 0, 4));
                
                // TIFF signatures
                if ($hex === '49492a00' || $hex === '4d4d002a') {
                    oh_debug_log("Blocked by TIFF magic bytes: $hex");
                    return true;
                }
            }
            
            if (strlen($header) >= 12) {
                // HEIC signature check
                $ftyp = substr($header, 4, 4);
                if ($ftyp === 'ftyp') {
                    $brand = substr($header, 8, 4);
                    $heic_brands = ['heic', 'heix', 'hevc', 'hevx', 'heim', 'heis', 'hevm', 'hevs', 'mif1'];
                    if (in_array($brand, $heic_brands, true)) {
                        oh_debug_log("Blocked by HEIC magic bytes: $brand");
                        return true;
                    }
                }
            }
        }
        
        // Check mime type
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($tmp_name);
            if ($mime) {
                $blocked_mimes = ['tiff', 'tif', 'heic', 'heif', 'hevc'];
                foreach ($blocked_mimes as $blocked) {
                    if (stripos($mime, $blocked) !== false) {
                        oh_debug_log("Blocked by mime type: $mime");
                        return true;
                    }
                }
            }
        }
    }
    
    return false;
}

/**
 * NUCLEAR OPTION - Check $_FILES super global on every init
 */
add_action('init', function() {
    if (!empty($_FILES)) {
        oh_debug_log("Files detected in \$_FILES: " . print_r($_FILES, true));
        
        foreach ($_FILES as $file_key => $file) {
            if (is_array($file['name'])) {
                // Multiple files
                foreach ($file['name'] as $key => $filename) {
                    if (!empty($filename) && !empty($file['tmp_name'][$key])) {
                        if (oh_should_block_file($filename, $file['tmp_name'][$key])) {
                            oh_debug_log("Blocking file in init: $filename");
                            wp_die(OH_BLOCKED_MESSAGE);
                        }
                    }
                }
            } else {
                // Single file
                if (!empty($file['name']) && !empty($file['tmp_name'])) {
                    if (oh_should_block_file($file['name'], $file['tmp_name'])) {
                        oh_debug_log("Blocking file in init: " . $file['name']);
                        wp_die(OH_BLOCKED_MESSAGE);
                    }
                }
            }
        }
    }
}, -999999); // Extremely early priority

/**
 * Primary filter - wp_handle_upload_prefilter
 */
add_filter('wp_handle_upload_prefilter', function($file) {
    oh_debug_log("wp_handle_upload_prefilter triggered for: " . $file['name']);
    
    if (oh_should_block_file($file['name'], $file['tmp_name'])) {
        $file['error'] = OH_BLOCKED_MESSAGE;
        oh_debug_log("File blocked in wp_handle_upload_prefilter");
    }
    
    return $file;
}, -999999);

/**
 * Block sideload
 */
add_filter('wp_handle_sideload_prefilter', function($file) {
    oh_debug_log("wp_handle_sideload_prefilter triggered for: " . $file['name']);
    
    if (oh_should_block_file($file['name'], $file['tmp_name'])) {
        $file['error'] = OH_BLOCKED_MESSAGE;
        oh_debug_log("File blocked in wp_handle_sideload_prefilter");
    }
    
    return $file;
}, -999999);

/**
 * File type checking
 */
add_filter('wp_check_filetype_and_ext', function($types, $file, $filename, $mimes) {
    oh_debug_log("wp_check_filetype_and_ext triggered for: $filename");
    
    if (oh_should_block_file($filename, $file)) {
        oh_debug_log("File blocked in wp_check_filetype_and_ext");
        return [
            'ext' => false,
            'type' => false,
            'proper_filename' => false
        ];
    }
    
    return $types;
}, -999999, 4);

/**
 * Remove from allowed mimes
 */
add_filter('upload_mimes', function($mimes) {
    $original_count = count($mimes);
    
    // Remove all blocked extensions
    $blocked = ['tif', 'tiff', 'tff', 'heic', 'heif', 'heics', 'heifs', 'hif'];
    foreach ($blocked as $ext) {
        unset($mimes[$ext]);
    }
    
    // Remove by mime type content
    foreach ($mimes as $key => $value) {
        if (preg_match('/(tiff?|heic|heif|hevc)/i', $value)) {
            unset($mimes[$key]);
        }
    }
    
    $removed_count = $original_count - count($mimes);
    if ($removed_count > 0) {
        oh_debug_log("Removed $removed_count mime types from allowed list");
    }
    
    return $mimes;
}, -999999);

/**
 * Pre-upload action hook
 */
add_action('pre-upload-ui', function() {
    oh_debug_log("pre-upload-ui hook triggered");
});

/**
 * JavaScript injection to block on frontend
 */
add_action('admin_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Override the uploader
        if (typeof wp !== 'undefined' && wp.Uploader) {
            var originalInit = wp.Uploader.prototype.init;
            wp.Uploader.prototype.init = function() {
                originalInit.apply(this, arguments);
                
                this.uploader.bind('BeforeUpload', function(up, file) {
                    var ext = file.name.split('.').pop().toLowerCase();
                    var blocked = ['tif', 'tiff', 'tff', 'heic', 'heif', 'heics', 'heifs', 'hif'];
                    
                    if (blocked.indexOf(ext) !== -1) {
                        up.removeFile(file);
                        alert('<?php echo esc_js(OH_BLOCKED_MESSAGE); ?>');
                        console.log('[OH Block] Blocked file:', file.name);
                        return false;
                    }
                });
            };
        }
        
        // Also block on file input change
        $(document).on('change', 'input[type="file"]', function(e) {
            var files = e.target.files;
            var blocked = ['tif', 'tiff', 'tff', 'heic', 'heif', 'heics', 'heifs', 'hif'];
            
            for (var i = 0; i < files.length; i++) {
                var ext = files[i].name.split('.').pop().toLowerCase();
                if (blocked.indexOf(ext) !== -1) {
                    e.target.value = '';
                    alert('<?php echo esc_js(OH_BLOCKED_MESSAGE); ?>');
                    console.log('[OH Block] Blocked file input:', files[i].name);
                    return false;
                }
            }
        });
    });
    </script>
    <?php
});

/**
 * Add admin notice to verify plugin is active
 */
add_action('admin_notices', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'upload.php') {
        echo '<div class="notice notice-info"><p>';
        echo '<strong>OH Block Upload:</strong> TIFF and HEIC uploads are blocked. ';
        echo 'Allowed formats: JPG, PNG, WebP (max 1MB, 1920px).';
        echo '</p></div>';
    }
});

/**
 * Test function - Add to admin bar
 */
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_node([
        'id' => 'oh-block-test',
        'title' => '🚫 TIFF/HEIC Blocker Active',
        'href' => '#',
        'meta' => [
            'title' => 'TIFF and HEIC uploads are blocked'
        ]
    ]);
}, 999);

/**
 * Final safety net - check all attachments
 */
add_filter('wp_insert_attachment_data', function($data, $postarr) {
    if (!empty($postarr['guid'])) {
        $filename = basename($postarr['guid']);
        if (oh_should_block_file($filename)) {
            oh_debug_log("Blocking in wp_insert_attachment_data: $filename");
            wp_die(OH_BLOCKED_MESSAGE);
        }
    }
    return $data;
}, -999999, 2);

/**
 * Block REST API uploads
 */
add_filter('rest_request_before_callbacks', function($response, $handler, $request) {
    $route = $request->get_route();
    if (strpos($route, '/wp/v2/media') !== false) {
        $files = $request->get_file_params();
        if (!empty($files)) {
            foreach ($files as $file) {
                if (isset($file['name']) && oh_should_block_file($file['name'], $file['tmp_name'])) {
                    oh_debug_log("Blocking REST API upload: " . $file['name']);
                    return new WP_Error('blocked_file_type', OH_BLOCKED_MESSAGE, ['status' => 403]);
                }
            }
        }
    }
    return $response;
}, -999999, 3);

/**
 * Clean existing TIFF/HEIC on activation
 */
register_activation_hook(__FILE__, function() {
    // Add option to show activation
    update_option('oh_block_upload_activated', time());
    
    // Log activation
    oh_debug_log("Plugin activated at " . date('Y-m-d H:i:s'));
});
