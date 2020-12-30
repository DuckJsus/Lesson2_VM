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

$this->addExternalCss("/bitrix/css/main/.default/vacancies/template_style.css");
$this->addExternalJS("/bitrix/js/.default/vacancies/jquery-1.8.2.min.js");
$this->addExternalJS("/bitrix/js/.default/vacancies/functions.js");
?>
<div class="sb_nav">
	<?foreach($arResult["GROUPS"] as $arGroup):?>
		<b><?if($arGroup["NAME"]):?></b>
			<ul>
			<li class="close">
			<span class="sb_showchild"></span>
			<a><span><?echo $arGroup["NAME"]?><br /></span></a>
				<ul><?foreach($arResult["ITEMS"] as $arItem):?>
					<?if($arGroup["ID"] == $arItem["IBLOCK_SECTION_ID"]):?>
						
						<li>
							<!--Эрмитаж-->
							<?
							$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
							$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage(
								'CT_BNL_ELEMENT_DELETE_CONFIRM')));
							?>
							<a>
							<div id="<?=$this->GetEditAreaId($arItem['ID']);?>">
							<?if($arItem["NAME"]):?>
								<p><b><object><a href="<?=$arItem["DETAIL_PAGE_URL"]?>" id="name_link"><?echo $arItem["NAME"]?></a></object></b></p><br />
							<?endif;?>
							<?foreach($arItem["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
								<p>
								<b><?=$arProperty["NAME"]?>:&nbsp;</b>
								<?if(is_array($arProperty["DISPLAY_VALUE"])):?>
									<?=implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);?>
								<?else:?>
									<?=$arProperty["DISPLAY_VALUE"];?>
								<?endif?>
								</p><br />
							<?endforeach;?>
							<?if($arParams["DISPLAY_PREVIEW_TEXT"] == "Y" && $arItem["PREVIEW_TEXT"]):?>
								<p><b><?=GetMessage("DESCRIPTION_PREVIEW_TEXT");?></b><?echo $arItem["PREVIEW_TEXT"];?></p><br />
							<?endif;?>
							</div></a>
							</li>	
						
					<?endif;?>
				<?endforeach;?></ul>
			</li>
			</ul>	
		<?endif;?>
	<?endforeach;?>
</div>