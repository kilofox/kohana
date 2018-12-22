<?php

// Get the latest logo contents
$data = base64_encode(file_get_contents('https://kohana.top/assets/img/logo.png'));

// Create the logo file
file_put_contents('logo.php', "<?php
/**
 * Kohana Logo, base64_encoded PNG
 *
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
return ['mime' => 'image/png', 'data' => '{$data}']; ?>");
