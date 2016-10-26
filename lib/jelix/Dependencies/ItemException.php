<?php

namespace Jelix\Dependencies;

class ItemException extends Exception
{
    protected $item;

    protected $relatedData;

    public function __construct($message, Item $item, $code = 0, $relatedData = null)
    {
        $this->item = $item;
        $this->relatedData = $relatedData;
        parent::__construct($message, $code);
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getRelatedData()
    {
        return $this->relatedData;
    }
}
