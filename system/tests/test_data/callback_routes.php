<?php

/**
 * A holding class for route callback tests
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Route_Holder
{
    /**
     * Route callback for test route_filter_modify_params
     *
     * @return array
     */
    public static function route_filter_modify_params_array(Route $route, $params): array
    {
        $params['action'] = 'modified';

        return $params;
    }

    /**
     * Route callback for test route_filter_modify_params
     *
     * @return bool
     */
    public static function route_filter_modify_params_false(): bool
    {
        return false;
    }

}
