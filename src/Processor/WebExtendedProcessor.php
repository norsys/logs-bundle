<?php

namespace Norsys\LogsBundle\Processor;

/**
 * Class WebExtendedProcessor
 */
class WebExtendedProcessor
{
    /**
     * @var array
     */
    protected $serverData;

    /**
     * @var array
     */
    protected $postData;

    /**
     * @var array
     */
    protected $getData;

    /**
     * @param array $serverData
     * @param array $postData
     * @param array $getData
     */
    public function __construct(array $serverData = null, array $postData = null, array $getData = null)
    {
        $this->serverData = $serverData ?? $_SERVER;
        $this->postData   = $postData ?? $_POST;
        $this->getData    = $getData ?? $_GET;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // Skip processing if for some reason request data
        // Is not present (CLI or wonky SAPIs)
        if (isset($this->serverData['REQUEST_URI']) === false) {
            return $record;
        }

        $record['http_server'] = $this->serverData;
        $record['http_post']   = $this->postData;
        $record['http_get']    = $this->getData;

        return $record;
    }
}
