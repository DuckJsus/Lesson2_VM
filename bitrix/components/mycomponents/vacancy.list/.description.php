<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("T_IBLOCK_DESC_LIST"),
	"DESCRIPTION" => GetMessage("T_IBLOCK_DESC_LIST_DESC"),
	"SORT" => 20,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "extra",
		"NAME" => GetMessage("T_IBLOCK_EXTRA"),
		"CHILD" => array(
			"ID" => "vacancies",
			"NAME" => GetMessage("T_IBLOCK_DESC_VACANCY"),
			"SORT" => 10,
			"CHILD" => array(
				"ID" => "vacancies_list",
			),
		),
	),
);

?>