<?php

namespace AmpProject\Optimizer;

use AmpProject\Optimizer\Error\UnknownError;
use AmpProject\Tests\TestCase;
use ReflectionClass;

/**
 * Test the error collection container.
 *
 * @covers \AmpProject\Optimizer\ErrorCollection
 * @package ampproject/amp-toolbox
 */
final class ErrorCollectionTest extends TestCase
{

    /**
     * Test whether we can add errors to the collection.
     *
     * @covers \AmpProject\Optimizer\ErrorCollection::add()
     */
    public function testAddingErrors()
    {
        $errorCollection = new ErrorCollection();
        $errorCollection->add(new UnknownError('first error'));
        $errorCollection->add(new UnknownError('second error'));
        $this->assertCount(2, $errorCollection);
        $this->assertEquals(2, $errorCollection->count());
    }

    /**
     * Test whether we can check for errors within the collection.
     *
     * @covers \AmpProject\Optimizer\ErrorCollection::add()
     * @covers \AmpProject\Optimizer\ErrorCollection::has()
     */
    public function testCheckingForErrors()
    {
        $errorCollection = new ErrorCollection();
        $errorCollection->add(new UnknownError('first error'));
        $errorCollection->add(new UnknownError('second error'));
        $this->assertTrue($errorCollection->has((new ReflectionClass(UnknownError::class))->getShortName()));
        $this->assertFalse($errorCollection->has('BAD_CODE'));
    }

    /**
     * Test whether we can iterate over errors in the collection.
     *
     * @covers \AmpProject\Optimizer\ErrorCollection::add()
     * @covers \AmpProject\Optimizer\ErrorCollection::getIterator()
     */
    public function testIteratingOverErrors()
    {
        $errorCollection = new ErrorCollection();
        $errorCollection->add(new UnknownError('first error'));
        $errorCollection->add(new UnknownError('second error'));
        foreach ($errorCollection as $error) {
            $this->assertInstanceOf(Error::class, $error);
            $this->assertEquals((new ReflectionClass(UnknownError::class))->getShortName(), $error->getCode());
        }
        $errors = iterator_to_array($errorCollection, false);
        $this->assertEquals('first error', $errors[0]->getMessage());
        $this->assertEquals('second error', $errors[1]->getMessage());
    }
}
