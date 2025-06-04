# Changelog

## 3.1.27 (April 3, 2024)

### Fixed

- Fix Maps widget

## 3.1.26 (March 22, 2024)

### Fixed

- Fix TinyMCE Editor (Joomla)

## 3.1.25 (March 13, 2024)

### Changed

- Use download key for updates (Joomla)

## 3.1.24 (February 13, 2024)

### Fixed

- Fix selecting widgets in TinyMCE Editors (Joomla)

## 3.1.23 (January 11, 2024)

### Fixed

- Fix deprecated usage of JError in installer script (Joomla)

## 3.1.22 (December 27, 2023)

### Fixed

- Fix scoped CSS for '3rd Party Theme' support
- Fix truncating text when string length is exceeded

## 3.1.21 (October 4, 2023)

### Fixed

- Fix MySQL 8+ compatibility in Widgetkit K2 plugin

## 3.1.20 (September 19, 2023)

### Add

- Add Joomla 5 support

### Fixed

- Fix "Clear Cache" button (Joomla 4+)

## 3.1.19 (July 31, 2023)

### Fixed

- Fix PHP 8.2+ deprecation warning

## 3.1.18 (April 24, 2023)

### Fixed

- Fix PHP 8.2+ deprecation warning

## 3.1.17 (April 20, 2023)

### Fixed

- Fix regression in media picker (Joomla 4.3+)

## 3.1.16 (March 16, 2023)

### Fixed

- Fix Google Maps API error message
- Fix routing regression in 3rd party components

## 3.1.15 (January 13, 2023)

### Fixed

- Fix double html encoded asset urls
- Fix YOOtheme Pro widget element widget picker

## 3.1.14 (November 7, 2022)

### Changed

- Replace deprecated Algolia Places with Google Maps geocoding

### Fixed

- Fix PHP 8 deprecation warnings

## 3.1.13 (October 10, 2022)

### Fixed

- Fix joomla content provider when `showTags` enabled (Joomla)
- Fix registerAssets compatibility (Joomla)

## 3.1.12 (August 4, 2022)

### Fixed

- Fix incompatibility with Toolset Types plugin (WordPress)

## 3.1.11 (July 13, 2022)

### Fixed

- Fix file upload in media manager (Joomla 4)

## 3.1.10 (July 7, 2022)

### Fixed

- Fix PHP 8.0 deprecation warnings
- Fix map dragging and zooming option
- Fix configurable file path in media manager (Joomla 4)

## 3.1.9 (February 21, 2022)

### Fixed

- Fix PHP 8.0 deprecation warnings

## 3.1.8 (December 15, 2021)

### Fixed

- Fix missing tmpl/default.php file in mod_widgetkit (Joomla)

## 3.1.7 (December 14, 2021)

### Added

- Add support for mod_widgetkit overrides (Joomla)

### Fixed

- Fix 'the_content' filter is no longer directly applied to content (WordPress)

## 3.1.6 (November 23, 2021)

### Fixed

- Fix card badge style in Grid, Grid Slider and Slideset widgets
- Fix twitter api authorization

## 3.1.5 (October 20, 2021)

### Fixed

- Fix error in smart search plugin when saving article (Joomla)

## 3.1.4 (October 6, 2021)

### Fixed

- Fix html entities in widget markup

## 3.1.3 (September 29, 2021)

### Fixed

- Fix compatibility issue with Divi (WordPress)

## 3.1.2 (September 27, 2021)

### Fixed

- Fix editor initialization with 3rd party xtd-button plugins (Joomla)

## 3.1.1 (September 24, 2021)

### Fixed

- Fix conflict with xtd-button plugins (Joomla)
- Fix Widgetkit xtd-editor button in Editor fields (Joomla)

## 3.1.0 (September 13, 2021)

### Added

- Add Joomla 4 support
- Add support for any Joomla editor plugin (Joomla)

### Changed

