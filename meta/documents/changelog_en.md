# Release Notes for Findologic

## 4.0.5 (2022-07-11)

### Behoben 
* [PLENTY-449] Review Error in terms of PHP8 Compability has been resolved

## 4.0.2 (2022-06-24)

### Changed
* [PLENTY-448] Plugin marked as PHP8 compatible

## 4.0.1 (2022-06-23)

### Fixed
* [PLENTY-443] A bug has been fixed, where an exception occurred for a price selection without products.
* [PLENTY-445] A bug has been fixed, where all products where shown for price selections without products.
* [PLENTY-447] A bug has been fixed, where a whitespace was rendered when the category filter should not be shown.

## 4.0.0 (2022-05-19)

### Changed
* [PLENTY-342] Migrate all components to Typescript and Vue single file components.
* [PLENTY-430] Update JS dependencies and migrate changes between plugin version 3.7.3 and 3.8.1
* [PLENTY-439] Change Github actions unit test build to use PHP 8.
* [PLENTY-440] Migrate changes between plugin version 3.9.0 and 3.9.2

### Fixed
* [PLENTY-441] A bug has been fixed, which caused a Vue warning for ItemFilter component on search result pages

## 3.9.3 (2022-06-23)

### Fixed

* [PLENTY-446] Add PHP8 support

## 3.9.2 (2022-04-11)

### Fixed
* [PLENTY-427] A bug has been fixed, where the category filter on navigation didn't show sub-categories.
* [PLENTY-437] A bug has been fixed, where an error in ItemFilterTagList occured when using a filter on navigation with plentyShop 5.0.48 and active SSR.


## 3.9.1 (2022-03-22)

### Fixed
* [PLENTY-434] A bug has been fixed, where a configured landingpage had no precedence over the redirect of one search result.

### Changed
* [PLENTY-428] Plentymarkets Review Version 3.9.0

## 3.9.0 (2022-03-17)

### Changed
* [PLENTY-429] Using new Plentymarkets API for searching variations to ensure compatibility for upcoming plentyShop version 5.0.47.

## 3.8.1 (2022-02-24)

### Fixed
* [PLENTY-426] For price filter with type text a min and max value must be given.

### Changed
* [PLENTY-428] Plentymarkets Review Version 3.8.0

## 3.8.0 (2022-02-14)

### Changed

* [PLENTY-423] The third-party library SVG-Injector is no longer loaded via Cloudflare, but is now bundled with the plugin assets.
* [PLENTY-420] The redirect to the PDP in case there is only one result, now uses a similar logic to the item URL in the export.

## 3.7.6 (2022-01-11)

### Changed

* [PLENTY-414] Logging for non-parsable XML strings has been improved.
* [PLENTY-366] Log messages contain more debug information, in case an error occurs.

## 3.7.5 (2021-12-21)

### Changed

* [PLENTY-403] Marketplace name and description have been changed.
* [PLENTY-411] In case a request to the Plentymarkets SDK fails, two more retries are being done, before a fallback is triggered.

### Fixed

* [PLENTY-412] Fixed a bug that caused the Landingpage redirect to no longer work as expected.

## 3.7.4 (2021-11-30)

### Fixed

* [PLENTY-388] Fixed a bug that caused Assisted Suggest to be shown on initial page load, due to an `autofocus` attribute on the search field.

## 3.7.3 (2021-10-27)

### Changed

* [PLENTY-383] The redirect to the product detail page will now properly parse ids, when the sent id also contains the id of the variant.

### Fixed

* [PLENTY-373] Fixed a bug that caused the search submission, to not take the language into consideration, which resulted in a search result page of a different language.
* [PLENTY-371] Fixed a bug that caused the redirect to the product detail page, to have precedence over the landingpage redirect.

## 3.7.2 (2021-08-30)

### Changed

* [PLENTY-365] The minimal required Ceres & IO version now is `5.0.35`.

### Fixed

* [PLENTY-362] Fixed a bug that caused selected filters not to properly render, when SSR has been enabled.
* [PLENTY-363] Fixed a bug that caused the wrong minimal price, in case a lower price was set than was possible.

## 3.7.1 (2021-08-16)

### Fixed

* [PLENTY-357] Fixed a bug that caused some filter containers not to take up the full width when using
server-side-rendering.
* [PLENTY-356] Fixed a bug that caused an error in case the Findologic API was not reachable by Plentymarkets.

## 3.7.0 (2021-07-26)

### Added

* [PLENTY-346] There now is an option that allows a minimal search term length to be configured.

### Changed

* [PLENTY-318] Increase navigation performance, by using a different endpoint provided by Ceres. Please note that the
  minimal Ceres version required now is 5.0.26.

### Fixed

* [PLENTY-345] Fixed a bug that caused search terms to be cut-off when they contained an ampersand.
* [PLENTY-351] Fixed several bugs related to Ceres server-side-rendering.

