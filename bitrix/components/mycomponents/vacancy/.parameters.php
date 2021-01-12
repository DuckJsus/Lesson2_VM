<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes(array("-"=>" "));

$arIBlocks=array();
$db_iblock = CIBlock::GetList(array("SORT"=>"ASC"), array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];

$arProperty_LNS = array();
$rsProp = CIBlockProperty::GetList(array("sort"=>"asc", "name"=>"asc"), array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"])?$arCurrentValues["IBLOCK_ID"]:$arCurrentValues["ID"])));
while ($arr=$rsProp->Fetch())
{
	$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
	{
		$arProperty_LNS[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

$arComponentParameters = array(
	"GROUPS" => array(
		"LIST_SETTINGS" => array(
			"NAME" => GetMessage("CN_P_LIST_SETTINGS"),
		),
		"DETAIL_SETTINGS" => array(
			"NAME" => GetMessage("CN_P_DETAIL_SETTINGS"),
		),	
	),
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => array(
			"SECTION_ID" => array("NAME" => GetMessage("BN_P_SECTION_ID_DESC")),
			"ELEMENT_ID" => array("NAME" => GetMessage("VACANCY_ELEMENT_ID_DESC")),
		),
		"SEF_MODE" => array(
			"list" => array(
				"NAME" => GetMessage("T_IBLOCK_SEF_PAGE_VACANCIES"),
				"DEFAULT" => "",
				"VARIABLES" => array(),
			),
			"detail" => array(
				"NAME" => GetMessage("T_IBLOCK_SEF_PAGE_VACANCIES_DETAIL"),
				"DEFAULT" => "#ELEMENT_ID#/",
				"VARIABLES" => array(
					"ELEMENT_ID",
					"ELEMENT_CODE",),
			),
			"resume" => array(
				"NAME" => GetMessage("T_IBLOCK_SEF_PAGE_VACANCIES_DETAIL"),
				"DEFAULT" => "#ELEMENT_ID#/resume/",
				"VARIABLES" => array(
					"ELEMENT_ID",
					"ELEMENT_CODE",
					"SECTION_ID",
					"SECTION_CODE",
					"SECTION_CODE_PATH", 
					"RESUME_ID",),
			),
		),
		"AJAX_MODE" => array(),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BN_P_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"DEFAULT" => "vacancies",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BN_P_IBLOCK"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => 5,
			"REFRESH" => "Y",
			"ADDITIONAL_VALUES" => "Y",
		),
		"CHECK_DATES" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_CHECK_DATES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"LIST_PROPERTY_CODE" => array(
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		),
		"DISPLAY_PREVIEW_TEXT" => array(
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("IBLOCK_DISPLAY_PREVIEW_TEXT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"DETAIL_PROPERTY_CODE" => array(
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		),
		"DISPLAY_DETAIL_TEXT" => array(
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("IBLOCK_DISPLAY_DETAIL_TEXT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CACHE_TIME"  =>  array("DEFAULT"=>36000000),
		
	),
);

CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);
?>
