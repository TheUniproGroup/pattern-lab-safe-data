[![Build Status](https://travis-ci.org/FabbDev/pattern-lab-php-safe-data.svg?branch=master)](https://travis-ci.org/FabbDev/pattern-lab-php-safe-data)

# Safe Data Pattern Lab PHP plugin 

The Safe Data plugin allows data from the data directory to be marked as safe when used with PHP Pattern Lab and Twig.

One place it could be useful is if
1. you want to use Pattern Lab with Drupal and have the same default autoescaping strategy in Pattern
   Lab as Drupal (`html`); and
1. from Drupal you pass a variable to a template that contains a render array which returns safe markup (ie it won't get
   escaped when used in a template despite the autoescaping strategy) – an example of this might be a formatted text
   field.
   
You can use this plugin to create data variables that will get marked as safe and can be used for eg. formatted text
field values. 

## Installation

To add the plugin to your project using [Composer] type:

```bash
compose config repositories.pattern-lab-safe-data '{"type": "vcs", "url": "https://github.com/FabbDev/pattern-lab-php-safe-data"}'
composer require fabb-dev/pattern-lab-safe-data
```

## Usage

You can mark data as safe in your json or yml data files by using this format: 

```
"key": "MakeSafe() > This will <em>not</em> get escaped."
```

## Disabling the Plugin

To disable the plugin you can either directly edit `./config/config.yml` or use the command line option:

```bash
php core/console --config --set plugins.safeData.enabled=false
```

## Todo

- Decide on final living place add to packagist.

[Composer]: https://getcomposer.org/
