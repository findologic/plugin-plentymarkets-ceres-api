# API plugin for Plentymarkets Ceres [![Build Status](https://travis-ci.org/findologic/plugin-plentymarkets-ceres-api.svg?branch=development)](https://travis-ci.org/findologic/plugin-plentymarkets-ceres-api)

Needs to be installed in Plentymarkets Ceres to utilize FINDOLOGIC's search API.

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

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/findologic/plugin-plentymarkets-ceres-api/tags). 