- Use own tinyMCE as visual editor (Joomla)

### Fixed

- Fix Panel `Card Default/Card Secondary` option in grid widget
- Fix missing sortable drag in custom content provider
- Fix Widgetkit Gutenberg block on widgets edit screen (WordPress)

## 3.0.20 (May 26, 2021)

### Fixed

- YOOtheme installer plugin is not removed during uninstall if other extensions still rely on it
- Fix php warning in rss and twitter plugin

## 3.0.19 (May 4, 2021)

### Fixed

- Fix badge position in grid widgets with blank panel style
- Fix error being thrown if installed with 1.x version of YOOtheme Pro

## 3.0.18 (April 6, 2021)

### Fixed

- Fix maps not displayed with caching enabled (Joomla)
- Fix missing closing tags in html markup

## 3.0.17 (March 17, 2021)

### Fixed

- Fix adding assets multiple times

## 3.0.16 (March 16, 2021)

### Fixed

- Fix maps widget (WordPress)

## 3.0.15 (March 15, 2021)

### Fixed

- Fix widget select uses thickbox as modal (WordPress)

## 3.0.14 (March 11, 2021)

### Fixed

- Fix image upload (Joomla)

## 3.0.13 (March 9, 2021)

### Fixed

- Fix tags filter in gallery and grid slider widgets

## 3.0.12 (March 9, 2021)

### Fixed

- Fix tag filter
- Fix 'Select Widget' button in customizer (WordPress)

## 3.0.11 (March 5, 2021)

### Fixed

- Fix image upload (Joomla)

## 3.0.10 (March 3, 2021)

### Fixed

- Fix selecting featured image in Woocommerce product edit view (WordPress)

## 3.0.9 (March 3, 2021)

### Fixed

- Fix marker clustering in Map widget

## 3.0.8 (March 2, 2021)

### Fixed

- Change URL generation to use content directory (WordPress)

## 3.0.7 (February 16, 2021)

### Added

- Add resizing for webp images

### Fixed

- Fix Widgetkit YOOtheme pro element (Wordpress)
- Fix Media Manager modal z-index issue (WordPress)
- Fix TinyMCE UI border (WordPress)

## 3.0.6 (February 05, 2021)

### Fixed

- Fix images not being resized

## 3.0.5 (February 04, 2021)

### Fixed

- Rename '3rd Party Theme' setting (WordPress)

## 3.0.4 (February 04, 2021)

### Change

- Gallery element appends modal and lightbox to container element in document body

### Fixed

- Fix loading editor (Joomla)
- Fix saving '3rd Party Theme' setting (Joomla)

## 3.0.3 (February 02, 2021)

### Added

- Add '3rd Party Theme' setting to control how Widgetkit loads UIkit

### Fixed

- Fix images not being resized
- Fix normal links from being opened broken in the lightbox in Gallery element
- Fix Maps widget only includes its assets when used
- Fix YOOtheme Pro Widget element uses iframe based widget picker

## 3.0.2

### Changed

- Move assets folder to 'media/com_widgetkit' (Joomla)
- Add check to prevent update from Widgetkit 2 to Widgetkit 3 if Warp theme is detected (Joomla)

### Fixed

- Fix image type detection
- Fix notifications

## 3.0.1

### Changed

- Use file locator for image paths

## 3.0.0

### Added

- Add `xl` for large screens to breakpoint settings
- Add HTML element option for the title to all widgets
- Add `small` and `medium` options to slide animations settings
- Add more slidenav position options to Slideset widget
- Add more list type options to List widget

### Changed

- Update to UIkit 3
- Change scrollspy delay to 200ms
- Prevent box-shadows from being clipped in Slideset widget

### Removed

- Remove jQuery dependency

## 2.9.27

### Fixed

- Fix warnings thrown by Instagram provider

## 2.9.26

### Fixed

- Fix wrong timezone for ZOO date fields

## 2.9.25

