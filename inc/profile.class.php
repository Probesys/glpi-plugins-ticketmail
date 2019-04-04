<?php

class PluginTicketmailProfile extends CommonDBTM
{
    public static function getTypeName($nb=0)
    {
        return "Ticket Mail";
    }

    public static function canCreate()
    {
        if (isset($_SESSION["glpi_plugin_ticketmail_profile"])) {
            return ($_SESSION["glpi_plugin_ticketmail_profile"]['ticketmail'] == 'w');
        }
        return false;
    }

    public static function canView()
    {
        if (isset($_SESSION["glpi_plugin_ticketmail_profile"])) {
            return ($_SESSION["glpi_plugin_ticketmail_profile"]['ticketmail'] == 'w'
            || $_SESSION["glpi_plugin_ticketmail_profile"]['ticketmail'] == 'r');
        }
        return false;
    }

    public static function createAdminAccess($ID)
    {
        $myProfil = new self();
        if (!$myProfil->getFromDB($ID)) {
            $myProfil->add(array(	'id'                       	=> $ID,
                                     'show_ticketmail_onglet' 	=> '1'));
        }
    }

    public function createAccess($ID)
    {
        $this->add(array('id' => $ID));
    }

    public static function changeProfile()
    {
        $profil = new self();
        if ($profil->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
            $_SESSION["glpi_plugin_ticketmail_profile"]=$profil->fields;
        } else {
            unset($_SESSION["glpi_plugin_ticketmail_profile"]);
        }
    }

    //profiles modification
    public function showForm($ID, $options=array())
    {
        global $LANG;

        $target = $this->getFormURL();
        if (isset($options['target'])) {
            $target = $options['target'];
        }

        /*if (!Session::haveRight("profile","r")) {
            return false;
        }*/

        $profil = new Profile();
        if ($ID) {
            $this->getFromDB($ID);
            $profil->getFromDB($ID);
        } ?>
		<form action='<?php echo $target ?>' method='post'>
			<table class='tab_cadre_fixe'>
				<tr>
					<th colspan='2' class='center b'><?php echo $LANG['plugin_ticketmail']['profile'][0] . " " . $profil->fields["name"]; ?></th>
				</tr>
				<tr class='tab_bg_2'>
					<td><?php echo $LANG['plugin_ticketmail']['profile'][1]; ?>:</td>
					<td>
						<?php Dropdown::showYesNo("show_ticketmail_onglet", $this->fields["show_ticketmail_onglet"]); ?>
					</td>
				</tr>
				<tr class='tab_bg_1'>
					<td class='center' colspan='2'>
						<input type='hidden' name='id' value= '<?php echo $ID; ?>'>
						<input type='submit' name='update_user_profile' value='<?php echo __s('Update'); ?>' class='submit'>
					</td>
				</tr>
			</table>

		<?php
        Html::closeForm();
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
    {
        global $LANG;

        if ($item->getType() == 'Profile' || $item->getType() == 'Ticket') {
            if (plugin_ticketmail_haveRight()) {
                return $LANG['plugin_ticketmail']['6'];
            }
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0)
    {
        global $CFG_GLPI, $DB;
        global $LANG;

        if ($item->getType() == 'Profile') {
            $profil = new self();
            $ID = $item->getField('id');
            if (!$profil->GetfromDB($ID)) {
                $profil->createAccess($ID);
            }
            $profil->showForm($ID);
        } elseif ($item->getType() == 'Ticket' && plugin_ticketmail_haveRight()) {
            $ID = $item->getField('id'); ?>
			<div align='center'>
			<?php
            // Initialise les libellés :
            $title = "Send";
            $but_label = $LANG['plugin_ticketmail']['5'];
            $but_name = "send";
            $to = "To ";
            $subject = "";
            $body = "";

            $query = "SELECT name, date, content FROM glpi_tickets WHERE id=".$ID;

            if ($result = $DB->query($query)) {
                if ($DB->numrows($result) > 0) {
                    $row = $DB->fetch_assoc($result);
                    $subject = $row['name'];
                    $body = Html::convDateTime($row['date'])."\n".$row['content']."\n\n";
                }
            }
            $query = "SELECT date, content FROM glpi_tickettasks WHERE tickets_id=".$ID." UNION SELECT date, content FROM glpi_ticketfollowups WHERE tickets_id=".$ID." ORDER BY 1";
            if ($result = $DB->query($query)) {
                if ($DB->numrows($result) > 0) {
                    while ($row = $DB->fetch_assoc($result)) {
                        $body .= Html::convDateTime($row['date'])."\n".$row['content']."\n\n";
                    }
                }
            }

            //Hide textbox for known user
            $onchange = '
			if (this.value != 0) {
			document.getElementById("address").style.display="none";
			document.getElementById("address").value=this.value;
			}
			else {
			document.getElementById("address").style.display="inline-block";
			document.getElementById("address").value="";
			}
			';


            // Affichage du formulaire : ?>
			<form method='post' action="<?php echo $CFG_GLPI["root_doc"] . "/plugins/ticketmail/front/ticketmail.form.php"; ?>" >

			<input type='hidden' name='id' value='<?php echo $ID; ?>'>
				<table class='tab_cadre' style='margin: 0; margin-top: 5px;'>
					<tr>
						<th colspan='2'><?php echo $LANG['plugin_ticketmail']['1']; ?></th>
					</tr>
					<tr class='tab_bg_1'>
						<td><?php echo $LANG['plugin_ticketmail']['2']; ?> : </td>
						<td>
						<?php
                        User::dropdown(array(	'name'   	=> 'users_id_ticketmail',
                                                'comments'	=> false,
                                                'value'  	=> 'users_id_ticketmail',
                                                'entity' 	=> $_SESSION["glpiactive_entity"],
                                                'right'  	=> array('ticket'),
                                                'on_change'	=> $onchange)); ?>
						<input type='text' name='address' id='address' size='40'></td>
					</tr>
					<tr class='tab_bg_1'>
						<td><?php echo $LANG['plugin_ticketmail']['3']; ?> : </td>
						<td>
							<input type='text' name='subject' maxlength='78' size='100' value='<?php echo $subject; ?>'>
						</td>
					</tr>
					<tr class='tab_bg_1'>
						<td><?php echo $LANG['plugin_ticketmail']['4']; ?> : </td>
						<td>
							<textarea name='body' rows='25' cols='100'><?php echo $body; ?></textarea>
						</td>
					</tr>
					<tr class='tab_bg_2'>
						<td colspan= '2' align='center'>
							<input type='submit' name='<?php echo $but_name; ?>' class='submit' value='<?php echo $but_label; ?>'>
						</td>
					</tr>
				</table>
			<?php Html::closeForm(); ?>
				</div>
		<?php
        }

        return true;
    }

    //TODO
    /*
        static function getName($userid) {
            $query = "SELECT name FROM glpi_users WHERE id=".$userid;
    
        }
    */
    public static function getEmail($userid)
    {
        if (!UserEmail::getDefaultForUser($userid)) {
            return '';
        }
        return UserEmail::getDefaultForUser($userid);
    }

    public static function getHistoryEntry($data)
    {
        $tmp = array();
        $tmp['change'] = sprintf(__("An email was sent to %s"), $data['old_value']) . " : " . $data['new_value'];
        return $tmp['change'];
    }
}
function plugin_ticketmail_haveRight()
{
    if (isset($_SESSION["glpi_plugin_ticketmail_profile"]) && $_SESSION['glpi_plugin_ticketmail_profile']['show_ticketmail_onglet'] == "1") {
        return true;
    } else {
        return false;
    }
}
?>
