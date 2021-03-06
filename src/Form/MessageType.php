<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToHtml5LocalDateTimeTransformer;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class, [
                'label' => 'Votre nom'
            ])
            ->add('email',TextType::class, [
                'label' => 'Votre email'
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre rappel'
            ])
            // ->add('date', DateTimeType::class, [
            //     'widget' => 'single_text',
            //     'html5' => false,
            //     'attr' => ['class' => 'datetimepicker'],
            //     'mapped' => false,
            //     // 'date_format' => 'dd-MM-yyyy HH:mm',
                
            // ])
            ->add(
                $builder
                    ->create('date', DateTimeType::class, [
                        'widget' => 'single_text',
                        'html5' => false,
                        'attr' => ['class' => 'datetimepicker'],
                        // 'date_format' => 'dd-MM-yyyy HH:mm',
                        
                    ])
                    ->addViewTransformer(new CallbackTransformer(
                        $transform = function ($datetime) {
                            return $datetime;
                        },
                        $reverseTransform = function ($string) {
                            $string = date_create_from_format('d/m/Y H:i', $string)->format('m/d/Y H:i');
                            // dd($string);
                            return date(
                                DateTimeToHtml5LocalDateTimeTransformer::HTML5_FORMAT,
                                strtotime($string)
                            );
                        }
                    ))
            )
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn-w-100 btn-primary'],
                'label' => 'Enregistrer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
