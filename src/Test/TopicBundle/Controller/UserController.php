<?php

namespace Test\TopicBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Test\TopicBundle\Entity\User;
use Test\TopicBundle\Entity\Role;
use Test\TopicBundle\Form\UserType;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UserController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('TestTopicBundle:User')->findAll();

        return $this->render('TestTopicBundle:User:index.html.twig', array(
            'users' => $users,
        ));
    }

    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('TestTopicBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User user.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TestTopicBundle:User:show.html.twig', array(
            'user'      => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function newAction()
    {
        $user = new User();
        $form   = $this->createForm(new UserType(), $user);

        return $this->render('TestTopicBundle:User:new.html.twig', array(
            'user' => $user,
            'form'   => $form->createView(),
        ));
    }

    public function createAction(Request $request)
    {
        $user  = new User();
        $form = $this->createForm(new UserType(), $user);
        $form->bind($request);
       
        // шифрует и устанавливает пароль для пользователя,
        // эти настройки совпадают с конфигурационными файлами
        $encoder = new MessageDigestPasswordEncoder('sha1', false, 1);
        $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
        $user->setPassword($password);
 
//        $user->getUserRoles()->add($role);
 
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('user_show', array('id' => $user->getId())));
        }

        return $this->render('TestTopicBundle:User:show.html.twig', array(
            'user' => $user,
            'form'   => $form->createView(),
        ));
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('TestTopicBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User user.');
        }

        $editForm = $this->createForm(new UserType(), $user);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TestTopicBundle:User:edit.html.twig', array(
            'user'      => $user,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('TestTopicBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User user.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new UserType(), $user);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return $this->render('TestTopicBundle:User:edit.html.twig', array(
            'user'      => $user,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('TestTopicBundle:User')->find($id);

            if (!$user) {
                throw $this->createNotFoundException('Unable to find User user.');
            }

            $em->remove($user);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
