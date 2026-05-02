<?php

namespace App\Form;

use App\Entity\Site;
use App\Model\Notifier\Notifier;
use App\Service\Notifier\NotifierHandlerCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteType extends AbstractType
{
    public function __construct(
        private readonly NotifierHandlerCollection $notifierHandlerCollection
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', options: ['label' => 'Название'])
            ->add('url', options: ['label' => 'Ссылка'])
            ->add('transport', ChoiceType::class, [
                'label' => 'Мессенджеры',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $this->notifierHandlerCollection->getListHandlers(),
                'choice_attr' => [
                    Notifier::TELEGRAM => ['checked' => 'checked']
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
        ]);
    }
}
