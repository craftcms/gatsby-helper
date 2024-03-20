<p align="center"><img src="./src/icon.svg" width="100" height="100" alt="Gatsby logo"></p>

<h1 align="center">Gatsby Helper</h1>

This plugin enables support for the [gatsby-source-craft](https://github.com/craftcms/gatsby-source-craft) Gatsby source plugin. Combined, they provide an integration between Craft CMS and [Gatsby](https://www.gatsbyjs.com/).

## Requirements

This plugin requires Craft CMS 4.0.0+ or 5.0.0+.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Navigate to the Plugin Store in your project’s control panel and search for “Gatsby Helper”. Then choose **Install** from in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/gatsby-helper

# tell Craft to install the plugin
./craft install/plugin gatsby-helper
```

See the `gatsby-source-craft` documentation for more on configuring the plugin with your Gatsby project: https://github.com/craftcms/gatsby-source-craft
