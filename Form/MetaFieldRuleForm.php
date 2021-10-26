<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Form;

use App\Form\Type\ActivityType;
use App\Form\Type\CustomerType;
use App\Form\Type\ProjectType;
use App\Form\Type\YesNoType;
use App\Repository\ActivityRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProjectRepository;
use App\Repository\Query\ActivityFormTypeQuery;
use App\Repository\Query\CustomerFormTypeQuery;
use App\Repository\Query\ProjectFormTypeQuery;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use KimaiPlugin\MetaFieldsBundle\Form\Type\DatePickerType;
use KimaiPlugin\MetaFieldsBundle\Form\Type\DateTimePickerType;
use KimaiPlugin\MetaFieldsBundle\Form\Type\MetaFieldRuleEntityInputType;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

final class MetaFieldRuleForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $customer = null;
        $project = null;
        $activity = null;
        $entityType = null;
        $new = true;
        $valueFieldType = TextType::class;
        $valueFieldOptions = ['required' => false, 'label' => 'label.defaultValue'];
        $rule = null;

        if (null !== $options['data']) {
            /** @var MetaFieldRule $rule */
            $rule = $options['data'];
            $customer = $rule->getCustomer();
            $project = $rule->getProject();
            $activity = $rule->getActivity();
            $entityType = $rule->getEntityType();
            $new = $rule->getId() === null;
            if (null !== $rule->getType() && !\in_array($rule->getType(), [MetaFieldRule::CHOICE_TYPE, MetaFieldRule::CHOICE_TYPE_MULTIPLE])) {
                $valueFieldType = $rule->getMappedFieldType();
            }
        }

        if ($valueFieldType === DatePickerType::class || $valueFieldType === DateTimePickerType::class) {
            $valueFieldOptions['model_timezone'] = $options['timezone'];
            $valueFieldOptions['view_timezone'] = $options['timezone'];
        }

        $builder
            ->add('displayName', TextType::class, [
                'label' => 'label.name',
                'required' => false,
                'constraints' => [
                    new Length(['min' => 3, 'max' => 50])
                ],
            ])
        ;

        if ($new) {
            $builder
                ->add('name', TextType::class, [
                    'label' => 'label.key',
                    'help' => 'label.key_help',
                    'constraints' => [
                        new Length(['min' => 3, 'max' => 50]),
                        new Regex(['pattern' => '/^[0-9a-z_]{3,50}$/'])
                    ],
                    'attr' => [
                        'maxlength' => 50
                    ],
                ])
                ->add('entityType', HiddenType::class, [
                    'constraints' => [
                        new Choice(['choices' => MetaFieldsRegistry::getAllEntityTypes()])
                    ]
                ])
            ;
        }

        $builder
            ->add('help', TextType::class, [
                'label' => 'label.help',
                'help' => 'label.help_help',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 200])
                ],
            ])
        ;

        if ($new) {
            $builder->add('type', MetaFieldRuleEntityInputType::class, [
                'label' => 'label.mf_inputType',
                'help' => 'help.mf_inputType',
            ]);
        }

        $builder->add('value', $valueFieldType, $valueFieldOptions);

        $valueTransformer = new CallbackTransformer(
            function ($transform) use ($rule) {
                switch ($rule->getType()) {
                    case 'boolean':
                        return (bool) $transform;
                    case 'datetime':
                    case 'date':
                        if (!empty($transform)) {
                            try {
                                new \DateTime($transform);
                            } catch (\Exception $ex) {
                                return '';
                            }
                        }
                        break;
                }

                return $transform;
            },
            function ($reverseTransform) use ($rule) {
                switch ($rule->getType()) {
                    case 'boolean':
                        if (false === $reverseTransform || null === $reverseTransform || '0' === $reverseTransform || '' === $reverseTransform) {
                            return '0';
                        } else {
                            return '1';
                        }
                        // no break
                    case 'datetime':
                    case 'date':
                        if (!empty($reverseTransform)) {
                            try {
                                new \DateTime($reverseTransform);
                            } catch (\Exception $ex) {
                                return '';
                            }
                        }
                        break;
                }

                return $reverseTransform;
            }
        );

        $builder->get('value')->addModelTransformer($valueTransformer);

        /*
         * EXPENSES HAVE NO LIMITATIONS FOR CUSTOMER/PROJECT/ACTIVITY
         */
        if ($new) {
            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) use ($builder, $customer, $project, $activity) {
                    $data = $event->getData();
                    $entityType = $data['entityType'];

                    if (\in_array($entityType, [MetaFieldsRegistry::TIMESHEET_ENTITY, MetaFieldsRegistry::PROJECT_ENTITY, MetaFieldsRegistry::ACTIVITY_ENTITY])) {
                        $event->getForm()
                            ->add('customer', CustomerType::class, [
                                'query_builder' => function (CustomerRepository $repo) use ($builder, $customer) {
                                    $query = new CustomerFormTypeQuery($customer);
                                    $query->setUser($builder->getOption('user'));

                                    return $repo->getQueryBuilderForFormType($query);
                                },
                                'data' => $customer ? $customer : '',
                                'required' => false,
                                'placeholder' => null === $customer ? '' : null,
                                'project_enabled' => true,
                            ]);
                    }

                    if (\in_array($entityType, [MetaFieldsRegistry::TIMESHEET_ENTITY, MetaFieldsRegistry::ACTIVITY_ENTITY])) {
                        $event->getForm()
                            ->add('project', ProjectType::class, [
                                'placeholder' => '',
                                'activity_enabled' => true,
                                'query_builder' => function (ProjectRepository $repo) use ($builder, $project, $customer) {
                                    $query = new ProjectFormTypeQuery($project, $customer);
                                    $query->setUser($builder->getOption('user'));

                                    return $repo->getQueryBuilderForFormType($query);
                                },
                                'required' => false,
                            ]);
                    }

                    if (\in_array($entityType, [MetaFieldsRegistry::TIMESHEET_ENTITY])) {
                        $event->getForm()
                            ->add('activity', ActivityType::class, [
                                'placeholder' => '',
                                'query_builder' => function (ActivityRepository $repo) use ($activity, $project) {
                                    $query = new ActivityFormTypeQuery($activity, $project);

                                    return $repo->getQueryBuilderForFormType($query);
                                },
                                'required' => false
                            ]);
                    }
                }
            );
        }

        if (\in_array($entityType, [MetaFieldsRegistry::TIMESHEET_ENTITY, MetaFieldsRegistry::PROJECT_ENTITY, MetaFieldsRegistry::ACTIVITY_ENTITY])) {
            $builder
                ->add('customer', CustomerType::class, [
                    'query_builder' => function (CustomerRepository $repo) use ($builder, $customer) {
                        $query = new CustomerFormTypeQuery($customer);
                        $query->setUser($builder->getOption('user'));

                        return $repo->getQueryBuilderForFormType($query);
                    },
                    'data' => $customer ? $customer : '',
                    'required' => false,
                    // removed the following line, as customers could not be resetted otherwise
                    // 'placeholder' => null === $customer ? '' : null,
                    'project_enabled' => true,
                ]);
        }

        if (\in_array($entityType, [MetaFieldsRegistry::TIMESHEET_ENTITY, MetaFieldsRegistry::ACTIVITY_ENTITY])) {
            $builder
                ->add('project', ProjectType::class, [
                    'placeholder' => '',
                    'activity_enabled' => true,
                    'query_builder' => function (ProjectRepository $repo) use ($builder, $project, $customer) {
                        $query = new ProjectFormTypeQuery($project, $customer);
                        $query->setUser($builder->getOption('user'));

                        return $repo->getQueryBuilderForFormType($query);
                    },
                    'required' => false,
                ]);
        }

        if (\in_array($entityType, [MetaFieldsRegistry::TIMESHEET_ENTITY])) {
            $builder
                ->add('activity', ActivityType::class, [
                    'placeholder' => '',
                    'query_builder' => function (ActivityRepository $repo) use ($activity, $project) {
                        $query = new ActivityFormTypeQuery($activity, $project);

                        return $repo->getQueryBuilderForFormType($query);
                    },
                    'required' => false
                ]);
        }

        $builder
            ->add('required', YesNoType::class, [
                'label' => 'label.mf_required',
                'help' => 'help.mf_required',
            ])
            ->add('visible', YesNoType::class, [
                'label' => 'label.visible',
                'help' => 'help.mf_visibility',
            ]);

        $builder
            ->add('weight', IntegerType::class, [
                'label' => 'label.mf_weight',
                'help' => 'help.mf_weight',
            ]);

        if (\in_array($entityType, [MetaFieldsRegistry::USER_ENTITY])) {
            $builder
                ->add('section', TextType::class, [
                    'required' => false,
                    'label' => 'label.mf_section',
                    'help' => 'help.mf_section',
                ]);
        }

        $builder
            ->add('permission', TextType::class, [
                'label' => 'label.permission',
                'help' => 'help.permission',
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MetaFieldRule::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'custom_meta_fields_edit',
            'method' => 'POST',
            'timezone' => date_default_timezone_get(),
            'attr' => [
                'data-form-event' => 'kimai.metaFieldRuleUpdate'
            ],
        ]);
    }
}
