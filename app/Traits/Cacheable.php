<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait Cacheable
{
    /**
     * Clear cache for the current model and related cache keys.
     *
     * @param array $relatedList An array of additional cache keys to clear.
     * @return void
     */
    public function clearCache(array $relatedList = []): void
    {
        // Get the model name in lowercase
        $modelName = strtolower(class_basename(static::class));

        // Log the model name for debugging purposes
        Log::info("Clearing cache for model: {$modelName}");

        // Generate the main cache key for the model
        $key = "cache_key_list_{$modelName}";

        // Retrieve the list of cache keys associated with the model
        $keyList = Cache::get($key, []);

        // Clear all cache entries related to the model
        foreach ($keyList as $cacheKey) {
            Cache::forget($cacheKey);
        }

        // Clear any additional related cache keys passed as an argument
        foreach ($relatedList as $cacheKey) {
            $keyList = Cache::get($cacheKey, []);
            foreach ($keyList as $cacheKey) {
                Cache::forget($cacheKey);
            }
        }

        // Finally, clear the main cache key for the model
        Cache::forget($key);
    }
}
