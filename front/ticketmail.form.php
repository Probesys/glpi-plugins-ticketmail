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

if (isset($_POST["send"])) {
    
    $header = "<!DOCTYPE html PUBLIC
                        'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
                        <html>
                        <head>
                         <META http-equiv='Content-Type' content='text/html; charset='utf-8'>
                         </head>
                         <body>";
    $footer = "</body></html>";
    
    $mmail = new GLPIMailer();
        
//    $query = "SELECT email, realname, firstname FROM glpi_useremails um
//                LEFT JOIN glpi_users u ON um.users_id=u.id
//                WHERE um.users_id=".$_SESSION['glpiID'];
//
//    if ($result = $DB->query($query)) {
//        if ($DB->numrows($result) > 0) {
//            $row = $result->fetch_assoc();
//            $mmail->setFrom($row['email'], $row['firstname'].' '.$row['realname']);
//        }
//    }
    
    $from = $_POST['from'];
    $mmail->SetFrom($_POST['from'], $_POST['from'], false);
    
    $body = str_replace("\\r", "", str_replace("\\n", "\n", html_entity_decode($_POST['body'])));
    $body = str_replace("\'", "'",$body);
    
    $hide_private_task = (array_key_exists('hide_private_task',$_POST) && $_POST['hide_private_task']=='1')?true:false;	
    if($hide_private_task) {
        $body = str_replace('<div class=\"is_private\" style=\"display: none;\">', 'PRIVATESTART', $body);
        $body = preg_replace('/PRIVATESTART[\s\S]+?<\/div>/', '', $body);
    }

    if ($_POST['users_id_ticketmail']) {
        $address = PluginTicketmailProfile::getEmail($_POST['users_id_ticketmail']);
    } else {
        $address = $_POST["address"];
    }
    if (! NotificationMailing::isUserAddressValid($address)) {
        Session::addMessageAfterRedirect(__("Invalid email address"), false, ERROR);
    }
    
    $mmail->AddCustomHeader("Auto-Submitted: auto-generated");
    // For exchange
    $mmail->AddCustomHeader("X-Auto-Response-Suppress: OOF, DR, NDR, RN, NRN");
    //$mmail->SetFrom($CFG_GLPI["admin_email"], $CFG_GLPI["admin_email_name"], false);
    $subject = $_POST["subject"];
    $mmail->AddAddress($address, $address);
    $mmail->isHTML(true);
    $mmail->Subject = $subject;
    $mmail->Body = $header.GLPIMailer::normalizeBreaks($body).$footer;
    $mmail->MessageID = "GLPI-ticketmail".time().".".rand(). "@".php_uname('n').'-Ticket-'.$_POST['id'];
    
    if (!$mmail->Send()) {
        Session::addMessageAfterRedirect(__("Your email could not be processed.\nIf the problem persists, contact the administrator"), false, ERROR);
        Toolbox::logInFile("mail", "\nError during send email form ticketMail plugin:\n ** RECIPIANT: ".$address. "\n ** SUBJECT: ".$subject."\n ** BODY: ".$body. "\n ** ERROR: ".$mmail->ErrorInfo);
    } else {
        Toolbox::logInFile("mail", '[plugin ticketmail] : '.sprintf(
                    __('%1$s: %2$s'),
                    sprintf(
                    __('An email was sent to %s'),
                    $address
                ),
                    $subject."\n"
                ));
        $changes[0] = 0;
        $changes[1] = $address;
        $changes[2] = $subject.'<br/>'.$body;
        
        Log::history($_POST['id'], 'Ticket', $changes, 'PluginTicketmailProfile', Log::HISTORY_PLUGIN + 1024);
        Session::addMessageAfterRedirect(sprintf(__('An email was sent to %s'), $address));
    }
    $mmail->ClearAddresses();
    Html::redirect($_SERVER['HTTP_REFERER']);
} else {
    Html::redirect("../index.php");
}
