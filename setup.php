<?php
const min_glpi_version = 9.2;

function plugin_version_ticketmail()
{
    return [	
      'name'		=> "Ticket Mail",
      'version'		=> '3.3.0',
      'author'		=> 'Probesys',
      'license'	 	=> 'GPLv3+',
      'homepage'	=> 'http://www.probesys.com',
      'minGlpiVersion'	=> min_glpi_version
      ];
}

function plugin_ticketmail_check_prerequisites()
{
    if (GLPI_VERSION>=min_glpi_version) {
        return true;
    } else {
        echo "GLPI version not compatible need ".min_glpi_version;
    }
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
