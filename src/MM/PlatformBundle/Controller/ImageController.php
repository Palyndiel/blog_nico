<?php

namespace MM\PlatformBundle\Controller;

use MM\PlatformBundle\Entity\Image;
use MM\PlatformBundle\Form\ImageType;
use MM\PlatformBundle\Form\ImageEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ImageController extends Controller
{
	public function viewAction($id)
  	{
	    $em = $this->getDoctrine()->getManager();

	    $image = $em->getRepository('MMPlatformBundle:Image')->find($id);

	    if (null === $image) {
	      throw new NotFoundHttpException("La photo d'id ".$id." n'existe pas.");
	    }

	    return $this->render('MMPlatformBundle:Image:view.html.twig', array(
	      'image'           => $image
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
    $listImages = $this->getDoctrine()
      ->getManager()
      ->getRepository('MMPlatformBundle:Image')
      ->findByPage($page, $nbPerPage)
    ;

    // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
    $nbPages = ceil(count($listImages) / $nbPerPage);

    // Si la page n'existe pas, on retourne une 404
    // if ($page > $nbPages) {
    //   throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    // }

    // On donne toutes les informations nécessaires à la vue
    return $this->render('MMPlatformBundle:Image:list.html.twig', array(
      'listImages' => $listImages,
      'nbPages'     => $nbPages,
      'page'        => $page,
    ));
  }

	/**
   	 * @Security("has_role('ROLE_AUTEUR')")
   	 */
  	public function addAction(Request $request)
  	{
	    $image = new Image();
	    $image->setAuthor($this->getUser());
        $image->setArticleLink(false);

	    $form   = $this->createForm(ImageType::class, $image);

	    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
	      $em = $this->getDoctrine()->getManager();
	      $em->persist($image);
	      $em->flush();

	      $request->getSession()->getFlashBag()->add('notice', 'Photo bien enregistrée.');

	      return $this->redirectToRoute('mm_platform_picture_view', array('id' => $image->getId()));
	    }

	    return $this->render('MMPlatformBundle:Image:add.html.twig', array(
	      'form' => $form->createView(),
	    ));
  	}

  	public function editAction(Image $image, Request $request)
  {
    $form = $this->get('form.factory')->create(ImageEditType::class, $image);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      // Inutile de persister ici, Doctrine connait déjà notre annonce
      $em = $this->getDoctrine()->getManager();
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Photo bien modifiée.');

      return $this->redirectToRoute('mm_platform_picture_view', array('id' => $image->getId()));
    }

    return $this->render('MMPlatformBundle:Image:edit.html.twig', array(
      'image' => $image,
      'form'   => $form->createView(),
    ));
  }

  public function deleteAction(Request $request, Image $image)
  {
    $em = $this->getDoctrine()->getManager();

    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression d'annonce contre cette faille
    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->remove($image);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', "La photo a bien été supprimée.");

      return $this->redirectToRoute('mm_platform_home');
    }
    
    return $this->render('MMPlatformBundle:Image:delete.html.twig', array(
      'image' => $image,
      'form'   => $form->createView(),
    ));
  }
}