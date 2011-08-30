		<form action="" method="post" id="modifform">
{if $error_detected|@count != 0}
		<div id="errorbox">
			<h1>{_T string="- ERROR -"}</h1>
			<ul>
	{foreach from=$error_detected item=error}
				<li>{$error}</li>
	{/foreach}
			</ul>
		</div>
{/if}
		<div class="bigtable">
			<fieldset class="cssform">
				<p>
					<label for="model" class="bline">{_T string="Model"}</label>
					<input type="text" name="model" id="model" value="{$model->model}" maxlength="20" required/>
				</p>
				<p>
{if $brands|@count gt 0}
					<label for="brand" class="bline">{_T string="Brand"}</label>
					<select name="brand" id="brand" required>
						<option value="-1">{_T string="Select one brand"}</option>
	{foreach from=$brands item=brand}
						<option value="{$brand->id_brand}"{if $brand->id_brand eq $model->brand} selected="selected"{/if}>{$brand->brand}</option>
	{/foreach}
					</select>
{else}
					<p>{php}echo preg_replace('/%(.*)%/', '<a href="object.php?set=brands&#038;addnew=1">\\1</a>', _T("No brand is registered yet. You have to %create at least one brand% to register models."));{/php}</p>
{/if}
				</p>
			</fieldset>
		</div>
		<div class="button-container">
			<input type="submit" id="btnsave" name="valid" value="{_T string="Save"}"/>
			<input type="reset" id="btncancel" name="cancel" value="{_T string="Cancel"}"/>
			<input type="hidden" name="{$mode}" value="1"/>
			<input type="hidden" name="model_id" value="{$model->pk}"/>
		</div>
		<p>{_T string="NB : The mandatory fields are in"} <span class="required">{_T string="red"}</span></p>
		</form>