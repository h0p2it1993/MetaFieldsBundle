<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\API;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use KimaiPlugin\MetaFieldsBundle\Repository\MetaFieldRuleRepository;
use KimaiPlugin\MetaFieldsBundle\Validator\Constraints\MetaFieldEntityType;
use Nelmio\ApiDocBundle\Annotation\Security as ApiSecurity;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @SWG\Tag(name="MetaField")
 */
final class MetaFieldsController
{
    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;
    /**
     * @var MetaFieldRuleRepository
     */
    private $repository;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $security;

    public function __construct(ViewHandlerInterface $viewHandler, MetaFieldRuleRepository $repository, AuthorizationCheckerInterface $security)
    {
        $this->viewHandler = $viewHandler;
        $this->repository = $repository;
        $this->security = $security;
    }

    /**
     * Returns a collection of meta-fields
     *
     * @SWG\Response(
     *      response=200,
     *      description="Returns a collection of meta-fields",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/MetaFieldRule")
     *      )
     * )
     * @Rest\QueryParam(name="entity", requirements=@MetaFieldEntityType, strict=true, nullable=true, description="The type of object to fetch meta-fields for. Allowed values: timesheet, customer, project, activity, user, expense - returns all if not given (default: all)")
     * @Rest\Get(path="/metafields")
     *
     * @ApiSecurity(name="apiUser")
     * @ApiSecurity(name="apiToken")
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher): Response
    {
        $entityType = null;

        if (null !== ($type = $paramFetcher->get('entity'))) {
            $entityType = MetaFieldsRegistry::mapExternalNameToEntityType($type);
        }

        if (null === $entityType) {
            $rules = $this->repository->getRules();
        } else {
            $rules = $this->repository->findRulesForEntityType($entityType);
        }

        $data = [];
        foreach ($rules as $rule) {
            if ($rule->getPermission() !== null && !$this->security->isGranted($rule->getPermission())) {
                continue;
            }
            $data[] = $rule;
        }

        $view = new View($data, 200);
        $view->getContext()->setGroups(['Default', 'Collection', 'MetaField']);

        return $this->viewHandler->handle($view);
    }
}
