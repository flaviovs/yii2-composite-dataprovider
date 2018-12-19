<?php

namespace fv\yii\data;

use yii\data\DataProviderInterface;
use yii\base\InvalidCallException;

class DataProviderPaginator implements \Countable, \ArrayAccess
{
    protected $dataProvider;
    protected $pagination;

    public function __construct(DataProviderInterface $provider)
    {
        $this->dataProvider = $provider;
        $this->pagination = $provider->getPagination();
    }


    public function count()
    {
        if ($this->pagination) {
            $this->dataProvider->prepare();
            $count = $this->pagination->getPageCount();
        } else {
            $count = $this->dataProvider->getTotalCount() > 0 ? 1 : 0;
        }

        return $count;
    }


    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < $this->count();
    }


    public function offsetGet($offset)
    {
        if ($this->pagination) {
            $cur_page = $this->pagination->getPage();
        } else {
            $cur_page = 0;
        }

        if ($offset !== $cur_page) {
            if ($this->pagination) {
                $this->pagination->setPage($offset);
            }
            $this->dataProvider->prepare(true);
        }

        return [
            $this->dataProvider->getModels(),
            $this->dataProvider->getKeys(),
        ];
    }


    public function offsetSet($offset, $value)
    {
        throw new InvalidCallException('DataProvider is read-only');
    }


    public function offsetUnset($offset)
    {
        throw new InvalidCallException('DataProvider is read-only');
    }
}
