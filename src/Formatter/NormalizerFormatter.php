<?php

namespace Norsys\LogsBundle\Formatter;

use Monolog\Formatter\NormalizerFormatter as BaseFormatter;

/**
 * Class NormalizerFormatter
 */
class NormalizerFormatter extends BaseFormatter
{
    /**
     * @param mixed $data
     *
     * @return array
     */
    protected function normalize($data)
    {
        $data = parent::normalize($data);

        if (is_array($data) === true) {
            foreach ($data as $key => &$value) {
                if (is_array($value) === true) {
                    $value = json_encode($value);
                }
            }
        }

        return $data;
    }
}
