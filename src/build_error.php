<?php

$isAsset = $url == 'browserconfig.xml/' &&
  $url == 'favicon.ico/' &&
  $url == 'apple-touch-icon.png/' &&
  $url == 'apple-touch-icon-precomposed.png/' &&
  $url == 'apple-touch-icon-76x76.png/' &&
  $url == 'apple-touch-icon-76x76-precomposed.png/' &&
  $url == 'apple-touch-icon-152x152.png/' &&
  $url == 'apple-touch-icon-152x152-precomposed.png/' &&
  $url == 'apple-touch-icon-120x120-precomposed.png/' &&
  $url == 'apple-touch-icon-120x120.png/';

if (!$isAsset) {

  $logtype = 'page';
  $loglink = strip_tags($url);
  $logcode = 404;
  $logref = 'none';

  if (isset($_SERVER['HTTP_REFERER'])) {
    $logref = strip_tags($_SERVER['HTTP_REFERER']);
  }

  $logagent = strip_tags($_SERVER['HTTP_USER_AGENT']);

  //Set header
  http_response_code(404);

  NF::$capi->post('errors', ['json' => [
    'type' => 'page',
    'error_code' => '404',
    'item_identifier' => $loglink,
    'message' => $logref,
    'user' => 'web',
    'agent' => $logagent
  ]]);

  if (file_exists('files/404.php')) {
    require('files/404.php');
  } else { ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>There was an error</title>
  <link media="all" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400" />
</head>

<body>
  <style>
    body {

      font-family: "Helvetica Neue",
        Helvetica,
        Arial,
        sans-serif;

      font-size: 16px;
      line-height: 1.42857143;
      color: #333;
      width: 100vw;
      height: 100vh;

      display: flex;
      align-items: center;
      justify-content: center;
    }

    h1 {
      font-size: 2.25rem;
      font-family: inherit;
      font-weight: 500;
    }

    article {
      display: flex;
      max-width: 55rem;
    }

    h3 {
      margin-bottom: 0rem;
    }

    section {
      display: flex;
      flex-direction: column;
    }

    p {
      margin-bottom: 0rem;
    }

    @media only screen and (max-width: 800px) {
      img {
        display: none;
      }

      h1 {
        text-align: center;
      }

      section {
        padding: 1rem;
      }
    }

    a {
      color: #428bca;
      text-decoration: none;
    }
  </style>

  <article>
    <section>
      <h1>We’re sorry - something has gone wrong on our end.</h1>
      <section>
        <h3>What could have caused this?</h3>
        <p>
          Well, something technical went wrong on our site.
          We might have removed the page when we redesigned our website.
          Or the link you clicked might be old and does not work anymore.
          Or you might have accidentally typed the wrong URL in the address bar.
        </p>
      </section>
      <section>
        <h3>What you can do?</h3>
        <p>You might try retyping the URL and trying again.</p>
        <p>Or we could take you back to the <a href="/" title="Home page">Home page</a>.</p>
        <p>This error has been logged and we should hopefully be able to fix it. Thank you for letting us know.</p>
      </section>
    </section>
    <section>
      <img src="https://s3-eu-west-1.amazonaws.com/netflexapp/frameworkassets/404error2.jpg">
    </section>
  </article>
</body>

</html>
<?php
  }
}
