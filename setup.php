<?php
/**
 * ---------------------------------------------------------------------
 *  ticketmail is a plugin to allows users to send ticket information by email
 *  ---------------------------------------------------------------------
 *  LICENSE
 *
 *  This file is part of ticketmail.
 *
 *  ticketmail is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  ticketmail is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with ticketmail. If not, see <http://www.gnu.org/licenses/>.
 *  ---------------------------------------------------------------------
 *  @copyright Copyright © 2022-2023 probeSys'
 *  @license   http://www.gnu.org/licenses/agpl.txt AGPLv3+
 *  @link      https://github.com/Probesys/glpi-plugins-ticketmail
 *  @link      https://plugins.glpi-project.org/#/plugin/ticketmail
 *  ---------------------------------------------------------------------
 */

define('TICKETMAIL_VERSION', '3.6.4');
define('TICKETMAIL_MIN_GLPI_VERSION', '9.4');
define('TICKETMAIL_MAX_GLPI_VERSION', '11.0');
if (!defined("PLUGIN_TICKETMAIL_DIR")) {
   define("PLUGIN_TICKETMAIL_DIR", Plugin::getPhpDir("ticketmail"));
}
if (!defined("PLUGIN_TICKETMAIL_WEB_DIR")) {
   define("PLUGIN_TICKETMAIL_WEB_DIR", Plugin::getWebDir("ticketmail"));
}

function plugin_version_ticketmail()
{
    return [	
      'name'		=> "Ticket Mail",
      'version'		=> TICKETMAIL_VERSION,
      'author'          => '<a href="http://www.probesys.com">Probesys</a>',
      'license'	 	=> 'GPLv3+',
      'homepage'	=> 'https://github.com/Probesys/glpi-plugins-vip',
      'requirements'    => [
         'glpi'   => [
            'min' => TICKETMAIL_MIN_GLPI_VERSION,
            'max' => TICKETMAIL_MAX_GLPI_VERSION,
         ],
         'php'    => [
            'min' => '7.0'
         ] 
        ]
    ];
}

function plugin_ticketmail_check_prerequisites()
{
    $prerequisites_check_ok = false;

   try {
      if (version_compare(GLPI_VERSION, TICKETMAIL_MIN_GLPI_VERSION, '<')) {
          throw new Exception('This plugin requires GLPI >= ' . TICKETMAIL_MIN_GLPI_VERSION);
      }

      $prerequisites_check_ok = true;
   } catch (Exception $e) {
       echo $e->getMessage();
   }

    return $prerequisites_check_ok;
}

function plugin_ticketmail_check_config($verbose=false)
{
    if ($verbose) {
        echo 'Installed / not configured';
    }
    return true;
}

function plugin_init_ticketmail()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['ticketmail'] = true;
    Plugin::registerClass('PluginTicketmailProfile');
    Plugin::registerClass('PluginTicketmailProfile', array('addtabon' => array('Profile','Ticket')));
    $PLUGIN_HOOKS['change_profile']['ticketmail'] = array('PluginTicketmailProfile','changeProfile');
}
