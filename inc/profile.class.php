<?php

class PluginTicketmailProfile extends CommonDBTM {

    public static function getTypeName($nb = 0) {
        return "Ticket Mail";
    }

    public static function canCreate() {
        if (isset($_SESSION["glpi_plugin_ticketmail_profile"])) {
            return ($_SESSION["glpi_plugin_ticketmail_profile"]['ticketmail'] == 'w');
        }
        return false;
    }

    public static function canView() {
        if (isset($_SESSION["glpi_plugin_ticketmail_profile"])) {
            return ($_SESSION["glpi_plugin_ticketmail_profile"]['ticketmail'] == 'w' || $_SESSION["glpi_plugin_ticketmail_profile"]['ticketmail'] == 'r');
        }
        return false;
    }

    public static function createAdminAccess($ID) {
        $myProfil = new self();
        if (!$myProfil->getFromDB($ID)) {
            $myProfil->add([
              'id' => $ID,
              'show_ticketmail_onglet' => '1'
            ]);
        }
    }

    public function createAccess($ID) {
        $this->add(['id' => $ID]);
    }

    public static function changeProfile() {
        $profil = new self();
        if (array_key_exists('glpiactiveprofile', $_SESSION) && $profil->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
            $_SESSION["glpi_plugin_ticketmail_profile"] = $profil->fields;
        } else {
            if(array_key_exists('glpi_plugin_ticketmail_profile', $_SESSION)) {
                unset($_SESSION["glpi_plugin_ticketmail_profile"]);
            }
        }
    }

