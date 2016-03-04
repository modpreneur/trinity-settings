#Trinity Settings

Bundle is used for storing Symfony parameters in database using Doctrine2 ORM.


##Usages

Set defaults variables:
---

config.yml

    trinity_settings:
        settings:
            null_value: ~
            key: "value"
            group.key: "value"


Note: The dot is defined for group.


In Controller:    
---
   
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
    
In Twig:
---
    
    {{ get_setting('some_setting') }} 
    {{ get_setting('some_user_setting', app.user.id) }}
    {{ get_setting('some_group_setting', null, 'MySettingGroup') }}
    {{ get_setting('some_user_group_setting', app.user.id, 'MySettingGroup') }}

    {{ has_setting('some_setting') }}
    {{ has_setting('some_user_setting', app.user.id) }}
    {{ has_setting('some_group_setting', null, 'MySettingGroup') }}
    {{ has_setting('some_user_group_setting', app.user.id, 'MySettingGroup') }}

    
    