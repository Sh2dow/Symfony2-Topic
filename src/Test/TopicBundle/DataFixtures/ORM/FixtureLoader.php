<?php

namespace Test\TopicBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
//use Test\TopicBundle\Entity\Category;
use Test\TopicBundle\Entity\Topic;
//use Test\TopicBundle\Entity\Tag;
use Test\TopicBundle\Entity\User;
use Test\TopicBundle\Entity\Role;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class FixtureLoader implements FixtureInterface {

    public function load($manager) {
        // создание роли ROLE_ADMIN
        $role = new Role();
        $role->setName('ROLE_ADMIN');

        $manager->persist($role);

        // создание пользователя
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john@example.com');
        $user->setUsername('john.doe');
        $user->setSalt(md5(time()));

        // шифрует и устанавливает пароль для пользователя,
        // эти настройки совпадают с конфигурационными файлами
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        $password = $encoder->encodePassword('admin', $user->getSalt());
        $user->setPassword($password);

        $user->getUserRoles()->add($role);

        $manager->persist($user);

        // ...
    }

}