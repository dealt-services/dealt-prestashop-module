{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
    {if !($validPhone)}
        <article id="dealt-offer-error" class="alert alert-danger" role="alert" data-alert="danger" style="margin-bottom: 10px">
            {l s='You must specify at least one phone number in your delivery address in order to confirm your services' sprintf=['%zipCode%' => $zipCode, '%country%' => $country] d='Modules.Dealtmodule.Shop'}
            : <i>{l s='Update your delivery address in the previous steps' d='Modules.Dealtmodule.Shop'}</i>
        </article>
    {/if}

    {if $valid}
        {l s='Services are available for <strong>%zipCode%, %country%</strong> and are awaiting order payment for confirmation' sprintf=['%zipCode%' => $zipCode, '%country%' => $country] d='Modules.Dealtmodule.Shop'}
    {else}
        <article id="dealt-offer-error" class="alert alert-danger" role="alert" data-alert="danger" style="margin-bottom: 10px">
            {l s='Unfortunately, Certain attached services are unvailable for <strong>%zipCode%, %country%</strong>' sprintf=['%zipCode%' => $zipCode, '%country%' => $country] d='Modules.Dealtmodule.Shop'}
            <br />
            <i>{l s='Try changing your delivery address or remove the service(s) from your cart' d='Modules.Dealtmodule.Shop'}</i>
        </article>
    {/if}
    <div>
        {foreach $offers as $data}
            {assign var=offerImage value=$data.offer.image}
            <div
                style="margin-top: 10px; border: 1px solid {if $data.available}#24b9d7{else}#a94442{/if}; border-radius: 5px; padding: 10px; margin-bottom: 15px;">
                <div>
                    <div style="display: flex; align-items: stretch;">
                        <div
                            style="flex: 1; display: block; border-radius: 5px; background-size: cover; background-image: url('{if $offerImage}{$offerImage}{else}{$urls.no_picture_image.bySize.medium_default.url}{/if}')">
                        </div>
                        <div
                            style="padding: 0 0 0 15px; display: flex; justify-content: center; flex-direction: column; flex: 2">
                            <div style="padding: 10px 0;">
                                <h5 style="margin: 0; color:  {if $data.available}inherit{else}#a94442{/if}">
                                    {if $data.available}
                                        {$data.offer.title} ✅
                                    {else}
                                        {$data.offer.title} ⛔️
                                        <span style="display: block;font-size: 10px; opacity: 0.7; margin-top: 8px">
                                            {l s='Unvailable for <strong>%zipCode%, %country%</strong>' sprintf=['%zipCode%' => $zipCode, '%country%' => $country] d='Modules.Dealtmodule.Shop'}
                                        </span>
                                    {/if}
                                </h5>
                                <h6 style="margin-top: 8px;">
                                    <span style="font-size: 11px">
                                        {l s='For  %quantity% x %title% %attributes%' sprintf=['%title%' => $data.binding.cartProduct.name, '%attributes%' => $data.binding.cartProduct.attributes, '%quantity%' => $data.binding.cartProduct.quantity] d='Modules.Dealtmodule.Shop'}
                                    </span>
                                </h6>
                                <p style="font-size: 12px; margin: 0;  line-height: 1.2;">
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
            </div>
        {/foreach}
    </div>
{/block}