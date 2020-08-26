<?php

define('TICKETMAIL_VERSION', '3.4.0');
define('MIN_GLPI_VERSION', '9.4');
define('MAX_GLPI_VERSION', '9.6');
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
            'min' => MIN_GLPI_VERSION,
            'max' => MAX_GLPI_VERSION,
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
      if (version_compare(GLPI_VERSION, MIN_GLPI_VERSION, '<')) {
          throw new Exception('This plugin requires GLPI >= ' . MIN_GLPI_VERSION);
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