### Fixed

- Fix warning message in WordPress content provider with PHP 7.4

## 2.9.24

### Fixed

- Fix warning message in gallery widget with PHP 7.3

### Changed

- Increased cache time in Instagram content provider

## 2.9.23

### Fixed

- Fix missing slideset link title
- Fix instagram content provider

## 2.9.22

### Fixed

- Fixed media alignment setting in slideset widget

## 2.9.21

### Fixed

- Fixed render images containing whitespace in filename
- Fixed no-conflict mode (J)

## 2.9.20

### Fixed

- Fixed Google Maps API key error in ZOO content provider (J)
- Fixed missing title attribute in links

## 2.9.19

### Fixed

- Fixed marker popup settings in maps widget
- Fixed articles published ordering in Joomla content provider (J)

## 2.9.18

### Changed

- Updated instagram content provider
- Strip whitespace in Google Maps and YOOtheme API key setting

### Fixed

- Fixed php notice in k2 provider

## 2.9.17

### Changed

- Updated instagram content provider

## 2.9.16

### Changed

- Updated twitter content provider to support 280 characters

### Fixed

- Fixed autoplay-interval default value in slideshow-, slider- and slideset widgets

## 2.9.15

### Fixed

- Fixed loosing fonts after cache clear on Windows systems

## 2.9.14

### Changed

- Updated Image Cache creation

## 2.9.13

### Changed

- Updated instagram content provider
- Updated RSS content provider: trim links containing whitespace

### Fixed

- Fixed article hits ordering in Joomla content provider (J)
- Fixed media picker with custom path settings (J)

## 2.9.12

### Added

- Added check if PHP GD is available

### Fixed

- Fixed switching items in JCE code view (J)
- Fixed deprecated plugin 'wpembed' in system editor (WP)

## 2.9.11

### Fixed

- Fixed media upload (J)

## 2.9.10

### Fixed

- Fixed look for plugins in child themes (WP)

## 2.9.9

### Added

- Add look for plugins in child themes (WP)

### Fixed

- Fixed loosing fonts after cache clear

## 2.9.8

### Changed

- Updated UIkit2 to latest version
- Updated twitter content provider to load media over https
- Updated frontend editing (J)

### Fixed

- Fixed datepicker field
- Fixed select article button in editor (J)
- Fixed error in JCE for Joomla 3.7.x

## 2.9.7

### Changed

- Updated UIkit2 to the newest version

### Fixed

- Fix for the newest JCE editor version

## 2.9.6

### Fixed

- Fixed system editor (WP)

## 2.9.5

### Fixed

- Fixed wysiwyg editor settings

## 2.9.4

### Fixed

- Fix YOOthemePro compatibility

## 2.9.3

### Added

- Added disable swipe option in switcher widget

### Fixed

- Fixed wysiwyg editor keep absolute_url setting
- Fixed use fontawesome from media folder when installed with YOOtheme Pro Template
- Fixed media picker compatibility for Joomla 3.7
- Fixed error messages in Media Picker (J)
- Fixed uploading .svg images (WP)

## 2.9.2

### Fixed

- Fixed list widget headings
- Fixed missing htmleditor field
- Fixed modal/lightbox in no-conflict mode

## 2.9.1

### Changed

- Updated theme support check (J)

### Fixed

- Fixed asset combine for UIkit scripts

## 2.9.0

### Added

- Added no-conflict mode
- Added ordering option for publish date in Joomla content provider (J)

### Fixed

- Fixed missing clear cache button (J)

## 2.8.2

### Fixed

- Fixed wysiwyg editor

## 2.8.1

### Fixed

- Fixed instagram content provider
- Fixed enable 'parse_shortcodes' in existing widgets
- Fixed PHP 5.3 compatibility

## 2.8.0

### Added

- Added system editor support on content fields
- Added cache clearing function
- Added language filter to article helper
- Added link media option in slideshow widget
- Added option to deactivate parsing shortcodes in the widget content

