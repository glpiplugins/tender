<?php

namespace GlpiPlugin\Tender;

use DBmysqlIterator;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;

class DBmysqlIteratorHelper extends DBmysqlIterator {

    public function toArray() : array {
        // print_r($this);
        $result = [$this->row];
        foreach ($this as $item) {
            print_r($item);
            $result[] = $item;
        }

        return $result;
    }

}