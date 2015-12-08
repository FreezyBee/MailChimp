Quickstart
==========


Installation
------------

The best way to install FreezyBee/MailChimp is using  [Composer](http://getcomposer.org/):

```sh
$ composer require freezy-bee/mail-chimp
```

With Nette `2.3` and newer, you can enable the extension using your neon config.

```yml
extensions:
	mailChimp: FreezyBee\MailChimp\DI\MailChimpExtension
```

Minimal configuration
------------------

```yml
mailChimp:
	apiKey: **your api key** # more info https://admin.mailchimp.com/account/api/
```


Debugging
---------

The extension monitors request and response, when in debug mode. All that information is available in Tracy panel



Example
-------

```php

class HomepagePresenter extends Presenter
{
    /** @var \FreezyBee\MailChimp\Api @inject */
    public $api;

    public function actionTest()
    {

        // get Campaigns
        try {
            $result = $this->api->call('GET', '/campaigns');
            dump($result);

        } catch (MailChimpException $e) {
            Debugger::log($e);
        }

        // create new Campaign
        $params = [
            "recipients" => [
                "list_id" => "aaa2**dsds"
            ],
            "type" => "regular",
            "settings" => [
                "subject_line" => "TEST",
                "reply_to" => "test@email.com",
                "from_name" => "Customer Service"
            ]
        ];

        try {
            $result = $this->api->call('POST', '/campaigns', $params);
            dump($result);

        } catch (MailChimpException $e) {
            Debugger::log($e);
        }
    }
}
```
