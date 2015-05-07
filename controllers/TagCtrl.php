<?php

class TagCtrl extends RMRController {

    protected $mediaTypes = ['json'];
    protected $properties = ['id', 'nombre', 'menciones'];
    protected $searchable = true;

    public function queryModel($meth, $repr) {
        return Tag::query();
    }

    public static function getTagIds(array $tags) {
        $vdt = new Validate\Validator();
        $vdt->addRule('tags', new Validate\Rule\Alphanum([' ']))
            ->addRule('tags', new Validate\Rule\MinLength(2))
            ->addRule('tags', new Validate\Rule\MaxLength(32));
        if (!$vdt->validate(['tags' => $tags])) {
            throw new TurnbackException($vdt->getErrors());
        } else if (count($tags) > 8) {
            throw new TurnbackException('No pueden asignarse más de 8 tags.');
        }
        $tagIds = array();
        foreach ($tags as $tagname) {
            $tagIds[] = Tag::firstOrCreate(['nombre' => FilterFactory::normalizeWhitespace($tagname)])->id;
        }
        return $tags;
    }

    public static function updateTags($taggable, $newTags) {
        $oldTags = $taggable->tags()->lists('id');
        $tagsIn = array_diff($newTags, $oldTags);
        $tagsOut = array_diff($oldTags, $newTags);
        Tag::whereIn('id', $tagsIn)->increment('menciones');
        Tag::whereIn('id', $tagsOut)->decrement('menciones');
        $taggable->tags()->sync($tagsIn);
    }

}
