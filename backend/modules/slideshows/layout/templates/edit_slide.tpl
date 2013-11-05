{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

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
        <a href="{$var|geturl:'delete_slide'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
            <span>{$lblDelete|ucfirst}</span>
        </a>
        <div class="buttonHolderRight">
            <input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblPublish|ucfirst}" />
        </div>
    </div>

    <div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
        <p>
            {$msgConfirmDelete|sprintf:{$item.title}}
        </p>
    </div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}