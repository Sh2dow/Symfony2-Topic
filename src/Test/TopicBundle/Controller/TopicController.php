<?php

namespace Test\TopicBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Test\TopicBundle\Entity\Topic;
use Test\TopicBundle\Form\TopicType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Test\TopicBundle\Controller\Voter;

/**
 * Topic controller.
 *
 */
class TopicController extends Controller {

    /**
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $topics = $em->getRepository('TestTopicBundle:Topic')->findAll();

        return array('topics' => $topics);
    }

    /**
     * Displays a form to create a new Topic topic.
     *
     */
    public function newAction() {
        if (!$this->get('security.context')->isGranted('ROLE_AUTHOR')) {
            throw new AccessDeniedException();
        }

        $request = $this->get('request');
        $em = $this->get('doctrine')->getEntityManager();

        $topic = new Topic();
        $user = $this->get('security.context')->isGranted('ROLE_USER') ? $this->get('security.context')->getToken()->getUser() : null;
        $topic->setUser($user);
//        $topic->setUser($this->get('security.context')->getToken()->getUser());
        $form = $this->createForm(new TopicType(), $topic);

        return $this->render('TestTopicBundle:Topic:new.html.twig', array(
                    'topic' => $topic,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new Topic topic.
     *
     */
    public function createAction(Request $request) {
        $topic = new Topic();
        $topic->setUser($this->get('security.context')->getToken()->getUser());
        $form = $this->createForm(new TopicType(), $topic);
        $form->bind($request);


        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($topic);
            $em->flush();

            // creating the ACL
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($topic);
            $acl = $aclProvider->createAcl($objectIdentity);

            // retrieving the security identity of the currently logged-in user
            $securityContext = $this->get('security.context');
            $user = $securityContext->getToken()->getUser();
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            // grant owner access
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);

            return $this->redirect($this->generateUrl('TestTopicBundle_topic_show', array('id' => $topic->getId())));
        }

        return $this->render('TestTopicBundle:Topic:new.html.twig', array(
                    'topic' => $topic,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Topic topic.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $topic = $em->getRepository('TestTopicBundle:Topic')->find($id);

        if (!$topic) {
            throw $this->createNotFoundException('Unable to find Topic topic.');
        }

        $comments = $em->getRepository('TestTopicBundle:Comment')
                ->getCommentsForTopic($topic->getId());

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TestTopicBundle:Topic:show.html.twig', array(
                    'topic' => $topic,
                    'comments' => $comments,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Topic topic.
     *
     */
    public function editAction($id) {
        $securityContext = $this->get('security.context');
        
        // check for edit access
        if (!$securityContext->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $topic = $em->getRepository('TestTopicBundle:Topic')->find($id);

        if (!$topic) {
            throw $this->createNotFoundException('Unable to find Topic topic.');
        }

        $editForm = $this->createForm(new TopicType(), $topic);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TestTopicBundle:Topic:edit.html.twig', array(
                    'topic' => $topic,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Topic topic.
     *
     */
    public function updateAction(Request $request, $id) {
        
        $securityContext = $this->get('security.context');
        // check for edit access
        if (!$securityContext->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();

        $topic = $em->getRepository('TestTopicBundle:Topic')->find($id);

        if (!$topic) {
            throw $this->createNotFoundException('Unable to find Topic topic.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new TopicType(), $topic);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($topic);
            $em->flush();

            return $this->redirect($this->generateUrl('TestTopicBundle_topic_show', array('id' => $id)));
        }

        return $this->render('TestTopicBundle:Topic:edit.html.twig', array(
                    'topic' => $topic,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Topic topic.
     *
     */
    public function deleteAction(Request $request, $id) {
        $securityContext = $this->get('security.context');

        // check for edit access
        if (false === $securityContext->isGranted('ROLE_AUTHOR', $id)) {
            throw new AccessDeniedException();
        }

        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $topic = $em->getRepository('TestTopicBundle:Topic')->find($id);

            if (!$topic) {
                throw $this->createNotFoundException('Unable to find Topic topic.');
            }

            $em->remove($topic);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('TestTopicBundle_topic'));
    }

    /**
     * Creates a form to delete a Topic topic by id.
     *
     * @param mixed $id The topic id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

    protected function getTopic($topic_id) {
        $em = $this->getDoctrine()
                ->getManager();

        $topic = $em->getRepository('TestTopicBundle:Topic')->find($topic_id);

        if (!$topic) {
            throw $this->createNotFoundException('Unable to find Topic post.');
        }

        return $topic;
    }

}
