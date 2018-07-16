<?php

namespace Norsys\LogsBundle\Tests\Units\Formatter;

use Norsys\LogsBundle\Tests\Units\Test;

class NormalizerFormatter extends Test
{
    public function testOnNormalizeMethod()
    {
        $this
            ->given(
                $data = [['val_1', 'val_1_bis'], 'val_2'],
                $this->newTestedInstance()
            )
            ->if($r = $this->testedInstance->format($data))
            ->then
            ->array($r)
            ->isEqualTo(['["val_1","val_1_bis"]', 'val_2']);
    }
}
