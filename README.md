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
    
In Twig:
---
    
    {{ get_setting('some_setting') }} 
    {{ get_setting('some_user_setting', app.user.id) }}  
    {{ has_setting('some_setting') }} 
    {{ get_setting('some_setting', null, 'default_value') }}  // default value
    
    