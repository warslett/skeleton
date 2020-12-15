<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Domain\Entity\User;
use App\Tests\Behat\Fixture\Factory\UserFactory;
use App\Tests\Behat\Fixture\Storage;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

final class UserContext implements Context
{
    private Storage $storage;
    private UserFactory $userFactory;

    public function __construct(Storage $storage, UserFactory $userFactory)
    {
        $this->storage = $storage;
        $this->userFactory = $userFactory;
    }

    /**
     * @Given that :userReference is a User with the following properties:
     * @Given that :userReference is a User
     * @param string $userReference
     * @param TableNode|null $table
     * @return void
     * @throws \Exception
     */
    public function thatIsAUserWithEmailAndPassword(string $userReference, TableNode $table = null): void
    {
        $properties = is_null($table) ? [] : $table->getRowsHash();
        $user = $this->userFactory->createUser($properties);
        $this->storage->set(User::class, $userReference, $user);
    }
}
