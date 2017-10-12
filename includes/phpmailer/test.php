    <?php
    require("phpmailer/class.phpmailer.php");
    $mail = new PHPMailer();
    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->Host = "10.222.150.7"; // SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = "ankeny\emelton";
    $mail->Password = "Pa\$\$word";

    $mail->FromName = "Eric Melton";
    $mail->From = "emelton@ankenyiowa.gov";//sender addy
    $mail->AddAddress("ericmelton1@gmail.com");//recip. email addy

    $mail->Subject = "Your Subject";
    $mail->Body = "hi ! \nBLA BLA BLA !";
    $mail->WordWrap = 50;

    if(!$mail->Send())
    {
    echo "Message was not sent";
    echo "Mailer Error: " . $mail->ErrorInfo;
    }
    else
    {
    echo "Message has been sent";
    }
    ?>
