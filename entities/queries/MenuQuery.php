<?php

namespace domain\modules\menu\entities\queries;

use paulzi\nestedsets\NestedSetsQueryTrait;
use yii\db\ActiveQuery;

class MenuQuery extends ActiveQuery
{
    use NestedSetsQueryTrait;
}