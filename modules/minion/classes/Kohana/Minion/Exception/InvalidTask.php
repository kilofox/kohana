<?php

/**
 * Invalid Task Exception
 *
 * @package    Kohana
 * @category   Minion
 * @author     Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Minion_Exception_InvalidTask extends Minion_Exception
{
    public function format_for_cli(): string
    {
        return 'ERROR: ' . $this->getMessage() . PHP_EOL;
    }

}
