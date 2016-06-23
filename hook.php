<?php

function plugin_ticketmail_install(){
	global $DB;

	$migration = new Migration(100);

	//Fresh install
	if (!TableExists('glpi_plugin_ticketmail_profiles')){
		$query = "CREATE TABLE `glpi_plugin_ticketmail_profiles` (
					`id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
					`show_ticketmail_onglet` char(1) collate utf8_unicode_ci default NULL,
					PRIMARY KEY  (`id`)
				  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
                $DB->queryOrDie($query, $DB->error());

				$migration->executeMigration();

                include_once(GLPI_ROOT."/plugins/ticketmail/inc/profile.class.php");
                PluginTicketmailProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
    }
	//Update
	else {
		// Since v0.84 remove "profiles_id" column and use "id"
		if (FieldExists('glpi_plugin_ticketmail_profiles', 'profiles_id')){
			$drop_column_query = "ALTER TABLE glpi_plugin_ticketmail_profiles DROP COLUMN `id`;";
			$rename_column_query = "ALTER TABLE glpi_plugin_ticketmail_profiles
									CHANGE profiles_id id int(11) NOT NULL default '0'
									COMMENT 'RELATION to glpi_profiles (id)';";
			$DB->queryOrDie($drop_column_query, $DB->error());
			$DB->queryOrDie($rename_column_query, $DB->error());
			$add_primarykey_query = "ALTER TABLE glpi_plugin_ticketmail_profiles
									 ADD PRIMARY KEY (id);";
			$drop_old_index_query = "ALTER TABLE glpi_plugin_ticketmail_profiles
									 DROP INDEX profiles_id;";
			$DB->queryOrDie($add_primarykey_query, $DB->error());
			$DB->queryOrDie($drop_old_index_query, $DB->error());

		}
	}
    return true;
}

function plugin_ticketmail_uninstall(){
    global $DB;

    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_ticketmail_profiles`;");

    return true;
}

function plugin_ticketmail_getPluginsDatabaseRelations()
{
    $plugin = new Plugin();
    if ($plugin->isActivated("ticketmail"))
            return array(
                "glpi_profiles" => array ("glpi_plugin_ticketmail_profiles" => "id")
                );
    else
            return array();
}

?>
