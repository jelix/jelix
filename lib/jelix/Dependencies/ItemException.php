<?php

namespace Jelix\Dependencies;

class ItemException extends Exception
{
    const ERROR_CIRCULAR_DEPENDENCY = 1;
    const ERROR_BAD_ITEM_VERSION = 2;
    const ERROR_REMOVED_ITEM_IS_NEEDED = 3;
    const ERROR_REVERSE_CIRCULAR_DEPENDENCY = 4;
    const ERROR_ITEM_TO_INSTALL_SHOULD_BE_REMOVED = 5;
    const ERROR_DEPENDENCY_MISSING_ITEM = 6;
    const ERROR_DEPENDENCY_CANNOT_BE_INSTALLED = 11;
    const ERROR_INSTALLED_ITEM_IN_CONFLICT = 7;
    const ERROR_ITEM_TO_INSTALL_IN_CONFLICT = 8;
    const ERROR_CHOICE_MISSING_ITEM = 9;
    const ERROR_CHOICE_AMBIGUOUS = 10;
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
