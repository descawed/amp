<?php

namespace Amp\Test;

use Amp\Failure;
use Amp\Loop;
use Amp\MultiReasonException;
use Amp\Pause;
use Amp\Promise;
use Amp\Success;

class FirstTest extends \PHPUnit\Framework\TestCase {
    /**
     * @expectedException \Error
     * @expectedExceptionMessage No promises provided
     */
    public function testEmptyArray() {
        Promise\first([]);
    }

    public function testSuccessfulPromisesArray() {
        $promises = [new Success(1), new Success(2), new Success(3)];

        $callback = function ($exception, $value) use (&$result) {
            $result = $value;
        };

        Promise\first($promises)->onResolve($callback);

        $this->assertSame(1, $result);
    }

    public function testFailedPromisesArray() {
        $exception = new \Exception;
        $promises = [new Failure($exception), new Failure($exception), new Failure($exception)];

        $callback = function ($exception, $value) use (&$reason) {
            $reason = $exception;
        };

        Promise\first($promises)->onResolve($callback);

        $this->assertInstanceOf(MultiReasonException::class, $reason);
        $this->assertSame([$exception, $exception, $exception], $reason->getReasons());
    }

    public function testMixedPromisesArray() {
        $exception = new \Exception;
        $promises = [new Failure($exception), new Failure($exception), new Success(3)];

        $callback = function ($exception, $value) use (&$result) {
            $result = $value;
        };

        Promise\first($promises)->onResolve($callback);

        $this->assertSame(3, $result);
    }

    public function testPendingAwatiablesArray() {
        Loop::run(function () use (&$result) {
            $promises = [
                new Pause(20, 1),
                new Pause(30, 2),
                new Pause(10, 3),
            ];

            $callback = function ($exception, $value) use (&$result) {
                $result = $value;
            };

            Promise\first($promises)->onResolve($callback);
        });

        $this->assertSame(3, $result);
    }

    /**
     * @expectedException \TypeError
     */
    public function testNonPromise() {
        Promise\first([1]);
    }
}
