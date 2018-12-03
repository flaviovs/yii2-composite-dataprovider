Yii2 Composite Data Provider
============================

Composite Data Provider (CDP) is a [yii\data\DataProviderInterface]
implementation that allows you to compose and browser several standard
Yii2 data providers as if they were just one.

CDP takes care of properly paginating source data providers, so you
can use any combination of pagination (or no pagination) on them --
CDPs will always paginate using the composite pagination configuration
(if any).


Installation
------------

```
composer require flaviovs/yii2-composite-dataprovider
```


Usage
-----

```php
$cdp = new \fv\yii\data\CompositeDataProvider([
    'dataProviders' => [
	
		// A \yii\data\DataProviderInterface instance.
		$data_provider1,
		
		// Object configuration is also supported.
		[
			'class' => \yii\data\ActiveDataProvider::class,
			'query' => $my_model->find();
		],
	],
]);
```

CDPs also acceps a `pagination` property that works the same as in
regular Yii2 data providers.

Additionally, you can use the `addDataProvider($value)` to add new
data providers to a CDP.


Important
---------

* CDPs cannot be sorted. Of course, source data providers can be
  sorted normally.
  
* Models and keys returned by CDPs come straight from the source data
  providers. That means that your data provider consumer (for example,
  `GridView` column configuration) must be prepared to handle models
  of varied type in case your source data providers return different
  models.


Issues
------

Visit http://github.com/flaviovs/yii2-composite-dataprovider



[yii\data\DataProviderInterface]: https://www.yiiframework.com/doc/api/2.0/yii-data-dataproviderinterface

