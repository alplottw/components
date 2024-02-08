<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

class howToCalculatorComponent extends CBitrixComponent
{
    function __construct($component = null)
    {
        parent::__construct($component);
    }

    function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}