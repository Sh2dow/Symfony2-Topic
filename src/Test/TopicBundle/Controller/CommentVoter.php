<?php

namespace Test\TopicBundle\Controller;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Test\TopicBundle\Entity\Comment;

class CommentVoter implements VoterInterface {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function supportsAttribute($attribute) {
        return $attribute === 'EDIT';
    }

    public function supportsClass($class) {
        return true;
    }

    /**
     * Checks whether or not the current user can edit a topic or comment.
     * 
     * Users with the role ROLE_ADMIN may always edit.
     * 
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes) {
        
        if ( !($object instanceof Comment) ) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // Only supports 'EDIT' for now.
//        if ( !$this->supportsAttribute($attributes[0]) ) {
//            return VoterInterface::ACCESS_ABSTAIN;
//        }

        $user = $token->getUser();
        if ( !($user instanceof UserInterface) ) {
            return VoterInterface::ACCESS_DENIED;
        }

        // Is the token a Admin?
        if ( $this->container->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // Is the token the author of the post and within the edit window.
        $originalAuthor = $object->getUser();
        if ( $originalAuthor->equals($user) ) 
        {
                return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

}