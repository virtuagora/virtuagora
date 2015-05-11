<?php use Augusthur\Validation as Validate;

class TagCtrl extends RMRController {

    protected $mediaTypes = ['json'];
    protected $properties = ['id', 'nombre', 'menciones'];
    protected $searchable = true;

    public function queryModel($meth, $repr) {
        return Tag::query();
    }

    public static function getTagIds($tags) {
        if (!is_array($tags)) {
            throw new TurnbackException('Tags incorrectas.');
        }
        $vdt = new Validate\Validator();
        $vdt->addRule('tags', new Validate\Rule\AlphaNumeric([' ']))
            ->addRule('tags', new Validate\Rule\MinLength(2))
            ->addRule('tags', new Validate\Rule\MaxLength(32));
        if (!$vdt->validate(['tags' => $tags])) {
            throw new TurnbackException($vdt->getErrors());
        } else if (count($tags) > 8) {
            throw new TurnbackException('No pueden asignarse más de 8 tags.');
        }
        $tagIds = array();
        foreach ($tags as $tag) {
            $tagIds[] = Tag::firstOrCreate(['nombre' => FilterFactory::normalizeWhitespace($tag)])->id;
        }
        return $tagIds;
    }

    public static function updateTags($taggable, $newTags) {
        $oldTags = $taggable->tags()->lists('tag_id');
        $tagsIn = array_diff($newTags, $oldTags);
        $tagsOut = array_diff($oldTags, $newTags);
        if (!empty($tagsIn)) {
            Tag::whereIn('id', $tagsIn)->increment('menciones');
            $taggable->tags()->attach($tagsIn);
        }
        if (!empty($tagsOut)) {
            Tag::whereIn('id', $tagsOut)->decrement('menciones');
            $taggable->tags()->detach($tagsOut);
        }
    }

}
