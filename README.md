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
    $table->setTemplatePath(__DIR__ . '/templates/Byty/cenikTable.latte');
    $table->setTableName(PriceList::TABLE_NAME);
    $table->setColumns('designation, floor, disposition, total_area, price, state, pdf');
    $table->setColumnLocale(null);
    $table->setOrder('position');

    return $table;
}
```

usage:
```latte
{control table1}
```
