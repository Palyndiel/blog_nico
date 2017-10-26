<?php

namespace MM\PlatformBundle\Email;

use MM\PlatformBundle\Entity\Article;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class ArticleMailer
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

  public function sendNewNotification(Article $article)
  {
    $userManager = $this->container->get('fos_user.user_manager');
    $listUsers = $userManager->findUsers();
    
    $message = \Swift_Message::newInstance()
        ->setSubject('Un nouvel article a été publié sur le site.')
        ->setFrom('no-reply@legtux.org')
        ->setBody(
          $this->container->get('templating')->render(
                'Emails/newsLetter.html.twig',
                array('name' => $article->getAuthor(),
                      'title' => $article->getTitle(),
                      'id' => $article->getId(),
                      'image' => $article->getImage()->getId())
            ),
            'text/html')
    ;
        
    foreach ($listUsers as $user)
    {
        $message -> addBcc($user->getEmail());
    }
    
    $this->mailer->send($message);
  }
}
