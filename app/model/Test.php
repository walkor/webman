<?php

namespace app\model;

use support\Model;

class Test extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}