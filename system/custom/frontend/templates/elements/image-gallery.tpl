<div class="element image-gallery-element">
    <div class="image-gallery-container">
        {foreach from=$images item=item}
            <div class="image-gallery-item">
                {if $item.image.url != ''}
                    <a href="{$item.image.additionalSizes.popup.url}"><img src="{$item.image.url}" width="{$item.image.width}" height="{$item.image.height}" alt="{$item.alt|escape}"></a>
                {/if}
                <p>{$item.title}</p>
            </div>
        {$text}
        {/foreach}
    </div>
</div>