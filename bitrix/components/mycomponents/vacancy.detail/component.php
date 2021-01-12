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

/** @global CIntranetToolbar $INTRANET_TOOLBAR */
global $INTRANET_TOOLBAR;

use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if($arParams["IBLOCK_TYPE"] == '')
	$arParams["IBLOCK_TYPE"] = "vacancies";
$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);
$arParams["CHECK_DATES"] = $arParams["CHECK_DATES"]!="N";

if(empty($arParams["PROPERTY_CODE"]) || !is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["PROPERTY_CODE"][$key]);

$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);

//Начало кэширования
if($this->startResultCache(false, array()))
{
	if(!Loader::includeModule("iblock"))
	{
		$this->abortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	if(is_numeric($arParams["IBLOCK_ID"]))
	{
		$rsIBlock = CIBlock::GetList(array(), array(
			"ACTIVE" => "Y",
			"ID" => $arParams["IBLOCK_ID"],
		));
	}
	else
	{
		$rsIBlock = CIBlock::GetList(array(), array(
			"ACTIVE" => "Y",
			"CODE" => $arParams["IBLOCK_ID"],
			"SITE_ID" => SITE_ID,
		));
	}

	$arResult = $rsIBlock->GetNext();
	if (!$arResult)
	{
		$this->abortResultCache();
		return;
	}

	//SELECT
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"NAME",
		"RESUME_PAGE_URL",
		"DETAIL_PAGE_URL",
		"LIST_PAGE_URL",
		"DETAIL_TEXT",
		"DETAIL_TEXT_TYPE",
	);
	$bGetProperty = !empty($arParams["PROPERTY_CODE"]);
	
		$elementFilter = array(
			"ID" => $arParams["ELEMENT_ID"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);
		
		$iterator = CIBlockElement::GetList(array(), $elementFilter, false, false, $arSelect);
		$iterator->SetUrlTemplates($arParams["DETAIL_URL"], "", $arParams["IBLOCK_URL"]);
		while ($arItem = $iterator->GetNext())
		{
			//подключение кнопок Эрмитажа (нет актуального кэша)
			$arButtons = CIBlock::GetPanelButtons(
				$arItem["IBLOCK_ID"],
				$arItem["ID"],
				0,
				array("SECTION_BUTTONS" => false, "SESSID" => false)
			);
			$arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
			$arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];
			
			if ($bGetProperty)
			{
				$arItem["PROPERTIES"] = array();
			}
			$arItem["DISPLAY_PROPERTIES"] = array();

			$id = (int)$arItem["ID"];
			$arResult["ITEMS"][$id] = $arItem;
		}
		unset($obElement);
		unset($iterator);

		if ($bGetProperty)
		{
			unset($elementFilter['IBLOCK_LID']);
			CIBlockElement::GetPropertyValuesArray(
				$arResult["ITEMS"],
				$arResult["ID"],
				$elementFilter
			);
		}
	$arResult['ITEMS'] = array_values($arResult['ITEMS']);

	foreach ($arResult["ITEMS"] as &$arItem)
	{
		if ($bGetProperty)
		{
			foreach ($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if (
					(is_array($prop["VALUE"]) && count($prop["VALUE"]) > 0)
					|| (!is_array($prop["VALUE"]) && $prop["VALUE"] <> '')
				)
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "vacancy_out");
				}
			}
		}
	}
	unset($arItem);

	//Формирование массива с ключами кэша
	$this->setResultCacheKeys(array(
        "ID",
        "IBLOCK_ID",
		"NAME",
		"ELEMENTS",
        "IPROPERTY_VALUES",
        "PROPERTIES",
	));

	//component template connection
	$this->includeComponentTemplate();
}

//подключение кнопок Эрмитажа (если кэш уже собран)
if(isset($arResult["ID"]))
{
	$arTitleOptions = null;
	if($USER->IsAuthorized())
	{
		if(
			$APPLICATION->GetShowIncludeAreas()
			|| (is_object($GLOBALS["INTRANET_TOOLBAR"]) && $arParams["INTRANET_TOOLBAR"]!=="N")
			|| $arParams["SET_TITLE"]
		)
		{
			if(Loader::includeModule("iblock"))
			{
				$arButtons = CIBlock::GetPanelButtons(
					$arResult["ID"],
					0,
					$arParams["PARENT_SECTION"],
					array("SECTION_BUTTONS"=>false)
				);

				if($APPLICATION->GetShowIncludeAreas())
					$this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));

				if(
					is_array($arButtons["intranet"])
					&& is_object($INTRANET_TOOLBAR)
					&& $arParams["INTRANET_TOOLBAR"]!=="N"
				)
				{
					$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
					foreach($arButtons["intranet"] as $arButton)
						$INTRANET_TOOLBAR->AddButton($arButton);
				}

				if($arParams["SET_TITLE"])
				{
					$arTitleOptions = array(
						'ADMIN_EDIT_LINK' => $arButtons["submenu"]["edit_iblock"]["ACTION"],
						'PUBLIC_EDIT_LINK' => "",
						'COMPONENT_NAME' => $this->getName(),
					);
				}
			}
		}
	}
}