## 3.6.1 (2021-05-31)

### Fixed

* [PLENTY-340] Fixed a bug that may caused filter values showing up behind the filter count, when the filter value
  contained too many characters.
* [PLENTY-341] Fixed a bug that caused the Smart Suggest not being shown on non-search pages, when Findologic has
  been disabled on category pages.

## 3.6.0 (2021-05-18)

### Added

* [PLENTY-310] The selected filter count is can now shown next to the name of the filter.
  * [PLENTY-337] Added a configuration option, to enable/disable this feature.
* [PLENTY-329] Added a `translation.json`, which allows storefront-translations to be customized.
* [PLENTY-330] Added translations for all languages supported by Findologic.
* [PLENTY-327] Parameters `shopType` and `shopVersion` are now sent to the Findologic API.

### Fixed

* [PLENTY-309] Fixed a bug that caused already selected filters, to show a frequency of `0`.

## 3.5.3 (2021-04-22)

### Fixed

* [PLENTY-332] Fixed a bug that caused the Findologic plugin no longer to properly
  handle search/navigation requests, when the IO plugin had a higher priority than the Findologic plugin.

## 3.5.2 (2021-04-19)

### Fixed

* [PLENTY-332] Fixed a bug that caused the Findologic to be in an inactive State, when
 the IO and the Findologic plugin have been assigned the same priority.

## 3.5.1 (2021-04-19)

### Changed

* [PLENTY-307] Filters that have at least one selected filter, will now receive a `fl-active` CSS class.

### Fixed

* [PLENTY-314/PLENTY-324] Fixed a bug that caused an error, when a shop had multiple languages and only some of
  them had a shopkey configured.

## 3.5.0 (2021-03-08)

### Changed

* [PLENTY-305] The usability of the range slider has been improved.
  * The currency/unit is now shown in the selected filter.
  * Entered commas (`,`) will be replaced by dots (`.`).
  * Non-numerical inputs can no longer be submitted.
* [PLENTY-313] The performance on pages, where vendor image, or color image filters are displayed, has been improved.

### Fixed

* [PLENTY-315] Fixed a bug that may have caused a long loading time on no-result pages.
* [PLENTY-316] Fixed a bug that caused a wrong product count on category pages.

## 3.4.0 (2021-02-15)

### Added

* [PLENTY-245] Selected filters now also contain the name of the filter.
* [PLENTY-273] The category filter will now be properly shown as a dropdown, when it is configured as a dropdown,
  in the filter configuration.
* [PLENTY-308] Hovering over any color filter value, will now show the name of the color as a title.

### Changed

* [PLENTY-287] The search bar component is now globally overridden, instead of using a container link.

### Fixed

* [PLENTY-311] Fixed a bug that would allow execution of XSS, when using the Smart Did-You-Mean container link.
* [PLENTY-299] Fixed a bug that would throw console errors when both topbar and filter widgets were used on the same page.

## 3.3.0 (2021-01-12)

### Added

* [PLENTY-266] Using the ShopBuilder filter widgets individually is now supported.

### Changed

* [PLENTY-242] Direct Integration container configuration has been removed.

### Fixed

* [PLENTY-280] Fixed a bug that caused gaps in the product listing, when using Ceres version lower or equal to 5.0.2.

## 3.2.2 (2020-12-17)

### Fixed

* [PLENTY-294] Fixed a bug that caused a redirect to a 404 page, in case the main variation id of a product has been inactive.

## 3.2.1 (2020-11-24)

### Fixed

* [PLENTY-286] Fixed a bug that caused the pagination not to work properly.

## 3.2.0 (2020-11-19)

### Changed

* [PLENTY-257] The product redirect URL has been changed from the way Calisto defined it, to the new Ceres URL structure.
* [PLENTY-159] Findologic will no longer do an internal Plentymarkets search to ensure that all displayed products can be displayed properly, when using Ceres version > 5.0.2

### Fixed

* [PLENTY-276] Fixed a bug that caused the product redirect, to not take the language into account, which may have caused customers being redirected to a different language.

## 3.1.5 (2020-11-02)

### Fixed

* [PLENTY-277] Fixed a bug that caused Findologic not to be active, in case a shopkey has been set in the configuration, that did not contain a specific language key.

## 3.1.4 (2020-10-05)

### Fixed

* [PLENTY-274] Fixed a bug that caused category/vendor clicks in the Smart Suggest not to work properly.
* [PLENTY-275] Fixed a bug that caused Findologic to be active for a language, even if that language has not been set in the configuration.

## 3.1.3 (2020-10-05)

### Fixed

