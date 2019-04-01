<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;
use Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureLoader;
use Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureReferenceLoader;
use Shopsys\ShopBundle\DataFixtures\Loader\ProductParameterValueDataLoader;

class ProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    const PRODUCT_PREFIX = 'product_';
    const FAKER_SEED_NUMBER = 1;
    const PRODUCT_COUNT = 150;
    const VARIANTS_PER_PRODUCT = 3;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureLoader
     */
    private $productDataFixtureLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Loader\ProductParameterValueDataLoader
     */
    private $productParameterValueDataLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade
     */
    private $productVariantFacade;

    /**
     * @param \Faker\Generator $faker
     * @param \Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureLoader $productDataFixtureLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\ShopBundle\DataFixtures\Loader\ProductParameterValueDataLoader $productParameterValueDataLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade
     */
    public function __construct(
        Generator $faker,
        ProductDataFixtureLoader $productDataFixtureLoader,
        ProductFacade $productFacade,
        ProductParameterValueDataLoader $productParameterValueDataLoader,
        ProductRepository $productRepository,
        ProductVariantFacade $productVariantFacade
    ) {
        $this->faker = $faker;
        $this->productDataFixtureLoader = $productDataFixtureLoader;
        $this->productFacade = $productFacade;
        $this->productRepository = $productRepository;
        $this->productParameterValueDataLoader = $productParameterValueDataLoader;
        $this->productVariantFacade = $productVariantFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->productDataFixtureLoader->loadReferences();
        $counter = 0;
        while (++$counter < self::PRODUCT_COUNT) {
            $this->faker->seed($counter);
            $productData = $this->productDataFixtureLoader->getProductsDataForFakerSeed($counter);

            $hasParameters = $this->faker->boolean(40);
            if ($hasParameters) {
                $productData->parameters = $this->productParameterValueDataLoader->getParameterValueDataParametersForFakerSeed($counter);
            }
            $product = $this->productFacade->create($productData);

            $hasVariants = $this->faker->boolean(30);
            if ($hasVariants) {
                $variantsData = $this->productDataFixtureLoader->createVariantsProductDataForProduct($product, self::VARIANTS_PER_PRODUCT);
                $variants = [];
                foreach ($variantsData as $variantData) {
                    $counter++;
                    $variants[] = $this->productFacade->create($variantData);
                }

                $this->productVariantFacade->createVariant($product, $variants);
            }
        }

        $this->setReferencesByProductId();
    }

    protected function setReferencesByProductId()
    {
        $allProducts = $this->productRepository->getAll();

        foreach ($allProducts as $product) {
            $this->addReference(self::PRODUCT_PREFIX . $product->getId(), $product);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return ProductDataFixtureReferenceLoader::getDataFixtureDependenciesForProduct();
    }
}
