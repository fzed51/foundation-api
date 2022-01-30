<?php
declare(strict_types=1);

namespace Api\Utilities;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class PhpMailerAdapter
 * @package Api\Utilities
 */
class PhpMailerAdapter extends MailerAdapter implements MailerInterface
{
    private PHPMailer $mailer;

    /**
     * PhpMailerAdapter constructor.
     * @throws Exception
     */
    public function __construct(
        string  $host,
        int $port,
        string $username,
        string $password,
        string $from,
        string $fromName,
        string $replyToEmail,
        string $replyToName
    ) {
        $mailer = new PHPMailer();
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->Username = $username;
        $mailer->Password = $password;
        $mailer->From = $from;
        $mailer->FromName = $fromName;
        $mailer->IsHTML(true);
        $mailer->CharSet = 'utf-8';
        $mailer->IsSMTP();
        $mailer->SMTPAuth = true;
        $mailer->WordWrap = 50;
        $mailer->AddReplyTo($replyToEmail, $replyToName);
        $mailer->IsHTML(true); // envoyer au format html, passer a false si en mode texte
        $this->mailer=$mailer;
    }

    /**
     * @param string $emailTo
     * @param string $template
     * @param array<string,mixed> $data
     * @return void
     * @throws Exception
     */
    public function send(string $emailTo, string $template, array $data):void
    {
        $this->mailer->Subject= $this->getTitle($template); // sujet
        $this->mailer->AltBody= $this->getText($template, $data) ;
        $this->mailer->MsgHTML($this->getbody($template, $data));
        $this->mailer->AddAddress($emailTo);
        $this->mailer->Send();
    }
}
