<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<?$ElementID = $APPLICATION->IncludeComponent(
	"mycomponents:vacancy.detail",
	".default",
	Array(
		"DISPLAY_NAME" => $arParams["DISPLAY_NAME"],
		"DISPLAY_DETAIL_TEXT" => $arParams["DISPLAY_DETAIL_TEXT"],
		"DISPLAY_PREVIEW_TEXT" => $arParams["DISPLAY_PREVIEW_TEXT"],
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PROPERTY_CODE" => $arParams["DETAIL_PROPERTY_CODE"],
		"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"],
		"RESUME_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["resume"],
		"IBLOCK_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
		"ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		),
	$component
);?>
<div>
	<p><a href="<?=$arResult["FOLDER"] . strtr($arResult["URL_TEMPLATES"]["resume"], array('#ELEMENT_ID#' => $arResult["VARIABLES"]["ELEMENT_ID"]))?>"><?=GetMessage("T_REPLY_VACANCY")?></a></p>
</div>


<p><a href="<?=$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list"]?>"><?=GetMessage("T_NEWS_DETAIL_BACK")?></a></p>
