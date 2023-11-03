{*
* MIT License
* Copyright (c) 2022 itivos Teams
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:

* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.

* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*}

<div class="panel">
	<h3>
		<i class="icon-list-ul"></i> 
		{l s='Imagen list' mod='itivosslider'}
		<span class="panel-heading-action">
			<a id="desc-product-new" 
			   class="list-toolbar-btn" 
			   href="{$link->getAdminLink('AdminModules')}&configure=itivosslider&addSlider=1">
				<span title="" 
					  data-toggle="tooltip" 
					  class="label-tooltip" 
					  data-original-title="{l s='Add new' mod='itivosslider'}" 
					  data-html="true">
					<i class="process-icon-new "></i>
				</span>
			</a>
		</span>
	</h3>
	<div id="labelsContent">
		<div id="images_slider">
			{foreach from=$images_list item=slider}
				<div id="order_{$slider.id}" class="panel">
					<div class="row">
						<div class="col-lg-1">
							<span><i class="icon-arrows "></i></span>
						</div>
						<div class="col-md-4">
							<img class="img_list_slider" src="{$link->getBaseLink()}modules/itivosslider/views/img/{$slider.image_desktop}">
						</div>
						<div class="col-md-3">
							{$slider.name}
						</div>
						<div class="col-md-4">
							<div class="btn-group-action pull-right">
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminModules')}&configure=itivosslider&editSlider={$slider.id}">
									<i class="icon-edit"></i>
									{l s='Edit' d='Admin.Actions'}
								</a>
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminModules')}&configure=itivosslider&delIdSlider={$slider.id}">
									<i class="icon-trash"></i>
									{l s='Delete' mod='itivosslider'}
								</a>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
</div>
