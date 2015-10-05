{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
  <h2>{$lblSlideshows|ucfirst}: {$lblEdit} {$item.title}</h2>
</div>

{form:edit}
{$txtTitle} {$txtTitleError}
<div class="box" style="margin-top: 20px;">
  <div class="heading">
    <h3>{$lblContent|ucfirst}</h3>
  </div>
  <div class="options">
    <p>
      <label for="image">{$lblImage|ucfirst}</label>
      <span class="helpTxt">{$helpImageDimensions}</span>
      {$fileImage} {$fileImageError}
      <img src="{$item.image_preview}" />
    </p>
    <p>
      <label for="link">{$lblLink|ucfirst}</label>
      {$txtLink} {$txtLinkError}
    </p>
  </div>
</div>

<div class="fullwidthOptions">
  <a href="{$var|geturl:'DeleteSlide'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
    <span>{$lblDelete|ucfirst}</span>
  </a>
  <div class="buttonHolderRight">
    <input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
  </div>
</div>

<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
  <p>
    {$msgConfirmDelete|sprintf:{$item.title}}
  </p>
</div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}
