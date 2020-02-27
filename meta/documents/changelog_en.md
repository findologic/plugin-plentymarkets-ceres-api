# Release Notes for FINDOLOGIC

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

* [PLENTY-179] Added a config option to disable CSS added by FINDOLOGIC for the
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

* Support for the "no available filters" text, if set in the FINDOLOGIC backend.

### Fixed

* Range slider filters that are no price slider filters work again.
* Vue errors will no longer be thrown when using the ShopBuilder checkout.
* For Firefox three filters are now being shown per row instead of two.
* When all filters are configured as "other filter", the filter button will no longer
 use the full width of the current row.

## 2.1.0 (2019-09-23)

### Added

* Support for fixed filter values for dropdown filters, if set in the FINDOLOGIC
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

* Navigating to the second search result page would cause an error since Plentymarkets couldn't load metadata for FINDOLOGIC's plugin.

### Changed

* Updated version constraint for plugin Ceres, now supporting 4.0.0 - 4.x.x.

## 1.2.0 (2019-07-29)

### Added

* Support for landing pages that are configured in FINDOLOGIC's backend.
* Support for promotion banners (incl. a respective data provider).

## 1.1.2 (2019-07-19)

### Changed

* Added constraints for the latest supported versions for required plugins Ceres (4.0.2) and IO (4.1.2).
* Updated user guide and images.
* Currently used plugin version, which is sent to FINDOLOGIC with each request, is loaded dynamically via Plentymarkets.
* Third party JS libraries are loaded via a CDN.
* A minified version of this plugin's JS files are used.
* Updated the FINDOLOGIC snippet and added new configuration options for it.

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

  At the moment only one of the available shop languages is supported. The actively used language can be configured in consultation with FINDOLOGIC.

* Personalization

  With FINDOLOGIC Personalization you now have a specifically optimized store for every target group.

* Search

  The search function represents the most important sales tool in your online store. Our algorithm, which has been refined for over 10 years, lets your customers really find what they are looking for on a 1:1 personalized basis. At the outset of their search, our Smart Suggest offers an intelligent drop-down to give your users the fastest possible orientation. Especially for mobile users.

* Navigation

  Your users will see the most relevant products via your online store globally at any point during the customer journey. With 1:1 personalization and our sophisticated merchandising. Provide a fully-integrated, high-performance, consistent and personalized user experience across all category pages.

* Merchandising

  Use the intuitive FINDOLOGIC backend with our specially developed tools for compact and efficient sales and onsite marketing controls.

* Shopping Guide

  As part of a shopping advisor campaign, ask your users smart questions that a salesperson from a stationary branch store would also ask. Thus your users will find the right product quickly and easily.
