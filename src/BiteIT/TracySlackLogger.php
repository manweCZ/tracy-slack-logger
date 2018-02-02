<?php
namespace BiteIT;

use Tracy\Debugger;
use Tracy\ILogger;
use Tracy\Logger;

class TracySlackLogger extends Logger {

    protected $slackWebhookURL;
    protected $reportedPriorities = [ILogger::ERROR, ILogger::CRITICAL, ILogger::EXCEPTION];
    protected $slackIconURL;
    protected $slackIconEmoji;
    protected $slackUsername;
    protected $useFileLoggerAsWell = true;

    public function __construct($webhookURL, $useDefaultLogger = false)
    {
        parent::__construct(Debugger::$logDirectory, Debugger::$email, Debugger::getBlueScreen());
        $this->slackWebhookURL = $webhookURL;
        $this->useFileLoggerAsWell = $useDefaultLogger;
    }

    public function addReportingLevel($level){
        $this->reportedPriorities[] = $level;
        $this->reportedPriorities = array_unique($this->reportedPriorities);
    }

    /**
     * @param $levels
     * @return $this
     */
    public function setReportingLevels($levels){
        $this->reportedPriorities = (array) $levels;
        return $this;
    }

    /**
     * @param $slackIconURL
     * @return $this
     */
    public function setSlackIconURL($slackIconURL){
        $this->slackIconEmoji = null;
        $this->slackIconURL = $slackIconURL;
        return $this;
    }

    /**
     * @param $emoji
     * @return $this
     */
    public function setSlackIconEmoji($emoji){
        $this->slackIconEmoji = $emoji;
        $this->slackIconURL = null;
        return $this;
    }

    /**
     * @param $slackUsername
     * @return $this
     */
    public function setSlackUsername($slackUsername){
        $this->slackUsername = $slackUsername;
        return $this;
    }

    function log($value, $priority = self::INFO)
    {
        if(is_array($value)){
            $value = implode(' ', $value);
        }

        if($this->useFileLoggerAsWell)
        {
            parent::log($value, $priority);
        }

        if($this->reportedPriorities)
        {
            if (!in_array($priority, $this->reportedPriorities))
                return;

            if (!in_array(ILogger::INFO, $this->reportedPriorities) && strpos($value, 'PHP Notice') !== false)
                return;
        }

        $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $payload['text'] = "*{$priority}* on *{$_SERVER['HTTP_HOST']}* (URL: $url): $value";
        if($this->slackUsername)
            $payload['username'] = $this->slackUsername;
        if($this->slackIconURL)
            $payload['icon_url'] = $this->slackIconURL;
        if($this->slackIconEmoji)
            $payload['icon_emoji'] = $this->slackIconEmoji;

        $message = array(
            'payload' => json_encode($payload)
        );
        // Use curl to send your message
        try {
            if (ini_get('allow_url_fopen')) {
                $result = $this->sendByFileGetContents($message);
            } else {
                $result = $this->sendByCurl($message);
            }
        }
        catch (\Exception $e){
            echo 'Unable to use either file_get_conents nor curl';
        }


    }

    protected function sendByCurl($message){
        $c = curl_init($this->slackWebhookURL);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $message);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($c);
        curl_close($c);

        return $result;
    }

    protected function sendByFileGetContents($message){
        $postdata = http_build_query(
            $message
        );

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );

        $context  = stream_context_create($opts);

        $result = file_get_contents($this->slackWebhookURL, false, $context);
        return $result;
    }
}