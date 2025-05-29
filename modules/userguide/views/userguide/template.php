<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>

        <title><?php echo $title ?> | Kohana <?php echo 'User Guide'; ?></title>

        <?php
        foreach ($styles as $style => $media)
            echo HTML::style($style, ['media' => $media], null, true), "\n"
            ?>

        <?php
        foreach ($scripts as $script)
            echo HTML::script($script, null, null, true), "\n"
            ?>
    </head>
    <body>

        <div id="kodoc-header">
            <div class="container">
                <a href="https://kohana.top/" id="kodoc-logo">
                    <img src="<?php echo Route::url('docs/media', ['file' => 'img/kohana.png']) ?>" alt="Kohana Framework Logo"/>
                </a>
                <div id="kodoc-menu">
                    <ul>
                        <li class="guide first">
                            <a href="<?php echo Route::url('docs/guide') ?>">User Guide</a>
                        </li>
<?php if (Kohana::$config->load('userguide.api_browser')): ?>
                            <li class="api">
                                <a href="<?php echo Route::url('docs/api') ?>">API Browser</a>
                            </li>
<?php endif ?>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kodoc-content">
            <div class="wrapper">
                <div class="container">
                            <?php if (count($breadcrumb) > 1): ?>
                        <div class="span-22 prefix-1 suffix-1">
                            <ul id="kodoc-breadcrumb">
                                <?php foreach ($breadcrumb as $link => $title): ?>
                                    <?php if (is_string($link)): ?>
                                        <li><?php echo HTML::anchor($link, $title) ?></li>
                                    <?php else: ?>
                                        <li class="last"><?php echo $title ?></li>
        <?php endif ?>
                        <?php endforeach ?>
                            </ul>
                        </div>
                            <?php endif ?>
                    <div class="span-6 prefix-1">
                        <div id="kodoc-topics">
<?php echo $menu ?>
                        </div>
                    </div>
                    <div id="kodoc-body" class="span-16 suffix-1 last">
                        <?php echo $content ?>

<?php if ($show_comments): ?>
                            <div id="disqus_thread" class="clear"></div>
                            <script type="text/javascript">
                                const disqus_identifier = '<?php echo HTML::chars(Request::current()->uri()) ?>';
                                (function() {
                                    const dsq = document.createElement('script');
                                    dsq.type = 'text/javascript';
                                    dsq.async = true;
                                    dsq.src = 'http://kohana.disqus.com/embed.js';
                                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
                                })();
                            </script>
                            <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=kohana">comments powered by Disqus.</a></noscript>
                            <a href="http://disqus.com" class="dsq-brlink">Documentation comments powered by <span class="logo-disqus">Disqus</span></a>
<?php endif ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="kodoc-footer">
            <div class="container">
                <div class="span-12">
                    <?php if (isset($copyright)): ?>
                        <p><?php echo $copyright ?></p>
                    <?php else: ?>
                        &nbsp;
<?php endif ?>
                </div>
                <div class="span-12 last right">
                    <p>Powered by <?php echo HTML::anchor('https://kohana.top/', 'Kohana') ?> v<?php echo Kohana::VERSION ?></p>
                </div>
            </div>
        </div>

<?php if (Kohana::$environment === Kohana::PRODUCTION): ?>
            <script type="text/javascript">
                //<![CDATA[
                (function() {
                    const links = document.getElementsByTagName('a');
                    let query = '?';
                    for (let i = 0; i < links.length; i++) {
                        if (links[i].href.indexOf('#disqus_thread') >= 0) {
                            query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
                        }
                    }
                    document.write('<script charset="utf-8" type="text/javascript" src="http://disqus.com/forums/kohana/get_num_replies.js' + query + '"></' + 'script>');
                })();
                //]]>
            </script>
<?php endif ?>
    </body>
</html>
