<?php

namespace Anteris\Cache\Tests\TestNamespace;

class TestClass
{
    public $firstName = 'Test';
    public $lastName  = 'Case';

    public static function sayHello()
    {
        return 'Hi!';
    }

    public function getName()
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function realtimeName()
    {
        return $this->getName();
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
}
