<?php

namespace fv\test;

use yii\data\DataProviderInterface;
use yii\data\ArrayDataProvider;

use flaviovs\yii\data\CompositeDataProvider;

class CompositeDataProviderTest extends \Codeception\Test\Unit
{
    protected function newDataProvider($count = 4, $start = 1, $config = [])
    {
        $models = [];
        for ($i = 0; $i < $count; $i++, $start++) {
            $models[] = [
                'key' => "key$start",
                'value' => "val$start",
            ];
        }

        return new ArrayDataProvider($config + [
            'allModels' => $models,
            'key' => 'key',
        ]);
    }

    public function testDefaultPagination()
    {
        $cdp = new CompositeDataProvider();
        $this->assertInstanceOf(
            \yii\data\Pagination::class,
            $cdp->getPagination()
        );
    }


    public function testSetConfigArray()
    {
        $cdp = new CompositeDataProvider([
            'dataProviders' => [
                [
                    'class' => ArrayDataProvider::class,
                    'allModels' => [],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            DataProviderInterface::class,
            $cdp->getDataProviders()[0]
        );
    }


    public function testAddDataProvider()
    {
        $cdp = new CompositeDataProvider([
            'dataProviders' => [
                [
                    'class' => ArrayDataProvider::class,
                    'id' => 'dp1',
                    'allModels' => [],
                ],
            ],
        ]);

        $cdp->addDataProvider(new ArrayDataProvider([
            'id' => 'dp2',
            'allModels' => [],
        ]));

        $cdp->addDataProvider(new ArrayDataProvider([
            'id' => 'dp3',
            'allModels' => [],
        ]));

        $providers = $cdp->getDataProviders();

        $this->assertCount(3, $providers);
        $this->assertInstanceOf(DataProviderInterface::class, $providers[0]);
        $this->assertInstanceOf(DataProviderInterface::class, $providers[1]);
        $this->assertInstanceOf(DataProviderInterface::class, $providers[2]);
        $this->assertEquals('dp1', $providers[0]->id);
        $this->assertEquals('dp2', $providers[1]->id);
        $this->assertEquals('dp3', $providers[2]->id);
    }


    public function testSingleNonPaginatedProviderWithoutPagination()
    {
        $dp = $this->newDataProvider(4, 1, ['pagination' => false]);

        $cdp = new CompositeDataProvider([
            'dataProviders' => [$dp],
            'pagination' => false,
        ]);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertFalse($cdp->getPagination());

        $this->assertCount(4, $models);
        $this->assertCount(4, $keys);

        $this->assertEquals(4, $cdp->getTotalCount());

        $this->assertEquals(4, $dp->getCount());
        $this->assertEquals($dp->allModels, $models);
        $this->assertEquals(['key1', 'key2', 'key3', 'key4'], $keys);
    }


    public function testSinglePaginatedProviderWithoutPagination()
    {
        $dp = $this->newDataProvider(4, 1, [
            'pagination' => [
                'pageSize' => 2,
            ],
        ]);

        $cdp = new CompositeDataProvider([
            'dataProviders' => [$dp],
            'pagination' => false,
        ]);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertFalse($cdp->getPagination());

        $this->assertCount(4, $models);
        $this->assertCount(4, $keys);

        $this->assertEquals(4, $cdp->getCount());
        $this->assertEquals(4, $cdp->getTotalCount());

        //$this->assertEquals($data, $models);
        $this->assertEquals(['key1', 'key2', 'key3', 'key4'], $keys);
    }


    public function testMultipleNonPaginatedProvidersWithoutPagination()
    {
        $dp1 = $this->newDataProvider(4, 1, ['pagination' => false]);
        $dp2 = $this->newDataProvider(4, 5, ['pagination' => false]);

        $cdp = new CompositeDataProvider([
            'dataProviders' => [$dp1, $dp2],
            'pagination' => false,
        ]);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertFalse($cdp->getPagination());

        $this->assertCount(8, $models);
        $this->assertCount(8, $keys);

        $this->assertEquals(8, $cdp->getCount());
        $this->assertEquals(8, $cdp->getTotalCount());

        $this->assertEquals(
            ['key1', 'key2', 'key3', 'key4', 'key5', 'key6', 'key7', 'key8'],
            $keys
        );
    }


    public function testMultiplePaginatedProvidersWithoutPagination()
    {
        $dp1 = $this->newDataProvider(
            4,
            1,
            ['pagination' => ['pageSize' => 2]]
        );
        $dp2 = $this->newDataProvider(
            4,
            5,
            ['pagination' => ['pageSize' => 2]]
        );

        $cdp = new CompositeDataProvider([
            'dataProviders' => [$dp1, $dp2],
            'pagination' => false,
        ]);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertFalse($cdp->getPagination());

        $this->assertCount(8, $models);
        $this->assertCount(8, $keys);

        $this->assertEquals(8, $cdp->getCount());
        $this->assertEquals(8, $cdp->getTotalCount());

        $this->assertEquals(
            ['key1', 'key2', 'key3', 'key4', 'key5', 'key6', 'key7', 'key8'],
            $keys
        );
    }


    public function testMultiplePaginatedProvidersWithLargePagination()
    {
        $dp1 = $this->newDataProvider(
            4,
            1,
            ['pagination' => ['pageSize' => 2]]
        );
        $dp2 = $this->newDataProvider(
            4,
            5,
            ['pagination' => ['pageSize' => 2]]
        );

        $cdp = new CompositeDataProvider([
            'dataProviders' => [$dp1, $dp2],
            'pagination' => [
                'pageSize' => 3,
            ],
        ]);

        $this->assertEquals(8, $cdp->getTotalCount());

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(['key1', 'key2', 'key3'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key1', 'value' => 'val1'],
                ['key' => 'key2', 'value' => 'val2'],
                ['key' => 'key3', 'value' => 'val3'],
            ],
            $models
        );

        // Test 2nd page.
        $cdp->pagination->page++;
        $cdp->prepare(true);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(3, $cdp->getCount());
        $this->assertEquals(['key4', 'key5', 'key6'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key4', 'value' => 'val4'],
                ['key' => 'key5', 'value' => 'val5'],
                ['key' => 'key6', 'value' => 'val6'],
            ],
            $models
        );

        // Test 3rd page.
        $cdp->pagination->page++;
        $cdp->prepare(true);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(2, $cdp->getCount());
        $this->assertEquals(['key7', 'key8'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key7', 'value' => 'val7'],
                ['key' => 'key8', 'value' => 'val8'],
            ],
            $models
        );
    }


    public function testMultiplePaginatedProvidersWithSmallPagination()
    {
        $dp1 = $this->newDataProvider(
            4,
            1,
            ['pagination' => ['pageSize' => 3]]
        );
        $dp2 = $this->newDataProvider(
            4,
            5,
            ['pagination' => ['pageSize' => 3]]
        );

        $cdp = new CompositeDataProvider([
            'dataProviders' => [$dp1, $dp2],
            'pagination' => [
                'pageSize' => 2,
            ],
        ]);

        $this->assertEquals(8, $cdp->getTotalCount());

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(2, $cdp->getCount());
        $this->assertEquals(['key1', 'key2'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key1', 'value' => 'val1'],
                ['key' => 'key2', 'value' => 'val2'],
            ],
            $models
        );

        // Test 2nd page.
        $cdp->pagination->page++;
        $cdp->prepare(true);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(2, $cdp->getCount());
        $this->assertEquals(['key3', 'key4'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key3', 'value' => 'val3'],
                ['key' => 'key4', 'value' => 'val4'],
            ],
            $models
        );

        // Test 3rd page.
        $cdp->pagination->page++;
        $cdp->prepare(true);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(2, $cdp->getCount());
        $this->assertEquals(['key5', 'key6'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key5', 'value' => 'val5'],
                ['key' => 'key6', 'value' => 'val6'],
            ],
            $models
        );

        // Test 4th page.
        $cdp->pagination->page++;
        $cdp->prepare(true);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(2, $cdp->getCount());
        $this->assertEquals(['key7', 'key8'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key7', 'value' => 'val7'],
                ['key' => 'key8', 'value' => 'val8'],
            ],
            $models
        );
    }


    /**
     * Mixed provider test.
     *
     * dp1(2)  k1   k2 | k3
     * dp2(*)
     * dp3(*)                 k4
     * dp4(1)                      k5 | k6
     * dp5(!)                                k7   k8   k9
     * cdp(4)  k1   k2   k3   k4 | k5   k6   k7   k8 | k9
     *
     * (*) Default pagination (pagesize = 20)
     * (!) Non-paginated
     * (N) Page size = N
     */
    public function testMixedProviders()
    {
        $cdp = new CompositeDataProvider([
            'dataProviders' => [
                // dp1
                $this->newDataProvider(
                    3,
                    1,
                    ['pagination' => ['pageSize' => 2]]
                ),
                // dp2 (empty data provider)
                new ArrayDataProvider(),
                // dp3
                $this->newDataProvider(1, 4),
                // dp4
                $this->newDataProvider(
                    2,
                    5,
                    ['pagination' => ['pageSize' => 1]]
                ),
                // dp5
                $this->newDataProvider(3, 7, ['pagination' => false]),
            ],
            'pagination' => [
                'pageSize' => 4,
            ],
        ]);

        $this->assertEquals(4, $cdp->getCount());
        $this->assertEquals(9, $cdp->getTotalCount());

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(['key1', 'key2', 'key3', 'key4'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key1', 'value' => 'val1'],
                ['key' => 'key2', 'value' => 'val2'],
                ['key' => 'key3', 'value' => 'val3'],
                ['key' => 'key4', 'value' => 'val4'],
            ],
            $models
        );

        // Test 2nd page.
        $cdp->pagination->page++;
        $cdp->prepare(true);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(4, $cdp->getCount());
        $this->assertEquals(['key5', 'key6', 'key7', 'key8'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key5', 'value' => 'val5'],
                ['key' => 'key6', 'value' => 'val6'],
                ['key' => 'key7', 'value' => 'val7'],
                ['key' => 'key8', 'value' => 'val8'],
            ],
            $models
        );


        // Test 3rd page.
        $cdp->pagination->page++;
        $cdp->prepare(true);

        $models = $cdp->getModels();
        $keys = $cdp->getKeys();

        $this->assertEquals(1, $cdp->getCount());
        $this->assertEquals(['key9'], $keys);
        $this->assertEquals(
            [
                ['key' => 'key9', 'value' => 'val9'],
            ],
            $models
        );
    }


    public function testGetCountersAfterAddingDataProvider()
    {
        $dp = $this->newDataProvider(4, 1, ['pagination' => false]);

        $cdp = new CompositeDataProvider([
            'dataProviders' => [$dp],
            'pagination' => [
                'pageSize' => 6,
            ],
        ]);

        $cdp->addDataProvider(
            $this->newDataProvider(4, 1, ['pagination' => false])
        );

        $this->assertEquals(6, $cdp->getCount());
        $this->assertEquals(8, $cdp->getTotalCount());

    }
}
