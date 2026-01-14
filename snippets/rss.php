<?php echo '<?xml version="1.0" encoding="utf-8"?>'?>
<?php echo '<?xml-stylesheet href="/assets/css/pretty-feed-v3.xsl" type="text/xsl"?>'?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"  xmlns:atom="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss" xmlns:gml="http://www.opengis.net/gml">
  <channel>
    <title><?=$feedTitle?></title>
    <link><?=$feedLink?></link>
    <description>?=$feedDescription?></description>
    <language>en</language>
    <pubDate><?=date('r', time())?></pubDate>
    <lastBuildDate><?=date('r', time())?></lastBuildDate>
    <atom:link href="<?=$feedUrl?>" rel="self" type="application/rss+xml"/>
<?php foreach($posts as $post): ?>
    <item>
      <title><?=$post->title()?></title>
      <link><?=$post->url()?></link>
      <description>
        <?=html($post->openingContent())?>
      </description>
      <pubDate><?=date('r', $post->publishedDate()->toDate())?></pubDate>
    </item>
<?php endforeach ?>
  </channel>
</rss>