### Fixed

- Fixed parallax content width with given image width
- Fixed https protocol errors on Vimeo api
- Fixed truncating strings with no spaces
- Fixed large tooltip for lightbox slideshow in gallery widget

## 2.7.9

### Fixed

- Fixed ZOO + K2 content provider

## 2.7.8

### Fixed

- Fixed ZOO + K2 content provider

## 2.7.7

### Added

- Added date format option in grid widget
- Added load jQuery in frontend if not already loaded (J)
- Added text sizes h1-h6 in widgets
- Added random order option in custom content provider

### Fixed

- Fixed MooTools hiding 'New Field' button in widgetkit modal view (J)

## 2.7.6

### Added

- Added blacklist for twitter content provider
- Added missing google maps api key warning in location field

### Changed

- Updated popover description label
- Updated grid-widget panel sequence

### Fixed

- Fixed javascript error in slideshow-panel widget
- Fixed wordpress content provider content field excerpt (WP)
- Fixed show widgetkit within menu for users with the capability 'manage_widgetkit' (WP)

## 2.7.5

### Added

- Added featured articles ordering in Joomla content provider

### Fixed

- Fixed handling of multiple extrafields in K2 content provider
- Fixed Google API key error

## 2.7.4

### Fixed

- Fixed error in slider- & parallax-widget
- Fixed broken twitter post ids

## 2.7.3

### Fixed

- Fixed PHP 5.3 compatibility
- Fixed map widget warnings

## 2.7.2

### Added

- Added support for @3x density images (supported by iPhone 6+)
- Added placeholder image as fallback in WooCommerce provider (WP)
- Added iframe size options into custom content provider
- Added parallax min-width option

### Fixed

- Fixed link required to enable overlay in grid-stack widget
- Fixed wrong image path with nice URLs (WP)
- Fixed custom map marker image src
- Fixed maps cluster icon sources
- Fixed disappearing widgetkit button (WP)

## 2.7.1

### Fixed

- Fixed preview within media picker (J)
- Fixed use of deprecated function add_object_page() (WP)
- Fixed images invisible in gallery, grid and grid-slider widgets
- Fixed slideshow-panel autoplay pauseOnHover

## 2.7.0

### Added

- Added content rss content provider
- Added custom field 'date'
- Added custom field 'pathpicker'
- Added save place information if using google autocompleter for maps

### Fixed

- Fixed warning in Instagram content provider when open_basedir is used
- Fixed media picker selecting videos (J)
- Fixed loading article tags for filter function also when setting is disabled (J)
- Fixed instagram caching error of emoticons

## 2.6.5

### Changed

- Updated UIkit to 2.26.2

## 2.6.4

### Fixed

- Fixed ZOO/K2 plugins not loading

## 2.6.3

### Added

- Added customizable map marker
- Added gutter large option

### Fixed

- Fixed switcher not loaded in Zoo content provider
- Fixed gallery / grid / grid-slider overlapping images wrong selector
- Fixed default mapping in K2 content provider
- Fixed mapping of K2 extra fields
- Fixed instagram content provider for php 5.3
- Fixed Notice when using random sorting in folder content

## 2.6.2

### Fixed

- Fixed Joomla content provider with missing tags property


## 2.6.1

### Added

- Added media2 image to Joomla content provider

### Fixed

- Fixed items not sortable in custom content provider

## 2.6.0

### Added

- Added instagram content provider
- Added HTML editor field
- Added Joomla 3.5 compatibility
- Added map widget directions text into language file
- Added textfield for sorting filter tags

### Changed

- Changed content field and gallery lightbox content to HTML editor
- Moved Font Awesome icon files to media folder (J)

### Fixed

- Fixed custom content editing for iPad
- Fixed gallery / grid / grid-slider dynamic grid overlapping images

## 2.5.3

### Fixed

