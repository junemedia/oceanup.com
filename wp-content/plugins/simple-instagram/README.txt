=== Simple Instagram ===
Contributors: mr_speer, werkpress
Donate link: http://aaronspeer.com/simple_instagram_donation.php
Tags: instagram, social, simple, Instagram, feed, pictures
Requires at least: 3.5.1
Tested up to: 4.2.2
Stable tag: 2.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple, versatile plugin that allows you to display your Instagram feed, profile, and popular Instagram posts using shortcodes and widgets. 

== Description ==

= Simple Instagram just got Simpler =

New in version 2.0 - authorize your account in a single step. Now it's quicker and easier than ever to get your Instagram feed up and running on your site! Getting your feed up and running just takes one step - authorize your account and you're ready to go! Getting an Instagram feed on your site has never been easier. 

*Note:* If you are upgrading from a previous version, you'll need to re-authorize your Instagram account on the Settings page. Once you do, all of your shortcodes and widgets will continue to work as before. 

= Finally, a simple, versatile Instagram plugin for your blog. =

Simple Instagram allows you to display your Instagram profile information, image/video feed, or Popular posts from Instagram. It includes both shortcodes to use within your posts and widgets to include in your sidebar. 

To help ease installation, the setting page includes simple, step-by-step instructions with links and detailed descriptions. 

The plugin uses Instagram’s PHP API, so you don’t need to worry about Javascript conflicts. Plus, the settings page monitors your connection to the API, so if anything goes wrong, you’ll know right away. 

The feed shortcodes and widgets allow you to specify wrapper type (div or li), whether the images are linked to their respective Instagram instances, user ID, tags, image size, and number of images pulled. The resulting feed uses unique class names and minimal styling in order to make it as easy as possible to integrate into your blog’s look and feel.  

The profile shortcode and widget allow you to specify which elements are included and give you the option of displaying an unformatted layout (easier to customize) or one with minimal styling applied for a quicker setup.  


== Installation ==


= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for ‘Simple Instagram’
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `simple-instagram.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `simple-instagram.zip`
2. Extract the `simple-instagram` directory to your computer
3. Upload the `simple-instagram` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

= Registering and Activating the Instagram App =

Once you have the plugin installed, go to Settings->Simple Instagram and follow the steps displayed there. Once you have successfully completed those steps, the section titled “Authorize Your Instagram Application” will read “Alright! You're all set up and ready to go!”

If at any time your authorization with Instagram expires, you’ll simply need to re-click the link in Step 03. 

= Basic Shortcode Usage =

**Feed Shortcode**

To display a feed of your Instagram posts, use the short code `[si_feed]`. You may optionally include the following parameters:

* limit - The maximum number of items to include
* size - Image size. Options are `small`, `medium`, and `full`
* wrapper - The element used as the item wrapper. Options are `div` and `li`
* link - Whether the items should link to their respective Instagram entities. Boolean `true` or `false`
* width - Used to call a *specific* image size in pixels. Use instead of “Size”.
* user - Used to specify which user to pull the feed from. Default is your own profile. Must be the User ID (not user name). 
* tag - A tag to pull media from. Specifying a tag will override the user setting. Eg. 'food' or 'hipsters'.

**Popular Shortcode**

To display a feed of Popular Instagram posts, use the short code `[si_popular]`. This short code employs the same parameters as the `[si_feed]` short code. 

**Profile Shortcode**

To display your Instagram Profile information, use the short code `[si_profile]`. You may optionally include the following parameters:

* username - Include username. Boolean `true` or `false`
* profile_picture - Include your profile picture. Boolean `true` or `false`
* bio - Include your Instagram bio. Boolean `true` or `false`
* website - Include your Instagram website. Boolean `true` or `false`
* full_name - Include your full name. Boolean `true` or `false`
* themed - Display your profile with some simple theming applied. Useful for quickly deploying your profile. Boolean `true` or `false`

== Frequently Asked Questions ==

= My feeds/profile suddenly disappeared! What’s wrong? =

Chances are you either updated your App credentials in Instagram, or your authorization expired. Simply visit Settings->Simple Instagram and follow Step 02 and Step 03 again. 

= Why do I have to use that specific URL for my App Oauth Redirect? =

Simple Instagram uses an iFrame for all calls to Instagram for authorization. Using the URL provided in Step 01 points the Instagram API to that iFrame to verify all of your credentials. 

= When I click on “Log in with Instagram”, I get redirected to a page with a bunch of errors. =

This is likely due to a typo in the OAuth Redirect URL you provided in your App Setup. To avoid typos, use the handy “Copy” button next to the URL in Step 01 to copy the URL directly to your clipboard for pasting.  

== Screenshots ==

1. The Simple Instagram settings page with steps and instructions

== Changelog ==

= 2.0 =
* Adds: New authorization flow is now a single step.  
* Adds: Images now have proper Alt tags to avoid validation errors.
* Adds: Popular feeds now have an unlimited count.
* Fixes: Refactored codebase to be cleaner and more manageable. 

= 1.2 =
* Fixes: Allows secure sites to load images via https

= 1.1.1 =
* Fixes: Forces tag feeds to respect the limit provided

= 1.1.0 =
* Adds: Ability to specify a user or tag in the feed shortcode and widget
* Adds: Tag widget
* Adds: Tool on settings page to search for User ID by username for use in the shortcodes and widgets
* Fixes: Authorization link opens in a new window; settings page will automatically check for authorization and update once authorized. 

= 1.0.3 =
* Fixes iframe closing tag bug

= 1.0.2 =
* Temporary fix to make authorization link open in new window to avoid 302 redirect from Instagram
* Fix deactivation error

= 1.0 =
* Initial Version

== Upgrade Notice ==

= 2.0 =
* Adds: New authorization flow is now a single step.  
* Adds: Images now have proper Alt tags to avoid validation errors.
* Adds: Popular feeds now have an unlimited count.
* Fixes: Refactored codebase to be cleaner and more manageable.

= 1.2 =
* Fixes: Allows secure sites to load images via https

= 1.1.0 =
Adds user selection and tag specification. Minor fixes to authorization process. Adds tool to search for User ID by user name. 

= 1.0.3 =
Fixes iframe closing tag bug

= 1.0.2 =
Fixes the authorize button to avoid a 302 redirect and deactivation error. Upgrade immediately. 

