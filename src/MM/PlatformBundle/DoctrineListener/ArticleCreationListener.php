<?php

namespace MM\PlatformBundle\DoctrineListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use MM\PlatformBundle\Email\ArticleMailer;
use MM\PlatformBundle\Entity\Article;

class ArticleCreationListener
{
  /**
   * @var ArticleMailer
   */
  private $articleMailer;

  public function __construct(ArticleMailer $articleMailer)
  {
    $this->articleMailer = $articleMailer;
  }

  public function postPersist(LifecycleEventArgs $args)
  {
    $entity = $args->getObject();

    if (!$entity instanceof Article) {
      return;
    }

    $this->articleMailer->sendNewNotification($entity);
  }
}
