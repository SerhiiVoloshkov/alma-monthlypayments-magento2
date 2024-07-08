<?php

namespace Alma\MonthlyPayments\Test\Unit\Model\Adminhtml\Config\Insurance;

use Alma\MonthlyPayments\Helpers\InsuranceProductHelper;
use Alma\MonthlyPayments\Helpers\Logger;
use Alma\MonthlyPayments\Model\Adminhtml\Config\Insurance\ConfigurationSave;
use Alma\MonthlyPayments\Model\Exceptions\AlmaInsuranceProductException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;

class ConfigurationSaveTest extends TestCase
{
    private $context;
    private $registry;
    private $config;
    private $cacheTypeList;
    private $logger;
    private $productRepository;
    private $insuranceProductHelper;
    private $configurationSave;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->config = $this->createMock(ScopeConfigInterface::class);
        $this->cacheTypeList = $this->createMock(TypeListInterface::class);
        $this->logger = $this->createMock(Logger::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->insuranceProductHelper = $this->createMock(InsuranceProductHelper::class);
        $this->configurationSave = new ConfigurationSave(
            $this->context,
            $this->registry,
            $this->config,
            $this->cacheTypeList,
            $this->logger,
            $this->productRepository,
            $this->insuranceProductHelper
        );
    }

    public function testNoChangeDirectReturn(): void
    {
        $this->configurationSave->setValue($this->configFactory());
        $this->config->method('getValue')->willReturn($this->configFactory());
        $this->assertEquals($this->configurationSave, $this->configurationSave->beforeSave());
    }
    public function testisInsuranceActivatedNotExistInGetValueDirectReturn()
    {
        $this->configurationSave->setValue('{"active":true}');
        $this->config->method('getValue')->willReturn($this->configFactory());
        $this->assertEquals($this->configurationSave, $this->configurationSave->beforeSave());
    }

    public function testIfInsuranceNotActivatedDirectReturn(): void
    {
        $this->configurationSave->setValue($this->configFactory());
        $this->config->method('getValue')->willReturn($this->configFactory('true'));
        $this->assertEquals($this->configurationSave, $this->configurationSave->beforeSave());
    }

    public function testInsuranceProductAlreadyExistDirectReturn(): void
    {
        $this->configurationSave->setValue($this->configFactory('true'));
        $this->config->method('getValue')->willReturn($this->configFactory());
        $this->productRepository->method('get')->willReturn($this->createMock(ProductRepositoryInterface::class));
        $this->assertEquals($this->configurationSave, $this->configurationSave->beforeSave());
    }

    public function testInsuranceProductNotExistCreateInsuranceProductOk(): void
    {
        $this->configurationSave->setValue($this->configFactory('true'));
        $this->config->method('getValue')->willReturn($this->configFactory());
        $this->productRepository->method('get')->willThrowException(new NoSuchEntityException());
        $this->insuranceProductHelper->expects($this->once())->method('createInsuranceProduct');
        $this->assertEquals($this->configurationSave, $this->configurationSave->beforeSave());
    }

    public function testInsuranceProductNotExistCreateInsuranceProductThrowAnError(): void
    {
        $this->configurationSave->setValue($this->configFactory('true'));
        $this->config->method('getValue')->willReturn($this->configFactory());
        $this->productRepository->method('get')->willThrowException(new NoSuchEntityException());
        $this->insuranceProductHelper->expects($this->once())->method('createInsuranceProduct')->willThrowException(new AlmaInsuranceProductException('Impossible to create insurance product'));
        $this->expectException(AlmaInsuranceProductException::class);
        $this->expectExceptionMessage('Impossible to create insurance product');
        $this->configurationSave->beforeSave();
    }

    private function configFactory(
        $isInsuranceActivated = 'false',
        $isInsuranceOnProductPageActivated = 'false',
        $isAddToCartPopupActivated = 'false',
        $isInCartWidgetActivated = 'false'
    ): string {
        return '{"isInsuranceActivated":' . $isInsuranceActivated .
            ',"isInsuranceOnProductPageActivated":' . $isInsuranceOnProductPageActivated .
            ',"isAddToCartPopupActivated":' . $isAddToCartPopupActivated .
            ',"isInCartWidgetActivated":' . $isInCartWidgetActivated .
            '}';
    }
}
