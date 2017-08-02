<?php

use Nette\Localization\ITranslator;
use Nette\Application\UI\Control;
use Dibi\Connection;
use Locale\Locale;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * Class Table
 *
 * @author  geniv
 */
class Table extends Control
{
    /** @var string */
    private $prefix;
    /** @var string */
    private $tableName;
    /** @var Connection */
    private $connection;
    /** @var int|null */
    private $idLocale;
    /** @var Cache */
    private $cache;
    /** @var ITranslator|null */
    private $translator;
    /** @var string */
    private $templatePath;

    private $isCache = false;
    private $columnId = 'id', $columnLocale = 'id_locale';

    private $columns;
    private $where = null;
    private $order = null;
    private $limit = null, $offset = null;


    /**
     * Table constructor.
     *
     * @param                  $prefix
     * @param Connection       $connection
     * @param Locale           $locale
     * @param ITranslator|null $translator
     * @param IStorage         $storage
     */
    public function __construct($prefix, Connection $connection, Locale $locale, ITranslator $translator = null, IStorage $storage)
    {
        parent::__construct();

        $this->prefix = $prefix;
        $this->connection = $connection;
        $this->idLocale = $locale->getId();
        $this->translator = $translator;
        $this->cache = new Cache($storage, 'cache-Table');

        $this->templatePath = __DIR__ . '/Table.latte';    // default path
    }


    /**
     * Set template path.
     *
     * @param string $path
     * @return $this
     */
    public function setTemplatePath($path)
    {
        $this->templatePath = $path;
        return $this;
    }


    /**
     * Enable cache.
     *
     * @param mixed $isCache
     * @return $this
     */
    public function enableCache($isCache)
    {
        $this->isCache = $isCache;
        return $this;
    }


    /**
     * Set columns.
     *
     * @param mixed $columns
     * @return $this
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }


    /**
     * Set prefix table name.
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }


    /**
     * Set table name.
     *
     * @param string $tableName
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }


    /**
     * Set sql where.
     *
     * @param mixed $where
     * @return $this
     */
    public function setWhere($where)
    {
        $this->where[] = $where;
        return $this;
    }


    /**
     * Set sql order.
     *
     * @param mixed  $order
     * @param string $direction
     * @return $this
     */
    public function setOrder($order, $direction = 'asc')
    {
        $this->order[] = [$order, $direction];
        return $this;
    }


    /**
     * Set sql limit.
     *
     * @param mixed $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }


    /**
     * Set sql offset.
     *
     * @param mixed $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }


    /**
     * Set name column id.
     *
     * @param string $columnId
     * @return $this
     */
    public function setColumnId($columnId)
    {
        $this->columnId = $columnId;
        return $this;
    }


    /**
     * Set name column locale or disable locale.
     *
     * @param string $columnLocale
     * @return $this
     */
    public function setColumnLocale($columnLocale)
    {
        $this->columnLocale = $columnLocale;
        return $this;
    }


    /**
     * Get list data from db.
     */
    public function getList()
    {
        $cacheKey = 'list' . $this->prefix . $this->tableName . $this->idLocale;
        // ovladani cachovani
        if ($this->isCache) {
            $list = $this->cache->load($cacheKey);
            if ($list !== null) {
                return $list;
            }
        }

        // primare sql
        $cursor = $this->connection->select($this->columnId)->select($this->columns)
            ->from($this->prefix . $this->tableName);

        // set locale
        if (isset($this->columnLocale)) {
            $cursor->where([$this->columnLocale => $this->idLocale]);
        }

        // set where
        if (isset($this->where)) {
            $cursor->where($this->where);
        }

        // set order
        if (isset($this->order)) {
            $cursor->orderBy($this->order);
        }

        // set limit
        if ($this->limit) {
            $cursor->limit($this->limit);
        }

        // set offset
        if ($this->offset) {
            $cursor->offset($this->offset);
        }

        // ovladani cachovani
        if ($this->isCache) {
            if ($list === null) {
                $list = $cursor->fetchAll();
                // ulozeni cache
                $this->cache->save($cacheKey, $list
//                    , [
//                    Nette\Caching\Cache::EXPIRE => '30 minutes',
//                    Nette\Caching\Cache::TAGS   => ['getListItems'],
//                ]
                );
                return $list;
            }
        }
        return $cursor;
    }


    /**
     * Render default.
     */
    public function render()
    {
        $template = $this->getTemplate();

        $template->setTranslator($this->translator);
        $template->setFile($this->templatePath);
        $template->list = $this->getList();
        $template->render();
    }
}
