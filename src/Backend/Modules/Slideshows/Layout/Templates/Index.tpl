{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
  <h2>{$lblSlideshows|ucfirst}</h2>

  <div class="buttonHolderRight">
    <a class="button icon iconAdd" href="{$var|geturl:'Add'}"><span>{$lblAdd|ucfirst}</span></a>
  </div>
</div>
<div class="dataGridHolder">
  {option:dataGrid}{$dataGrid}{/option:dataGrid}
</div>

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}
