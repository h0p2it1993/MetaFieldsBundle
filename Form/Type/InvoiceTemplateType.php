<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Form\Type;

use App\Entity\InvoiceTemplate;
use App\Form\Type\InvoiceTemplateType as BaseInvoiceTemplateType;
use App\Repository\InvoiceTemplateRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class InvoiceTemplateType extends BaseInvoiceTemplateType implements DataTransformerInterface
{
    /**
     * @var InvoiceTemplateRepository
     */
    private $invoiceTemplateRepository;

    public function __construct(InvoiceTemplateRepository $invoiceTemplateRepository)
    {
        $this->invoiceTemplateRepository = $invoiceTemplateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addModelTransformer($this);
    }

    public function transform($value)
    {
        if (empty($value)) {
            return $value;
        }

        if ($value instanceof InvoiceTemplate) {
            return $value;
        }

        if (\is_string($value) || \is_int($value)) {
            $value = (int) $value;
            $tpl = $this->invoiceTemplateRepository->find($value);
            if (null !== $tpl) {
                return $tpl;
            }
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if ($value instanceof InvoiceTemplate) {
            return $value->getId();
        }

        return $value;
    }
}
