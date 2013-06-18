<?php

namespace Test\TopicBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class SubscriptionVoter implements VoterInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, 'SUBSCRIPTION_');
    }

    public function supportsClass($class)
    {
        return true;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $user = $token->getUser();

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $securedArea = substr($attribute, strlen('SUBSCRIPTION_'));

            // use the entity manager to query for active
            // subscriptions that connect the current user to the
            // requested secured area
            // $success = ...

            if ($success) {
                return VoterInterface::ACCESS_GRANTED;
            }

            $result = VoterInterface::ACCESS_DENIED;
        }

        return $result;
    }
}