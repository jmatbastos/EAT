<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
		    ->add('name', TextType::class, ['required' => false])
            ->add('email', EmailType::class, ['required' => false])
			->add('plainPassword', RepeatedType::class, [
			        'mapped' => false,
					'type' => PasswordType::class, 
					'required' => false, 
					'first_options'  => ['label' => 'Password'], 
					'second_options' => ['label' => 'Repeat Password'], 
					'invalid_message' => 'The password fields must match.',
					'constraints' => [
						new NotBlank([
							'message' => 'The password fields must not be blank.',
						]),
					],
				])
						
			->add('role', ChoiceType::class, 
				[
					'choices' => [
						'Teacher' => 'ROLE_TEACHER',
						'Student' => 'ROLE_STUDENT',						
					],
					'expanded' => true,
					'multiple'=>false,
					'mapped' => false,
					'choice_attr' => [
						'Teacher' => ['style' => 'margin:10px'],
						'Student' => ['style' => 'margin:10px'],
					],
				])	
		
		
        ;
		
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
