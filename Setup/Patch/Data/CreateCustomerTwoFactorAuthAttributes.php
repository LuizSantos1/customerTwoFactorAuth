<?php
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magetarian\CustomerTwoFactorAuth\Model\Config\Source\Provider;

/**
 * Class CreateCustomerTwoFactorAuthAttributes
 * Customer attributes for 2FA
 */
class CreateCustomerTwoFactorAuthAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * Constants
     */
    const PROVIDERS        = 'encoded_providers';
    const CONFIG           = 'encoded_config';
    const DEFAULT_PROVIDER = 'default_provider';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;

    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        SetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->createProviderAttribute();
        $this->createProviderConfigAttribute();
        $this->createDefaultProviderAttribute();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(
            Customer::ENTITY,
            self::PROVIDERS
        );
        $customerSetup->removeAttribute(
            Customer::ENTITY,
            self::DEFAULT_PROVIDER
        );
        $customerSetup->removeAttribute(
            Customer::ENTITY,
            self::CONFIG
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    private function createProviderAttribute()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(
            Customer::ENTITY
        );
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet Set */
        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::PROVIDERS,
            [
                'label'    => 'Two Factor Auth Providers',
                'input'    => 'multiselect',
                'type'     => 'varchar',
                'source'   => Provider::class,
                'required' => false,
                'position' => 100,
                'visible'  => true,
                'system'   => false,
                'backend'  => ArrayBackend::class
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::PROVIDERS
        );

        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms'      => [
                'adminhtml_customer',
                'customer_account_edit'
            ]
        ]);

        $attribute->save();
    }

    /**
     *
     */
    private function createProviderConfigAttribute()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(
            Customer::ENTITY
        );
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet Set */
        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::CONFIG,
            [
                'label'     => 'Encoded Config',
                'input'     => 'textarea',
                'type'      => 'text',
                'required'  => false,
                'position'  => 101,
                'visible'   => false,
                'system'    => false,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::CONFIG
        );

        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms'      => [
                'customer_account_edit'
            ]
        ]);

        $attribute->save();
    }

    private function createDefaultProviderAttribute()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(
            Customer::ENTITY
        );
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet Set */
        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::DEFAULT_PROVIDER,
            [
                'label'     => 'Default Provider',
                'input'     => 'text',
                'type'      => 'varchar',
                'required'  => false,
                'position'  => 101,
                'visible'   => false,
                'system'    => false,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::DEFAULT_PROVIDER
        );

        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms'      => [
                'customer_account_edit'
            ]
        ]);

        $attribute->save();
    }
}
