<?php

declare(strict_types=1);

  use PHPUnit\Framework\TestCase;
  use PHPUnit\Framework\Attributes\Depends;
  use PHPUnit\Framework\Attributes\CoversClass;
  use PHPUnit\Framework\Attributes\CoversMethod;

  #[CoversClass(Queue::class)]
  final class QueueTest extends TestCase {

    private Queue $queue;

    // Prepare test class. Runs before tests.
    protected function setUp(): void {
      $this->queue = new Queue;
    }

    // Runs after each test.
    protected function tearDown(): void {
      // some cleanup logic.
      // unset($this->queue);
    }

    public function testNewQueueEmpty(): void {
      $this->assertEquals(0, $this->queue->getSize());
    }

    #[CoversMethod(Queue::class, 'push')]
    public function testQueuePushItems(): Queue {
      $this->queue->push('item1');
      $this->assertSame(1, $this->queue->getSize());
      // Return queue as a dependency example.
      return $this->queue;
    }

  #[Depends('testQueuePushItems')]
  public function testQueuePopItems(Queue $queue): void {
    $this->assertSame('item1', $queue->pop());
    $this->assertSame(0, $queue->getSize());
  }

  public function testQueuePopFirstItem(): void {
    $this->queue->push('item1');
    $this->queue->push('item2');
    $this->assertSame('item1', $this->queue->pop());
  }

  public function testQueuePopExceptionWhenEmpty(): void {
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Empty queue.');
    $this->queue->pop();
  }
}
