<?php

class regions implements \Jelix\Forms\Datasource\DatasourceInterface
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

  public function getLabel($key, $form)
  {
    if(isset($this->data[$key]))
      return $this->data[$key];
    else
      return null;
  }

    public function hasGroupedData()
    {
        return false;
    }

    public function setGroupBy($group)
    {
        // nothing
    }
}