- Fixed truncate helper warning

## 2.5.2

### Fixed

- Fixed truncate helper function
- Fixed PHP 5.3 compatibility

## 2.5.1

### Added

- Added truncate helper function

### Fixed

- Fixed resize images for slideshow modal in gallery
- Fixed map get direction for marker set to "show" only
- Fixed ZOO GoogleMaps API warning if location is empty

## 2.5.0

### Added

- Added custom content required fields
- Added required fields map & popover widget
- Added slideshow-panel widget
- Added switcher-panel widget
- Added popover widget
- Added list widget
- Added new slideshow modal to gallery
- Added panel style sequence option in grid widget
- Added media2 to core custom fields
- Added alternative lightbox image to gallery
- Added contrast color option to parallax
- Added alternative content field for the lightbox to gallery

### Fixed

- Fixed gallery lightbox width / height settings
- Fixed media 'auto' width / height settings
- Fixed map get direction without content
- Fixed caching resized images (WP)

## 2.4.8

### Changed

- Removed doubled content field in WP content provider (WP)

### Fixed

- Fixed image file format case sensitive (J)
- Fixed show template specific widgets only (J)

## 2.4.7

### Changed

- Moved image cache to /media (J)

## 2.4.6

### Added

- Added dropdown.less, fixes responsive tabs

### Changed

- Updated maps widget: get directions link always opens in new tab/window
- Removed include redundant subcategories checkbox in Joomla content provider
- Updated language file

### Fixed

- Fixed caching thumbnails images

## 2.4.5

### Fixed

- Fixed ZOO item field mapping
- Fixed encoding blanks in image filenames
- Fixed prevent double_encoding htmlspecialchars in image src

## 2.4.4

### Added

- Added missing language strings

### Fixed

- Fixed Folder Content provider issue on non GNU systems
- Fixed Folder Content provider issues with file titles

## 2.4.3

### Added

- Added images from folders are loaded via add media
- Added Joomla category multi selection
- Added Joomla modified order option
- Added max images option to folder content
- Added autoplay options to slider

### Changed

- Updated content provider markup

### Fixed

- Fixed K2 image source
- Fixed slideset filter options
- Fixed widget selection in the frontend
- Fixed different protocols in the link
- Fixed widget settings merge
- Fixed ZOO/K2/Joomla Item/Articles links

## 2.4.2

### Fixed

- Fixed sorting items in custom content provider

## 2.4.1

### Added

- Added support for shortcodes in custom content

## 2.4.0

### Added

- Added Twitter content provider
- Added widget type filter to list view
- Added option for kenburns animation to slideshow

### Changed

- Changed click events to anchors at map widget

## 2.3.1

### Fixed

- Fixed ZOO & K2 mapping (date, author, categories)

## 2.3.0

### Added

- Added slideset widget
- Added slider widget
- Added parallax widget
- Added folder content provider
- Added filter option to show all items
- Added option to use a second image as overlay to widgets
- Added option to open all links in a new window
- Added option to close first item initially to accordion
- Added option for the kenburns duration to slideshow
- Added option for the content text size to slideshow
- Added breakpoint option to grid stack
- Added vertical gutter option to grids
- Added button link option to all widgets
- Added gutter medium option to grid widgets
- Added responsive tabs for filter nav
- Added meta data support for content providers to grid widget
- Added ZOO reversed ordering

### Fixed

- Fixed media breakpoint option in all widgets
- Fixed button functionality when creating a new widget
- Fixed content keeping when switching the widget-type of an unsaved widget
- Fixed HTML tags for image alt attribute
- Fixed maps.js for IE10/11
- Fixed ZOO ordering

## 2.2.1

### Added

- Added active state for selected widgets

### Fixed

- Fixed relative URL conversion

## 2.2.0

### Added

- Added copy functionality for widgets

### Changed

- Reworked admin UI by coupling widget settings and content
- Moved widget settings from shortcode to database

