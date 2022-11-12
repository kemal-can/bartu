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

namespace App\Innoclapps\Timeline;

class Timeline
{
    /**
     * Registered pinable subjects
     *
     * @var array
     */
    protected static array $pinableSubjects = [];

    /**
     * Register pinable subject
     *
     * @param array $subject List of the models that are pinable
     *
     * @return void
     */
    public static function acceptsPinsFrom(array $subject) : void
    {
        // If exists, merge the accepts only
        if (static::getPinableSubject($subject['as'])) {
            $index = array_search($subject['as'], array_column(static::$pinableSubjects, 'as'));

            static::$pinableSubjects[$index]['accepts'] = array_merge(
                static::$pinableSubjects[$index]['accepts'],
                $subject['accepts']
            );

            return;
        }

        static::$pinableSubjects[] = $subject;
    }

    /**
     * Flush the pinable subjects cache
     *
     * @return void
     */
    public static function flushPinableSubjects() : void
    {
        static::$pinableSubjects = [];
    }

    /**
     * Get pinable subject
     *
     * @param string $subject
     *
     * @return array|null
     */
    public static function getPinableSubject(string $subject) : ?array
    {
        return collect(static::$pinableSubjects)->firstWhere('as', $subject);
    }

    /**
     * Get subject accepted timelineable
     *
     * @param string $subject
     * @param string $timelineableType
     *
     * @return array|null
     */
    public static function getSubjectAcceptedTimelineable(string $subject, string $timelineableType) : ?array
    {
        $accepts = static::getPinableSubject($subject)['accepts'] ?? [];

        return collect($accepts)->firstWhere('as', $timelineableType);
    }
}
