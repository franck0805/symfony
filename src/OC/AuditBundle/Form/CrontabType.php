<?php

namespace Jiwon\AuditBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\CallbackTransformer;

class CrontabType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recurrence', 'text', array(
                    'label' => 'Récurrence'))
            ->add('heure', 'text', array(
                    'label' => 'Heure'))
            ->add('type', 'choice', array(
                    'choices' => array('inventaire' => 'Inventaire', 'sauvegarde' => 'Sauvegarde'),
                    'label' => 'Type'))
            ->add('exports', 'entity', array(
                    'class' => 'JiwonAuditBundle:Export',
                    'multiple' => 'true',
                    'required' => 'false',
                    'label' => 'Exports'))
            ->add('enable', 'checkbox', array(
                    'label' => 'Enable'))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Jiwon\AuditBundle\Entity\Crontab',
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'crontab';
    }
}