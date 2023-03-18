<?php

namespace App\Services;

use App\Models\App;
use App\Models\AppPosition;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class AppService
{
    /**
     * Get application positions from database.
     */
    public function getSavedAppPositions(App $app, string $selectedDate): Collection
    {
        return AppPosition::where([
            'app_id' => $app->id,
            'date' => $selectedDate,
        ])->get();
    }

    public function createAppPositions(App $app, array $positions, string $selectedDate): void
    {
        foreach ($positions as $categoryId => $position) {
            $category = Category::firstOrCreate(['id' => $categoryId]);

            $appPosition = new AppPosition();
            $appPosition->app_id = $app->id;
            $appPosition->category_id = $category->id;
            $appPosition->position = $position;
            $appPosition->date = $selectedDate;
            $appPosition->save();
        }
    }
}
