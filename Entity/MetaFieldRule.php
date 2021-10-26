<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Entity;

use App\Entity\Activity;
use App\Entity\Customer;
use App\Entity\Project;
use App\Form\Type\ColorPickerType;
use App\Form\Type\DurationType;
use App\Form\Type\YesNoType;
use Doctrine\ORM\Mapping as ORM;
use KimaiPlugin\MetaFieldsBundle\Form\Type\DatePickerType;
use KimaiPlugin\MetaFieldsBundle\Form\Type\DateTimePickerType;
use KimaiPlugin\MetaFieldsBundle\Form\Type\InvoiceTemplateType;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="KimaiPlugin\MetaFieldsBundle\Repository\MetaFieldRuleRepository")
 * @ORM\Table(name="kimai2_meta_field_rules",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="meta_field_rule_entity_type_name_uniq", columns={"entity_type", "name"})
 *  },
 *  indexes={
 *      @ORM\Index(name="meta_field_rule_entity_type_idx", columns={"entity_type"}),
 *  }
 * )
 * @UniqueEntity({"name", "entityType"})
 * @KimaiPlugin\MetaFieldsBundle\Validator\Constraints\MetaFieldRule
 */
class MetaFieldRule
{
    public const CHOICE_TYPE = 'choice';
    public const CHOICE_TYPE_MULTIPLE = 'choice-multiple';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(name="entity_type", type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=100)
     */
    private $entityType;
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(min=1, max=50)
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=50, nullable=true)
     * @Assert\Length(max=50)
     */
    private $label;
    /**
     * @var string
     *
     * @ORM\Column(name="help", type="string", length=200, nullable=true)
     * @Assert\Length(max=200)
     */
    private $help;
    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=false)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     */
    private $type;
    /**
     * @var Customer|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $customer;
    /**
     * @var Project|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Project")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $project;
    /**
     * @var Activity|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $activity;
    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     * @Assert\NotNull()
     */
    private $visible = true;
    /**
     * @var bool
     *
     * @ORM\Column(name="required", type="boolean", nullable=false)
     * @Assert\NotNull()
     */
    private $required = false;
    /**
     * @var string
     *
     * @ORM\Column(name="permission", type="string", length=100, nullable=true)
     * @Assert\Length(max=100)
     */
    private $permission = null;
    /**
     * For grouping values in separate sections (currently only supported by user-preferences).
     *
     * @var string|null
     *
     * @ORM\Column(name="section", type="string", length=100, nullable=true)
     * @Assert\Length(max=100)
     */
    private $section = null;
    /**
     * @var int
     *
     * @ORM\Column(name="weight", type="integer", nullable=false, options={"default": 0})
     * @Assert\NotNull()
     */
    private $weight = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isRuleForUser(): bool
    {
        return $this->entityType === MetaFieldsRegistry::USER_ENTITY;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): MetaFieldRule
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function isTimesheetRule(): bool
    {
        return $this->entityType === MetaFieldsRegistry::TIMESHEET_ENTITY;
    }

    public function isExpenseRule(): bool
    {
        return $this->entityType === MetaFieldsRegistry::EXPENSE_ENTITY;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): MetaFieldRule
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Only used in the MetaFieldRuleForm.
     * We cannot use getLabel(), as that displays (if unset) the name and doesn't save if it isn't changed.
     *
     * @param string|null $label
     * @return MetaFieldRule
     */
    public function setDisplayName(?string $label): MetaFieldRule
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Only used in the MetaFieldRuleForm.
     * We cannot use getLabel(), as that displays (if unset) the name and doesn't save if it isn't changed.
     *
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->label;
    }

    public function getLabel(): ?string
    {
        if (empty($this->label)) {
            return $this->name;
        }

        return $this->label;
    }

    public function setLabel(string $label): MetaFieldRule
    {
        $this->label = $label;

        return $this;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): MetaFieldRule
    {
        $this->help = $help;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): MetaFieldRule
    {
        $this->value = $value;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): MetaFieldRule
    {
        $this->type = $type;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): MetaFieldRule
    {
        $this->customer = $customer;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): MetaFieldRule
    {
        $this->project = $project;

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): MetaFieldRule
    {
        $this->activity = $activity;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): MetaFieldRule
    {
        $this->visible = $visible;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): MetaFieldRule
    {
        $this->required = $required;

        return $this;
    }

    public function getPermission(): ?string
    {
        return $this->permission;
    }

    public function setPermission(?string $permission): MetaFieldRule
    {
        $this->permission = $permission;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): MetaFieldRule
    {
        $this->section = $section;

        return $this;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): MetaFieldRule
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * USED IN API DEFINITION!
     *
     * @return string
     */
    public function getTypeIdentifier(): string
    {
        $values = explode('\\', $this->entityType);

        return strtolower(array_pop($values));
    }

    public function getMappedFieldType(): ?string
    {
        // before release 1.2 the form type class name was saved in the database
        $type = $this->getType();

        // with >= 1.2 we save only the field alias in the database
        switch ($type) {
            case 'text':
                $type = TextType::class;
                break;
            case 'textarea':
                $type = TextareaType::class;
                break;
            case 'email':
                $type = EmailType::class;
                break;
            case 'integer':
                $type = IntegerType::class;
                break;
            case 'duration':
                $type = DurationType::class;
                break;
            case 'money':
                $type = MoneyType::class;
                break;
            case 'number':
                $type = NumberType::class;
                break;
            case 'boolean':
                $type = YesNoType::class;
                break;
            case 'datetime':
                $type = DateTimePickerType::class;
                break;
            case 'date':
                $type = DatePickerType::class;
                break;
            case 'color':
                $type = ColorPickerType::class;
                break;
            case 'language':
                $type = LanguageType::class;
                break;
            case 'country':
                $type = CountryType::class;
                break;
            case 'currency':
                $type = CurrencyType::class;
                break;
            case 'url':
                $type = UrlType::class;
                break;
            case self::CHOICE_TYPE:
            case self::CHOICE_TYPE_MULTIPLE:
                $type = ChoiceType::class;
                break;
            case 'invoice-template':
                $type = InvoiceTemplateType::class;
                break;
        }

        return $type;
    }
}
