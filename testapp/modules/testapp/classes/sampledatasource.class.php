<?php


class sampledatasource extends jFormsDynamicStaticDatasource
{
    public $data = array(
        [ 'label' => 'value 1', 'value' => '1', 'cat'=>'1'],
        [ 'label' => 'value 2', 'value' => '2', 'cat'=>'1'],
        [ 'label' => 'value 0', 'value' => '0', 'cat'=>'2'],
        [ 'label' => 'value 3', 'value' => '3', 'cat'=>'2'],
        [ 'label' => 'value 4', 'value' => '4', 'cat'=>'2'],
        [ 'label' => 'value 5', 'value' => '5', 'cat'=>'3'],
    );

    protected $filterKeysMapping = array('listcat' => 'cat');
}