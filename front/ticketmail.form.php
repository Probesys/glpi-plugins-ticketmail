<?php

include ("../../../inc/includes.php");

if (isset($_POST["send"])) {
        $mmail=new GLPIMailer();
        
        $query = "SELECT email, realname, firstname FROM glpi_useremails um
                LEFT JOIN glpi_users u ON um.users_id=u.id
                WHERE um.users_id=".$_SESSION['glpiID'];

        if ($result = $DB->query($query)){
                if ($DB->numrows($result) > 0) {
                        $row = $DB->fetch_assoc($result);
                        $mmail->setFrom($row['email'], $row['firstname'].' '.$row['realname']);
                }
        }

        $body= str_replace("\\r","",str_replace("\\n","\n",$_POST['body']));

		if ($_POST['users_id_ticketmail']) {
			$address = PluginTicketmailProfile::getEmail($_POST['users_id_ticketmail']);
		}
		else {
			$address = $_POST["address"];
		}
		if (! NotificationMail::isUserAddressValid($address)) {
			Session::addMessageAfterRedirect(__("Invalid email address"),false,ERROR);
		}
        $mmail->AddAddress($address, $address);
        $mmail->Subject=$_POST["subject"];
        $mmail->Body=$body;
        $mmail->MessageID = "GLPI-ticketmail".time().".".rand(). "@".php_uname('n');

        if(!$mmail->Send()){
                Session::addMessageAfterRedirect(__("Your email could not be processed.\nIf the problem persists, contact the administrator"),false,ERROR);
        } else {
				Toolbox::logInFile("mail", sprintf(__('%1$s: %2$s'), sprintf(__('An email was sent to %s'),
											$address),
                                            $_POST["subject"]."\n"));
				$changes[0] = 0;
				$changes[1] = $address;
				$changes[2] = $_POST['subject'];
      			Log::history($_POST['id'], 'Ticket', $changes, 'PluginTicketmailProfile', Log::HISTORY_PLUGIN + 1024);
				Session::addMessageAfterRedirect(sprintf(__('An email was sent to %s'), $address));
        }
        $mmail->ClearAddresses();
        Html::redirect($_SERVER['HTTP_REFERER']);
}
else {
        Html::redirect("../index.php");
}
?>
