<?php

class Menu extends \Larmias\Repository\Models\ThinkModel
{
    protected $table = 'system_menu';
}

class MenuRepository extends Larmias\Repository\AbstractRepository
{
    public function model(): string
    {
        return Menu::class;
    }
}