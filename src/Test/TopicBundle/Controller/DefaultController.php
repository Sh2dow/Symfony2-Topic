<?php

namespace Test\TopicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('TestTopicBundle:Default:index.html.twig', array('name' => $name));
    }
}
