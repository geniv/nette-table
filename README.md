Table
=====

Installation
------------

```sh
$ composer require geniv/nette-configurtableator
```
or
```json
"geniv/nette-table": ">=1.0.0"
```

require:
```json
"php": ">=5.6.0",
"nette/nette": ">=2.4.0",
"dibi/dibi": ">=3.0.0",
"geniv/nette-locale": ">=1.0.0"
```

Include in application
----------------------

neon configure:
```neon
services:
    - Table(%tablePrefix%)
```

usage:
```php
use Table;

protected function createComponentTable1(Table $table)
{
    $table->setTemplatePath(__DIR__ . '/templates/Byty/cenikTable.latte')
        ->setTableName(PriceList::TABLE_NAME)
        ->setTableName(PriceList::TABLE_NAME, 'tab')
        ->setColumns('designation, floor')
        ->setColumns(['designation', 'floor'])
        ->setColumnLocale(null)     //<-default value is null
        ->setColumnLocale('language_col')
        ->addJoin('table_join', 'alias', 'alias.id=tab.id')
        ->addLeftJoin('table_join', 'alias', ['alias.id' => 'tab.id'])
        ->addLeftJoin('table_has_locale', 'lo_alias', 'lo_alias.id=tab.id AND lo_alias.id_locale=' . $table->getIdLocale())
        ->addWhere('image=1')
        ->addWhere('image=3')
        ->addWhere(['image' => 3])
        ->addOrder('position')
        ->addOrder('position', 'desc');
        
        // $table->getList()
        
    $table->enableCache(true)
        ->setCacheDependencies([Nette\Caching\Cache::EXPIRE => '30 minutes']);

    return $table;
}
```

usage:
```latte
{control table1}
```

usage in template:
```latte
<div n:foreach="$list as $item">
    <h1>{$item['title']}</h1>
</div>

{if $iterations==0}
    0 položek
{/if}
```
