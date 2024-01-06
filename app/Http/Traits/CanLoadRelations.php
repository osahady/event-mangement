<?php
namespace App\Http\Traits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;

Trait CanLoadRelations
{
    public function loadRelations(
        Model | EloquentBuilder | QueryBuilder | HasMany $for,
        array $rels = null
    ) : Model | EloquentBuilder | QueryBuilder | HasMany
    {
        $rels = $rels ?? $this->allowedRelations ?? [];

        foreach($rels as $rel){
            // check if the $for is an instance of Model so use load method else use with method with ternary operator

            $for->when($this->isAllowedRel($rel),
                fn($q) => $for instanceof Model ? $for->load($rel) : $q->with($rel));
        }
        return $for;
    }
    public function isAllowedRel(string $rel)
    {
        $rels = request()->query('rel');
        if(!$rels){
            return false;
        }
        $rels = array_map('trim', explode(',', $rels));
        return in_array($rel, $rels);
    }


}
