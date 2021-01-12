<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if($arParams["IBLOCK_TYPE"] == '')
	$arParams["IBLOCK_TYPE"] = "vacancies";

$arParams["ELEMENT_ID"] = intval($arParams["~ELEMENT_ID"]);
$arParams["CHECK_DATES"] = $arParams["CHECK_DATES"]!="N";

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PROPERTY_CODE"][$k]);

$arParams["IBLOCK_URL"]=trim($arParams["IBLOCK_URL"]);
$arParams["SHOW_WORKFLOW"] = $_REQUEST["show_workflow"]=="Y";

if($arParams["SHOW_WORKFLOW"] || $this->startResultCache(false, array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()),$bUSER_HAVE_ACCESS)))
{

	if(!Loader::includeModule("iblock"))
	{
		$this->abortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	$arFilter = array(
		"IBLOCK_LID" => SITE_ID,
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
		"SHOW_HISTORY" => $arParams["SHOW_WORKFLOW"]? "Y": "N",
	);
	if($arParams["CHECK_DATES"])
		$arFilter["ACTIVE_DATE"] = "Y";
	if(intval($arParams["IBLOCK_ID"]) > 0)
		$arFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
	else
		$arFilter["=IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE"];

	//Handle case when ELEMENT_CODE used
	if($arParams["ELEMENT_ID"] <= 0)
		$arParams["ELEMENT_ID"] = CIBlockFindTools::GetElementID(
			$arParams["ELEMENT_ID"],
			$arParams["~ELEMENT_CODE"],
			$arFilter
		);

	$WF_SHOW_HISTORY = "N";
	if ($arParams["SHOW_WORKFLOW"] && Loader::includeModule("workflow"))
	{
		$WF_ELEMENT_ID = CIBlockElement::WF_GetLast($arParams["ELEMENT_ID"]);

		$WF_STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ELEMENT_ID, $WF_STATUS_TITLE);
		$WF_STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($WF_STATUS_ID);

		if ($WF_STATUS_ID == 1 || $WF_STATUS_PERMISSION < 1)
			$WF_ELEMENT_ID = $arParams["ELEMENT_ID"];
		else
			$WF_SHOW_HISTORY = "Y";

		$arParams["ELEMENT_ID"] = $WF_ELEMENT_ID;
	}

	$arSelect = array(
		"ID",
		"NAME",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"DETAIL_TEXT",
		"DETAIL_TEXT_TYPE",
		"LIST_PAGE_URL",
		"DETAIL_PAGE_URL",
	);
	$bGetProperty = count($arParams["PROPERTY_CODE"]) > 0;
	if($bGetProperty)
		$arSelect[]="PROPERTY_*";
	if ($arParams['SET_CANONICAL_URL'] === 'Y')
		$arSelect[] = 'CANONICAL_PAGE_URL';

	$arFilter["ID"] = $arParams["ELEMENT_ID"];
	$arFilter["SHOW_HISTORY"] = $WF_SHOW_HISTORY;

	$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
	$rsElement->SetUrlTemplates($arParams["DETAIL_URL"], "", $arParams["IBLOCK_URL"]);
	if($obElement = $rsElement->GetNextElement())
	{
		$arResult = $obElement->GetFields();

		$arResult["NAV_RESULT"] = new CDBResult;
		if(($arResult["DETAIL_TEXT_TYPE"]=="html") && (mb_strstr($arResult["DETAIL_TEXT"], "<BREAK />") !== false))
			$arPages=explode("<BREAK />", $arResult["DETAIL_TEXT"]);
		elseif(($arResult["DETAIL_TEXT_TYPE"]!="html") && (mb_strstr($arResult["DETAIL_TEXT"], "&lt;BREAK /&gt;") !== false))
			$arPages=explode("&lt;BREAK /&gt;", $arResult["DETAIL_TEXT"]);
		else
			$arPages=array();
		$arResult["NAV_RESULT"]->InitFromArray($arPages);
		$arResult["NAV_RESULT"]->NavStart($arNavParams);

		$ipropValues = new Iblock\InheritedProperty\ElementValues($arResult["IBLOCK_ID"], $arResult["ID"]);
		$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();

		Iblock\Component\Tools::getFieldImageData(
			$arResult,
			array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
			Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
			'IPROPERTY_VALUES'
		);

		if($bGetProperty)
			$arResult["PROPERTIES"] = $obElement->GetProperties();
		$arResult["DISPLAY_PROPERTIES"]=array();
		foreach($arParams["PROPERTY_CODE"] as $pid)
		{
			$prop = &$arResult["PROPERTIES"][$pid];
			if(
				(is_array($prop["VALUE"]) && count($prop["VALUE"])>0)
				|| (!is_array($prop["VALUE"]) && $prop["VALUE"] <> '')
			)
			{
				$arResult["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arResult, $prop, "news_out");
			}
		}

		$arResult["IBLOCK"] = GetIBlock($arResult["IBLOCK_ID"], $arResult["IBLOCK_TYPE"]);

		$arResult["SECTION"] = array("PATH" => array());
		$arResult["SECTION_URL"] = "";
		if($arParams["ADD_SECTIONS_CHAIN"] && $arResult["IBLOCK_SECTION_ID"] > 0)
		{
			$rsPath = CIBlockSection::GetNavChain(
				$arResult["IBLOCK_ID"],
				$arResult["IBLOCK_SECTION_ID"],
				array(
					"ID", "CODE", "XML_ID", "EXTERNAL_ID", "IBLOCK_ID",
					"IBLOCK_SECTION_ID", "SORT", "NAME", "ACTIVE",
					"DEPTH_LEVEL", "SECTION_PAGE_URL"
				)
			);
			$rsPath->SetUrlTemplates("", $arParams["SECTION_URL"]);
			while($arPath = $rsPath->GetNext())
			{
				$ipropValues = new Iblock\InheritedProperty\SectionValues($arParams["IBLOCK_ID"], $arPath["ID"]);
				$arPath["IPROPERTY_VALUES"] = $ipropValues->getValues();
				$arResult["SECTION"]["PATH"][] = $arPath;
				$arResult["SECTION_URL"] = $arPath["~SECTION_PAGE_URL"];
			}
		}

		$resultCacheKeys = array(
			"ID",
			"IBLOCK_ID",
			"NAV_CACHED_DATA",
			"NAME",
			"IBLOCK_SECTION_ID",
			"IBLOCK",
			"LIST_PAGE_URL", "~LIST_PAGE_URL",
			"SECTION_URL",
			"CANONICAL_PAGE_URL",
			"SECTION",
			"IPROPERTY_VALUES",
			"ELEMENTS",
			"PROPERTIES",
		);

		$this->setResultCacheKeys($resultCacheKeys);

		$this->includeComponentTemplate();
	}
	else
	{
		$this->abortResultCache();
		Iblock\Component\Tools::process404(
			trim($arParams["MESSAGE_404"]) ?: GetMessage("T_NEWS_DETAIL_NF")
			,true
			,$arParams["SET_STATUS_404"] === "Y"
			,$arParams["SHOW_404"] === "Y"
			,$arParams["FILE_404"]
		);
	}
}

if(isset($arResult["ID"]))
{
	$arTitleOptions = null;
	if(Loader::includeModule("iblock"))
	{
		CIBlockElement::CounterInc($arResult["ID"]);

		if($USER->IsAuthorized())
		{
			if(
				$APPLICATION->GetShowIncludeAreas()
				|| isset($arResult[$arParams["BROWSER_TITLE"]])
			)
			{
				$arReturnUrl = array(
					"add_element" => CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "DETAIL_PAGE_URL"),
					"delete_element" => (
						empty($arResult["SECTION_URL"])?
						$arResult["LIST_PAGE_URL"]:
						$arResult["SECTION_URL"]
					),
				);

				$arButtons = CIBlock::GetPanelButtons(
					$arResult["IBLOCK_ID"],
					$arResult["ID"],
					$arResult["IBLOCK_SECTION_ID"],
					Array(
						"RETURN_URL" => $arReturnUrl,
						"SECTION_BUTTONS" => false,
					)
				);

				if($APPLICATION->GetShowIncludeAreas())
					$this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));

				if($arParams["SET_TITLE"] || isset($arResult[$arParams["BROWSER_TITLE"]]))
				{
					$arTitleOptions = array(
						'ADMIN_EDIT_LINK' => $arButtons["submenu"]["edit_element"]["ACTION"],
						'PUBLIC_EDIT_LINK' => $arButtons["edit"]["edit_element"]["ACTION"],
						'COMPONENT_NAME' => $this->getName(),
					);
				}
			}
		}
	}

	$this->setTemplateCachedData($arResult["NAV_CACHED_DATA"]);

	return $arResult["ID"];
}
else
{
	return 0;
}