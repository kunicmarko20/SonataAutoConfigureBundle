<?php

namespace KunicMarko\SonataAutoConfigureBundle\Features\Context;

use Behat\MinkExtension\Context\MinkContext;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Behat\Symfony2Extension\Context\KernelDictionary;
use KunicMarko\SonataAutoConfigureBundle\Features\Fixtures\CategoryFixtures;
use Doctrine\Common\DataFixtures\Loader;

class AdminContext extends MinkContext
{
    use KernelDictionary;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @BeforeScenario
     */
    public function clearData(): void
    {
        $this->getPurger()->purge();
    }

    private function getPurger(): ORMPurger
    {
        return new ORMPurger($this->entityManager);
    }

    /**
     * @Given I am on the dashboard
     */
    public function iAmOnTheDashboard(): void
    {
        $this->visitPath('/admin/dashboard');
    }

    /**
     * @Given I have items in the database
     */
    public function iHaveItemsInTheDatabase(): void
    {
        $loader = new Loader();
        $loader->addFixture(new CategoryFixtures());

        $executor = new ORMExecutor($this->entityManager, $this->getPurger());
        $executor->execute($loader->getFixtures());
    }

    /**
     * @Then I should see :button button
     */
    public function iShouldSeeAButton(string $button): void
    {
        $this->getSession()->getPage()->find('xpath', "//a[contains(text(), $button)]");
    }
}
