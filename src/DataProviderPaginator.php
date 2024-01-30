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


    public function count(): int
    {
        if ($this->pagination) {
            $this->dataProvider->prepare();
            $count = $this->pagination->getPageCount();
        } else {
            $count = $this->dataProvider->getTotalCount() > 0 ? 1 : 0;
        }

        return $count;
    }


    public function offsetExists(mixed $offset): bool
    {
        return $offset >= 0 && $offset < $this->count();
    }


    public function offsetGet(mixed: $offset) mixed
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


    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new InvalidCallException('DataProvider is read-only');
    }


    public function offsetUnset(mixed $offset): void
    {
        throw new InvalidCallException('DataProvider is read-only');
    }
}
