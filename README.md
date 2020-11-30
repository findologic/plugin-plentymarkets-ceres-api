# Findologic API plugin for Plentymarkets Ceres
[![Build Status](https://travis-ci.org/findologic/plugin-plentymarkets-ceres-api.svg?branch=development)](https://travis-ci.org/findologic/plugin-plentymarkets-ceres-api)

## Table of Contents

1. [Setup](#setup)
   1. [Prerequisites](#prerequisites)
   1. [Installation](#installation)
1. [Development](#development)
   1. [Installing dependencies](#installing-dependencies)
   1. [Development cycle](#development-cycle)
   1. [Running unit-tests locally](#running-unit-tests-locally)
1. [Deployment & Release](#deployment--release)
1. [Versioning](#versioning)

## Setup

### Prerequisites

Installation of Plentymarkets plugins:

* [Ceres](https://marketplace.plentymarkets.com/ceres_4697) >= 5.0
* [IO](https://marketplace.plentymarkets.com/io_4696) >= 5.0

### Installation

For development install the Findologic plugin in the Plentymarkets backend via Git.
For production install the Findologic plugin via the [Plentymarkets marketplace](https://marketplace.plentymarkets.com/findologic_6390).

> Note: In the Plentymarkets Backend you have the possibility to change the code of your plugin but this only works if
the plugin has been installed via the marketplace.

## Development

Plentymarkets is a cloud hosted shop system, this means that it's not possible to setup on a local machine.
Create separate plugin sets in the Plentymarkets for development or debugging purposes.

### Installing dependencies

This project contains PHP and Javascript dependencies.
Make sure you have [Composer](https://getcomposer.org/) >= 1.8 as well as [Node.js](https://nodejs.org/en/) and [npm](https://www.npmjs.com/) installed.

Install PHP dependencies:
```bash
composer install
```

Install JS dependencies:
```
npm install
```

Running the following command, will enable a Git hook, which prevents you from pushing
your code, in case you have made JS/CSS changes, but did not build them:
```
gulp install-hooks
```

### Development cycle

Since you can't setup a local instance of Plentymarkets, it's necessary to push your changes
to a branch which can be pulled in the Plentymarkets backend.

```
git checkout -b PLENTY-<story-id>_<story-title>
```

Before pushing you may run build to ensure your JS/CSS is built.

```
npm run-script build
```

Alternatively if you have `gulp` installed globally, simply run `gulp`.

### Running unit-tests locally

Make sure to include the `tests/phpunit.xml` as an *alternative configuration file* in your IDE.

Alternatively run all tests with

```
composer test
```

## Deployment & Release
1. Update the German and English change logs in folder `meta/documents`.
1. Bump the plugin version in files `plugin.json` and `src/Constants/Plugin.php`.
1. Open the backend from our Plentymarkets shop.
1. Go to *Plugins > Plugin overview > Ceres > Findologic > Git* and fetch & pull the `main` branch.
1. Go back to *Plugin overview* and click *Save & deploy plugin set*.
1. Open the plugin again and click on *Upload to plentyMarketplace*.
1. The plugin may not be available yet, Plentymarkets has to do a review on their side.
1. After review was successful notify colleagues in [Basecamp Plugin Releases](https://basecamp.com/2574673/projects/5676139/messages/70951064).

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/findologic/plugin-plentymarkets-ceres-api/tags). 
