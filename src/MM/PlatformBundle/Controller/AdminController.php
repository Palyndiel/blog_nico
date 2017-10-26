<?php

namespace MM\PlatformBundle\Controller;

use MM\PlatformBundle\Entity\Article;
use MM\PlatformBundle\Form\ArticleType;
use MM\PlatformBundle\Entity\Comment;
use MM\PlatformBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminController extends Controller {

	public function indexAction() {
        $listArticles = $this->getDoctrine()
      		->getManager()
      		->getRepository('MMPlatformBundle:Article')
      		->findByAll();
    	;
        $listImages = $this->getDoctrine()
      		->getManager()
      		->getRepository('MMPlatformBundle:Image')
      		->findByAll();
    	;
        $listVideos = $this->getDoctrine()
      		->getManager()
      		->getRepository('MMPlatformBundle:Video')
      		->findByAll();
    	;
        $listComments = $this->getDoctrine()
      		->getManager()
      		->getRepository('MMPlatformBundle:Comment')
      		->findByAll();
    	;
      $userManager = $this->get('fos_user.user_manager');
     //    $listUsers = $this->getDoctrine()
     //  		->getManager()
     //  		->getRepository('MMUserBundle:User')
     //  		->findByAll();
    	// ;
      $listUsers = $userManager->findUsers();

        return $this->render('MMPlatformBundle:Admin:index.html.twig', array(
          'listArticles' => $listArticles,
          'listImages' => $listImages,
          'listVideos' => $listVideos,
          'listComments' => $listComments,
          'listUsers' => $listUsers,
    	));
    }

  public function promoteUserAction($id) {
    $userManager = $this->get('fos_user.user_manager');
    $user = $userManager->findUserBy(array('id' => $id));
    $currentRole = $user->getRoles();
    if($currentRole[0] == 'ROLE_AUTEUR'){
      $user->addRole('ROLE_MODERATEUR');
    }
    elseif($currentRole[0] == 'ROLE_MODERATEUR'){
      $user->addRole('ROLE_ADMIN');
    }
    elseif ($currentRole[0] == 'ROLE_ADMIN'){
    }
    else{ 
      $user->addRole('ROLE_AUTEUR');
    }
    $userManager->updateUser($user);
    $this->get('session')->getFlashBag()->add('success', 'The user was succesfully promoted.');
    return $this->redirect($this->generateUrl('mm_admin_home'));
  }

  public function demoteUserAction($id) {
    $userManager = $this->get('fos_user.user_manager');
    $user = $userManager->findUserBy(array('id' => $id));
    $currentRole = $user->getRoles();
    if($currentRole[0] == 'ROLE_AUTEUR'){
      $user->removeRole('ROLE_AUTEUR');
    }
    elseif($currentRole[0] == 'ROLE_MODERATEUR'){
      $user->removeRole('ROLE_MODERATEUR');
    }
    elseif ($currentRole[0] == 'ROLE_ADMIN'){
      $user->removeRole('ROLE_ADMIN');
    }
    else{ 
    }
    $userManager->updateUser($user);
    $this->get('session')->getFlashBag()->add('success', 'The user was succesfully demoted.');
    return $this->redirect($this->generateUrl('mm_admin_home'));
  }

  public function deleteUserAction($id) {
    $userManager = $this->get('fos_user.user_manager');
    $user = $userManager->findUserBy(array('id' => $id));
    $userManager->deleteUser($user);
    $this->get('session')->getFlashBag()->add('success', 'The user was succesfully removed.');
    return $this->redirect($this->generateUrl('mm_admin_home'));
  }

  public function editCommentAction(Comment $comment, Request $request) {
      $form = $this->get('form.factory')->create(CommentType::class, $comment);

      if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      // Inutile de persister ici, Doctrine connait déjà notre annonce
      $em = $this->getDoctrine()->getManager();
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Commentaire bien modifié.');

      return $this->redirectToRoute('mm_platform_view', array('id' => $comment->getArticle()->getId()));
    }

    return $this->render('MMPlatformBundle:Comment:edit.html.twig', array(
      'comment' => $comment,
      'form'   => $form->createView(),
    ));
  }

  public function deleteCommentAction(Comment $comment, Request $request) {
    $em = $this->getDoctrine()->getManager();

    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression d'annonce contre cette faille
    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->remove($comment);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', "Le commentaire a bien été supprimé.");

      return $this->redirectToRoute('mm_platform_home');
    }
    
    return $this->render('MMPlatformBundle:Comment:delete.html.twig', array(
      'comment' => $comment,
      'form'   => $form->createView(),
    ));
  }
}