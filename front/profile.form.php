<?php

include("../../../inc/includes.php");

//Session::checkRight("profile","r");

$prof = new PluginTicketmailProfile();

if (isset($_POST['update_user_profile'])) {
    //$prof->update($_POST);
    majDroit($_POST);
    Html::back();
}

/**
* Fonction qui modifie les droits dans la base
* @param type $arrayItem (id, right)
*/
function majDroit($arrayItem)
{
    global $DB;
    //Mise à jour des droits
    $query = "SELECT * FROM glpi_plugin_ticketmail_profiles WHERE id=".$arrayItem['id'];
    if ($result = $DB->query($query)) {
        if ($DB->numrows($result) > 0) {
            $query = "UPDATE glpi_plugin_ticketmail_profiles SET show_ticketmail_onglet='".$arrayItem['show_ticketmail_onglet']."' WHERE id=".$arrayItem['id'];
            $DB->query($query);
        }
    }
}
