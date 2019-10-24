<?php

/**
 * Internal Filebrowser -- admin.php
 *
 * @category  CMSimple_XH
 * @package   Database access
 * @author    Thomas Winkler <thomas.winkler@iggmp.net>
 * @copyright 2018 nibble-arts <http://www.nibble-arts.org>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

define (DATABASE_PLUGIN_BASE, $pth["folder"]["plugin"]);


/*
 * Register the plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(true);
}

if (function_exists('database') 
    && XH_wantsPluginAdministration('database') 
    || isset($database) && $database == 'true')
{


    $o .= print_plugin_admin('on');

    switch ($admin) {

	    case '':
	        $o .= '<h1>Database</h1>';
    		$o .= '<p>Version 0.9</p>';
            $o .= '<p>Copyright 2018</p>';
    		$o .= '<p><a href="http://www.nibble-arts.org" target="_blank">Thomas Winkler</a></p>';
            $o .= '<p>Mit dem Plugin ist es möglich Unterschiedliche Datenbanken anzusprechen und die Felder auf einer CMSimple-XH Seite anzuzeigen.</p>';

	        break;

        case 'plugin_main':
            include_once(DATABASE_PLUGIN_BASE."settings.php");

            database_settings($action, $admin, $plugin);
            break;

	    default:
	        plugin_admin_common($action, $admin, $plugin);
            break;
    }

}
?>
