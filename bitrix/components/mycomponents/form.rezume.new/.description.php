<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("FORM_RESULT_NEW_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("FORM_RESULT_NEW_COMPONENT_DESCR"),
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "extra",
		"NAME" => GetMessage("T_IBLOCK_EXTRA"),
		"CHILD" => array(
			"ID" => "vacancies",
			"NAME" => GetMessage("T_IBLOCK_DESC_VACANCY"),
			"CHILD" => array(
				"ID" => "form_vacancies",
			),
		)
	),
);
?>