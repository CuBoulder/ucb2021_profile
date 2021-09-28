# ucb_2021_profile
Drupal 9 install profile for Nextpress.

## Installation

Add below into the `require` section of `composer.json`:
````
"cu-boulder/ucb_2021_profile": "dev-main"
````

Also add in the `repositories` section:
````
  {
      "type": "git",
      "url": "https://github.com/CuBoulder/ucb_2021_profile.git"
  }
````

This profile will NOT work without the `cu-boulder/*` modules or the theme present.

> To install a local version of Nextpress, follow the steps outlined in the
> [nextpress-project-template](https://github.com/CuBoulder/nextpress-project-template)

# Directory Structure

The profile primarily contains the configuration files to support the installation. Put config related to paragraphs or page types in the [ucb_custom_page_types](https://github.com/CuBoulder/ucb_custom_page_types) and [ucb_custom_paragraph_types](https://github.com/CuBoulder/ucb_custom_paragraphs) repositories. 
## Configuration
### config/install
  - Anything core related
  - Custom Media Types
  - Custom View Modes
  - User Roles
  - Site Settings
  - Block Layout
  - Any config that the custom paragrpahs and page types will depend on
### config/optional
  - Image Styles
  - WYSIWYG text editor settings
  - Any config that depend on config in the `config/install` directory

## ucb2021_profile.info.yml
Contains a list of modules to install. Make sure that the module also is required in the `composer.json` file if it's not a core module.

## ucb2021_profile.menu.links.yml
Add menu links to the UI
## ucb2021_profile.install
Contains `hook_install()` and `hook_update()`

## ucb2021_profile.profile
Contains `hook_modules_installed()` which runs after the modules defined in the .info file are installed. This is
where most `cu-boulder/*` modules are installed.