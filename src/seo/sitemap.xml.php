<?php
// Custom sitemap functions
function getRevisionDate($page_id, $revision)
{
  $revision = json_decode(NF::$capi->get('builder/pages/' . $page_id . '/revisions/' . $revision)->getBody(), true);
  return date('c', strtotime($revision['publish_date']));
}


function generateUrl($entry)
{
  $structure = NF::$site->structures[$entry['directory_id']];
  $schema = $structure['url_scheme'];

  if ($structure['url_scheme'] == 'url/') {
    $url_asset[1] = rtrim($entry['url'], '/');
    $url = $url . $url_asset[1] . '/';
  } else if ($structure['url_scheme'] == 'id/') {
    $url_asset[1] = $entry['id'];
    $url = $url . $url_asset[1] . '/';
  } else if ($structure['url_scheme'] == 'id/url/') {
    $url_asset[1] = rtrim($entry['url'], '/');
    $url_asset[2] = $entry['id'];
    $url = $url . $url_asset[2] . '/' . $url_asset[1] . '/';
  } else if ($structure['url_scheme'] == 'yyyy/mm/dd/url/') {
    $url_asset[1] = rtrim($entry['url'], '/');
    $url_asset[2] = convert_datetime($entry['created'], 'd');
    $url_asset[3] = convert_datetime($entry['created'], 'm');
    $url_asset[4] = convert_datetime($entry['created'], 'Y');
    $url = $url . $url_asset[4] . '/' . $url_asset[3] . '/' . $url_asset[2] . '/' . $url_asset[1] . '/';
  } else if ($structure['url_scheme'] == 'yyyy/mm/url/') {

    $url_asset[1] = rtrim($entry['url'], '/');
    $url_asset[2] = convert_datetime($entry['created'], 'm');
    $url_asset[3] = convert_datetime($entry['created'], 'Y');
    $url = $url . $url_asset[3] . '/' . $url_asset[2] . '/' . $url_asset[1] . '/';
  } else if ($structure['url_scheme'] == 'yyyy/url/') {
    $url_asset[1] = rtrim($entry['url'], '/');
    $url_asset[2] = convert_datetime($entry['created'], 'Y');
    $url = $url . $url_asset[2] . '/' . $url_asset[1] . '/';
  }

  $page = NF::$site->pages[$structure['canonical_page_id']]['url'];

  return $page . $url;
}

// Settings
$site_url = get_setting('site_protocol') . '://' . get_setting('site_url') . '/';


// Sent the correct header so browsers display properly.
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '
<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>' . "\n";
?>
<urlset xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php
  // Check if routing is active
  if (isset(NF::$config['domains']['default'])) { } else {

    $sitemap = \NF::$cache->fetch("sitemap");
    if ($sitemap == null) {

      if (!get_setting('sitemap_hide_entries')) {

        function loadAllEntries()
        {

          $entries = json_decode(NF::$capi->post('search/raw', [
            'json' => [
              'index' => 'entry',
              'body' => [
                'query' => [
                  'query_string' => [
                    'query' => 'published:1'
                  ]
                ]
              ],
              '_source' => [
                'id', 'name', 'directory_id', 'url', 'created', 'updated', 'published'
              ],
              'size' => 10000,
              'from' => 0
            ]
          ])->getBody(), true)['hits']['hits'];


          foreach ($entries as &$entry) {
            $entry = $entry['_source'];
          }
          return $entries;
        }

        NF::$site->entries = loadAllEntries();
      }

      //First, list out all pages
      foreach (NF::$site->pages as $page) {

        if (
          $page['template'] != 'i'
          &&
          $page['template'] != 'e'
          &&
          $page['template'] != 'f'
          &&
          $page['url'] != null
        ) {

          if ($page['url'] == 'index/' || $page['url'] == 'index') {
            $page['url'] = '';
          }

          if ($page['visible']) {

            $sitemap[] = [
              'loc' => $site_url . $page['url'],
              'lastmod' => getRevisionDate($page['id'], $page['revision'])
            ];
          }
        }
      }

      //Then, list out all entries

      foreach (NF::$site->entries as $entry) {

        if (
          NF::$site->structures[$entry['directory_id']]['generate_sitemap'] == "1"
          &&
          NF::$site->structures[$entry['directory_id']]['canonical_page_id'] != "0"
          &&
          $entry['url'] != null
        ) {

          if ($entry['updated'] == '0000-00-00 00:00:00') {
            $entry['updated'] = $entry['created'];
          }

          $sitemap[] = [
            'loc' => $site_url . generateUrl($entry),
            'lastmod' => date('c', strtotime($entry['updated']))
          ];
        }
      }

      //Cache the sitemap
      NF::$cache->save("sitemap", $sitemap);
    }

    foreach ($sitemap as $item) { ?>
      <url>
        <loc><?= $item['loc']; ?></loc>
        <lastmod><?= $item['lastmod']; ?></lastmod>
      </url>
    <?php }
} ?>
</urlset>
