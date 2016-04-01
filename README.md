Milkyway Multimedia - Silverstripe Core Extensions
==================================================

Features
--------
* New utility methods to use across your projects
* Allow uploads in UploadField
* Tabs can be loaded from URL hashes
    - You can also link to tabs if your link has the class: `ss-tabset-goto`
* New context menu options for your SiteTree
    - Publish Record
    - Unpublish Record
    - Delete permanently
* Allow cookie_path and cookie_domain to be set for Cookies

Additional methods included on extensions
-----------------------------------------
* Controller
    - ->BackLink
    - ->displayNiceView(): Display this controller in a pretty website style page
    - ->respondToFormAppropriately(): Either return a json encoded response or a redirect when not in an ajax request
* DataObject
    - ->i18n_description()
    - ->firstOrMake($filter = [], $additionalData = [], $write = true): Get matching record, or make if it doesnt exist
    - ->is_a($class): *for templates*
    - ->is_not_a($class): *for templates*
    - ->InheritedObj($fieldName): Get an inherited object (dot notation allowed). It will check the following:
        1. Cache
        2. Method on current object
        3. If object has parent method (or extends @Hierarchy), it will check the parents
        4. Check home page if it exists
        5. Check SiteConfig if it exists
* DBField: This has quite a few new methods, check the phpdoc for more info
* Member:
    - ->canAccessCMS()

singleton('director')
---------------------
This extends Director and has a few utility methods related to dealing with the SiteTree and Controllers, and adds some new template globals

- secureBaseURL
- nonSecureBaseURL
- baseWebsiteURL - The url without the protocol or www, the pretty url
- protocol
- homePage
- isHomePage($page = SiteTree|int)
- adminLink
- siteConfig

singleton('mwm')
----------------
Some utility methods to deal with some stuff I could not do with vanilla Silverstripe, and also adds some new template globals

- canAccessCMS
- canEditCurrentPage
- appDir
- is($type = string)

## Install
Add the following to your composer.json file

```

    "require"          : {
		"milkyway-multimedia/ss-mwm-core": "dev-master"
	}

```

## License
* MIT

## Version
* Version 0.3 (Alpha)

## Contact
#### Mellisa Hankins
* E-mail: mellisa.hankins@me.com
* Twitter: [@mi3ll](https://twitter.com/mi3ll "mi3ll on twitter")
* Website: mellimade.com.au
