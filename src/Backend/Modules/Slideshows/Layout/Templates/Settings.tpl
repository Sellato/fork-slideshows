{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
  <h2>{$lblSlideshows|ucfirst}: {$lblSettings}</h2>
</div>

{form:settings}
<div class="box" style="margin-top: 20px;">
  <div class="heading">
    <h3>{$lblSlides|ucfirst}</h3>
  </div>
  <div class="options">
    <p>
      <label for="slideWidth">{$lblSlideWidth|ucfirst}</label>
      {$txtSlideWidth}px {$txtSlideWidthError}
    </p>
    <p>
      <label for="slideHeight">{$lblSlideHeight|ucfirst}</label>
      {$txtSlideHeight}px {$txtSlideHeightError}
    </p>
  </div>
</div>

<div class="fullwidthOptions">
  <div class="buttonHolderRight">
    <input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
  </div>
</div>
{/form:settings}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}
