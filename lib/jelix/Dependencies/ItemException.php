<?php

namespace Jelix\Dependencies;

class ItemException extends Exception
{
    protected $item;

    public function __construct($message, Item $item, $code = 0)
    {
        $this->item = $item;
        parent::__construct($message, $code);
    }

    public function getItem()
    {
        return $this->item;
    }
}
