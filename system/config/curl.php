<?php

defined('SYSPATH') OR die('No direct script access.');

return [
    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Kohana v' . Kohana::VERSION . ' +https://kohana.top/)',
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_HEADER => false,
];
