# Sylius Referral Plugin [![Build Status](https://travis-ci.org/PlumTreeSystems/SyliusReferralPlugin.svg?branch=master)](https://travis-ci.org/PlumTreeSystems/SyliusReferralPlugin)

A plugin that lets you customize how you filter your orders and save them into batches.

## Installation

Install the package with this command: `composer require plumtreesystems/sylius-referral`

Add plugin dependencies to your bundles.php file:

```php
return [
    PTS\SyliusReferralPlugin\PTSSyliusReferralPlugin::class => ['all' => true],
];
```

Import the bundle's configuration to your `_sylius.yaml` file

```yaml
imports:
    - { resource: "@PTSSyliusReferralPlugin/Resources/config/config.yml" }
```

Import the bundle's routes to your `routes.yaml` file

```yaml
pts_sylius_referral_plugin:
  resource: "@PTSSyliusReferralPlugin/Resources/config/routes.yaml"
```

Copy the bundle's templates from `src/Resources/templates` to your project `templates/` folder

Install the assets of the bundle by executing this command: `php bin/console assets:install public`

Add your store's channels to your configuration.
```yaml
pts_sylius_referral:
  channel_paths:
    -   name: 'C_STORE'
        default: true
```
If you desire the ability to edit customer's enrollers in your admin panel, enable it in your configuration
```yaml
pts_sylius_referral:
  customers:
    enroller_edit:
      enabled: true     # false by default
```