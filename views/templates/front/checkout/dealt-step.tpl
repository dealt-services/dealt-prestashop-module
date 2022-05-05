{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
    {if $valid}
        {l s='Services are available for <strong>%zipCode%, %country%</strong> and are awaiting order payment for confirmation' sprintf=['%zipCode%' => $zipCode, '%country%' => $country] d='Modules.DealtModule.Front'}
    {else}
        <article id="dealt-offer-error" class="alert alert-danger" role="alert" data-alert="danger">
            {l s='Unfortunately, Certain attached services are unvailable for <strong>%zipCode%, %country%</strong>' sprintf=['%zipCode%' => $zipCode, '%country%' => $country] d='Modules.DealtModule.Front'}
            <br />
            <i>{l s='Try changing your delivery address or remove the service(s) from your cart' d='Modules.DealtModule.Front'}</i>
        </article>
    {/if}
    <div style="margin-top: 20px">
        {foreach $offers as $data}
            {assign var=offerImage value=$data.offer.image}
            <div style="border: 1px solid {if $data.available}#24b9d7{else}#a94442{/if}; border-radius: 5px; padding: 10px;">
                <div>
                    <div style="display: flex; align-items: stretch;">
                        <div
                            style="flex: 1; display: block; border-radius: 5px; background-size: cover; background-image: url('{if $offerImage}{$offerImage}{else}{$urls.no_picture_image.bySize.medium_default.url}{/if}')">
                        </div>
                        <div
                            style="padding: 0 0 0 15px; display: flex; justify-content: center; flex-direction: column; flex: 2">
                            <div style="padding: 10px 0;">
                                <h4 style="margin: 0; color:  {if $data.available}#24b9d7{else}#a94442{/if}">
                                    {if $data.available}
                                        ✅ {$data.offer.title}
                                    {else}
                                        ⛔️ {$data.offer.title}
                                        <span style="display: block;font-size: 10px; opacity: 0.7; margin: 8px 0;">
                                            {l s='Unvailable for <strong>%zipCode%, %country%</strong>' sprintf=['%zipCode%' => $zipCode, '%country%' => $country] d='Modules.DealtModule.Front'}
                                        </span>
                                    {/if}
                                </h4>
                                <h6>
                                    <span style="font-size: 11px">
                                        {l s='For  %quantity% x %title% (%attributes%)' sprintf=['%title%' => $data.binding.cartProduct.name, '%attributes%' => $data.binding.cartProduct.attributes, '%quantity%' => $data.binding.cartProduct.quantity] d='Modules.DealtModule.Front'}
                                    </span>
                                </h6>
                                <p style="font-size: 12px; margin: 0">
                                    {($data.offer.description[$language['id']])|strip_tags}</span>
                            </div>
                            <div
                                style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end; margin-top: 20px">
                                <h4 style="margin: 0">
                                    <span id="dealt-offer-price">{$data.offer.price}</span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>

{/block}