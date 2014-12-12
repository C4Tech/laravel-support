<?php namespace dukky\repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Node Repository
 *
 * Common business logic for Node-based Models.
 */
abstract class NodeRepository extends Repository
{
    /**
     * Tags to use for flushing parent caches.
     * @var array
     */
    protected $parent_tags = null;

    /**
     * Tags to use for flushing child caches.
     * @var array
     */
    protected $child_tags = null;

    public static function boot($class = null)
    {
        $class = ($class) ?: get_called_class();
        parent::boot($class);

        // Trigger save events after move events
        $touch_model = function ($node) {
            if (Config::get('app.debug')) {
                Log::debug('Touching parents.', $node->getAncestors()->lists('id'));
            }

            // Force parent caches to flush
            if ($node->parent) {
                $node->parent->touch();
            }
        };
        static::moved($touch_model);

        // Flush caches related to the ancestors
        $clear_parent_cache = function ($node) use ($class) {
            $tags = $class::make($node)
                ->getParentTags();
            if (Config::get('app.debug')) {
                Log::debug('Flushing parent caches.', ['tags' => $tags]);
            }
            Cache::tags($tags)->flush();
        };
        static::saved($clear_parent_cache);
        static::deleted($clear_parent_cache);
    }

    /**
     * Get Parent Tags
     *
     * Retrieves the parent tags which should be flushed after a write query.
     * @return array Cache tags
     */
    protected function getParentTags()
    {
        if (!$this->parent_tags) {
            $self = get_called_class();
            $this->parent_tags = array_build(
                $this->getParentsIds(),
                function ($key, $value) use ($self) {
                    return [$key, $self::formatTag($value)];
                }
            );
        }
        return $this->parent_tags;
    }

    /**
     * Get Child Tags
     *
     * Retrieves the child tags which should be flushed after a write query.
     * @return array Cache tags
     */
    protected function getChildTags()
    {
        if (!$this->child_tags) {
            $self = get_called_class();
            $this->child_tags = array_build(
                $this->getChildrenIds(false),
                function ($key, $value) use ($self) {
                    return [$key, $self::formatTag($value)];
                }
            );
        }
        return $this->child_tags;
    }

    /**
     * (Immediate) Children
     *
     * Retrieves and caches child objects.
     * @return \Illuminate\Support\Collection
     */
    public function children()
    {
        return $this->object->children()
            ->cacheTags($this->getTags())
            ->rememberForever();
    }

    /**
     * Get Children
     *
     * Retrieves and caches child objects.
     * @return \Illuminate\Support\Collection
     */
    public function getChildren($include_self = true)
    {
        $scope = ($include_self) ? 'descendantsAndSelf' : 'descendants';
        return $this->object->$scope()
            ->cacheTags($this->getTags())
            ->rememberForever()
            ->get();
    }

    /**
     * Get Parents
     *
     * Retrieves and caches parent objects.
     * @return \Illuminate\Support\Collection
     */
    public function getParents($include_self = true)
    {
        $scope = ($include_self) ? 'ancestorsAndSelf' : 'ancestors';
        return $this->object->$scope()
            ->cacheTags($this->getTags())
            ->rememberForever()
            ->get();
    }

    /**
     * Get Children IDs
     *
     * Retrieves child object IDs.
     * @return array
     */
    public function getChildrenIds($include_self = true)
    {
        return $this->getChildren($include_self)->lists('id');
    }

    /**
     * Get Parents IDs
     *
     * Retrieves parent object IDs.
     * @return array
     */
    public function getParentsIds($include_self = true)
    {
        return $this->getParents($include_self)->lists('id');
    }

    public static function roots()
    {
        $model = static::$class;
        return $model::roots()
            ->cacheTags(static::formatTag('roots'))
            ->rememberForever();
    }

    public static function getRoots()
    {
        return static::roots()
            ->get();
    }
}
