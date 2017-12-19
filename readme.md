Quick integration for Tracy Debugging panel for Slack.

**Installation:**
1. Create a webhook integration for you Slack team here: https://my.slack.com/services/new/incoming-webhook/
2. Initialize TracySlackLogger

```php
$logger = new \BiteIT\TracySlackLogger('YOUR_HOOK_URL');
\Tracy\Debugger::$productionMode = true;
\Tracy\Debugger::setLogger( $logger )
```

Now whenever an error or exception occures on your website, your selected Slack Channel will be notified.