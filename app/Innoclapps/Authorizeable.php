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

namespace App\Innoclapps;

use Closure;
use Illuminate\Support\Facades\Auth;

trait Authorizeable
{
    /**
     * Hold the canSee method closure
     *
     * @var \Closure
     */
    public $canSeeClosure = null;

    /**
     * Hold the canSeeWhen method data
     *
     * @var array
     */
    public $canSeeWhenArrayClosure = null;

    /**
     * canSee method to perform checks on specific class
     *
     * @param \Closure $callable
     *
     * @return static
     */
    public function canSee(Closure $callable)
    {
        $this->canSeeClosure = $callable;

        return $this;
    }

    /**
     * canSeeWhen, the same signature like user()->can()
     *
     * @param string $ability the ability
     * @param array $arguments
     *
     * @return static
     */
    public function canSeeWhen($ability, $arguments = [])
    {
        $this->canSeeWhenArrayClosure = [$ability, $arguments];

        return $this;
    }

    /**
     * Authorize or fail
     *
     * @param string $message The failure message
     *
     * @return mixed
     */
    public function authorizeOrFail($message = null)
    {
        if (! $this->authorizedToSee()) {
            abort(403, $message ?? 'You are not authorized to perform this action.');
        }

        return $this;
    }

    /**
     * Check whether the user can see a specific item
     *
     * @param \App\Models\User|null $user
     *
     * @return boolean
     */
    public function authorizedToSee($user = null)
    {
        if (! $this->hasAuthorization()) {
            return true;
        }

        if ($this->canSeeWhenArrayClosure) {
            if (! $user && ! Auth::user()) {
                return false;
            }

            return ($user ?: Auth::user())->can(...$this->canSeeWhenArrayClosure);
        }

        return call_user_func($this->canSeeClosure, request());
    }

    /**
     * Check whether on specific class/item is added authorization via canSee and canSeeWhen
     *
     * @return boolean
     */
    public function hasAuthorization()
    {
        return $this->canSeeClosure || $this->canSeeWhenArrayClosure;
    }
}
