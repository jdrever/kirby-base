<?php

declare(strict_types=1);

namespace BSBI\WebBase\cms;

use BSBI\WebBase\helpers\UserNameSearch;
use Kirby\Cms\Collection;
use Kirby\Cms\UserPicker;

/**
 * A user picker whose search narrows by full name using AND-of-words matching.
 *
 * Kirby's default {@see UserPicker} delegates to the global search component,
 * which matches *any* typed word across a user's name, email and role. On a site
 * with thousands of users this returns hundreds of loosely related results for a
 * full-name search. This subclass overrides only the search step so that every
 * typed word must appear in the user's name, leaving all other picker behaviour
 * (querying, listable filtering, sorting, pagination, formatting) unchanged.
 *
 * Wired up by the `usernamesearch` field registered in the plugin's index.php.
 *
 * @see UserNameSearch for the pure, unit-tested matching logic
 */
class UserNamePicker extends UserPicker
{
    /**
     * Narrows the picker's users to those whose name contains every typed word.
     *
     * @param Collection $items the listable users for this picker
     * @return Collection the users matching the current search (all, if no search)
     */
    public function search(Collection $items): Collection
    {
        $query = trim((string)($this->options['search'] ?? ''));

        if ($query === '') {
            return $items;
        }

        $words = UserNameSearch::words($query);

        // Nothing usable to search on (e.g. only single-character tokens) —
        // leave the list untouched rather than hiding everything.
        if ($words === []) {
            return $items;
        }

        return $items->filter(
            static fn ($user): bool => UserNameSearch::nameMatches((string)$user->name(), $words)
        );
    }
}
