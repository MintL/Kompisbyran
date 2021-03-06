<?php

namespace AppBundle\Mailer;

use AppBundle\Entity\User;
use AppBundle\Enum\FriendTypes;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class UserMailer
 * @package AppBundle\Mailer
 */
class UserMailer extends Mailer
{
    protected $translator;

    /**
     * @param \Swift_Mailer $mailer
     * @param RouterInterface $router
     * @param EngineInterface $templating
     * @param TranslatorInterface $translator
     */
    public function __construct(\Swift_Mailer $mailer, RouterInterface $router, EngineInterface $templating, TranslatorInterface $translator)
    {
        $this->translator = $translator;

        parent::__construct($mailer, $router, $templating);
    }

    /**
     * @param User $user
     */
    public function sendRegistrationWelcomeEmailMessage(User $user)
    {
        $subject = sprintf('email.welcome.%s.subject', $user->getType());
        $htmlBody = sprintf('email.welcome.%s.body', $user->getType());

        $subject = $this->translator->trans($subject);
        $htmlBody = $this->translator->trans(
            $htmlBody,
            [
                '%firstName%' => $user->getFirstName(),
            ]
        );

        $html = $this->templating->render('email/welcome.html.twig', [
            'body' => $htmlBody
        ]);

        $replyEmail = null;
        if ($user->getType() == FriendTypes::START) {
            $replyEmail = 'start@kompisbyran.se';
        }

        $this->sendEmailMessage($html, null, $subject, $user->getEmail(), null, $replyEmail);
    }

    /**
     * @param User $user
     * @param User $matchUser
     * @param $body
     * @param $fromEmail
     */
    public function sendMatchEmailMessage(User $user, User $matchUser, $body, $fromEmail)
    {
        $typeText = $matchUser->getType() == FriendTypes::MUSIC
            ? $this->translator->trans('global.music_buddy')
            : $this->translator->trans('global.fika_buddy');

        $subject = sprintf('%s, här är din %s från Kompisbyrån', $user->getFullName(), $typeText);

        $this->sendEmailMessage(null, $body, $subject, $user->getEmail(), $fromEmail, $fromEmail);
    }
}
