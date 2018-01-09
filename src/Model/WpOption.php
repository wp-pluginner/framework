<?php

namespace WpPluginner\Framework\Model;

use WpPluginner\Framework\Foundation\Model;

class WpOption extends Model {

    protected $table = 'options';
    public $timestamps = false;
    protected $primaryKey = 'option_id';
    protected $fillable = ['option_name','option_value','autoload'];

}
