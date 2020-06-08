# API plugin for Plentymarkets Ceres
[![Build Status](https://travis-ci.org/findologic/plugin-plentymarkets-ceres-api.svg?branch=development)](https://travis-ci.org/findologic/plugin-plentymarkets-ceres-api)

Needs to be installed in Plentymarkets Ceres to utilize Findologic's search API.

## Getting started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

Make sure that the listed software is installed:

* Npm
* Node
* Composer (1.8.0 or higher)

### Installing

Install the composer dependencies:

```
$ composer install
```

Install node dependencies:

```
$ npm install
```

Install git hooks:

```
$ gulp install-hooks
```

### Development

Run following commands if JS or CSS changes were made respectively:

```
$ gulp js
```

```
$ gulp sass
```

Commit and push any local changes.

Pull the changes in Plentymarkets' backend (be sure to select the correct plugin set). Click on "Save & deploy plugin set" and start the preview for the relevant shop.

## Running the tests

Use phpunit installed in the vendor folder like so:

```
$ php vendor/bin/phpunit -c tests/phpunit.xml 
```

## Deployment & Release
1. Update the German and English changelogs in folder `meta/documents`.
1. Bump the plugin version in files `plugin.json` and `src/Constants/Plugin.php`.
1. Open the backend from our Plentymarkets test shop and login. 
1. Go to *Plugins > Plugin overview > Ceres 4.2 > Findologic > Git* and fetch & pull the master branch.
1. Go back to *Plugin overview* and click *Save & deploy plugin set*.
1. Open the plugin again and click on *Upload to plentyMarketplace*.
1. The plugin may not be available yet, Plentymarkets has to do a review on their side. But once that is
 done, you can notify everyone via Basecamp.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/findologic/plugin-plentymarkets-ceres-api/tags). 
