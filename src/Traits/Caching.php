<?php namespace C4tech\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait Caching
{
    /**
     * @inheritDoc
     */
    public function getCacheId($suffix, $object_id = null)
    {
        if (!isset($object_id)) {
            $object_id = '';

            if ($this->object && $this->object->id) {
                $object_id = $this->object->id;
            }
        }

        return md5($this->getModelClass() . $object_id . $suffix);
    }

    /**
     * @inheritDoc
     */
    public function getTags($suffix = null)
    {
        $tags = [$this->formatTag($this->object->id)];
        if (!is_null($suffix)) {
            $tags[] = $this->formatTag($this->object->id, $suffix);
        }

        return $tags;
    }

    /**
     * @inheritDoc
     */
    public function formatTag($oid, $suffix = null)
    {
        return $this->buildTag($this->getModelClass(), $oid, $suffix);
    }

    /**
     * Build Tag
     *
     * Helper method to create a cache tag for the related model. General format
     * is {model}-{id}(-{suffx})? (e.g. App\Models\Users-18, App\Models\Users-10-posts)
     * @param  string $prefix Base tag
     * @param  int    $oid    Object ID
     * @param  string $suffix Additional text to inject into tag
     * @return string         Cache tag
     */
    protected function buildTag($prefix, $oid = null, $suffix = null)
    {
        if (is_null($oid)) {
            return str_plural($prefix);
        } elseif (is_null($suffix)) {
            return $prefix . '-' . $oid;
        } else {
            return $prefix . '-' . $oid . '-' . $suffix;
        }
    }
}
