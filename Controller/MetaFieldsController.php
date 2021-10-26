<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Controller;

use App\Controller\AbstractController;
use App\Plugin\PluginManager;
use Exception;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use KimaiPlugin\MetaFieldsBundle\Form\MetaFieldRuleForm;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use KimaiPlugin\MetaFieldsBundle\Repository\MetaFieldRuleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/admin/custom-meta-fields")
 * @Security("is_granted('configure_meta_fields')")
 */
final class MetaFieldsController extends AbstractController
{
    /**
     * @var MetaFieldRuleRepository
     */
    protected $repository;

    public function __construct(MetaFieldRuleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route(path="", name="custom_meta_fields", methods={"GET", "POST"})
     */
    public function indexAction(PluginManager $pluginManager): Response
    {
        $rules = $this->repository->getRules();

        $all = [];

        foreach (MetaFieldsRegistry::getAllEntityTypes() as $entityType) {
            $all[$entityType] = [
                'title' => MetaFieldsRegistry::mapEntityTypeToTitle($entityType),
                'type' => MetaFieldsRegistry::mapEntityTypeToExternalName($entityType),
                'rules' => []
            ];
        }

        foreach ($rules as $rule) {
            $all[$rule->getEntityType()]['rules'][] = $rule;
        }

        if ($pluginManager->getPlugin('ExpensesBundle') === null) {
            unset($all[MetaFieldsRegistry::EXPENSE_ENTITY]);
        }

        return $this->render('@MetaFields/index.html.twig', ['types' => $all]);
    }

    /**
     * @Route(path="/{id}/edit", name="custom_meta_fields_edit", methods={"GET", "POST"})
     */
    public function editAction(MetaFieldRule $rule, Request $request): Response
    {
        return $this->renderEditForm($rule, $request, null);
    }

    /**
     * @Route(path="/create", name="custom_meta_fields_create", methods={"GET", "POST"})
     */
    public function createAction(Request $request): Response
    {
        $rule = new MetaFieldRule();

        $entityType = $request->get('entityType');

        $type = MetaFieldsRegistry::mapExternalNameToEntityType($entityType);
        $rule->setEntityType($type);

        return $this->renderEditForm($rule, $request, $entityType);
    }

    /**
     * @Route(path="/{id}/delete", name="custom_meta_fields_delete", methods={"GET", "POST"})
     */
    public function deleteAction(MetaFieldRule $rule, Request $request): Response
    {
        $deleteForm = $this->createFormBuilder(null, [
                'attr' => [
                    'data-form-event' => 'kimai.metaFieldRuleUpdate'
                ],
            ])
            ->setAction($this->generateUrl('custom_meta_fields_delete', ['id' => $rule->getId()]))
            ->setMethod('POST')
            ->getForm();

        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            try {
                $this->repository->deleteRule($rule);
                $this->flashSuccess('action.delete.success');
            } catch (Exception $ex) {
                $this->flashError('action.delete.error', ['%reason%' => $ex->getMessage()]);
            }

            return $this->redirectToRoute('custom_meta_fields');
        }

        return $this->render(
            '@MetaFields/delete.html.twig',
            [
                'rule' => $rule,
                'counter' => $this->repository->countRuleUsages($rule),
                'form' => $deleteForm->createView(),
            ]
        );
    }

    private function renderEditForm(MetaFieldRule $rule, Request $request, ?string $entityType): Response
    {
        $editForm = $this->createEditForm($rule, $entityType);
        $editForm->handleRequest($request);

        if (!\in_array($rule->getEntityType(), MetaFieldsRegistry::getAllEntityTypes())) {
            throw new \RuntimeException(
                sprintf('Invalid type given: %s', null === $rule->getEntityType() ? 'null' : (string) $rule->getEntityType())
            );
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                $this->repository->saveRule($rule);
                $this->flashSuccess('action.update.success');

                return $this->redirectToRoute('custom_meta_fields');
            } catch (Exception $ex) {
                $this->flashError('action.update.error', ['%reason%' => $ex->getMessage()]);
            }
        }

        return $this->render(
            '@MetaFields/edit.html.twig',
            [
                'rule' => $rule,
                'form' => $editForm->createView(),
                'entityType' => $rule->getEntityType()
            ]
        );
    }

    private function createEditForm(MetaFieldRule $entity, ?string $entityType = null): FormInterface
    {
        if (null === $entity->getId()) {
            $url = $this->generateUrl('custom_meta_fields_create', ['entityType' => $entityType]);
        } else {
            $url = $this->generateUrl('custom_meta_fields_edit', ['id' => $entity->getId()]);
        }

        return $this->createForm(MetaFieldRuleForm::class, $entity, [
            'action' => $url,
        ]);
    }
}
