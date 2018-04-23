=== Terms & Conditions Connector of IT-Recht Kanzlei ===
Contributors: inpsyde, danielhuesken
Tags: Law, API, XML
Requires at least: 4.4
Tested up to: 4.9.5
Stable tag: 1.0.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Transfers the legal texts of “IT-Recht Kanzlei München” via API into your WordPress-installation and keeps your judicial pages up to date.

== Description ==

Due to the **Terms & Conditions Connector of IT-Recht Kanzlei**, the legal texts needn’t be transferred from the clients’ portal to your website by yourself.

Create the judicial pages as empty pages in WordPress and set up the connection to the clients’ portal of IT-Recht Kanzlei. From now on, your judicial pages are always up to date, as amendments of the legal texts in the clients’ portal are immediately transferred to WordPress.

To use this plugin, you need an access to the clients’ portal of “IT-Recht Kanzlei München”. You are no client yet? On the website of “[IT-Recht Kanzlei München](https://www.it-recht-kanzlei.de/agb-starterpaket.php)”, you can find all protection packages.

= Features =

* Automatic transfer of legal texts from “IT-Recht Kanzlei München” to your WordPress-installation
* Automatic update of legal texts in WordPress in case of amendments in the clients’ portal
* Suitable for both WordPress websites/blogs and WordPress shops, irrespective of your shop system
* Appends selected legal texts as PDF to the WooCommerce-Email “New Order”
* Simple connection to the clients’ portal of “IT-Recht Kanzlei München” via API-Token and URL

= Benefits =

* Always up-to-date and dunning-proof legal texts for your WordPress
* Automatic updates via Terms & Conditions Connector
* Self-evident: legal liability (As other legal offices, “IT-Recht Kanzlei” bears liability for the dunning-proof legal texts in the context of legal provisions in force)
* Full flexibility: As necessary, the terms and conditions Connector is cancelable at the end of a month any time
* Fair: monthly payment (No annual fee ahead)

= Support = 

You can find technical support for this plugin in the [wordpress.org forum](https://wordpress.org/support/plugin/agb-connector).

Please read the FAQ (frequently asked questions) first and make sure you have installed the newest version of the plugin before contacting us.

If you have questions concerning the legal texts, please contact “IT-Recht Kanzlei München” directly via +49 89 13014330 or info@it-recht-kanzlei.de.

**Made by [Inpsyde](https://inpsyde.com) &middot; We love WordPress**

== Frequently Asked Questions ==

Here, you find answers to frequently asked questions about this plugin.

= Do I need an access to “IT-Recht Kanzlei München” to use the plugin Terms & Conditions Connector of IT-Recht Kanzlei? =

Yes, you need to have booked one of the [protection packages of “IT-Recht Kanzlei München”](https://www.it-recht-kanzlei.de/agb-starterpaket.php) to transfer legal texts to your WordPress-installation.

= Can I use the plugin "Terms & Conditions Connector of IT-Recht Kanzlei" both for websites/blogs and shops? =

Yes. The plugin does not distinguish shops or “simple” websites. The only thing you need to do is assigning a page in your WordPress-Installation to every single legal text being made by IT-Recht Kanzlei.

= Concerning Online-Shops: Does the shop need to be based on WooCommerce? =

No. The plugin does not require special demands for your WordPress shop. Merely, there must be the appropriate pages to transfer the legal texts.

== Screenshots ==

1. Terms & Conditions Connector of IT-Recht Kanzlei - Settings Page

2. Protection package of IT-Recht Kanzlei München – Registration to the clients’ portal.

3. Protection package of IT-Recht Kanzlei München – Overview of legal texts

4. Protection package of IT-Recht Kanzlei München – Configuration of legal texts

5. Protection package of IT-Recht Kanzlei München – Choose shop system

6. Protection package of IT-Recht Kanzlei München – API-Token and Shop URL

== Upgrade Notice ==

== Installation ==

= Minimum requirements =
* WordPress version >= 4.4
* PHP Version >= 5.6
* Access to a protection package of “IT-Recht Kanzlei München”

= Installation via WordPress Backend = 

In the WordPress Backend, go to *Plugins => Add New*. Search for the **Terms & Conditions Connector of IT-Recht Kanzlei**. Click on *Install Now* and then *Activate*.


= Setting the automatic transfer of legal texts =

To set the transfer of the legal texts, go to *Settings => AGB Connector*. 
1. Generate the API-Token by clicking *Regenerate*.
2. Assign a page of your WordPress-installation to each legal text. Eventually, you need to create these pages first.
3. Save your settings.
4. First, the legal texts need to be configured in the clients’ portal of “IT-Recht Kanzlei München”. Then, you set up the data interface by indicating the Shop-URL and the API-Token. Once after the installation, you need to transfer the texts manually. After that, amendments of the legal texts are transferred automatically. (see screenshots.)


= Appending the legal texts to WooCommerce-Emails =

As far as you run a WordPress shop based on WooCommerce, you have the possibility to append selected legal texts as PDF to the Email “New Order”. Therefore, you simply need to go to *Settings => AGB Connector* and then place a hook at your selected legal texts at “Send PDF with WooCommerce order on hold email.”


== Changelog ==

= Version 1.0.4 =

 - Add shortcode for legal text

= Version 1.0.3 =

Use WordPress internal file get functions
Hide DOC ID
Bugfix where you couldn't regenerate your API-Token

= Version 1.0.2 =

plugindirectory fixes

= Version 1.0.1 =

plugindirectory fixes

= Version 1.0 =

Init Release
