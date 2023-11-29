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
