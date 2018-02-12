<?php

use Nette\Localization\ITranslator;
use Nette\Application\UI\Control;
use Dibi\Connection;
use Locale\ILocale;
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
    private $cacheDependencies = null;

    private $columnId = 'id';
    private $columnLocale = null;

    private $columns;
    /** @var array */
    private $join = [];
    /** @var array */
    private $leftJoin = [];
    /** @var array */
    private $where = [];
    /** @var array */
    private $order = [];
    private $limit = null, $offset = null;


    /**
     * Table constructor.
     *
     * @param string           $prefix
     * @param Connection       $connection
     * @param ILocale          $locale
     * @param ITranslator|null $translator
     * @param IStorage         $storage
     */
    public function __construct($prefix, Connection $connection, ILocale $locale, ITranslator $translator = null, IStorage $storage)
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
     * @param bool $isCache
     * @return $this
     */
    public function enableCache($isCache)
    {
        $this->isCache = $isCache;
        return $this;
    }


    /**
     * Set cache dependencies.
     *
     * @param array $cacheDependencies
     * @return $this
     */
    public function setCacheDependencies(array $cacheDependencies)
    {
        $this->cacheDependencies = $cacheDependencies;
        return $this;
    }


    /**
     * Set columns.
     *
     * @param array $columns
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
     * @param null   $as
     * @return $this
     */
    public function setTableName($tableName, $as = null)
    {
        $this->tableName = [$tableName, $as];
        return $this;
    }


    /**
     * Add sql join.
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @return $this
     */
    public function addJoin($table, $as, $on)
    {
        $this->join[] = [$table, $as, $on];
        return $this;
    }


    /**
     * Add sql left join.
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @return $this
     */
    public function addLeftJoin($table, $as, $on)
    {
        $this->leftJoin[] = [$table, $as, $on];
        return $this;
    }


    /**
     * Add sql where.
     *
     * @param mixed $where
     * @return $this
     */
    public function addWhere($where)
    {
        $this->where[] = $where;
        return $this;
    }


    /**
     * Add sql order.
     *
     * @param mixed  $order
     * @param string $direction
     * @return $this
     */
    public function addOrder($order, $direction = 'ASC')
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
     * usually: id_locale.
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
     * Get id locale.
     *
     * @return int|null
     */
    public function getIdLocale()
    {
        return $this->idLocale;
    }


    /**
     * Get list data from db.
     *
     * @return mixed
     */
    public function getList()
    {
        $cacheKey = 'list' . $this->prefix . $this->tableName[0] . $this->idLocale;
        // control cache
        if ($this->isCache) {
            $list = $this->cache->load($cacheKey);
            if ($list !== null) {
                return $list;
            }
        }

        list($tableName, $tableNameAs) = $this->tableName;
        // primary sql
        $cursor = $this->connection->select(($tableNameAs ? $tableNameAs . '.' : '') . $this->columnId)->select($this->columns)
            ->from($this->prefix . $tableName);
        // set from as
        if ($tableNameAs) {
            $cursor->as($tableNameAs);
        }

        // add join
        if (isset($this->join) && $this->join) {
            foreach ($this->join as $item) {
                list($table, $as, $on) = $item;
                $cursor->join($this->prefix . $table)->as($as)->on($on);
            }
        }

        // add left join
        if (isset($this->leftJoin) && $this->leftJoin) {
            foreach ($this->leftJoin as $item) {
                list($table, $as, $on) = $item;
                $cursor->leftJoin($this->prefix . $table)->as($as)->on($on);
            }
        }

        // set locale
        if (isset($this->columnLocale) && $this->columnLocale) {
            $cursor->where([$this->columnLocale => $this->idLocale]);
        }

        // add where
        if (isset($this->where) && $this->where) {
            if (is_array($this->where)) {
                foreach ($this->where as $where) {
                    $cursor->where($where);
                }
            } else {
                $cursor->where($this->where);
            }
        }

        // add order
        if (isset($this->order) && $this->order) {
            $cursor->orderBy($this->order);
        }

        // set limit
        if (isset($this->limit) && $this->limit) {
            $cursor->limit($this->limit);
        }

        // set offset
        if (isset($this->offset) && $this->offset) {
            $cursor->offset($this->offset);
        }

        // control cache
        if ($this->isCache) {
            if ($list === null) {
                $list = $cursor->fetchAll();
                // ulozeni cache
                $this->cache->save($cacheKey, $list, $this->cacheDependencies);
                return $list;
            }
        }
        return $cursor;
    }


    /**
     * Render.
     */
    public function render()
    {
        $template = $this->getTemplate();

        $template->list = $this->getList();

        $template->setTranslator($this->translator);
        $template->setFile($this->templatePath);
        $template->render();
    }
}
