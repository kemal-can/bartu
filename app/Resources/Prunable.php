<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Resources;

use Illuminate\Database\Eloquent\Prunable as LaravelPrunable;

trait Prunable
{
    use LaravelPrunable;

    /**
     * Prune the model in the database.
     *
     * @return boolean|null
     */
    public function prune()
    {
        $this->pruning();

        return static::resource()->repository()->forceDelete(
            $this->getKey()
        );
    }

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subDays(
            config('innoclapps.soft_deletes.prune_after')
        ));
    }
}
