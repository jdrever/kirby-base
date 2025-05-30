# Using Kirby-base as a Kirby CMS plugin

- Install the plugin into your repository

> git submodule add https://github.com/jdrever/kirby-base.git site/plugins/kirby-base

- Create your own Kirby plugin for the additonal code for the site, including a composer.json file with an entry to require KirbyBase

```

"require": {
"open-foundations/kirby-base": "*"
},

```

# Extending KirbyBase

- Create a WebPage model class that extends BaseWebPage. Add the fields each page for your site will need.
- Then create whatever other specialised WebPage models you need that extend WebPage
- Create a KirbyHelper class that extends KirbyBaseHelper.  
  - Implement getBasicPage to return your basic WebPage class.
  - Implement setBasicPage to populate the fields in your WebPage model
- Then implement settter functions in KirbyHelper for each of the specialised pages

## CoreLinks

- Create a CoreLinkTypes enum, and add has/get functions using the enum to the WebPage model to allow easy handling of core links

```
    /**
     * @param CoreLinkType $linkType
     * @return bool
     */
    public function hasCoreLink(CoreLinkType $linkType): bool {
        $coreLink = $this->getCoreLink($linkType);
        return  ($coreLink->getStatus() !== false);
    }

    /**
     * returns null if no link found.  Use hasCoreLink to check first.
     * @param CoreLinkType $linkType
     * @return CoreLink
     */
    public function getCoreLink(CoreLinkType $linkType): CoreLink
    {
        return $this->coreLinks->getPage($linkType->value);
    }
```

## Menu Pages

Set up a collection called menuPages - this will be used to setMenuPages for the BaseWebPage class.

# Updates

## Changing the KirbyBase version number

You need to do this to update the plugin in your project

- Change the composer.json version number
- Commit that change, and add a tag with the version number
- Push that change, along with the new tag

## Getting the latest version of KirbyBase in your project

- Run composer update
