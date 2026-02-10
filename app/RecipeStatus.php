<?php

namespace App;

enum RecipeStatus: string
{
    case Draft = 'draft';
    case Saved = 'saved';
}
