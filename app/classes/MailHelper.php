<?php
// Подключаем библиотеку PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    private static string $mailTo;
    private static string $subject;
    private static string $message;

    public static function mail()
    {
        // Настройки сервера на примере Яндекс почты
        $mail = new PHPMailer;
        $mail->isSMTP();                                     
        $mail->Host         = MAIL_HOST;
        $mail->SMTPAuth     = true;
        $mail->Username     = MAIL_USER; // Если почта для домена, то логин это полный адрес почты
        $mail->Password     = MAIL_PASS;
        $mail->SMTPSecure   = 'TLS';
        $mail->Port         = MAIL_PORT;

        // Авторизация
        $mail->CharSet = 'UTF-8';
        $mail->From = MAIL_USER;
        $mail->FromName = SITE_NAME;
        $mail->addAddress(self::$mailTo);

        // Контент                   
        $mail->isHTML(true);                    
        $mail->Subject = self::$subject;
        $mail->Body    = self::$message;

        // Отправка
        if(!$mail->send()) {
            echo 'Сообщение не может быть отправлено.';
            echo 'Ошибка: ' . $mail->ErrorInfo;
            exit;
        }
        else{
            echo 'Сообщение отправлено.';
        }
    }

    public static function mailData(string $mailTo, string $subject, string $message)
    {
        self::$mailTo = $mailTo;
        self::$subject = $subject;
        self::$message = $message;
    }
}