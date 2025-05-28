=== TopSMS ===
Contributors: Soumitra123, sameecwx
Source Code: https://github.com/topsmsau/topsms_plugin
Tags: woocommerce, sms, notifications, order, analytics
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Enhance your WooCommerce store with automated SMS notifications based on order status changes. Built exclusively for Australian businesses.

== Description ==

TopSMS is a powerful WooCommerce plugin developed by EUX Digital Agency that revolutionizes how you communicate with your customers through SMS.\

### Source Code

The complete source code for this plugin is available on GitHub at https://github.com/topsmsau/topsms_plugin

### Key Features

*1. Automated SMS System Based on Order Status*
* Send instant SMS notifications when order status changes (processing, completed, shipped, etc.)
* Fully customizable message templates for each order status
* Personalize messages with dynamic variables (customer name, customer last name, order ID.)
* Schedule delayed notifications for specific order statuses
* Supports multiple languages for global-ready WooCommerce stores (SMS sending remains AU-only)

*2. Comprehensive SMS Analytics*
* Track delivery rates and read receipts for all sent messages
* Monitor SMS credit usage with detailed reports
* Visual dashboard with key performance metrics and trends

*3. In-Store SMS Credit Recharging*
* Purchase SMS credits directly through your WooCommerce store
* Multiple credit packages to suit businesses of all sizes
* Automatic low-credit alerts and recharge reminders
* Special discount offers for bulk credit purchases

### Benefits for Store Owners
* Reduce customer support inquiries with proactive order updates
* Increase customer satisfaction through timely communication
* Lower cart abandonment rates with strategic SMS campaigns
* Build customer loyalty through consistent engagement
* Save time with automated workflows and notifications

### Technical Features
* Built exclusively for Australian businesses
* ACMA-compliant sender IDs and privacy protocols
* GDPR-compliant with explicit consent management
* Lightweight design with minimal impact on site performance

== External services ==

This plugin connects to Supabase Edge Functions to process and store application data.

### The plugin sends the following data to Supabase servers
* User input data (form submissions, content, preferences)
* User authentication tokens and session information

### Data is transmitted when
* Users submit forms or save content
* Authentication and user sessions are managed
* API calls are made to custom Edge Functions

This service is provided by Supabase Inc: https://supabase.com/docs/company/terms, https://supabase.com/docs/company/privacy.

== Installation ==

1. Upload the topsms folder to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to TopSMS → Settings to configure your SMS gateway credentials
4. Set up your message templates under TopSMS → Automations
5. Configure order status triggers under TopSMS → Automations
6. Purchase initial SMS credits under TopSMS → Settings

== Frequently Asked Questions ==

= Is TopSMS available outside of Australia? =
No. TopSMS is built exclusively for Australian businesses. Our SMS delivery infrastructure and compliance standards are designed to meet ACMA regulations and ensure optimal delivery rates within Australia only.

= Do my SMS credits expire? =
No, your credits never expire. You can use them whenever you like — no monthly minimums, no forced top-ups, and no hidden fees.

= Is my business name used as the sender ID? =
Yes. As required by the Australian Communications and Media Authority (ACMA), the sender name must clearly identify your business. TopSMS ensures your sender ID matches your brand name to improve trust and reduce SMS fraud risk.

= Can I use a custom sender name? =
Yes, but only if it aligns with your business name. Generic or misleading sender names will be rejected under ACMA anti-spam guidelines.

= How are payments processed? =
Payments for SMS credits are securely processed via Stripe. We do not store your card details. All transactions are encrypted and PCI-DSS compliant.

= Is TopSMS compliant with Australian privacy laws? =
Yes. TopSMS is fully compliant with both GDPR and ACMA privacy regulations. You can collect SMS consent at checkout, offer opt-out links, and manage your customers’ privacy preferences responsibly.

= Do I need customer consent to send SMS? =
Absolutely. ACMA requires clear and informed consent before sending SMS messages. TopSMS includes tools to collect and manage consent directly within WooCommerce.

= Can I preview messages before they are sent? =
Yes. You can preview each message template with dynamic sample data to ensure everything looks right before activating automations.

= Are there any long-term contracts or commitments? =
No. TopSMS is pay-as-you-go. You only pay for the SMS credits you use — simple, predictable pricing with no lock-in contracts.

= Do you offer volume discounts for larger businesses? =
Yes. We offer discounted SMS credit packages for stores with higher messaging needs. Contact us to discuss a custom plan for your business.

= Can I send SMS notifications for custom order statuses? =
Not yet. At present, custom order status support is not available but may be considered for a future release.

= How do I monitor my SMS credit balance? =
Your current credit balance is always visible in the TopSMS dashboard. You can also set up automatic alerts when your credits fall below a specified threshold.

== Screenshots ==

1. TopSMS dashboard with credit balance and recent SMS history
2. Automations editor with message template and status triggers
3. Settings screen for configuring Stripe and SMS options
4. Analytics dashboard showing delivery rates and usage