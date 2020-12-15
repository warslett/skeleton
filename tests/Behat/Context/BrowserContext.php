<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Fixture\Storage;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use PHPUnit\Framework\Assert;

final class BrowserContext implements Context
{
    public const LINK_TYPE = 'link';

    private Session $browserSession;
    private Storage $storage;
    private string $baseUrl;
    private string $screenShotPath;

    public function __construct(Session $browserSession, Storage $storage, string $baseUrl, string $screenShotPath)
    {
        $this->browserSession = $browserSession;
        $this->baseUrl = $baseUrl;
        $this->screenShotPath = $screenShotPath;
        $this->storage = $storage;
    }

    /**
     * @AfterStep
     * @param AfterStepScope $scope
     * @return void
     */
    public function takeFailedScenarioScreenShot(AfterStepScope $scope): void
    {
        if (false === $scope->getTestResult()->isPassed() && $this->browserSession->isStarted()) {
            $screenShot = $this->browserSession->getScreenshot();

            if (false === file_exists($this->screenShotPath)) {
                mkdir($this->screenShotPath, 0777, true);
            }

            $fileName = sprintf("%s.png", date('YmdHis'));
            $filePath = sprintf("%s/%s", $this->screenShotPath, $fileName);
            file_put_contents($filePath, $screenShot);
            chmod($filePath, 0777);
            print(sprintf("Saved browser screenshot %s", $fileName));
        }
    }

    /**
     * @AfterScenario
     * @return void
     */
    public function stopBrowserSession(): void
    {
        if ($this->browserSession->isStarted()) {
            $this->browserSession->stop();
        }
    }

    /**
     * @When I start a new browser session at :path
     * @When I start a new browser session
     * @param string $path
     * @return void
     */
    public function iStartABrowserSessionAt(string $path = '/'): void
    {
        if ($this->browserSession->isStarted()) {
            $this->browserSession->restart();
        } else {
            $this->browserSession->start();
        }
        $this->browserSession->visit($this->baseUrl . $path);
        $this->browserSession->setCookie('XDEBUG_SESSION', 'XDEBUG_ECLIPSE');
    }

    /**
     * @When I go to :path
     * @param string $path the path to visit
     * @return void
     */
    public function iGoTo(string $path): void
    {
        if (false === $this->browserSession->isStarted()) {
            throw new \Exception("Browser Session Not Started");
        }
        $this->browserSession->visit($this->baseUrl . $path);
    }

    /**
     * @When I fill in :field with :value
     * @param string $field
     * @param string $value
     * @return void
     * @throws ElementNotFoundException
     */
    public function iFillInWith(string $field, string $value): void
    {
        $this->browserSession->getPage()->fillField($field, $value);
    }

    /**
     * @When I press :button
     * @param string $button
     * @return void
     * @throws ElementNotFoundException
     */
    public function iPress(string $button): void
    {
        $this->browserSession->getPage()->pressButton($button);
    }

    /**
     * @When I follow :link
     * @param string $link
     * @return void
     * @throws ElementNotFoundException
     */
    public function iFollow(string $link): void
    {
        $this->browserSession->getPage()->clickLink($link);
    }

    /**
     * @When I follow :linkReference link
     * @param string $linkReference
     * @return void
     * @throws \Exception
     */
    public function iFollowLink(string $linkReference): void
    {
        /** @var string $linkUri */
        $linkUri = $this->storage->get(self::LINK_TYPE, $linkReference);
        $this->browserSession->visit($linkUri);
    }

    /**
     * @Then the title should be :title
     * @param string $title
     * @return void
     * @throws \Exception
     */
    public function theTitleShouldBe(string $title): void
    {
        $page = $this->browserSession->getPage();
        $pageTitleNode = $page->find('xpath', '//title');
        if (null === $pageTitleNode) {
            throw new \Exception("No title element found on page");
        }
        $pageTitle = $pageTitleNode->getHtml();
        Assert::assertEquals($title, $pageTitle);
    }

    /**
     * @Then there is an alert with the message :message
     * @param string $message
     * @return void
     * @throws \Exception
     */
    public function thereIsAnAlertWithTheMessage(string $message): void
    {
        $page = $this->browserSession->getPage();
        $locator = sprintf("//*[contains(@class, 'alert') and contains(text(), '%s')]", $message);
        if (!$page->has('xpath', $locator)) {
            $message = "Could not find the following alert\n$message\n";
            $alerts = $page->findAll('css', '.alert');
            if (count($alerts) > 0) {
                $message .= "The following alerts were found:\n";
                foreach ($alerts as $alert) {
                    /** @var NodeElement $alert */
                    $message .= trim($alert->getHtml()) . "\n";
                }
            }
            throw new \Exception($message);
        }
    }
}
