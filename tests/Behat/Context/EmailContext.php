<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Fixture\Storage;
use Behat\Behat\Context\Context;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Mime\Email;

final class EmailContext implements Context
{
    private FilesystemInterface $filesystem;
    private Storage $storage;

    public function __construct(FilesystemInterface $filesystem, Storage $storage)
    {
        $this->filesystem = $filesystem;
        $this->storage = $storage;
    }

    /**
     * @BeforeScenario
     * @return void
     * @throws FileNotFoundException
     */
    public function purgeMailQueue(): void
    {
        $fileData = $this->filesystem->listContents();
        foreach ($fileData as $fileDatum) {
            $this->filesystem->delete($fileDatum['path']);
        }
    }

    /**
     * @Then :emailReference is an email sent to :emailAddress with the subject :emailSubject
     * @param string $emailReference
     * @param string $emailAddress
     * @param string $emailSubject
     * @return void
     * @throws \Exception
     */
    public function isAnEmailSentToWithTheSubject(
        string $emailReference,
        string $emailAddress,
        string $emailSubject
    ): void {
        $fileData = $this->filesystem->listContents();

        foreach ($fileData as $fileDatum) {
            $fileContent = $this->filesystem->read($fileDatum['path']);
            if (false === $fileContent) {
                throw new \Exception(sprintf("Failed to read message file at %s", $fileDatum['path']));
            }

            /** @var Email $email */
            $email = unserialize($fileContent);
            if (in_array($emailAddress, array_keys($email->getTo())) && $email->getSubject() === $emailSubject) {
                $this->storage->set(Email::class, $emailReference, $email);
                $this->filesystem->delete($fileDatum['path']);
                return;
            }
        }

        throw new \Exception(sprintf(
            "No email found to recipient \"%s\" with subject \"%s\"",
            $emailAddress,
            $emailSubject
        ));
    }

    /**
     * @Given :linkReference is a link in :emailReference email labeled :linkLabel
     * @param string $linkReference
     * @param string $emailReference
     * @param string $linkLabel
     * @return void
     * @throws \Exception
     */
    public function isALinkInEmailLabeled(string $linkReference, string $emailReference, string $linkLabel): void
    {
        /** @var Email $email */
        $email = $this->storage->get(Email::class, $emailReference);

        $body = $email->getHtmlBody();
        if (null === $body) {
            throw new \Exception(sprintf("Email %s does not have an html body", $emailReference));
        }

        /** @var string $body */
        $xml = new \SimpleXMLElement($body);

        $linkElements = $xml->xpath(sprintf("//a[text()='%s']", $linkLabel));
        if (count($linkElements) === 0) {
            throw new \Exception(sprintf(
                "Link with label \"%s\" not found in email \"%s\"",
                $linkLabel,
                $emailReference
            ));
        }

        $uri = (string) $linkElements[0]['href'];
        if ("" === $uri) {
            throw new \Exception(sprintf(
                "Link with label \"%s\" in email \"%s\" has no href",
                $linkLabel,
                $emailReference
            ));
        }

        $this->storage->set(BrowserContext::LINK_TYPE, $linkReference, $uri);
    }
}