* [PLENTY-267] Fixed a bug, that caused the submit of the wrong search form, in case there are multiple search forms available in the DOM.
* [PLENTY-272] Fixed a bug, that caused the default sort of category pages to not work properly, when a Findologic unknown order has been configured as default.
* This release contains changes of the last release, as there was an issue while creating the release, which caused some
 changes not to be applied correctly. We apologize for any inconveniences this caused and try to prevent such issues in the future.

## 3.1.2 (2020-09-07)

### Fixed

* [PLENTY-260] Fixed a bug that caused no-results on any search term, when a category page was configured as search result page in the IO plugin.
* [PLENTY-262] Fixed a bug that caused a redirect to the product detail page, when the last pagination page had only one result.

## 3.1.1 (2020-08-03)

### Fixed

* [PLENTY-254] When refreshing the search result page, spaces in the search term should not be interpreted as plus sign.
* [PLENTY-225] Internally filter values were sent twice. This has been fixed.

### Changed

* [PLENTY-256] Updated text in marketplace to make it clear that an integration with Ceres < 5 is still possible.
* [PLENTY-259] Plugin name has been updated.

## 3.1.0 (2020-06-10)

### Added

* [PLENTY-187] Added support for Guided Shopping.

### Changed

* [PLENTY-244] Improved the plugin icons for the marketplace.

## 3.0.1 (2020-05-06)

### Changed

* [PLENTY-232] The image which is displayed, when no color tone/image is configured for a color, has been changed.
* [PLENTY-241] All occurrences of FINDOLOGIC have been replaced by Findologic.
* [PLENTY-243] & [PLENTY-239] Images for the plenty marketplace have been updated.

## 3.0.0 (2020-04-14)

### Support Ceres 5

<p align="center"><a href="https://marketplace.plentymarkets.com/en/plugins/sales/online-shops/ceres_4697" target="_blank"><img height="150" alt="Ceres 5" src="https://plentymarkets-assistant.s3.eu-central-1.amazonaws.com/ceres-5.svg"></a></p>

### Fixed

* [PLENTY-227] Fixed a bug that caused the first search form to be submitted,
 instead of the actual submitted form.

## 2.7.0 (2020-04-06)

### Added

* [PLENTY-193] Filters per row can now be configured.
* [PLENTY-196] Filters now contain a no-follow attribute which prevents them
 from being crawled.

### Changed

* [PLENTY-229] The query info message translations were adapted to match
 the style with our Direct Integration.
* [PLENTY-230] The Smart Did-You-Mean message will now be displayed
 below the query info message.
* [PLENTY-231] Color filter images are now set as background instead of a
 separate `<img>` tag.

### Fixed

* [PLENTY-220] Fixed a bug that caused the filter button on category pages
 to be misplaced when using the ShopBuilder.

## 2.6.0 (2020-03-13)

### Added

* [PLENTY-192] Plentymarkets tags are now supported. Findologic will automatically filter by the given tag on tag pages.

### Changed

* [PLENTY-199] Dropdown filters now have a fixed height. This prevents dropdowns from being bigger than the whole page,
 when having many filter values.
* [PLENTY-209] The redirect URL to a product detail page is now the same URL that is exported.
* [PLENTY-204] Links to our documentation were updated.
* [PLENTY-216] Added Ceres and IO 4.5 as minimal requirements for using the plugin.

### Fixed

* [PLENTY-210] Fixed a bug that caused a console error when configuring all filters as "other filter" and clicking on the filter
 button.
* [PLENTY-200] Fixed a bug that caused sorting options to disappear, even if Findologic hasn't provided product results
 yet. Some sorting options will still disappear when selecting a filter, as they may not be compatible with Findologic.

## 2.5.1 (2020-02-27)

### Fixed

* [PLENTY-203] Fixed a bug that caused users to be redirected to the product
detail page, after a filter was selected, that would only lead to one result.

## 2.5.0 (2020-02-25)

### Added

