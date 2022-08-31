<?php

namespace Testapp\Tests;

class EventForTest extends \jEvent
{
    protected $dummy = '';

    public function __construct()
    {
        parent::__construct('TestEventObject');
    }


    public function setDummyValue($val)
    {
        $this->dummy = $val;
    }

    public function getDummyValue()
    {
        return $this->dummy;
    }
}
