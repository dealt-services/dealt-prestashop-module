{assign var=offerImage value=$offer.image}

<div class="order-line row" style="background: rgba(36,185,215,0.04); border-radius: 8px; padding-top: 16px;">
    <div class="col-sm-2 col-xs-3">
        <span class="image">
            <img src="{if $offerImage}{$offerImage}{else}{$urls.no_picture_image.bySize.medium_default.url}{/if}"
                loading="lazy" style="border-radius: 5px;" />

        </span>
    </div>
    <div class="col-sm-4 col-xs-9 details">
        <span><strong>{$offer.title}</strong></span>
        <p style="font-size: 12px; margin-top: 8px; line-height: 1.2;">
            {$offer.description[$language['id']]|strip_tags}
        </p>
        {if !empty($offer.missions)}
            <div style="font-size: 12px; margin-top: 8px; ">
                <strong>{l s='Service reference(s) :' d='Modules.Dealtmodule.Shop'}</strong>
                <ul style="font-size: 10px;">
                    {foreach $offer.missions as $mission}
                        <li style="margin-bottom: 2px;"> •
                            <mark
                                style="padding: 2px 3px; font-size: 10px; border-radius: 3px; background-color:rgb(36,185,215); color: white; margin-right: 5px;">
                                <strong>{l s=$mission->getDealtMissionStatus() d='Modules.Dealtmodule.Shop'}</strong>
                            </mark>
                            <span
                                style="line-height: 1; display: inline-block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 70px;">{$mission->getDealtMissionId()}</span>
                        </li>
                    {/foreach}
                </ul>
            </div>
        {/if}
    </div>
    <div class="col-sm-6 col-xs-12 qty">
        <div class="row">
            <div class="col-xs-4 text-sm-center text-xs-left">{$offer.unitPriceFormatted}</div>
            <div class="col-xs-4 text-sm-center">{$binding.cartProduct["cart_quantity"]}</div>
            <div class="col-xs-4 text-sm-center text-xs-right bold">{$offer.price}</div>
        </div>
    </div>
</div>