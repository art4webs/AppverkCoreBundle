<?php

namespace Cube\CoreBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\UserBundle\Admin\Entity\UserAdmin as BaseUserAdmin;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAdmin extends BaseUserAdmin
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('export');
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username')
            ->add('email')
            ->add('enabled', null, ['editable' => true, 'ajax_hidden' => true])
            ->add('locked', null, ['editable' => true, 'ajax_hidden' => true])
            ->add('createdAt');
    }
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('General')
            ->add('username')
            ->add('email')
            ->end()
            ->with('Groups')
            ->add('groups')
            ->end()
            ->with('Profile')
            ->add('firstname')
            ->add('lastname')
            ->add('phone')
            ->end();
    }
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('General')
            ->with('General', ['class' => 'col-md-6'])
            ->add('username')
            ->add('email')
            ->add('plainPassword', 'text', [
                'required' => (!$this->getSubject() || is_null($this->getSubject()->getId()))
            ])
            ->end()
            ->with('Profile', ['class' => 'col-md-6'])
            ->add('firstname', null, ['required' => false])
            ->add('lastname', null, ['required' => false])
            ->add('phone', null, ['required' => false])
            ->end()
            ->end();
        $formMapper
            ->tab('Management')
            ->with('Groups', ['class' => 'col-md-6'])
            ->add('groups', 'sonata_type_model', [
                'required' => false,
                'expanded' => true,
                'multiple' => true
            ])
            ->end();
        $loggedUser = $this->getUser();
        if ($loggedUser && $loggedUser->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper
                ->with('Management', ['class' => 'col-md-6'])
                ->add('realRoles', 'sonata_security_roles', [
                    'label'    => 'form.label_roles',
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false
                ])
                ->add('locked', null, ['required' => false])
                ->add('expired', null, ['required' => false])
                ->add('enabled', null, ['required' => false])
                ->add('credentialsExpired', null, ['required' => false])
                ->end();
        }
        $formMapper->end();
    }
    public function preUpdate($user)
    {

    }
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    protected function getTokenStorage()
    {
        return $this->tokenStorage;
    }
    protected function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
