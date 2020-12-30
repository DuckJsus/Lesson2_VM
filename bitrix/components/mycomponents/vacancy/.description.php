<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_VACANCIES_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_VACANCIES_DESCRIPTION"),
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "extra",
		"NAME" => GetMessage("T_IBLOCK_EXTRA"),
		"CHILD" => array(
			"ID" => "vacancies",
			"NAME" => GetMessage("T_IBLOCK_DESC_VACANCIES"),
			"SORT" => 10,
			"CHILD" => array(
				"ID" => "vacancies",
			),
		),
	),
);

?>