<?php

namespace MM\PlatformBundle\Controller;

use MM\PlatformBundle\Entity\Video;
use MM\PlatformBundle\Form\VideoType;
use MM\PlatformBundle\Form\VideoEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class VideoController extends Controller
{
	public function viewAction($id)
  	{
	    $em = $this->getDoctrine()->getManager();

	    $video = $em->getRepository('MMPlatformBundle:Video')->find($id);

	    if (null === $video) {
	      throw new NotFoundHttpException("La video d'id ".$id." n'existe pas.");
	    }

	    return $this->render('MMPlatformBundle:Video:view.html.twig', array(
	      'video'           => $video
	    ));
	}

  public function viewAllAction($page)
  {
    // On ne sait pas combien de pages il y a
    // Mais on sait qu'une page doit être supérieure ou égale à 1
    if ($page < 1) {
      // On déclenche une exception NotFoundHttpException, cela va afficher
      // une page d'erreur 404 (qu'on pourra personnaliser plus tard d'ailleurs)
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
    }

    // Ici je fixe le nombre d'annonces par page à 3
    // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
    $nbPerPage = 10;

    // On récupère notre objet Paginator
    $listVideos = $this->getDoctrine()
      ->getManager()
      ->getRepository('MMPlatformBundle:Video')
      ->findByPage($page, $nbPerPage)
    ;

    // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
    $nbPages = ceil(count($listVideos) / $nbPerPage);

    // Si la page n'existe pas, on retourne une 404
    // if ($page > $nbPages) {
    //   throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    // }

    // On donne toutes les informations nécessaires à la vue
    return $this->render('MMPlatformBundle:Video:list.html.twig', array(
      'listVideos' => $listVideos,
      'nbPages'     => $nbPages,
      'page'        => $page,
    ));
  }

	/**
   	 * @Security("has_role('ROLE_AUTEUR')")
   	 */
  	public function addAction(Request $request)
  	{
	    $video = new Video();
	    $video->setAuthor($this->getUser());

	    $form   = $this->createForm(VideoType::class, $video);

	    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
	      $em = $this->getDoctrine()->getManager();
	      $em->persist($video);
	      $em->flush();

	      $request->getSession()->getFlashBag()->add('notice', 'Video bien enregistrée.');

	      return $this->redirectToRoute('mm_platform_video_view', array('id' => $video->getId()));
	    }

	    return $this->render('MMPlatformBundle:Video:add.html.twig', array(
	      'form' => $form->createView(),
	    ));
  	}

  	public function editAction(Video $video, Request $request)
  {
    $form = $this->get('form.factory')->create(VideoEditType::class, $video);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      // Inutile de persister ici, Doctrine connait déjà notre annonce
      $em = $this->getDoctrine()->getManager();
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Vidéo bien modifiée.');

      return $this->redirectToRoute('mm_platform_video_view', array('id' => $video->getId()));
    }

    return $this->render('MMPlatformBundle:Video:edit.html.twig', array(
      'video' => $video,
      'form'   => $form->createView(),
    ));
  }

  public function deleteAction(Request $request, Video $video)
  {
    $em = $this->getDoctrine()->getManager();

    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression d'annonce contre cette faille
    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->remove($video);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', "La vidéo a bien été supprimée.");

      return $this->redirectToRoute('mm_platform_home');
    }
    
    return $this->render('MMPlatformBundle:Video:delete.html.twig', array(
      'video' => $video,
      'form'   => $form->createView(),
    ));
  }
}