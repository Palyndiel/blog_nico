<?php

namespace MM\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AboutController extends Controller
{
	public function indexAction() {
		/*$message = \Swift_Message::newInstance()
        ->setSubject('Hello Email')
        ->setFrom('noreply@legtux.com')
        ->setTo('nico72700@gmail.com')
        ->setBody(
            $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                'Emails/notificationComment.html.twig',
                array('name' => "Maxime")
            ),
            'text/html'
        )
    ;
    $this->get('mailer')->send($message);*/
    
	    return $this->render('MMPlatformBundle:About:index.html.twig');
	}
}