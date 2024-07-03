<?php

namespace App\Services;

class CodeGeneratorService
{
    /**
     * Generate a unique code based on the tag, current year, and count of existing records.
     *
     * @param string $tag       Prefix or tag for the code
     * @param string $modelName Fully qualified model name (including namespace)
     * @return string           Generated code
     */
    public function generateCode(string $tag, string $modelName): string
    {
        $year = now()->year;
        
        // Dynamically resolve the model instance using the Laravel service container
        $modelInstance = resolve($modelName);

        // Count existing records with codes containing the current year
        $count = $modelInstance->where('code', 'like', "%$year%")->count();

        // Generate the code format: {tag}/{year}/{count}
        return "{$tag}/{$year}/{$count}";
    }
}
