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

    protected $messenger = null;
    protected $message = null;

    public function __construct($webhookURL, $useDefaultLogger = false)
    {
        parent::__construct(Debugger::$logDirectory, Debugger::$email, Debugger::getBlueScreen());
        $this->messenger = new SimpleSlackMessenger($webhookURL);
        $this->useFileLoggerAsWell = $useDefaultLogger;
        $this->message = new SimpleSlackMessage();
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
        $this->message->setIconEmoji(null);
        $this->message->setIconUrl($slackIconURL);
        return $this;
    }

    /**
     * @param $emoji
     * @return $this
     */
    public function setSlackIconEmoji($emoji){
        $this->message->setIconEmoji($emoji);
        $this->message->setIconUrl(null);
        return $this;
    }

    /**
     * @param $slackUsername
     * @return $this
     */
    public function setSlackUsername($slackUsername){
        $this->message->setName($slackUsername);
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

        if($_SERVER['HTTP_HOST'])
        {
            $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $host = $_SERVER['HTTP_HOST'];
        }
        else
        {
            $url = $_SERVER['argv'][0];
            $host = 'CLI interpreter';
        }

        $text = "*{$priority}* on *{$host}* (URL: $url): $value";
        $this->message->setText($text);

        try{
            $this->messenger->sendSimpleMessage($this->message);
        }
        catch (SimpleSlackException $exception){
            throw $exception;
        }

    }

}