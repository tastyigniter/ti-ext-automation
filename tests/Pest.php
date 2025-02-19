<?php

use SamPoyigi\Testbench\TestCase;

uses(TestCase::class)->in(__DIR__);

function callProtectedMethod(object $condition, string $methodName, array $args = []): mixed
{
    $reflection = new ReflectionClass($condition);
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($condition, $args);
}

