<?php

declare(strict_types=1);

namespace App\Tests\Behat\Fixture;

class Storage
{
    private array $storage = [];

    /**
     * @param string $type
     * @param string $reference
     * @param mixed $entity
     * @return void
     * @throws \Exception
     */
    public function set(string $type, string $reference, $entity): void
    {
        if (is_null($this->storage[$type])) {
            $this->storage[$type] = [];
        }
        if (!is_null($this->storage[$type][$reference])) {
            throw new \Exception(sprintf(
                "A fixture with reference %s is already registered for the type %s",
                $reference,
                $type
            ));
        }
        $this->storage[$type][$reference] = $entity;
    }

    /**
     * @param string $type
     * @param string $reference
     * @throws \Exception
     * @return mixed
     */
    public function get(string $type, string $reference)
    {
        if (is_null($this->storage[$type])) {
            throw new \Exception(sprintf(
                "No fixtures registered for the type %s",
                $type
            ));
        }
        if (is_null($this->storage[$type][$reference])) {
            throw new \Exception(sprintf(
                "No fixture registered with reference %s for the type %s",
                $reference,
                $type
            ));
        }

        return $this->storage[$type][$reference];
    }
}
