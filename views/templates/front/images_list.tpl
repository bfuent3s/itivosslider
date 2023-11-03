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

{if $itivos_homeslider|count}
	<input type="hidden" value="{$itivos_homeslider.speed}" id="itivos_slider_speed">
	{if $itivos_homeslider.mode eq 1}
		<div class="itivos_slider">
			<ul class="itivos_slides">
				{foreach from=$itivos_homeslider.slides item=$slide key=key}
			  		<li>
			  			{if $itivos_homeslider.show_text eq 1}
				  			<div class="fondoElementosSlider {$slide.position}">
								{$slide.description|cleanHtml nofilter}
								<button type="button" class="btn btn-primary">{l s='Show more' mod='itivosslider'}</button>
							</div>
			  			{/if}
						<img class="" 
							 {if $itivos_homeslider.device eq "pc"}
						     	src="{$urls.base_url}modules/itivosslider/views/img/{$slide.image_desktop}" 
							 	{else}
						     	src="{$urls.base_url}modules/itivosslider/views/img/{$slide.image_mobile}" 
							 {/if}
						     alt="{$slide.legend|escape:'html':'UTF-8'}" title="{$slide.name|escape:'html':'UTF-8'}"/>
			  		</li>
				{/foreach}
			</ul>
		</div>
		{else}
		<div id="itivos_carousel" class="itivos_no_focus_slick">
			<div class="itivos_carousel_slick">
				{foreach from=$itivos_homeslider.slides item=$slide key=key}
					<img class="" 
						 {if $itivos_homeslider.device eq "pc"}
					     	src="{$urls.base_url}modules/itivosslider/views/img/{$slide.image_desktop}" 
						 	{else}
					     	src="{$urls.base_url}modules/itivosslider/views/img/{$slide.image_mobile}" 
						 {/if}
					     alt="{$slide.legend|escape:'html':'UTF-8'}">
				{/foreach}
			</div>
		</div>
	{/if}
{/if}
