php-akismet
===========

A simple PHP class for using the Akismet.com anti-spam API.

## Requirements

You will need an Akismet.com API key. You can obtain this by signing up at https://akismet.com/plans/ There is a free version for non-commercial sites.

## Usage

This example is for processing content 'live' as it is received. Refer to the documentation linked below for processing after the fact.

Start by creating a new Akismet object using your API key.

```php
$Akismet = new GregorMorrill\Akismet\Akismet('YOUR_API_KEY');
```

To check if content is spam, build an array of the fields you want to submit (refer to the [Akismet API documentation](http://akismet.com/development/api/#comment-check)). Then call the `checkSpam` method.

```php
$comment_data = array(
    'blog'                  => 'http://example.com',
    'user_ip'               => 'x.x.x.x',
    'user_agent'            => $_SERVER['HTTP_USER_AGENT'],
    'referrer'              => $_SERVER['HTTP_REFERER'],
    'comment_type'          => 'comment',
    'comment_author'        => 'John Doe',
    'comment_author_email'  => 'john@example.com',
    'comment_author_url'    => 'http://example.com/johndoe',
    'comment_content'       => 'Lorem ipsum',
);

$response = $Akismet->checkSpam($comment_data);
```

`$response` will be an array of data with keys
* `info`: cURL request information
* `header`: cURL response header
* `body`: cURL response body
* `spam`: boolean indicating whether the content is spam or not

At this point you can choose what to do with the content based on whether it is likely spam or not.

Please refer to the Akiset API documentation for more information: http://akismet.com/development/api/
