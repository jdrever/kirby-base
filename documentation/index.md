# Using Kirby-base as a Kirby CMS plugin

- Install the plugin into your repository

> git submodule add https://github.com/jdrever/kirby-base.git site/plugins/kirby-base

- Create your own Kirby plugin for the additonal code for the site, 
- including a composer.json file with an entry to require KirbyBase

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

- Create a CoreLinkTypes enum, and add has/get functions using the enum 
- to the WebPage model to allow easy handling of core links

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

## Lists and Pagination

- Use getSpecificModelList and pass into the parameter $modelListClass  the list class you want to create a 
list for, e.g. EventsList (it must extend BaseList)
- In that list class, overrride getItemType to return the class you want the list to be made up of, 
e.g. Event (it must extend BaseModel)
- If you're using a filter, create a Filter class (extending BaseFilter) to hold the parameters you can filter by, 
and implement a filter function based on the list class name, e.g. filterEventsList
- If you are using pagination, override UsePagination in the model list class.  
You can optionally also override getPaginationPerPage (which is 10 by default) and call the base/pagination snippet, 
passing in the getPagination() function for your class list 


## Menu Pages

Set up a collection called menuPages - this will be used to setMenuPages for the BaseWebPage class.

## File Links

If you want file links to download the associated file, you need to add a controller for file_link which calls the 
redirectToFile function, e.g:

```
    $helper = new KirbyHelper($kirby, $site, $page);
    $helper->redirectToFile($page);
```

## Sitemap

There is a route set for sitemap.xml to generate a sitemap.  It will ignore all templates in the config field 
sitemapExclude

## robots.txt

There is a route set up for robots.txt excluding the kirby folders and search/login folder, plus AI bots.  To add 
additional elements to the robots.txt, create a snippet called robots-txt-additional

# Updates

## Changing the KirbyBase version number

You need to do this to update the plugin in your project

- Change the composer.json version number
- Commit that change, and add a tag with the version number
- Push that change, along with the new tag

## Getting the latest version of KirbyBase in your project

- Run composer update
