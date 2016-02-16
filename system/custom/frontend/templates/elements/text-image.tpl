<div class="element text-image-element">
    {if $image.url != ''}
        <img src="{$image.url}" width="{$image.width}" height="{$image.height}" alt="{$altText|escape}">
    {/if}
    {$text}
</div>
