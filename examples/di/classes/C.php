<?php

namespace Di;

use Larmias\Di\Annotation\Inject;
use Larmias\Di\Annotation\Scope;
use Larmias\Validation\Validator;

#[Scope(Scope::PROTOTYPE)]
class C
{
    #[Inject()]
    #[Scope(Scope::PROTOTYPE)]
    public Validator $validator;
}