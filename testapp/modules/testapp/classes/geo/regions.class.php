<?php

class regions implements jIFormsDatasource
{
  protected $formId = 0;

  protected $data = array(
    'finistere'=> 'Finistère',
    'touraine'=> 'Touraine',
    'polynesia'=> 'Polynésie',
    );

  function __construct($id)
  {
    $this->formId = $id;
  }

  public function getData($form)
  {
    return ($this->data);
  }

  public function getLabel($key)
  {
    if(isset($this->data[$key]))
      return $this->data[$key];
    else
      return null;
  }

}
