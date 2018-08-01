<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CurrencyDataFixture extends AbstractReferenceFixture
{
    const CURRENCY_CZK = 'currency_czk';
    const CURRENCY_EUR = 'currency_eur';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface
     */
    private $currencyDataFactory;

    public function __construct(
        CurrencyFacade $currencyFacade,
        CurrencyDataFactoryInterface $currencyDataFactory
    ) {
        $this->currencyFacade = $currencyFacade;
        $this->currencyDataFactory = $currencyDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * The "EUR" currency is created in database migration.
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135342
         */
        $currencyEur = $this->currencyFacade->getById(1);
        $this->addReference(self::CURRENCY_EUR, $currencyEur);

        $currencyData = $this->currencyDataFactory->create();
        $currencyData->name = 'Česká koruna';
        $currencyData->code = Currency::CODE_CZK;
        $currencyData->exchangeRate = 0.039;
        $currencyCzk = $this->currencyFacade->create($currencyData);

        $this->addReference(self::CURRENCY_CZK, $currencyCzk);
    }
}
