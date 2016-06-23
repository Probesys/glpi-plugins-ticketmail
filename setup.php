<?php

function plugin_version_ticketmail(){

	return array(	'name'				=> "Ticket Mail",
					'version' 			=> '3.2.0',
					'author'			=> 'Probesys',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'http://www.probesys.com',
					'minGlpiVersion'	=> '0.90');
}

function plugin_ticketmail_check_prerequisites(){
        if (GLPI_VERSION>=0.84){
                return true;
        } else {
                echo "GLPI version not compatible need 0.90";
        }
}

function plugin_ticketmail_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}

function plugin_init_ticketmail() {
    global $PLUGIN_HOOKS;

	$PLUGIN_HOOKS['csrf_compliant']['ticketmail'] = true;
	Plugin::registerClass('PluginTicketmailProfile');
	Plugin::registerClass('PluginTicketmailProfile', array('addtabon' => array('Profile','Ticket')));
	$PLUGIN_HOOKS['change_profile']['ticketmail'] = array('PluginTicketmailProfile','changeProfile');
}

?>
