<?php

namespace MM\PlatformBundle\DoctrineListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use MM\PlatformBundle\Email\CommentMailer;
use MM\PlatformBundle\Entity\Comment;

class CommentCreationListener
{
  /**
   * @var CommentMailer
   */
  private $commentMailer;

  public function __construct(CommentMailer $commentMailer)
  {
    $this->commentMailer = $commentMailer;
  }

  public function postPersist(LifecycleEventArgs $args)
  {
    $entity = $args->getObject();

    if (!$entity instanceof Comment) {
      return;
    }

    $this->commentMailer->sendNewNotification($entity);
  }
}
