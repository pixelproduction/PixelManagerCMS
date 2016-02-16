<!DOCTYPE html>

    <head>
        {block name="metaTags"}
            <meta charset="utf-8">
        {/block}
        <title>{block name=title}{/block}</title>

        {block name=css_files}
            <link rel="stylesheet" href="{versionize file='css/main.css'}">
        {/block}
    </head>

    <body>
        <header>
            <p>
                {foreach from=$languages item=language}
                    <a href="{pageurl page=$pageId language=$language.id}">{$language.name}</a>{if !$language@last} | {/if}
                {/foreach}
            </p>
            {module id="navigation" page=$pageId language=$languageId onlyactive=true}
        </header>

        {block name=content}{/block}

        <footer>
            {$globalElements.strings.footer}
        </footer>
    </body>

</html>
