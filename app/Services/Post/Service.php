<?php

namespace App\Services\Post;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class Service
{
    public function store($data)
    {
        try {
            Db::beginTransaction();

            $tags = $data['tags'];
            $category = $data['category'];
            unset($data['tags'], $data['category']);


            $tagIds = $this->getTagIds($tags);
            $data['category_id'] = $this->getCategoryId($category);

            $post = Post::create($data);

            $post->tags()->attach($tagIds);

            Db::commit();

        } catch (\Exception $exception) {
            Db::rollBack();
            return $exception->getMessage();
        }

        return $post;
    }

    public function update($post, $data)
    {
        $tags = $data['tags'];
        $category = $data['category'];
        unset($data['tags'], $data['category']);


        $tagIds = $this->getTagIdsWithUpdate($tags);
        $data['category_id'] = $this->getCategoryId($category);

        $post->update($data);
        $post->tags()->sync($tagIds);
        return $post->fresh();
    }

    private function getCategoryId($item)
    {
        $category = !isset($item['id']) ? Category::create($item) : Category::find($item['id']);
    }

    private function getTagIdsWithUpdate($tags)
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            if (!isset($tag['id'])) {
                $tag = Tag::create($tag);
            } else {
                $currentTag = Tag::find($tag['id']);
                $currentTag->update($tag);
                $tag = $currentTag->fresh();
            }
            $tagIds[] = $tag->id;
        }

        return $tagIds;
    }

}