* [PLENTY-125] Smart Did-You-Mean is now supported. Find more information
 [in our documentation](https://docs.findologic.com/doku.php?id=integration_documentation:plentymarkets_ceres_en:ceres_sdym_en).
* [PLENTY-185] Using Smart Did-You-Mean will now show a user-friendly text after
 selecting a category or a vendor from the Smart Suggest.
* [PLENTY-188] If there is only one search result, you will be redirected
 to the product detail page.

### Changed

* The "page" parameter will no longer be appended after deselecting a filter.
* [PLENTY-197] Documentation for Smart Did-You-Mean was added.
* Internal: JavaScript is now automatically compiled before committing.

## 2.4.2 (2020-02-03)

### Fixed

* [PLENTY-190] Clicking on mobile Smart Suggest suggestions will no longer
 hide the mobile Smart Suggest without submitting the search. This includes
 clicks on "Show all results" for the regular Smart Suggest.

## 2.4.1 (2020-01-17)

### Fixed

* [PLENTY-186] Restore compatibility with Ceres version v4.5.0.

## 2.4.0 (2020-01-07)

### Added

* [PLENTY-179] Added a config option to disable CSS added by Findologic for the
 filter styles.

### Fixed

* [PLENTY-184] Fixed a bug that caused multi-language shops to always redirect
 to the main shop.
* [PLENTY-180] The plugin will no longer try to load a non-existing CSS file.
* [PLENTY-176] The "no-filters-available" text will now be displayed for all filter types.
* [PLENTY-183] Only categories for the current tree will be displayed.

## 2.3.0 (2019-11-29)

### Fixed

* Submitting a search with multiple words separated by a space character will
 no longer cause the spaces to be replaced with a "+" sign.

### Changed

* jQuery-UI was completely removed and was replaced by noUiSlider, as it caused
 errors in the browser console. If you have custom slider styles, they
 may need to be adapted.

## 2.2.0 (2019-11-21)

### Added

* Support for the "no available filters" text, if set in the Findologic backend.

### Fixed

* Range slider filters that are no price slider filters work again.
* Vue errors will no longer be thrown when using the ShopBuilder checkout.
* For Firefox three filters are now being shown per row instead of two.
* When all filters are configured as "other filter", the filter button will no longer
 use the full width of the current row.

## 2.1.0 (2019-09-23)

### Added

* Support for fixed filter values for dropdown filters, if set in the Findologic
backend.

### Fixed

* The whole category tree is marked as selected. Before this change only the
latest category was marked as selected.
* Vue errors will no longer be thrown if the ceres plugins performance level was
set to "Development".

### Changed

* The installation guide now links to our documentation at https://docs.findologic.com.

## 2.0.1 (2019-09-13)

### Fixed

* Deselection of already selected category filter works as expected.

## 2.0.0 (2019-09-11)

### Added

* Support for response type `XML_2.1`. Be aware that this caused some CSS style
 changes which may affect filters. **Make sure to check your filter styles before upgrading.**
* Support for dropdown filters when configured in FINDOLOGC's backend.

### Fixed

* Only allow a single value to be selected for category filter.

## 1.2.3 (2019-08-21)

### Fixed

* Default layout container definitions can be selected and saved in the backend.
* Filters of type range-slider allow min/max range to be the same.

## 1.2.2 (2019-08-06)

### Fixed

* Whitespaces in configured shopkeys will be trimmed, since they would be invalid.
* Filters of type range-slider allow floating point numbers smaller than 1 to be submitted.

## 1.2.1 (2019-07-31)

### Fixed

* Navigating to the second search result page would cause an error since Plentymarkets couldn't load metadata for Findologic's plugin.

### Changed

* Updated version constraint for plugin Ceres, now supporting 4.0.0 - 4.x.x.

## 1.2.0 (2019-07-29)

### Added

* Support for landing pages that are configured in Findologic's backend.
* Support for promotion banners (incl. a respective data provider).

## 1.1.2 (2019-07-19)

### Changed

* Added constraints for the latest supported versions for required plugins Ceres (4.0.2) and IO (4.1.2).
* Updated user guide and images.
* Currently used plugin version, which is sent to Findologic with each request, is loaded dynamically via Plentymarkets.
* Third party JS libraries are loaded via a CDN.
* A minified version of this plugin's JS files are used.
* Updated the Findologic snippet and added new configuration options for it.

## 1.1.1 (2019-07-03)

### Fixed

* Sort option "Top seller" is supported.

### Changed

* Removed wrong feature statement in user guide.

## 1.1.0 (2019-06-27)

### Added

* Added support for shops with multiple languages.

### Fixed

* The plugin explicitly uses the correct output format (XML).

## 1.0.3 (2019-06-04)

* Added installation guide.
* Updated plugin description.

## 1.0.2 (2019-06-03)

* Added disclaimer to user guide about support for multilingualism.

## 1.0.0 (2019-06-03)

### Features

* Multilingualism

  At the moment only one of the available shop languages is supported. The actively used language can be configured in consultation with Findologic.

* Personalization

  With Findologic Personalization you now have a specifically optimized store for every target group.

* Search

  The search function represents the most important sales tool in your online store. Our algorithm, which has been refined for over 10 years, lets your customers really find what they are looking for on a 1:1 personalized basis. At the outset of their search, our Smart Suggest offers an intelligent drop-down to give your users the fastest possible orientation. Especially for mobile users.

* Navigation

  Your users will see the most relevant products via your online store globally at any point during the customer journey. With 1:1 personalization and our sophisticated merchandising. Provide a fully-integrated, high-performance, consistent and personalized user experience across all category pages.

* Merchandising

  Use the intuitive Findologic backend with our specially developed tools for compact and efficient sales and onsite marketing controls.

* Shopping Guide

  As part of a shopping advisor campaign, ask your users smart questions that a salesperson from a stationary branch store would also ask. Thus your users will find the right product quickly and easily.
