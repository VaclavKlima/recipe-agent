<?php

namespace App;

enum RecipePromptType: string
{
    case Suggestions = 'suggestions';
    case Structured = 'structured';
    case Refinement = 'refinement';
}