## 2.1.5

### Added

- Added escaping of content link and social links

### Fixed

- Fixed email cloaking conflict (J)

## 2.1.4

### Added

- Added dotnav to nav options in switcher
- Added support to create a slideshow without media element
- Added ZOO item reference in item object

### Fixed

- Fixed PHP notice in gallery
- Fixed missing first folder in Joomla media picker
- Fixed image paths in maps widget
- Fixed ZOO edit view fields reseting issue

## 2.1.3

### Added

- Added last option for media position in switcher

### Fixed

- Fixed Zoo mapping issues
- Fixed 1-Click Updates issues

## 2.1.2

### Added

- Added multiple media select with shift-key (J)
- Added filter sorting + uppercase for filter words
- Added title auto-format after add media

### Changed

- Updated responsive behavior of grid-stack

### Fixed

- Fixed missing language folder (J)
- Fixed heading margin-bottom in gallery
- Fixed customizer in WordPress
- Fixed double animation parameter in grid-slider
- Fixed get item option for php 5.3

## 2.1.1

### Added

- Added title and title size option to accordion

### Changed

- Updated custom field override priority

### Fixed

- Fixed navigation position option in switcher
- Fixed social button alignment in all widgets
- Fixed lightbox link in gallery
- Fixed jumping to top of the page when clicking the Widgetkit button
- Fixed multiple media fields and its meta data
- Fixed thumbnail calculation in slideshow
- Fixed broken Joomla cache for ZOO (J)
- Fixed autofocus for content title


## 2.1.0

### Added

- Added translation support
- Added gallery widget with lightbox
- Added grid-slider widget
- Added accordion widget
- Added dynamic grid and filter options to grid widget
- Added new overlay and image animations to all widgets
- Added image resize options to all widgets
- Added option to use an alternative image as thumbnail
- Added support for CKEditor
- Added Widgetkit shortcodes for text widgets (WP)
- Added custom field mappings for content providers (WordPress, WooCommerce, Joomla, Zoo, K2)
- Added marker cluster option for map widget
- Added badge field to grid widgets
- Added better overlay link to all widgets
- Added support for PCRE versions pre 7.0

### Changed

- Updated all widgets according to UIkit 2.17.0
- Optimized all widget options

### Fixed

- Fixed zoomwheel setting for map widget
- Fixed directions setting for map widget

## 2.0.7

### Fixed

- Fixed adding Video URLs to Custom Content Type

## 2.0.6

### Added

- Added ZOO link mapping
- Added better support for ZOO plugin 3rd party integrations

## 2.0.5

### Added

- Added selected Widget indicator in the Widget/Module buttons

### Changed

- Updated UIkit to 2.16.2

### Fixed

- Fixed another path issue on installations located in root

## 2.0.4

### Added

- Added improved default item name (J)

### Fixed

- Fixed default item name on media select
- Fixed Google Maps search results z-index
- Fixed ZOO Content Categories list display issue
- Fixed path issue on installations located in root

## 2.0.3

### Fixed

- Fixed editor button

## 2.0.2

### Added

- Added content view modes and filter
- Added Google Maps API lazy loading
- Added Advanced Module Manager compatibility
- Added link none option for Grid, Grid-Stack and Switcher
- Added RokPad Editor support
- Added error notifications when uploading media (J)
- Added image option to Joomla content plugin (J)

### Fixed

- Fixed routing issues
- Fixed Vimeo media parameters
- Fixed slideshow nav hidden on touch devices
- Fixed margin in modal when editing content (J)
- Fixed Grid-Stack text align on small devices
- Fixed incompatibility with older Composer versions
- Fixed overlay if media has rounded border for Grid, Grid-Stack and Switcher

## 2.0.1

### Added

- Added site styles/scripts caching
- Added featured filter for Joomla content (J)
- Added JCE editor compatibility

## 2.0.0

### Added

- Initial Release
