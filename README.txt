=== Smart Fonts for Elementor ===
Contributors: codevision
Donate link: https://www.codevision.io
Tags: elementor, fonts, support
Requires at least: 5.9.3
Requires PHP: 7.4
Tested up to: 6.0.0
Stable tag: 2.1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adding your beloved TrueType Fonts to your website was never easier before. Comes also with full Elementor Styling Support!

== Description ==

Did you ever wanted to use a font for the website of your company that does not exist on Google Fonts or that is not available as default typography such as Arial or Times New Roman at all?

Would you like to incorporate beautiful handwritings into your website, for example to present a signature?

This is now possible with Smart Fonts for Elementor! Here you can automatically integrate your .ttf font (True Type Font) into your website with only one click. No coding skills required! Just upload your font and you can use the typography on the entire website.

In addition, we have integrated a feature that allows you to use these fonts in the Elementor Page Builder – which was never been possible before!

With our built-in free TrueType Converter service, it’s easy to convert any TrueType fonts (.ttf) into WebFont packages.

These work in any (modern) browser and on any device available. Our plugin generates the appropriate CSS definitions and files for you automatically so that you can start using your favourite fonts immediatly.

Our plugin comes with full Elementor support for your favorite fonts. Once you have uploaded your TTF file, it will automatically appear as a new font in the Elementor Font selection „Style“ » „Typography“. We have added our own group „Smart Fonts“ to the listing, so that you can find them better.

Our plugin generates a separate CSS file for each uploaded TrueType font. This file contains all the necessary information to insert your favorite font directly. In addition, a new CSS class with the name of the font will be added so you can easily display any text in your new font. This works everywhere on your website now!

== Legal Notices ==

Attention: This plugin uses an external service hosted on our servers located in germany to process your TTF file.

= Service URL =

https://webfont.codevision.io

= Privacy Statement and Terms of Service =

We do not collect any personal information on the website.
However, we reserve the right to limit the potential unauthorized use of this service at a later date, while allowing authorized users who present an API key the full access again.

The file(s) you upload there will be converted into a webfont zip archive which contains the following converted variants of it:

The original font file(s) provided
- A converted font in WOFF format
- A converted font in WOFF2 format
- A converted font in SVG format
- A converted font in TTF format
- A converted font in EOF format

After converting the files we delete your uploaded Fonts from our servers. We do not keep any files you upload there.

By uploading, you assure that you own the rights to use this/these file(s) and you grant permission to codevision.io to convert the/these file(s) into a webfont.

Please visit https://webfont.codevision.io for more information

= Imprint =

https://www.codevision.io/en/imprint/

= How do we process your data =

After uploading a TTF File with the form provided by this plugin, we will send your File and your license key for verification purposes to our service at
https://webfont.codevision.io, which we are hosting on a german server abiding the strict german laws about your privacy. This service then converts your TTF file into
a set of webfont files, which are required to be used by all the different browsers. After converting your TTF the file is removed from the Service.
If you want to know more about how the service works, please visit: https://webfont.codevision.io.


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `codevision-elementor-smart-fonts` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==
1. The Configuration Screen. Click the "Add File" Button to upload/select a TTF-File. Click the Update button on the right side and let the plugin do its magic.
2. Here you can see the License status of the plugin. The Key shown here is not working anymore, duh!

== Changelog ==

= 2.1.3 =
* WP 5.7.2 Support
* Latest ACF Support
* Elementor 3.3.x Support
* Elementor Pro 3.3.x Support

= 2.1.2 =
* WP 5.7 Support
* Latest ACF Support
* Elementor 3.1.4 Support

= 2.1.1 =
* WP 5.5.3 Support
* Latest ACF Support

= 2.1.0 =
* Added cache refresh for generated css files
* Stability Improvements

= 2.0.0 =
* Added font preload
* Latest security updates
* Improved compatibility to latest elementor version
* Stability Improvements

= 1.4.0 =
* Wordpress 5.4 ready
* Latest security updates
* Improved compatibility to latest elementor version
* Stability Improvements

= 1.3.0 =
* Preparations for wordpress 3.4
* Minor wordpress related fixes
* Latest security updates
* Improved compatibility to latest elementor version
* Stability Improvements

= 1.2.0 =
* Allow greater variety of true type fonts
* Latest security updates
* Improved compatibility to latest elementor version
* Stability Improvements

= 1.1.0 =
* Removed PHP 7.0 Compatibility
* Removed Wordpresss 5.0 Compatibility
* Stability Improvements
* New Dashboard Widget

= 1.0.5 =
* Updated Plugin to work with Wordpress 5+ nicely.
* Added new environment check after activation to ensure a working website

= 1.0.4 =
* Bugfix: Missing dependencies in main class causing errors when trying to upload a font. We are deeply sorry for this bug...

= 1.0.3 =
* The first Release, yay!
* Reverted the removal of cache folder (user should keep their converted webfonts regardless of the state of the plugin)
* optimized the readme.txt
* prepared everything for public release

= 1.0.2 =
* API Access to https://webfont.codevision.io working
* Added Licensing system
* Upon Addon Removal, remove the cache folder

= 1.0.1 =
* Testing the connection with https://webfont.codevision.io with guzzle
* Added prefix to generated css Classes

= 1.0.0 =
* Initial Commit, only the barebone of the plugin, nothing special here, go along ;)

== Upgrade Notice ==

Please update as soon as possible to 1.0.4, due to a dependency bug, the required helper classes could not be included
which causes errors while trying to upload fonts

No prior updates required, this is the very first release

== Frequently Asked Questions ==

= How does this plugin work?

After uploading a TTF File with the form provided by this plugin, we will send your File and your license key for verification purposes to our service at
https://webfont.codevision.io, which we are hosting on a german server abiding the strict german laws about your privacy. This service then converts your TTF file into
a set of webfont files, which are required to be used by all the different browsers.
For every font uploaded we add a new entry to your elementor font selection and also create a special css file for you which defines a css class you then can use throughout your website to correctly display your fonts.

= Do you rely on external services? =

Yes, we rely on a specific external service.
To give you the best experience possible and the least hassle with your fonts, we use our own service hosted on servers located in germany (abiding to the strict german privacy laws)
to convert your true-type-fonts to webfont packages.
Prior you have had to do it on your own, now our plugin deals with everything regarding this matter.

= What about the Features of Elementor Pro regarding Uploading my own font? =

Of course, you can use the paid version of Elementor Pro to upload existing webfonts and using them, but keep in mind that you have to upload many different font types(!) for
every browser imaginable to be compatible with every device.

= What about all the other custom font uploader ? =

Well, as long as you already have a working webfont (package), there is nothing wrong in using those addons as well.
But if you only have the ttf file our plugin comes to the rescue. It removes all the hassle to convert upload and create css classes for you.
