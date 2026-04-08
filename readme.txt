This is the Pro version of SpeakOut! Email Petitions

=== SpeakOut! Email Petitions ===
Contributors: 123host
Tags: petition, activism, community, email, social media
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 7.4
License: GPLv2 or later 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

SpeakOut! Email Petitions allows you to easily create petition forms on your site.

When visitors to your site submit the petition form, a copy of your message will be sent to the email address you specified e.g. your mayor. They can also choose to have the email BCC'd to themselves (default).  The petition message will be signed with the contact information provided by the form submitter. After signing the petition, visitors will have the option of sharing your petition page with their followers on Facebook or X. 

Signatures are stored in the WordPress database and can be easily exported to CSV format for further analysis (there is no import function). You may set a goal for the number of signatures you hope to collect and then watch as a progress bar tracks your petition's advance toward it's goal - the goal can even update automatically when a % of your goal is reached. Petitions may also be configured to stop accepting new signatures on a specified date.

More information about the plugin can be found at the official SpeakOut! WordPress petition plugin website: https://speakoutpetitions.com

== Changelog ==

https://speakoutpetitions.com/changelog

== Installation ==

Use the WordPress plugin installer for the free version - search for 'speakout'

The Pro version is available at https://latest.speakoutpetitions.com

== Frequently Asked Questions ==

= Where is the FAQ? =
[https://speakoutpetitions.com/FAQ][1]
[1]: https://speakoutpetitions.com/FAQ "SpeakOut! FAQ"

== Localizations ==

* Albanian **sq_AL** Incomplete
* Arabic **ar_AR**
* Arabic **ar** (Faisal Kadri)
* Catalan **ca**  (Alberto Canals)
* Czech **cs_CZ** (Petr Štepán, Michal Hradecký)
* Danish **da_DK** (A. L.)
* Dutch **nl_NL** (Kris Zanders, Petronella van Leusden)
* Finnish **fi_FI** 
* French **fr_FR**
* German **de_DE** (Hannes Heller, Armin Vasilico, Andreas Kumlehn, Frank Jermann)
* Hebrew **he_IL** (Oren L)
* Korean **ko_KO** (Paul Lawley-Jones)
* Icelandic **is_IS** (Hildur Sif Thorarensen)
* Italian **it_IT** ([MacItaly](http://wordpress.org/support/profile/macitaly), Davide Granti, Simone Apollo)
* Norwegian **nb_NO** (Howard Gittela)
* Polish **pl_PL** (Damian Dzieduch)
* Portuguese (Brazil) **pt_BR** (Tel Amiel)
* Romanian **ro_RO** ([Web Hosting Geeks](http://webhostinggeeks.com))
* Russian **ru_RU** ([Teplitsa](te-st.ru))
* Serbian **sr_SE** (Mikhailo Matovic)
* Slovak **sk_SK** (@Beata)
* Slovenian **sl_SI** ([MA-SEO](http://ma-seo.com))
* Spanish **es_ES**
* Swedish **sv_SE** (Susanne Nyman Furugård @sunyfu)

If you would like to request or contribute a specific translation not listed above, visit the [SpeakOut! Email Petitions website](http://speakoutpetitions.com/) and use the contact form.

== Emailpetition Shortcode Attributes ==
The following attributes may be applied when using the '[emailpetition]' shortcode

= id =
The ID number of your petition (required). To display a basic petition, use this format:
'[emailpetition id="1"]'

= width =
This sets the width of the wrapper "<div>" that surrounds the petition form. Format as you would a width rule for any standard CSS selector. Values can be denominated in px, pt, em, % etc. The units marker (px, %) must be included.

To set the petition from to display at 100% of it's container, use:
'[emailpetition id="1" width="100%"]'

A petition set to display at 500 pixels wide can be achieved using:
'[emailpetition id="1" width="500px"]'

= height =
This sets the height of the petition message box (rather than the height of the entire form). Format as you would a height rule for any standard CSS selector. Values can be denominated in px, pt, em, % etc. The units marker (px, %) must be included.

A few notes on using percentages:

Using a % value only works when the "Allow messages to be edited" feature is turned off—because the petition message will be displayed in a '<div>'. When "Allow  messages to be edited" is turned on, the petition message is displayed in a '<textarea>', which cannot be styled with % heights. Use px to set the height on petitions that allow message customization.
To set the message box to scale to 100% of the height of the message it contains, use any % value (setting this to 100%, 0%, 200% or any other % value has the same result). Use px if you want the box to scale to a specific height.

Examples:

'[emailpetition id="1" height="500px"]'
'[emailpetition id="1" height="100%"]'

= progresswidth =

Sets the width of the outer progress bar. The filled area of the progress bar will automatically scale proportionally with the width of the outer prgress bar. Provide a numeric value in pixels only. Do not include the px unit marker.
To display the progress bar at 300 pixels wide, use:
'[emailpetition id="1" progresswidth="300"]'

= class =

Adds an arbitrary class name to the wrapper '<div>' that surrounds the petition form. Typically used to assign the alignright, alignleft or aligncenter classes to the petition in order to float the petition form to one side of its container. To assign multiple classes, separate the class names with spaces.

Examples:

'[emailpetition id="1" class="alignright"]'
'[emailpetition id="1" class="style1 style2"]'

== Signaturelist Shortcode Attributes ==

= id =

The ID number of your petition (required). To display a basic signature list, use this format:
'[signaturelist id="1"]'

= rows =

The number of signature rows to display in the table. This will override the default value provided on the Settings page. To display 10 rows, use:
'[signaturelist id="1" rows="10"]'

= dateformat =

Format of values in the date column. Use any of the standard [PHP date formating characters](http://php.net/manual/en/function.date.php). Default is 'M d, Y'. A date such as "Sunday October 14, 2012 @ 9:42 am" can be displayed using:

'[signaturelist id="1" dateformat="l F d, Y @ g:i a"]'

= prevbuttontext =

The text that displays in the previous signatures pagination button. Default is &lt;.

= nextbuttontext =

The text that displays in the next signatures pagination button. Default is &gt;.

== signaturecount Shortcode ==

Display the number (as text) of signatures collected for a given petition:

= id =

The ID number of your petition (required).

'[signaturecount id="3"]'

== signaturegoal Shortcode ==

Display the number (as text) of goal for a given petition:

= id =

The ID number of your petition (required).

'[signaturegoal id="3"]'