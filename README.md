# OH Block TIFF & HEIC Uploads

A WordPress Must-Use (MU) plugin that blocks TIFF and HEIC/HEIF image uploads to ensure optimal website performance and compatibility.

## Features

- 🚫 **Blocks TIFF files** (.tif, .tiff)
- 🚫 **Blocks HEIC/HEIF files** (.heic, .heif, .heics, .heifs, .hif)
- ✅ **Must-Use Plugin** - Always active, can't be deactivated
- ✅ **Magic byte detection** - Detects actual file type regardless of extension
- ✅ **JavaScript validation** - Blocks files before upload starts
- ✅ **Custom error messages** - Clear feedback for users
- ✅ **Admin bar indicator** - Shows blocking status
- ✅ **Multiple upload method support** - Works with all upload methods

## Installation

### As Must-Use Plugin (Recommended)

Must-Use plugins are always active and cannot be deactivated through the WordPress admin.

1. Navigate to `/wp-content/mu-plugins/` directory
   - Create the `mu-plugins` folder if it doesn't exist
2. Download `oh-block-uploads.php` from this repository
3. Upload the file directly to `/wp-content/mu-plugins/`
4. The plugin is automatically active (no activation needed)

### Alternative: Regular Plugin Installation

If you prefer to use it as a regular plugin (can be deactivated):

1. Create folder `/wp-content/plugins/oh-block-uploads/`
2. Download `oh-block-uploads.php` into that folder
3. Activate in WordPress admin → Plugins

## File Structure

```
wp-content/
├── mu-plugins/
│   └── oh-block-uploads.php    # Must-Use plugin (recommended)
OR
├── plugins/
│   └── oh-block-uploads/
│       └── oh-block-uploads.php # Regular plugin
```

## Why Use Must-Use Plugin?

- **Always Active**: Cannot be accidentally deactivated
- **Security**: Ensures blocking is always enforced
- **Performance**: Loads before regular plugins
- **No Database Queries**: Doesn't add to active_plugins option
- **Simplicity**: Single file, no activation needed

## Usage

Once installed as MU plugin:

- **No activation required** - Works immediately
- **Cannot be deactivated** - Always protecting your site
- **Admin bar indicator** - Shows "🚫 TIFF/HEIC Blocked"
- **Upload attempts** - Blocked with clear error messages

## Recommended Image Formats

Instead of TIFF/HEIC, use:

- **JPG/JPEG** - For photographs
- **PNG** - For images with transparency
- **WebP** - For modern browsers (best compression)

### Optimization Guidelines
- Maximum file size: 1MB
- Maximum dimensions: 1920px width
- Use image compression tools before uploading

## Error Messages

Users will see specific messages:

- **TIFF Upload:** "❌ TIFF files (.tif, .tiff) are not allowed. Please convert to JPG or WebP format."
- **HEIC Upload:** "❌ HEIC/HEIF files are not allowed. Please convert to JPG or WebP format."

## File Conversion Tools

### Online Converters
- [CloudConvert](https://cloudconvert.com)
- [Convertio](https://convertio.co)
- [ILoveIMG](https://www.iloveimg.com)

### Desktop Software
- **Windows:** Photos app, Paint, IrfanView
- **Mac:** Preview, Photos app
- **Linux:** GIMP, ImageMagick

### Command Line (ImageMagick)
```bash
# Convert TIFF to JPG
convert image.tiff image.jpg

# Convert HEIC to JPG
convert image.heic image.jpg

# Batch conversion
mogrify -format jpg *.tiff
mogrify -format jpg *.heic
```

## Verification

To verify the MU plugin is active:

1. Check admin bar for "🚫 TIFF/HEIC Blocked" indicator
2. Go to Media Library - see info notice
3. Try uploading a TIFF/HEIC file - should be blocked
4. Check `/wp-content/mu-plugins/` for the file

## Debugging

To enable debug logging:

1. Edit `/wp-content/mu-plugins/oh-block-uploads.php`
2. Change `define('OH_BLOCK_DEBUG', false);` to `true`
3. Enable `WP_DEBUG` in `wp-config.php`
4. Check debug.log for `[OH Block Upload]` entries

## Compatibility

- WordPress 5.0+
- PHP 7.0+
- Works with Classic Editor and Block Editor (Gutenberg)
- Compatible with all upload methods
- Works alongside other plugins

## Uninstallation

### For MU Plugin
1. Delete `/wp-content/mu-plugins/oh-block-uploads.php`
2. Clear any caches

### For Regular Plugin
1. Deactivate in WordPress admin
2. Delete the plugin

## Repository

- **GitHub:** https://github.com/WPSpeedExpert/oh-block-uploads
- **Issues:** https://github.com/WPSpeedExpert/oh-block-uploads/issues

## License

GPL v2 or later

## Credits

Developed by [OctaHexa](https://octahexa.com) for [WP Speed Expert](https://github.com/WPSpeedExpert)

## Changelog

### Version 2.0.0
- Optimized for Must-Use plugin deployment
- Simplified code for MU compatibility
- Removed activation/deactivation hooks
- Added MU plugin indicators
- Updated documentation

### Version 1.6.0
- Improved error message display
- Added specific messages for TIFF vs HEIC
- Enhanced JavaScript validation
- Added admin notices

### Version 1.0.0
- Initial release
