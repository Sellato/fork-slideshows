{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblSlideshows|ucfirst}: {$lblAdd}</h2>
</div>

{form:add}
	{$txtTitle} {$txtTitleError}
    <div class="box" style="margin-top: 20px;">
        <div class="heading">
            <h3>{$lblContent|ucfirst}</h3>
        </div>
        <div class="options">
           <p>
               <label for="image">{$lblImage|ucfirst}</label>
               {$fileImage} {$fileImageError}
           </p>
           <p>
               <label for="link">{$lblLink|ucfirst}</label>
               {$txtLink} {$txtLinkError}
           </p>
        </div>
    </div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblPublish|ucfirst}" />
		</div>
	</div>
{/form:add}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}