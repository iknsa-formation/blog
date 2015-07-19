<?php

namespace Blog\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Blog\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUser extends AbstractFixture implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->getContainer = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->getContainer->get('fos_user.user_manager');
     
        $khalid = $userManager->createUser();
        $khalid->setUsername('khalid');
        $khalid->setEmail('khalid@iknsa.com');
        $khalid->setPlainPassword('khalid');
        $khalid->setName("Sookia");
        $khalid->setFirstname('Khalid');
        $khalid->setEnabled(true);
        $khalid->setLastLogin(new \Datetime('2015-05-05 00:22:03'));
        $khalid->setRoles(array('ROLE_ADMIN'));
        $manager->persist($khalid);

        $user = $userManager->createUser();
        $user->setUsername('user');
        $user->setEmail('user@iknsa.com');
        $user->setPlainPassword('user');
        $user->setName("Iknsa");
        $user->setFirstname('user');
        $user->setEnabled(true);
        $user->setLastLogin(new \Datetime('2015-05-05 00:22:03'));
        $user->setRoles(array('ROLE_USER'));
        $manager->persist($user);

        $admin = $userManager->createUser();
        $admin->setUsername('admin');
        $admin->setEmail('admin@iknsa.com');
        $admin->setPlainPassword('admin');
        $admin->setName("Iknsa");
        $admin->setFirstname('admin');
        $admin->setEnabled(true);
        $admin->setLastLogin(new \Datetime('2015-05-05 00:22:03'));
        $admin->setRoles(array('ROLE_ADMIN'));
        $manager->persist($admin);

        $manager->flush();

        $this->addReference('admin-khalid', $khalid);
        $this->addReference('user-user', $user);
        $this->addReference('admin-admin', $admin);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}