    //profiles modification
    public function showForm($ID, $options = array()) {

        $target = $this->getFormURL();
        if (isset($options['target'])) {
            $target = $options['target'];
        }

        /* if (!Session::haveRight("profile","r")) {
          return false;
          } */

        $profil = new Profile();
        if ($ID) {
            $this->getFromDB($ID);
            $profil->getFromDB($ID);
        }
        ?>
        <form action='<?php echo $target ?>' method='post'>
           <table class='tab_cadre_fixe'>
              <tr>
                 <th colspan='2' class='center b'><?php echo __('User rights management','ticketmail') . " " . $profil->fields["name"]; ?></th>
              </tr>
              <tr class='tab_bg_2'>
                 <td><?php echo  __('Tab display','ticketmail'); ?>:</td>
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

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

        if ($item->getType() == 'Profile' || $item->getType() == 'Ticket') {
            if (plugin_ticketmail_haveRight()) {
                return __('Ticket-mail','ticketmail');
            }
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $CFG_GLPI, $DB;
        
        if ($item->getType() == 'Profile') {
            $profil = new self();
            $ID = $item->getField('id');
            if (!$profil->GetfromDB($ID)) {
                $profil->createAccess($ID);
            }
            $profil->showForm($ID);
        } elseif ($item->getType() == 'Ticket' && plugin_ticketmail_haveRight()) {
            $ID = $item->getField('id');
            ?>
               <div align='center'>
               <?php
               // Initialise les libellés :
               $title = "Send";
               
               $but_label = __('Send','ticketmail');
               $but_name = "send";
               $to = "To ";
               $subject = "";
               $body = "";

               $query = "SELECT id, name, date, content FROM glpi_tickets WHERE id=" . $ID;

               if ($result = $DB->query($query)) {
                   if ($DB->numrows($result) > 0) {
                       $row = $DB->fetchAssoc($result);
                       $subject = $row['name'] . '(' . $row['id'] . ')';
                       $body = '<h3>'.__('Content of the initial ticket','ticketmail').'</h3>';
                       $body .= Html::convDateTime($row['date']) . "\n" . $row['content'] . "\n\n";
                   }
               }
               // tickettasks and itilfollowups
               $query = "SELECT date, content, is_private FROM glpi_tickettasks WHERE tickets_id=" . $ID 
                       . " UNION SELECT date, content, is_private FROM glpi_itilfollowups WHERE itemtype='Ticket' AND items_id=" . $ID 
                       . " ORDER BY date DESC";
               if ($result = $DB->query($query)) {
                   $body .= '<h3>'.__('Ticket tasks and followups associate to the ticket','ticketmail').'</h3>';
                   if ($DB->numrows($result) > 0) {
                       while ($row = $DB->fetchAssoc($result)) {
                           if($row['is_private'] ==  1) {
                               $body .= '<div class="is_private">';
                           }
                           $body .= Html::convDateTime($row['date']) . ":\n" . $row['content'] . "\n\n";
                           if($row['is_private'] ==  1) {
                               $body .= '</div>';
                           }
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


               // Affichage du formulaire : 
               ?>
                  <form method='post' action="<?php echo PLUGIN_TICKETMAIL_WEB_DIR . "/front/ticketmail.form.php"; ?>" >
                     <div class="spaced" id="tabsbody">
                     <input type='hidden' name='id' value='<?php echo $ID; ?>'>
                     <table class='tab_cadre_fixe' style='margin: 0; margin-top: 5px;'>
                        <tbody>
                        <tr class="tab_bg_2">
                           <th colspan='2'><?php echo __('Send ticket information by email','ticketmail'); ?></th>
                        </tr>
                        <tr class='tab_bg_1'>
                           <th><?php echo __('To','ticketmail'); ?> : </th>
                           <td>
                            <?php
                            User::dropdown(['name' => 'users_id_ticketmail',
                              'comments' => false,
                              'value' => 'users_id_ticketmail',
                              'entity' => $_SESSION["glpiactive_entity"],
                              'right' => ['ticket'],
                              'on_change' => $onchange]);
                            ?>
                              <br/><input type='text'  name='address' id='address' size='40' required></td>
                        </tr>
                        <tr class='tab_bg_1'>
                            <th><?php echo __('Hide private tasks and private followups','ticketmail'); ?></th>
                            <td><input type="checkbox" name="hide_private_task" id="hidePrivateTask" value="1"></td>
                        </tr>
                        <tr class='tab_bg_1'>
                           <th><?php echo __('Subject','ticketmail'); ?> : </th>
                           <td>
                              <input type='text' name='subject' maxlength='78' size='100' value='<?php echo $subject; ?>'>
                           </td>
                        </tr>
                        <tr class='tab_bg_1'>
                           <th><?php echo __('Message','ticketmail'); ?> : </th>
                           <td>
                              <textarea name='body' id='ticketMailBody' rows='25' cols='100'><?php echo $body; ?></textarea>
                           </td>
                        </tr>
                        <tr class='tab_bg_2'>
                           <td colspan= '2' align='center'>
                              <input type='submit' name='<?php echo $but_name; ?>' class='submit' value='<?php echo $but_label; ?>'>
                           </td>
                        </tr>
                        </tbody>
                     </table>
                     </div>
            <?php Html::closeForm(); ?>
               </div>
               <script>
                   <?php
                   $language = $_SESSION['glpilanguage'];
                    if (!file_exists(GLPI_ROOT."/public/lib/tinymce-i18n/langs/$language.js")) {
                       $language = $CFG_GLPI["languages"][$_SESSION['glpilanguage']][2];
                       if (!file_exists(GLPI_ROOT."/public/lib/tinymce-i18n/langs/$language.js")) {
                          $language = "en_GB";
                       }
                    }
                    $language_url = $CFG_GLPI['root_doc'] . '/public/lib/tinymce-i18n/langs/' . $language . '.js';
                   ?>
                   tinymce.init({
                      language_url: '<?php echo $language_url ?>',
                      invalid_elements: 'form,iframe,script,@[onclick|ondblclick|'
                              + 'onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|'
                              + 'onkeydown|onkeyup]',
                      browser_spellcheck: true,
                      mode: 'exact',
                      selector: '#ticketMailBody',
                      relative_urls: false,
                      remove_script_host: false,
                      entity_encoding: 'raw',
                      paste_data_images: $('.fileupload').length,
                      menubar: false,
                      statusbar: false,
                      skin_url: '<?php echo $CFG_GLPI['root_doc']; ?>/css/tiny_mce/skins/light',
                      content_css: '<?php echo $CFG_GLPI['root_doc']; ?>/css/tiny_mce_custom.css'
                   });
                   $( "#hidePrivateTask" ).on( "click", function() {
                       $('#ticketMailBody_ifr').contents().find('.is_private').toggle();
                      });
               </script>

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
    public static function getEmail($userid) {
        if (!UserEmail::getDefaultForUser($userid)) {
            return '';
        }
        return UserEmail::getDefaultForUser($userid);
    }

    public static function getHistoryEntry($data) {
        $tmp = [];
        $tmp['change'] = sprintf(__("An email was sent to %s"), $data['old_value']) . " : " . $data['new_value'];
        return $tmp['change'];
    }

}

function plugin_ticketmail_haveRight() {
    if (isset($_SESSION["glpi_plugin_ticketmail_profile"]) && $_SESSION['glpi_plugin_ticketmail_profile']['show_ticketmail_onglet'] == "1") {
        return true;
    } else {
        return false;
    }
}

