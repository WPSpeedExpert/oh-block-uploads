# OH Block TIFF & HEIC Uploads

A WordPress plugin that blocks TIFF and HEIC/HEIF image uploads to ensure optimal website performance and compatibility.

## Features

- 🚫 **Blocks TIFF files** (.tif, .tiff)
- 🚫 **Blocks HEIC/HEIF files** (.heic, .heif, .heics, .heifs, .hif)
- ✅ **Magic byte detection** - Detects actual file type regardless of extension
- ✅ **JavaScript validation** - Blocks files before upload starts
- ✅ **Custom error messages** - Clear feedback for users
- ✅ **Admin bar indicator** - Shows blocking status
- ✅ **Multiple upload method support** - Works with drag & drop, media library, REST API

## Why Block These Formats?

### TIFF Files
- Large file sizes that slow down websites
- Not web-optimized
- Poor browser compatibility
- Can cause server processing issues

### HEIC/HEIF Files
- Limited browser support
- Requires server-side conversion
- Can cause compatibility issues
- Not universally supported by WordPress

## Installation

### Method 1: Manual Installation

1. Download the `oh-block-tiff-heic-uploads.php` file
2. Upload to your WordPress `/wp-content/plugins/` directory
3. Navigate to **Plugins** in WordPress admin
4. Activate **OH – Block TIFF & HEIC Uploads**

### Method 2: Direct Upload

1. Download the `oh-block-tiff-heic-uploads.php` file
2. Go to **Plugins > Add New** in WordPress admin
3. Click **Upload Plugin**
4. Choose the file and click **Install Now**
5. Activate the plugin

### Method 3: Create Plugin File

1. Navigate to `/wp-content/plugins/` directory
2. Create a new file named `oh-block-tiff-heic-uploads.php`
3. Copy and paste the plugin code
4. Save the file
5. Activate in WordPress admin

## Usage

Once activated, the plugin works automatically:

- **Upload attempts** of TIFF/HEIC files will be blocked
- **Error messages** will inform users to convert to JPG/WebP
- **Admin bar** shows "🚫 TIFF/HEIC Blocked" indicator
- **Media Library** displays a notice about file restrictions

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

Users will see specific messages when uploading blocked files:

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
