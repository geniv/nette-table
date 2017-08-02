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
//  $list
//            //->setPathTemplate(__DIR__ . '/../templates/Pravidla/list.latte')
//            ->setColumns('Title, Description')
//            ->setLanguage(null)
//            ->setOrder('IdNews', 'asc');

    return $table;
}
