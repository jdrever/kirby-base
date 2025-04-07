# Using Kirby-base as a Kirby CMS plugin

- Install the plugin into your repository

> git submodule add https://github.com/jdrever/kirby-base.git site/plugins/kirby-base

- Create your own Kirby plugin for the additonal code for the site, including a composer.json file like the below

> {
"name": "bsbi/bsbi-docs",
"description": "A set of Kirby elements and models for the BSBI docs website",
"type": "project",
"autoload": {
"psr-4": {
"BSBI\\Docs\\": "src/"
}
},
"authors": [
{
"name": "James Drever",
"email": "james.drever@bsbi.org"
}
],
"require": {
"open-foundations/kirby-base": "*"
},
"config": {
"optimize-autoloader": true,
"allow-plugins": {
"getkirby/composer-installer": true
}
},
"allow-plugins": {
"getkirby/composer-installer": true
},
"extra": {
"kirby-plugin-path": "../../plugins"
},
"minimum-stability": "stable"
}
