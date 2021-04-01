<?php

namespace App\Tests\Renderer;

use App\Entity\Item;
use App\Renderer\ItemCount;
use PHPUnit\Framework\TestCase;

class ItemCountTest extends TestCase
{

    public function testAdd()
    {
        $first = new ItemCount();
        $second = new ItemCount();

        $first->setOpenCount(5);
        $second->setOpenCount(6);
        $first->setClosedCount(3);
        $second->setClosedCount(7);

        $first->add($second);

        $this->assertEquals(11, $first->getOpenCount());
        $this->assertEquals(10, $first->getClosedCount());
    }

    public function testAddClosed()
    {
        $count = new ItemCount();
        $count->addClosed();
        $count->addClosed();
        $count->addClosed();
        $this->assertEquals(3, $count->getClosedCount());
        $count->addClosed(2);
        $this->assertEquals(5, $count->getClosedCount());
        $this->assertEquals(0, $count->getOpenCount());
    }

    public function testAddOpen()
    {
        $count = new ItemCount();
        $count->addOpen();
        $count->addOpen();
        $count->addOpen();
        $this->assertEquals(3, $count->getOpenCount());
        $count->addOpen(3);
        $this->assertEquals(6, $count->getOpenCount());
        $this->assertEquals(0, $count->getClosedCount());
    }

    public function testGetTotalCount()
    {
        $count = new ItemCount();
        $count->setOpenCount(100);
        $count->setClosedCount(5);
        $this->assertEquals(105, $count->getTotalCount());
    }

    public function testSetClosedCount()
    {
        $count = new ItemCount();
        $count->setClosedCount(50);
        $this->assertEquals(50, $count->getClosedCount());
        $this->assertEquals(0, $count->getOpenCount());
    }

    public function testSetOpenCount()
    {
        $count = new ItemCount();
        $count->setOpenCount(100);
        $this->assertEquals(100, $count->getOpenCount());
        $this->assertEquals(0, $count->getClosedCount());
    }
}
