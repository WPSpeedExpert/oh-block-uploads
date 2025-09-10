<?php
/**
 * Plugin Name:       OH – Block TIFF & HEIC Uploads
 * Plugin URI:        https://github.com/WPSpeedExpert/oh-block-uploads
 * Description:       Blocks TIFF and HEIC image uploads with custom error messaging. Must-Use plugin version.
 * Version:           2.0.0
 * Author:            OctaHexa
 * Author URI:        https://octahexa.com
 * License:           GPL v2 or later
 * Text Domain:       oh-block-uploads
 * 
 * Filename:          oh-block-uploads.php
 * Location:          /wp-content/mu-plugins/
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('OH_BLOCK_DEBUG', false); // Set to true for debugging
define('OH_BLOCKED_MESSAGE', '❌ TIFF and HEIC files are not allowed. Please convert to JPG or WebP format (max 1MB, 1920px recommended).');

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
        return $ext;
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
                    return 'TIFF';
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
                        return 'HEIC';
                    }
                }
            }
        }
        
        // Check mime type
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($tmp_name);
            if ($mime) {
                if (stripos($mime, 'tiff') !== false || stripos($mime, 'tif') !== false) {
                    oh_debug_log("Blocked by TIFF mime type: $mime");
                    return 'TIFF';
                }
                if (stripos($mime, 'heic') !== false || stripos($mime, 'heif') !== false || stripos($mime, 'hevc') !== false) {
                    oh_debug_log("Blocked by HEIC mime type: $mime");
                    return 'HEIC';
                }
            }
        }
    }
    
    return false;
}

/**
 * Get custom error message based on file type
 */
function oh_get_error_message($file_type) {
    if ($file_type === 'TIFF' || in_array($file_type, ['tif', 'tiff', 'tff'], true)) {
        return '❌ TIFF files (.tif, .tiff) are not allowed. Please convert to JPG or WebP format.';
    } elseif ($file_type === 'HEIC' || in_array($file_type, ['heic', 'heif', 'heics', 'heifs', 'hif'], true)) {
        return '❌ HEIC/HEIF files are not allowed. Please convert to JPG or WebP format.';
    }
    return OH_BLOCKED_MESSAGE;
}

/**
 * Primary filter - wp_handle_upload_prefilter
 */
add_filter('wp_handle_upload_prefilter', function($file) {
    $blocked_type = oh_should_block_file($file['name'], $file['tmp_name']);
    if ($blocked_type) {
        $file['error'] = oh_get_error_message($blocked_type);
    }
    return $file;
}, -999999);

/**
 * Block sideload
 */
add_filter('wp_handle_sideload_prefilter', function($file) {
    $blocked_type = oh_should_block_file($file['name'], $file['tmp_name']);
    if ($blocked_type) {
        $file['error'] = oh_get_error_message($blocked_type);
    }
    return $file;
}, -999999);

/**
 * File type checking
 */
add_filter('wp_check_filetype_and_ext', function($types, $file, $filename, $mimes) {
    $blocked_type = oh_should_block_file($filename, $file);
    if ($blocked_type) {
        // Store error message in transient
        set_transient('oh_blocked_upload_' . get_current_user_id(), oh_get_error_message($blocked_type), 10);
        
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
    $blocked = ['tif', 'tiff', 'tff', 'heic', 'heif', 'heics', 'heifs', 'hif'];
    foreach ($blocked as $ext) {
        unset($mimes[$ext]);
    }
    
    foreach ($mimes as $key => $value) {
        if (preg_match('/(tiff?|heic|heif|hevc)/i', $value)) {
            unset($mimes[$key]);
        }
    }
    
    return $mimes;
}, -999999);

/**
 * JavaScript injection to block on frontend
 */
add_action('admin_footer', function() {
    ?>
    <script>
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            // Override the uploader
            if (typeof wp !== 'undefined' && wp.Uploader) {
                var originalInit = wp.Uploader.prototype.init;
                wp.Uploader.prototype.init = function() {
                    originalInit.apply(this, arguments);
                    
                    this.uploader.bind('BeforeUpload', function(up, file) {
                        var ext = file.name.split('.').pop().toLowerCase();
                        var blockedTiff = ['tif', 'tiff', 'tff'];
                        var blockedHeic = ['heic', 'heif', 'heics', 'heifs', 'hif'];
                        
                        if (blockedTiff.indexOf(ext) !== -1) {
                            up.removeFile(file);
                            alert('❌ TIFF files (.tif, .tiff) are not allowed.\n\nPlease convert to JPG or WebP format.');
                            return false;
                        }
                        
                        if (blockedHeic.indexOf(ext) !== -1) {
                            up.removeFile(file);
                            alert('❌ HEIC/HEIF files are not allowed.\n\nPlease convert to JPG or WebP format.');
                            return false;
                        }
                    });
                };
            }
            
            // Block on file input change
            $(document).on('change', 'input[type="file"]', function(e) {
                var files = e.target.files;
                var blockedTiff = ['tif', 'tiff', 'tff'];
                var blockedHeic = ['heic', 'heif', 'heics', 'heifs', 'hif'];
                
                for (var i = 0; i < files.length; i++) {
                    var ext = files[i].name.split('.').pop().toLowerCase();
                    
                    if (blockedTiff.indexOf(ext) !== -1) {
                        e.target.value = '';
                        alert('❌ TIFF files (.tif, .tiff) are not allowed.\n\nPlease convert to JPG or WebP format.');
                        return false;
                    }
                    
                    if (blockedHeic.indexOf(ext) !== -1) {
                        e.target.value = '';
                        alert('❌ HEIC/HEIF files are not allowed.\n\nPlease convert to JPG or WebP format.');
                        return false;
                    }
                }
            });
        });
    }
    </script>
    <?php
});

/**
 * Add admin bar indicator
 */
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!current_user_can('upload_files')) {
        return;
    }
    
    $wp_admin_bar->add_node([
        'id' => 'oh-block-status',
        'title' => '🚫 TIFF/HEIC Blocked',
        'href' => admin_url('upload.php'),
        'meta' => [
            'title' => 'TIFF and HEIC uploads are blocked (MU Plugin Active)'
        ]
    ]);
}, 999);

/**
 * Add admin notice on media library
 */
add_action('admin_notices', function() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'upload') {
        echo '<div class="notice notice-info is-dismissible"><p>';
        echo '<strong>Upload Restrictions (MU Plugin):</strong> TIFF (.tif, .tiff) and HEIC (.heic, .heif) files are blocked. ';
        echo 'Please use JPG, PNG, or WebP formats.';
        echo '</p></div>';
    }
});

// Log that MU plugin is loaded (only if debug is enabled)
oh_debug_log('MU Plugin loaded successfully');
