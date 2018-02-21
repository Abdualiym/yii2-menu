<?php

namespace abdualiym\menu\entities\queries;

use paulzi\nestedsets\NestedSetsQueryTrait;
use yii\db\ActiveQuery;

class MenuQuery extends ActiveQuery
{
    use NestedSetsQueryTrait;
}