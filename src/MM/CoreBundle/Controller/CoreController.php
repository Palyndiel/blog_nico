<?php
namespace MM\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use MM\PlatformBundle\Entity\Article;
use MM\PlatformBundle\Entity\Video;

class CoreController extends Controller
{
  // La page d'accueil
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
    
    $nbItems = count($listArticles) + count($listImages) + count($listVideos);
      
    return $this->render('MMCoreBundle:Core:index.html.twig', array(
      'listArticles' => $listArticles,
      'listImages'  => $listImages,
      'listVideos' => $listVideos,
      'nbItems' => $nbItems,
    ));
  }
}