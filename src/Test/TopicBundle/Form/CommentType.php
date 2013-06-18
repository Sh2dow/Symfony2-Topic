<?php

namespace Test\TopicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('user')
            ->add('comment')
//            ->add('topic')
        ;
    }
//
//    public function setDefaultOptions(OptionsResolverInterface $resolver)
//    {
//        $resolver->setDefaults(array(
//            'data_class' => 'Test\TopicBundle\Entity\Comment'
//        ));
//    }

    public function getName()
    {
        return 'test_topicbundle_commenttype';
    }
}
