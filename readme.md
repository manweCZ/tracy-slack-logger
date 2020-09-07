Quick integration for Tracy Debugging panel for Slack.

**Installation:**
1. Create a webhook integration for you Slack team here: https://my.slack.com/services/new/incoming-webhook/
2. Initialize TracySlackLogger

```php
$logger = new \BiteIT\TracySlackLogger('YOUR_HOOK_URL');
\Tracy\Debugger::$productionMode = true;
\Tracy\Debugger::setLogger( $logger );
```

Now whenever an error or exception occures on your website, your selected Slack Channel will be notified.

If you want to customize what log priorities should be notified to your Slack Channel, use the method
```php
$logger->setReportingLevels( [ ILogger::INFO ] );
```

If you want to report ALL priorities, use an empty array for the method.
By default, the TracySlackLogger notifies `ILogger::ERROR`, `ILogger::CRITICAL` and `ILogger::EXCEPTION` errors. 

If you want to enable or disable advanced information in slack message you can use these methods.

```php
$logger->setEnabledMessageData([\BiteIT\TracySlackLogger::MESSAGE_ALL]);
$logger->setDisabledMessageData(\BiteIT\TracySlackLogger::MESSAGE_IP);
```

If you want to add custom data to your messages, you can do so by using custom messages callback (added in v 0.5).
Message will be inserted before error description.
```php
$logger->addCustomMessageCallback(function() use ($myDependencies){
    return "*Logged user*: {$myDependencies->getLoggedUser()->getName()}";
});
```
