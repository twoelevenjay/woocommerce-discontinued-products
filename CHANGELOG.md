

## Changelog

### 2.0.7 03.02.2026
* Fixed - Stray closing `</h4>` tag in discontinued notice output producing invalid HTML.
* Fixed - Inconsistent HTML tag casing in discontinued notice markup.
* Added - Settings link on the WordPress plugins page for quick access to plugin configuration.
* Compatibility - Tested with WordPress 6.9 and WooCommerce 10.5.2.

### 2.0.6 02.22.2026
* Fixed - Shop page ID override returning 0 when no discontinued archive page is configured, causing shop links (e.g. mobile navbar) to redirect to homepage.
* Fixed - Discontinued product filtering not applying on product category pages when no discontinued archive page is configured.

### 2.0.5 02.22.2026
* Fixed - AJAX compatibility issue with themes using WooCommerce AJAX filtering and infinite pagination (e.g. Woodmart). Tax query modifications are now skipped during AJAX requests where template conditional tags do not resolve correctly.

### 2.0.4 02.22.2026
* Compatibility - Tested with WooCommerce 10.5 and WordPress 6.7.
* Compatibility - Declared HPOS (High-Performance Order Storage) compatibility.
* Compatibility - Added multisite network-wide activation support for WooCommerce detection.
* Fixed - PHP 8.1+ TypeError in CSV export when alternative products meta is empty.
* Improved - Updated GitHub Actions workflow to use current checkout action.
* Added - WC requires/tested headers to plugin file.
* Added - Requires PHP header to plugin file and readme.

### 2.0.3 11.09.2024
* Fixed - Queries that were causing blank search pages.

### 2.0.2 10.29.2024
* Fixed - Conditionals that were causing blank archive pages.

### 2.0.1 10.29.2024
* Fixed - Optimize tax queries being added to the main query.

### 2.0.0 10.27.2024
* Major update to the plugin.
* Fixed - Queries use the taxonomy table for better performance.
* Added - DB updater to migrate product meta data to the new taxonomies.

### 1.1.7 04.25.2020
* Fixed - price class .discontinued
* Removed - span.discontinued into the price

### 1.1.6 04.23.2020
* Added span.discontinued into the price
* Added CSS Style

### 1.1.5 02.19.2019
* Add the DP_CSV_Import_Export class to include this plugins fields in the WooCommerce import / export feature.
* Fixed .pot file to include missing strings.

### 1.1.4 02.19.2019
* Replace price with discontinued text in shop loop to be constant with single product page behavior.

### 1.1.3 10.04.2018
* Add discontiued-notice class.

### 1.1.2 02.08.2018
* Add spacing in data panel tab.
* Fix missing gettext text domains.

### 1.1.1 11.07.2017
* Hide from product category pages when hidden from shop.
* Replace depreciated action hook.
* Fix alternative product search.

### 1.1.0 07.08.2016
* Add settings page.

### 1.0.0 07.08.2016
* Initial release, looking forward to adding more features soon.
