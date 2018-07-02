<?php

namespace Norsys\LogsBundle\Model;

/**
 * Class Log
 */
class Log
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var integer
     */
    protected $level;

    /**
     * @var string
     */
    protected $levelName;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var array
     */
    protected $extra;

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
     * Log constructor.
     *
     * @param array $data
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        if (isset($data['id']) === false) {
            throw new \InvalidArgumentException();
        }

        $this->id         = $data['id'];
        $this->channel    = $data['channel'];
        $this->level      = $data['level'];
        $this->levelName  = $data['level_name'];
        $this->message    = $data['message'];
        $this->date       = new \DateTime($data['datetime']);
        $this->context    = (isset($data['context']) === true) ? json_decode($data['context'], true) : array();
        $this->extra      = (isset($data['extra']) === true) ? json_decode($data['extra'], true) : array();
        $this->serverData = (isset($data['http_server']) === true) ? json_decode($data['http_server'], true) : array();
        $this->postData   = (isset($data['http_post']) === true) ? json_decode($data['http_post'], true) : array();
        $this->getData    = (isset($data['http_get']) === true) ? json_decode($data['http_get'], true) : array();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (mb_strlen($this->message) > 100) ? sprintf('%s...', mb_substr($this->message, 0, 100)) : $this->message;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getLevelName()
    {
        return $this->levelName;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @return array
     */
    public function getServerData()
    {
        return $this->serverData;
    }

    /**
     * @return array
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @return array
     */
    public function getGetData()
    {
        return $this->getData;
    }
}
