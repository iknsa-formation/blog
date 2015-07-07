<?php

namespace Blog\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    public function indexAction()
    {
        return $this->render('BlogAppBundle:App:index.html.twig', array());
    }
}
