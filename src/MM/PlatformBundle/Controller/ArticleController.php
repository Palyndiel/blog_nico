<?php

// src/MM/PlatformBundle/Controller/ArticleController.php

namespace MM\PlatformBundle\Controller;

use MM\PlatformBundle\Entity\Article;
use MM\PlatformBundle\Entity\Video;
use MM\PlatformBundle\Form\ArticleType;
use MM\PlatformBundle\Form\ArticleEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;


class ArticleController extends Controller
{
  public function indexAction($limit)
  {
    $listArticles = $this->getDoctrine()
      ->getManager()
      ->getRepository('MMPlatformBundle:Article')
      ->findBy(
        array(),                 // Pas de critère
        array('date' => 'desc'), // On trie par date décroissante
        $limit,                  // On sélectionne $limit annonces
        0                        // À partir du premier
        );
    ;
    $listImages = $this->getDoctrine()
      ->getManager()
      ->getRepository('MMPlatformBundle:Image')
      ->findBy(
        array('articleLink' => '0'),
        array('id' => 'desc'), // On trie par date décroissante
        $limit,                  // On sélectionne $limit annonces
        0                        // À partir du premier
        );
    ;
    $listVideos = $this->getDoctrine()
      ->getManager()
      ->getRepository('MMPlatformBundle:Video')
      ->findBy(
        array(),
        array('id' => 'desc'), // On trie par date décroissante
        $limit,                  // On sélectionne $limit annonces
        0                        // À partir du premier
        );
    ;
    return $this->render('MMPlatformBundle:Article:index.html.twig', array(
      'listArticles' => $listArticles,
      'listImages'  => $listImages,
      'listVideos' => $listVideos,
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

    $listThreads = $this->getDoctrine()
      ->getManager()
      ->getRepository('MMPlatformBundle:Thread')
      ->findAll()
    ;
    
    // On récupère notre objet Paginator
    $listArticles = $this->getDoctrine()
      ->getManager()
      ->getRepository('MMPlatformBundle:Article')
      ->findByPage($page, $nbPerPage)
    ;

    // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
    $nbPages = ceil(count($listArticles) / $nbPerPage);

    // Si la page n'existe pas, on retourne une 404
    // if ($page > $nbPages) {
    //   throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    // }

    // On donne toutes les informations nécessaires à la vue
    return $this->render('MMPlatformBundle:Article:list.html.twig', array(
      'listArticles' => $listArticles,
      'nbPages'     => $nbPages,
      'page'        => $page,
      'listThreads'      => $listThreads,
    ));
  }

  public function viewAction($slug, Request $request)
  {
    $userManager = $this->get('fos_user.user_manager');
    $em = $this->getDoctrine()->getManager();
    $user = $this->getUser();
    $hasLiked = false;

    $article = $em->getRepository('MMPlatformBundle:Article')->findBySlug($slug);
    
    $article=$article[0];

    if (null === $article) {
      throw new NotFoundHttpException("L'article avec pour titre '".$slug."' n'existe pas.");
    }
      
    foreach ($article->getLikes() as $like) {
    if ($like == $user){
            $hasLiked = true;
      }
    }

    return $this->render('MMPlatformBundle:Article:view.html.twig', array(
      'article'           => $article,
      'hasLiked'          => $hasLiked,
    ));
  }

  /**
   * @Security("has_role('ROLE_AUTEUR')")
   */
  public function addAction(Request $request)
  {
    $article = new Article();
    $article->setAuthor($this->getUser());

    $form   = $this->createForm(ArticleType::class, $article);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($article);

      if($article->getImage()->getFile() != null)
      {
        $article->getImage()->setArticleLink(true);
        $article->getImage()->setTitle($form["title"]->getData());
        $article->getImage()->setDescription($form["resume"]->getData());
        $article->getImage()->setAuthor($this->getUser());

        $em->persist($article->getImage());
      }
      else
      {
        $article->setImage(null);
      }
        
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Article bien enregistrée.');

      return $this->redirectToRoute('mm_platform_view', array('id' => $article->getId()));
    }

    return $this->render('MMPlatformBundle:Article:add.html.twig', array(
      'form' => $form->createView(),
    ));
  }

  public function editAction(Article $article, Request $request)
  {
    $form = $this->get('form.factory')->create(ArticleEditType::class, $article);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      // Inutile de persister ici, Doctrine connait déjà notre annonce
      $em = $this->getDoctrine()->getManager();

      if($article->getImage()->getFile() != null)
      {
        $article->getImage()->setArticleLink(true);
        $article->getImage()->setTitle($form["title"]->getData());
        $article->getImage()->setDescription($form["resume"]->getData());
        $article->getImage()->setAuthor($this->getUser());

        $em->persist($article->getImage());
      }

      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Article bien modifiée.');

      return $this->redirectToRoute('mm_platform_view', array('id' => $article->getId()));
    }

    return $this->render('MMPlatformBundle:Article:edit.html.twig', array(
      'article' => $article,
      'form'   => $form->createView(),
    ));
  }

  public function deleteAction(Request $request, Article $article)
  {
    $em = $this->getDoctrine()->getManager();

    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression d'annonce contre cette faille
    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->remove($article);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', "L'article a bien été supprimée.");

      return $this->redirectToRoute('mm_platform_home');
    }
    
    return $this->render('MMPlatformBundle:Article:delete.html.twig', array(
      'article' => $article,
      'form'   => $form->createView(),
    ));
  }

  public function menuAction($limit)
  {
  	$em = $this->getDoctrine()->getManager();

    $listArticles = $em->getRepository('MMPlatformBundle:Article')->findBy(
      array(),                 // Pas de critère
      array('date' => 'desc'), // On trie par date décroissante
      $limit,                  // On sélectionne $limit annonces
      0                        // À partir du premier
    );

    return $this->render('MMPlatformBundle:Article:menu.html.twig', array(
      'listArticles' => $listArticles
    ));
  }
    
    public function likeAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $article = $em->getRepository('MMPlatformBundle:Article')->findById($request->request->get('id'));
    //if($request->isXmlHttpRequest())
    //{
      $article[0]->addLike($this->getUser());
      $em->flush();
    //}
    return new Response('done');
  }
    
    public function getlikeAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $article = $em->getRepository('MMPlatformBundle:Article')->findById($request->request->get('id'));
    //if($request->isXmlHttpRequest())
    //{
      $nbLikes = $article[0]->getNbLikes();
    //}
    return new Response($nbLikes);
  }
    
  public function dislikeAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $article = $em->getRepository('MMPlatformBundle:Article')->findById($request->request->get('id'));
    //if($request->isXmlHttpRequest())
    //{
      $article[0]->removeLike($this->getUser());
      $em->flush();
    //}
    return new Response('done');
  }
}