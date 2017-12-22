<?php
namespace BiteIT;

use Tracy\ILogger;

class TracySlackLogger implements ILogger {

    protected $webhookURL;
    protected $reportedPriorities = [ILogger::ERROR, ILogger::CRITICAL, ILogger::EXCEPTION];
    protected $iconURL;
    protected $iconEmoji;
    protected $username;


    public function __construct($webhookURL)
    {
        $this->webhookURL = $webhookURL;
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
     * @param $iconURL
     * @return $this
     */
    public function setIconURL($iconURL){
        $this->iconEmoji = null;
        $this->iconURL = $iconURL;
        return $this;
    }

    /**
     * @param $emoji
     * @return $this
     */
    public function setIconEmoji($emoji){
        $this->iconEmoji = $emoji;
        $this->iconURL = null;
        return $this;
    }

    /**
     * @param $username
     * @return $this
     */
    public function setUsername($username){
        $this->username = $username;
        return $this;
    }

    function log($value, $priority = self::INFO)
    {
        if($this->reportedPriorities)
        {
            if (!in_array($priority, $this->reportedPriorities))
                return;

            if (!in_array(ILogger::INFO, $this->reportedPriorities) && strpos($value, 'PHP Notice') !== false)
                return;
        }

        $payload['text'] = "*{$priority}* on *{$_SERVER['HTTP_HOST']}*: $value";
        if($this->username)
            $payload['username'] = $this->username;
        if($this->iconURL)
            $payload['icon_url'] = $this->iconURL;
        if($this->iconEmoji)
            $payload['icon_emoji'] = $this->iconEmoji;

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
        $c = curl_init($this->webhookURL);
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

        $result = file_get_contents($this->webhookURL, false, $context);
        return $result;
    }
}