<?php

namespace Test\TopicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Test\TopicBundle\Entity\Comment;
use Test\TopicBundle\Form\CommentType;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Comment controller.
 */
class CommentController extends Controller {

    /**
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $comments = $em->getRepository('TestTopicBundle:Comment')->findAll();

        return array('comments' => $comments);
    }
    
    public function newAction($topic_id) {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        
        $topic = $this->getTopic($topic_id);

        $comment = new Comment();
        $comment->setUser($this->get('security.context')->getToken()->getUser());
        $comment->setTopic($topic);
        $form = $this->createForm(new CommentType(), $comment);

        return $this->render('TestTopicBundle:Comment:form.html.twig', array(
                    'comment' => $comment,
                    'form' => $form->createView()
        ));
    }

    public function createAction($topic_id) {
        $topic = $this->getTopic($topic_id);

        $comment = new Comment();
        $comment->setUser($this->get('security.context')->getToken()->getUser());
        $comment->setTopic($topic);
        $request = $this->getRequest();
        $form = $this->createForm(new CommentType(), $comment);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()
                    ->getManager();
            $em->persist($comment);
            $em->flush();
            
             // creating the ACL
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($comment);
            $acl = $aclProvider->createAcl($objectIdentity);

            // retrieving the security identity of the currently logged-in user
            $securityContext = $this->get('security.context');
            $user = $securityContext->getToken()->getUser();
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            // grant owner access
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);

            return $this->redirect($this->generateUrl('TestTopicBundle_topic_show', array(
                                'id' => $comment->getTopic()->getId(),
                                '#comment-' . $comment->getId()
                                    )
            ));
        }

        return $this->render('TestTopicBundle:Comment:create.html.twig', array(
                    'comment' => $comment,
                    'form' => $form->createView()
        ));
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
    
    public function editAction($id) {
        $securityContext = $this->get('security.context');

        // check for edit access
        if (false === $securityContext->isGranted('EDIT', $id)) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $comment = $em->getRepository('TestTopicBundle:Comment')->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Unable to find Comment.');
        }

        $editForm = $this->createForm(new CommentType(), $comment);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TestTopicBundle:Comment:edit.html.twig', array(
                    'comment' => $comment,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }
    
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $comment = $em->getRepository('TestTopicBundle:Comment')->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Unable to find Comment.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new TopicType(), $comment);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($comment);
            $em->flush();

            return $this->redirect($this->generateUrl('TestTopicBundle_topic'));
        }

        return $this->render('TestTopicBundle:Comment:edit.html.twig', array(
                    '$comment' => $comment,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    public function deleteAction($id) {
        
        $securityContext = $this->get('security.context');

        // check for delete access
        if (false === $securityContext->isGranted('DELETE', $id)) {
            throw new AccessDeniedException();
        }
        
//        $topic = $this->getTopic($topic_id);
        $form = $this->createDeleteForm($id);
//        $form->bind($request);

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('TestTopicBundle:Comment')->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Unable to find Comment.');
        }

        $em->remove($comment);
        $em->flush();

        return $this->redirect($this->generateUrl('TestTopicBundle_topic'));
//        return $this->redirect($this->generateUrl('TestTopicBundle_topic_show', array('topic_id' => $topic_id)));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

}
