#Trinity Settings

[![Coverage Status](https://coveralls.io/repos/github/modpreneur/trinity-settings/badge.svg?branch=master)](https://coveralls.io/github/modpreneur/trinity-settings?branch=master)
[![Build Status](https://travis-ci.org/modpreneur/trinity-settings.svg?branch=master)](https://travis-ci.org/modpreneur/trinity-settings)

Bundle for storing Symfony parameters in database using Doctrine2 ORM and easy reach from Symfony Controller and twig.

##Installation

### 1. Add trinity/settings to your composer.json

    //composer.json
    {
        //..
        "require": {
            //..
            "trinity/settings": "~1.0",
            //..
        }
        //..
    }

### 2. Enable trinity/settings in the kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new \Trinity\Bundle\SettingsBundle\SettingsBundle(),
            // ...
        );
    }


##Usages

###Set defaults variables:

In some config.yml

    trinity_settings:
        settings:
            null_value: ~
            key: "value"
            group.key: "value"


Note: The dot is defined for group.


### Usage in Controller:
   
    //Global Setting
    $this->get('trinity.settings')->set('parameter', $parameter);
    $this->get('trinity.settings')->get('parameter');
    $this->get('trinity.settings')->has('parameter');

    //User Setting
    $this->get('trinity.settings')->set('parameter', $parameter, $owner);
    $this->get('trinity.settings')->get('parameter', $owner);
    $this->get('trinity.settings')->has('parameter', $owner);

    //Setting for some setting group ($owner can be null)
    $this->get('trinity.settings')->set('parameter', $parameter, $owner, $group);
    $this->get('trinity.settings')->get('parameter', $owner, $group);
    $this->get('trinity.settings')->has('parameter', $owner, $group);
    
###Usage In Twig:
    
    {{ get_setting('some_setting') }} 
    {{ get_setting('some_user_setting', app.user.id) }}
    {{ get_setting('some_group_setting', null, 'MySettingGroup') }}
    {{ get_setting('some_user_group_setting', app.user.id, 'MySettingGroup') }}

    {{ has_setting('some_setting') }}
    {{ has_setting('some_user_setting', app.user.id) }}
    {{ has_setting('some_group_setting', null, 'MySettingGroup') }}
    {{ has_setting('some_user_group_setting', app.user.id, 'MySettingGroup') }}
