# Findologic API plugin for Plentymarkets Ceres
[![Build Status](https://travis-ci.org/findologic/plugin-plentymarkets-ceres-api.svg?branch=development)](https://travis-ci.org/findologic/plugin-plentymarkets-ceres-api)

## Table of Contents

1. [Install](#install)
   1. [Prerequisites](#prerequisites)
   1. [Installation](#installation)
1. [Development](#development)
   1. [Installing dependencies](#installing-dependencies)
   1. [Development cycle](#development-cycle)
   1. [Running unit-tests locally](#running-unit-tests-locally)
1. [Deployment & Release](#deployment--release)
1. [Versioning](#versioning)

## Install

### Prerequisites

The following plugins must be installed in order for the Findologic plugin
to be installed.

* Ceres >= 5.0
* IO >= 5.0

### Installation

When developing, install the Findologic Plugin in the Plentymarkets backend via GIT.  
For non-development work, it can also be installed via the [marketplace](https://marketplace.plentymarkets.com/findologic_6390).  

*Note: In the Plentymarkets Backend you have the possibility to change the code of your plugin. This only works, if
the plugin is installed via the marketplace.*

## Development

Developing Plentymarkets plugins is very different from other shopsystems, as Plentymarkets
is a Cloud-Hosted Shopsystem, which uses internal proprietary code by Plentymarkets.
This also means that it can not be setup on a local machine.

### Installing dependencies

We have PHP dependencies, as well as Javascript dependencies. Make sure you have Composer (min. 1.8),
as well as Node and NPM installed.

Install all PHP/Composer dependencies:
```bash
composer install
```

Install all JS/NPM packages:
```
npm install
```

Running the following command, will enable a Git hook, which prevents you from pushing
your code, in case you have made JS/CSS changes, but did not build them:
```
gulp install-hooks
```

### Development cycle

Since you can not setup a local instance of Plentymarkets, you always need to push your changes
onto a separate branch, which you then can checkout on the Plentymarkets backend.

```
git checkout -b PLENTY-<story-id>_<short-description>
```

Before pushing you may run build to ensure your JS/CSS is built.

```
npm run-script build
```

Alternatively if you have `gulp` installed globally, you can also simply run `gulp`.

### Running unit-tests locally

Make sure to include the `test/phpunit.xml` as an *alternative configuration file* in your IDE.

Alternatively all tests can be run, by executing

```
composer test
```

## Deployment & Release
1. Update the German and English changelogs in folder `meta/documents`.
1. Bump the plugin version in files `plugin.json` and `src/Constants/Plugin.php`.
1. Open the backend from our Plentymarkets test shop and login. 
1. Go to *Plugins > Plugin overview > Ceres > Findologic > Git* and fetch & pull the master branch.
1. Go back to *Plugin overview* and click *Save & deploy plugin set*.
1. Open the plugin again and click on *Upload to plentyMarketplace*.
1. The plugin may not be available yet, Plentymarkets has to do a review on their side. But once that is
 done, you can notify everyone via Basecamp.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/findologic/plugin-plentymarkets-ceres-api/tags). 
