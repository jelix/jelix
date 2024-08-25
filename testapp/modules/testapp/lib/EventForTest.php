<?php

namespace Testapp\Tests;

class EventForTest extends \jEvent
{
    protected $dummy = '';
    protected $dummy2 = '';

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

    public function setDummy2Value($val)
    {
        $this->dummy2 = $val;
    }

    public function getDummy2Value()
    {
        return $this->dummy2;
    }
}
