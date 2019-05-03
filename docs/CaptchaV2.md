# CaptchaV2 Class

The CaptchaV2 class implements Google's `reCaptcha V2`.
It implements functions to add Javascript imports, the checkbox and a validation function that
checks `$_GET` and `$_POST` automatically for the correct variables that is needed to validate a request.

## Setup
To make this work on your site, go to [https://www.google.com/recaptcha/](https://www.google.com/recaptcha/) and create a keyset for your site.
Then add two settings in Netflex. `captcha_site_key` and `captcha_site_secret`.


## Using CaptchaV2
The Recaptcha class is only loaded when required, so every file referencing CaptchaV2 has to have a corresponding use statement
```php
<?php
  use Netflex\Site\Support\CaptchaV2 as Captcha;
?>
```

### Installing dependencies
The reCaptcha API requires that a Javascript file is loaded.

Add this to wherever you declare your javascript files
```php
<?= Captcha::scriptTag(); ?>
```

### Add the CheckBox to your form
This function does not return anything other than the actual code for the checkbox. So you need to add your own
scaffolding for it to fit in forms.

```php
<div class="form-control">
  <?= Captcha::checkBox(); ?>
</div>
```

### Validate the Response
The `isValid()` function will perform a http call to Googles server and validate the request automatically.
It will return *true* if successful and will throw an exception should any errors occur

Here is some example code:

```php
<?php

use Netflex\Site\Support\CaptchaV2 as Captcha;

// This exception is thrown when Google rejects the captche
use Netflex\Site\Support\GoogleResponseException;

if (isset($_POST['newsletter-add'])) {
  try {
    if (ifCaptcha::isValid()) {
      // Captche validated
    }
  } catch (GoogleResponseException $ex) {
    // Tell user that he/she failed the Captcha Challenge.
  }
}
```
