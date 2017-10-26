<?php

namespace MM\PlatformBundle\Email;

use MM\PlatformBundle\Entity\Comment;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class CommentMailer
{
  /**
   * @var \Swift_Mailer
   */
  private $mailer;
  private $container;

  public function __construct(\Swift_Mailer $mailer, Container $container)
  {
    $this->mailer = $mailer;
    $this->container = $container;
  }

  public function sendNewNotification(Comment $comment)
  {    
    $userManager = $this->container->get('fos_user.user_manager');
      
    $repository = $this->container
      ->get('doctrine')
      ->getManager()
      ->getRepository('MMPlatformBundle:Article')
    ;

    $article = $repository->findByComment($comment);
    $user = $userManager->findUserByUsername($article[0]->getAuthor());
      
    $message = \Swift_Message::newInstance()
        ->setSubject('Vous avez reçu un nouveau commentaire !')
        ->setFrom('no-reply@legtux.org')
        ->setTo($user->getEmail())
        ->setBody(
          $this->container->get('templating')->render(
                'Emails/notificationComment.html.twig',
                array('name' => $comment->getAuthor(),
                      'content' => $comment->getBody(),
                      'title' => $article[0]->getTitle(),
                      'id' => $article[0]->getId(),
                      'image' => $article[0]->getImage()->getId())
            ),
            'text/html')
    ;

    $this->mailer->send($message);
  }
}
