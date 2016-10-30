<?php
abstract class BmobQuery {
    public abstract function build();
}

class BmobKeyQuery extends BmobQuery {
    protected $key;
    protected $value;

    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    public function build() {
        $value = is_string($this->value) ?
            $this->value : $this->value->build();
        return "{\"$this->key\":\"$value\"}";
    }
}

class BmobQueryList {
    private $queryList;

    public function __construct($queryList = null) {
        if (isset($queryList))
            $this->queryList = $queryList;
        else
            $this->queryList = array();
    }

    public function build() {
        $queryString = '[';
        $counter = 0;
        foreach ($this->queryList as $query) {
            if ($counter > 0)
                $queryString = $queryString.',';
            $queryString = $queryString.$query->build();
            ++$counter;
        }
        return $queryString.']';
    }

    public function addQuery($query) {
        array_push($this->queryList, $query);
        return $this;
    }

    public function addKeyQuery($key, $value) {
        return $this->addQuery(new BmobKeyQuery($key, $value));
    }
}

class BmobCompositeQuery extends BmobKeyQuery {
    public function __construct($op, $queryList = null) {
        if (!isset($queryList))
            $queryList = new BmobQueryList();
        parent::__construct($op, $queryList);
    }

    public function addQuery($key, $query) {
        $this->value->addQuery($key, $query);
        return $this;
    }

    public function addKeyQuery($key, $value) {
        $this->value->addKeyQuery($key, $value);
        return $this;
    }
}

class BmobAndQuery extends BmobCompositeQuery {
    public function __construct($queryList = null) {
        parent::__construct('$and', $queryList);
    }
}

class BmobOrQuery extends BmobCompositeQuery {
    public function __construct($queryList = null) {
        parent::__construct('$or', $queryList);
    }
}
?>