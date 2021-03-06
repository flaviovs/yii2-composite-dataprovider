<?php

namespace fv\yii\data;

use yii\data\Pagination;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

class CompositeDataProvider extends \yii\base\BaseObject implements \yii\data\DataProviderInterface
{
    public $id;

    protected $providers = [];

    protected $models;

    protected $keys;

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected $_pagination;

    protected $_totalCount = 0;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

    protected $counts = [];

    protected static $instanceCount = 0;


    public function init()
    {
        parent::init();

        if ($this->id === null) {
            if (self::$instanceCount > 0) {
                $this->id = 'cdp-' . self::$instanceCount;
            }
            self::$instanceCount++;
        }
    }


    public function addDataProvider($value)
    {
        if (is_array($value)) {
            if (!isset($value['class'])) {
                throw new InvalidConfigException(
                    'No "class" defined for data provider'
                );
            }
            $value = \Yii::createObject($value);
        }

        if (!($value instanceof \yii\data\DataProviderInterface)) {
            throw new InvalidArgumentException(
                'Config array or DataProviderInterface expected'
            );
        }

        $this->providers[] = $value;

        $count = $value->getTotalCount();

        $this->_totalCount += $count;
        $this->counts[] = $count;

        $this->models = $this->keys = null;
    }


    public function setDataProviders(array $config)
    {
        foreach ($config as $provider) {
            $this->addDataProvider($provider);
        }
    }


    public function getDataProviders()
    {
        return $this->providers;
    }


    public function getPagination()
    {
        if ($this->_pagination === null) {
            $this->setPagination([]);
        }
        return $this->_pagination;
    }


    protected function setPagination($config)
    {
        if (is_array($config)) {
            if (!isset($config['class'])) {
                $config['class'] = Pagination::class;
            }
            if ($this->id !== null) {
                $config['pageParam'] = $this->id . '-page';
                $config['pageSizeParam'] = $this->id . '-per-page';
            }
            $this->_pagination = \Yii::createObject($config);
        } elseif ($config instanceof Pagination || $config === false) {
            $this->_pagination = $config;
        } else {
            throw new InvalidArgumentException(
                'Only Pagination instance, configuration array or false is allowed.'
            );
        }
    }


    public function getCount()
    {
        return $this->getPagination()
            ? count($this->getModels())
            : $this->getTotalCount();
    }


    public function getKeys()
    {
        $this->prepare();
        return $this->keys;
    }


    public function getModels()
    {
        $this->prepare();
        return $this->models;
    }

    public function getSort()
    {
        return false;
    }



    public function getTotalCount()
    {
        return $this->_totalCount;
    }


    public function prepare($forcePrepare = false)
    {
        if ($this->models !== null && !$forcePrepare) {
            return;
        }

        $this->models = [];
        $this->keys = [];

        if (!$this->providers) {
            return;
        }

        $nr = count($this->providers);

        $pag = $this->getPagination();
        if ($pag) {
            $pag->totalCount = $this->getTotalCount();
            $offset = $pag->getOffset();
            $limit = $pag->getLimit();
        } else {
            $offset = 0;
            $limit = $this->getTotalCount();
        }

        for ($i = 0; $i < $nr && $offset > $this->counts[$i]; $i++) {
            $offset -= $this->counts[$i];
        }

        if ($i === $nr) {
            // No provider within the offset we're looking for. Point to last
            // provider then.
            $i--;
        }


        for (; $i < $nr && $limit > 0; $i++) {
            $dp = $this->providers[$i];

            $pages = new DataProviderPaginator($dp);
            $nr_pages = count($pages);

            $pag = $dp->getPagination();
            if ($pag) {
                $page_size = $pag->getPageSize();
            } else {
                $page_size = $this->counts[$i];
            }

            if ($page_size === 0) {
                continue;
            }

            $starting_page = (int)($offset / $page_size);
            $page_offset = $offset % $page_size;

            for ($j = $starting_page; $j < $nr_pages && $limit > 0; $j++) {
                list($page_models, $page_keys) = $pages[$j];

                $models = array_slice(
                    $page_models,
                    $page_offset,
                    $limit
                );

                $keys = array_slice(
                    $page_keys,
                    $page_offset,
                    $limit
                );

                $this->models = array_merge($this->models, $models);
                $this->keys = array_merge($this->keys, $keys);

                $count = count($models);

                $limit -= $count;

                $page_offset = 0;
            }

            $offset = 0;
        }
    }
}
