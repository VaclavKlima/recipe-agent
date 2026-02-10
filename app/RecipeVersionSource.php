<?php

namespace App;

enum RecipeVersionSource: string
{
    case Initial = 'initial';
    case Refinement = 'refinement';
}
