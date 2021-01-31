<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class ClearLogAndCacheTestListener implements TestListener
{
    public function addError(Test $test, Throwable $t, float $time): void
    {
        // TODO: Implement addError() method.
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        // TODO: Implement addWarning() method.
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        // TODO: Implement addFailure() method.
    }

    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        // TODO: Implement addIncompleteTest() method.
    }

    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        // TODO: Implement addRiskyTest() method.
    }

    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        // TODO: Implement addSkippedTest() method.
    }

    public function startTestSuite(TestSuite $suite): void
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__ . '/../build/tests');
    }

    public function endTestSuite(TestSuite $suite): void
    {
        // TODO: Implement endTestSuite() method.
    }

    public function startTest(Test $test): void
    {
        // TODO: Implement startTest() method.
    }

    public function endTest(Test $test, float $time): void
    {
        // TODO: Implement endTest() method.
    }
}
