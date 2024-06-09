<?php

class mydatasource implements \Jelix\Forms\Datasource\DatasourceInterface {

    protected $datas = array(
        'aaa'=>'label for aaa',
        'bbb'=>'label for bbb',
        'ccc'=>'label for ccc',
        'ddd'=>'label for ddd',
    );

    public function __construct($id) {
    }

    public function getData($form) {
        return $this->datas;
    }

    /**
     * Return the label corresponding to the given key
     * @param string $key the key 
     * @return string the label
     */
    public function getLabel($key, $form) {
        return $this->datas[$key];
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

?>