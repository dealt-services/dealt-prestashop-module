{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
    {if empty($errors)}
        {l s='Services confirmation pending order payment' d='Modules.DealtModule.Front'}
    {else}
        <article id="dealt-offer-error" class="alert alert-danger" role="alert" data-alert="danger">
            {l s='Certain attached services are unvailable :' d='Modules.DealtModule.Front'}

            <div style="margin-top: 20px">

                {foreach $errors as $error}
                    {assign var=offerImage value=$error.offer->getImage()}
                    <div
                        style=" padding: 10px; border: 1px solid rgba(255,255,255,0.5); margin-bottom: 15px; background-color: rgba(255,255,255,0.2)">
                        <div>
                            <div class="row" style="display: flex;">
                                <div class="col-md-2">
                                    {if $offerImage}
                                        <img src="{$offerImage}" loading="lazy" class="product-image" style="max-width: 100%;">
                                    {else}
                                        <img src="{$urls.no_picture_image.bySize.medium_default.url}" loading="lazy"
                                            class="product-image" />
                                    {/if}
                                </div>
                                <div class="col-md-10">
                                    <h6 class="h6">{$error.offer->getOfferTitle()}</h6>
                                    {* <p class="product-price">{$offerPrice}</p> *}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>

                <i>{l s='Try changing your delivery address or remove the service(s) from your cart' d='Modules.DealtModule.Front'}</i>
        </article>
    {/if}
{/block}