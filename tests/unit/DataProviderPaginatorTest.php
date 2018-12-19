<?php

namespace fv\test;

use yii\data\ArrayDataProvider;
use yii\base\InvalidCallException;

use fv\yii\data\DataProviderPaginator;

class DataProviderPaginatorTest extends \Codeception\Test\Unit
{
    protected function newDataProvider($count = 4, $config = [])
    {
        $models = [];
        for ($i = 0; $i < $count; $i++) {
            $models[] = [
                'key' => "key$i",
                'value' => "val$i",
            ];
        }

        return new ArrayDataProvider($config + [
            'allModels' => $models,
            'key' => 'key',
        ]);
    }



    public function testCountOnEmptyDataProvider()
    {
        $dp = new ArrayDataProvider();
        $ddp = new DataProviderPaginator($dp);

        $this->assertCount(0, $ddp);
    }


    public function testCountOnNonPaginatedDataProvider()
    {
        $dp = $this->newDataProvider(4, ['pagination' => false]);

        $ddp = new DataProviderPaginator($dp);

        $this->assertCount(1, $ddp);
    }


    public function testCountOnPaginatedDataProvider()
    {
        $dp = $this->newDataProvider(12, [
            'pagination' => [
                'pageSize' => 3,
            ],
        ]);

        $ddp = new DataProviderPaginator($dp);

        $this->assertCount(4, $ddp);
    }



    public function testOffsetExistsOnEmptyDataProvider()
    {
        $dp = new ArrayDataProvider();
        $ddp = new DataProviderPaginator($dp);

        $this->assertFalse(isset($ddp[-1]));
        $this->assertFalse(isset($ddp[0]));
        $this->assertFalse(isset($ddp[1]));
    }


    public function testOffsetExistsOnNonPaginatedDataProvider()
    {
        $dp = $this->newDataProvider(4, ['pagination' => false]);

        $ddp = new DataProviderPaginator($dp);

        $this->assertFalse(isset($ddp[-1]));
        $this->assertTrue(isset($ddp[0]));
        $this->assertFalse(isset($ddp[1]));
    }


    public function testOffsetExistsOnPaginatedDataProvider()
    {
        $dp = $this->newDataProvider(11, [
            'pagination' => [
                'pageSize' => 4,
            ],
        ]);

        $ddp = new DataProviderPaginator($dp);

        $this->assertFalse(isset($ddp[-1]));
        $this->assertTrue(isset($ddp[0]));
        $this->assertTrue(isset($ddp[1]));
        $this->assertTrue(isset($ddp[2]));
        $this->assertFalse(isset($ddp[3]));
    }


    public function testOffsetSetThrowsException()
    {
        $dp = $this->newDataProvider(5);
        $ddp = new DataProviderPaginator($dp);

        $this->expectException(InvalidCallException::class);
        $ddp[0] = 'foo';
    }


    public function testOffsetUnsetThrowsException()
    {
        $dp = $this->newDataProvider(5);
        $ddp = new DataProviderPaginator($dp);

        $this->expectException(InvalidCallException::class);
        unset($ddp[0]);
    }
}
