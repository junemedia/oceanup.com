=== Disqus Recent Comments Widget ===
Contributors: DeusMachineLLC,aaron.white,Andrew Bartel
Tags: disqus, comments, widget, sidebar
Requires at least: 3.4.1
Tested up to: 3.6.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Disqus has dropped support for their recent comments widget.  This plugin ceates a configurable widget that will display your latest Disqus comments.

== Description ==

The Disqus Recent Comments Widget plugin will create a configurable widget that will allow you display comments in any widgetized area of your theme like sidebars and footers.

You can customize the comment length and date format, filter users and choose from two different markup templates, among other things.  The plugin has full support for custom markup defined with register_sidebars() and should integrate smoothly with most themes in the wp.org repository.

We're targeting the release of 1.2 to line up with the launch of WordPress 3.7 and we'll be adding the ability to define custom markup for the widget and the ability to cache comments along with new options and more premade markup templates.

We try to be very proactive and responsive with support.  So, if you have any issues, please post in the support forums and we'll do our best to resolve your issue promptly.

You can follow development here: https://github.com/andrewbartel/Disqus_Recent_Comments

== Installation ==

1. Unzip the ZIP file and drop the 'disqus-recent-comments' folder into your 'wp-content/plugins/' folder.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Enter your short name and api key in the settings page.
4. If you're having trouble configuring the settings, please see http://deusmachine.com/disqus-instructions.php

== Frequently Asked Questions ==

= Why did the comments stop appearing? =

Disqus caps the number of requests you can make to their api at 1000 an hour for free accounts. Comments will start appearing again next hour.

= I blocked a user, but their comments are still appearing =

Make sure you entered the exact author name. The plugin does its best to account for spaces, capitalization, etc but it can't read your mind. If all else fails, copy/paste their name into the filtered users field.

= I can't figure out this API key stuff, help? =

Please see this guide: http://deusmachine.com/disqus-instructions.php

= I found a bug or I have an idea for a new feature =

Fork the project and send us a pull request! We'll be happy to give you a shout out in the release notes. https://github.com/andrewbartel/Disqus_Recent_Comments
If you're not a developer, you can always drop us a line in the support forums and we'll do our best to integrate your requests into the next version or tackle the bug you found.

= Where can I find the original version of the script that this plugin was based on? =

You can view the original blog post on Aaron's site: http://www.aaronjwhite.org/index.php/14-web-development/php/11-updated-recent-comments-widget-in-php-for-disquss-api
Or, you can check out the script on github: https://github.com/AaronJWhite/Disqus_Recent_Comments

= Is the plugin available in languages other than English? =

Not currently, but if you'd like to put together a translation for us, please do!  We'll happily give you credit in the release notes.

== Screenshots ==

1. The Settings Page
2. Adding the widget to a sidebar

== Changelog ==

= 1.1 =

* Added support for register_sidebars()
* Fixed a bug that caused the posted date to display as today's date
* Added the option to disable the plugin's css file
* Added options to control what markup is generated (props to BramVanroy for the suggestion and code)
* Added the ability to change the widget title
* Added the option to change the markup surrounding the title

= 1.0 =

* Initial build