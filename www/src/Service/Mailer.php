<?php
namespace App\Service;

use App\Entity\User;

class Mailer
{
    private $twig;
    private $mailer;
    private $params = [];

    public function __construct(
        $mail_prefix,
        $mail_website,
        $mail_from,
        $mail_name,
        \Swift_Mailer $mailer,
        \Twig_Environment $twig
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->params['mail_prefix'] = $mail_prefix;
        $this->params['mail_website'] = $mail_website;
        $this->params['mail_from'] = $mail_from;
        $this->params['mail_name'] = $mail_name;
    }

    public function sendToUser(string $template, User $user, array $parameters = []): int
    {
        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($template);

        $parameters['user'] = $user;
        $parameters['mail_prefix'] = $this->params['mail_prefix'];
        $parameters['mail_website'] = $this->params['mail_website'];
        $subject  = $template->render(array_merge($parameters, ['block_name' => 'subject']));
        $bodyHtml = $template->render(array_merge($parameters, ['block_name' => 'body_html']));
        $bodyText = $template->render(array_merge($parameters, ['block_name' => 'body_text']));

        return $this->send(
            $subject,
            $bodyText,
            $bodyHtml,
            [$this->params['mail_from'] => $this->params['mail_name']],
            [$user->getEmail() => $user->getUsername()]
        );
    }

    public function send($subject, $bodyText, $bodyHtml, $from, $to, $reply = ''): int
    {
        if (!$reply) {
            $reply = $from;
        }

        $mail = \Swift_Message::newInstance();

        $mail
            ->addPart($bodyHtml, 'text/html')
            ->setTo($to)
            ->setFrom($from)
            ->setReplyTo($reply)
            ->setSubject($subject)
            ->setBody($bodyText, 'text/plain')
        ;

        return $this->mailer->send($mail);
    }
}
