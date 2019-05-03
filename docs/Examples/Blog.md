# Setting up a blog

Setting up a blog with Netflex is easy. To run a blog in netflex you need the following:

* A blog posts structure with your custom fields
* A blog template consisting of two or more parts:
  * Featured posts
  * Blog post
  * Archive (optional)
  * Search (optional)

## Configuring the blog posts structure

A blog post consists of a few fields that you need to add your content. At least you should have the following fields:

* Name/Title, slug/url, publish date, update date, tags (these are standard fields in all structures and does not need to be configured).
* Post Intro/Ingress (I would use a text area her to get clean text without formatting)
* Post (Large editor)
* Post image (Image)

Optionally you could also add:

* Categories (Relation to a different structure)
* Author (Relation to a different structure)
* Gallery (Image gallery)

## Configuring the blog template

A blog needs a main template for showing the latest post and a specific post. The first thing you do is to create a template. I am calling mine blog.php, and i put it in the "/templates" folder.

The first thing i do is to configure the part mapping:

blog.php
```php
<?php

if (isset($url_asset[1]) {
  //There is an url asset available, and i want to show the post
} else {
  //There is no url asset available, so i want to show the latest posts.
}
```

The part rules defines which part of my code i want to show. If there is an url asset preset in my template, i want to show the post details. If not, i want to show the latest post. An url example of this is:

`https://mysite.com/blog/`

`https://mysite.com/blog/my-blog-post/`

The last url contains an asset in addition to the template url, and this will be available in the `$url_asset[1]` array/map (my-blog-post/).

**Blog details**

Now, its time to start adding your markup. As usual you add the static parts of the page first, over the part mapping. Your html head, header, footers and other. Also add the blog template header if you have any. Then, split up your code with the featured list, and part list inside the mapping for each part.

blog.php
```php
<?php

get_block('head');
get_block('header');
?>
<div class="container">
<? if(isset($url_asset[1]) { ?>
  <? $blogpost = get_directory_entry(get_entry_id($url_asset[1])); ?>
  // Add markup for your blogpost here.
<? } else { ?>
  <? $latest = get_latest_entries(10000, 'ORDER BY created DESC LIMIT 10'); ?>
    // Creates array of ids of the 10 latest post based on creation date.
  <ul>
  <? foreach ($latest as $post) { ?>
    <? $blogpost=get_directory_entry($post); ?>
    <li>
      // Add markup for a post item
    </li>
  <? } ?>
  </ul>
<? } ?>
</div>
<? get_block('footer'); ?>
```

To test this template, we recommend adding a route to it. Add this to your config/routes.json file:

```javascript
{
  "blog/": "templates/blog.php"
}
```

Remember to clear the cache after you have added the route.

Now you can navigate to the page in your browser via https://mysite.netflex.dev/blog/.
