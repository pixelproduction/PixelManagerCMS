{extends file="../layout.tpl"}

{block name=title}{if $head.title}{$head.title} - {/if}{$globalElements.strings.pageTitle}{/block}

{block name="metaTags" append}
    {if $head.metaDescription|trim != ''}
        <meta name="description" content="{$head.metaDescription|trim|escape}">
    {/if}
{/block}

{block name=content append}
    {$content}
{/block}
