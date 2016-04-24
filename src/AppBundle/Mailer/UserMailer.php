<?php

namespace AppBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Entity\Connection;

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
     * @param UserInterface $user
     */
    public function sendRegistrationWelcomeEmailMessage(UserInterface $user)
    {
        $subject    = 'user.welcome.message.subject';
        $htmlBody   = 'user.welcome.message.body';
        $txtBody    = 'user.welcome.message.body.txt';

        if ($user->isMusicFriend()) {
            $subject    = 'user.friend.welcome.message.subject';
            $htmlBody   = 'user.friend.welcome.message.body';
            $txtBody    = 'user.friend.welcome.message.body.txt';
        }

        $subject    = $this->translator->trans($subject);
        $htmlBody   = $this->translator->trans($htmlBody);
        $txtBody    = $this->translator->trans($txtBody);

        $html       = $this->templating->render('email/welcome.html.twig', [
            'body'  =>  $htmlBody
        ]);

        $txt        = $this->templating->render('email/welcome.txt.twig', [
            'body'  => $txtBody
        ]);

        $this->sendEmailMessage($html, $txt, $subject, $user->getEmail());
    }

    /**
     * @param UserInterface $user
     * @param UserInterface $matchUser
     * @param $body
     */
    public function sendMatchEmailMessage(UserInterface $user, UserInterface $matchUser, $body)
    {
        $subject    = $this->translator->trans('match.email.user.subject', [
            '%match_music_friend%'    => ($matchUser->isMusicFriend()?  $this->translator->trans('global.music_buddy'):  $this->translator->trans('global.fika_buddy')),
            '%user_name%'             => $user->getFullName()
        ]);

        $this->sendEmailMessage(null, $body, $subject, $user->getEmail());
    }

    public function sendConnectionFollowUpEmailMessage(UserInterface $user, $days)
    {
        $wantToLearnType    = ($user->getWantToLearn()? 'learner': 'fluent');
        $htmlTemplate       = "email/connection_followup_{$days}_{$wantToLearnType}.html.twig";
        $txtTemplate        = "email/connection_followup_{$days}_{$wantToLearnType}.txt.twig";

        $html       = $this->templating->render($htmlTemplate, [
            'user'  =>  $user
        ]);

        $txt        = $this->templating->render($txtTemplate, [
            'user'  => $user
        ]);

        switch (true) {
            case $user->getWantToLearn() && $days == Connection::FOLLOW_UP_14_DAYS:
                $subject = 'Fråga från Kompisbyrån // Question from Kompisbyrån';
            break;
            case !$user->getWantToLearn() && $days == Connection::FOLLOW_UP_14_DAYS:
                $subject = 'Fråga från Kompisbyrån';
            break;
            case $user->getWantToLearn() && $days == Connection::FOLLOW_UP_42_DAYS:
                $subject = 'Uppföljning från Kompisbyrån (Follow-up from Kompisbyrån)';
            break;
            case !$user->getWantToLearn() && $days == Connection::FOLLOW_UP_42_DAYS:
                $subject = 'Uppföljning från Kompisbyrån';
            break;
        }

        $this->sendEmailMessage($html, $txt, $subject, $user->getEmail());
    }
}