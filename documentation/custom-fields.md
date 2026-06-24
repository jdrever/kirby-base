# Custom Fields

KirbyBase includes specialized field types that extend Kirby's built-in fields to address specific use cases.

## usernamesearch

A drop-in replacement for Kirby's built-in `users` field that uses AND-of-words matching on full names instead of Kirby's default OR-of-words search across name, email, and role.

### Problem

On sites with large user bases (1,000+), Kirby's default user picker uses the global search component, which matches *any* typed word across a user's name, email, and role fields. For example, searching "Janet Simkin" returns ~152 results (everyone called Janet OR anyone called Simkin) instead of narrowing to the specific person.

### Solution

The `usernamesearch` field type filters the user list using AND-of-words matching on the user's full name only. Every word in the search query must appear somewhere in the name (case-insensitive, substring-based). Tokens shorter than 2 characters are dropped as noise.

### Usage

In any blueprint, use `type: usernamesearch` instead of `type: users`:

```yaml
postedBy:
  type: usernamesearch
  label: Posted by
  max: 1
  readonly: false
```

It is a drop-in replacement — the stored value format, props (`readonly`, `min`, `max`, etc.), and panel UI are identical to the core `users` field.

### Implementation

The field is implemented in two parts:

1. **PHP backend** (`classes/cms/UserNamePicker.php` and `classes/helpers/UserNameSearch.php`):
   - `UserNameSearch` — pure, unit-tested helper with two static methods:
     - `words(string $query): array` — splits a query into lowercased tokens, dropping anything shorter than 2 characters
     - `nameMatches(string $name, array $words): bool` — returns true only if every word appears in the name
   - `UserNamePicker` — extends Kirby's `UserPicker` and overrides only the `search()` method to use `UserNameSearch` logic. All other picker behaviour (querying, listable filtering, sorting, pagination, formatting) is inherited unchanged.

2. **Vue panel component** (`index.js`):
   - Registers the panel field component `usernamesearch` which extends the core `k-users-field`, so it renders identically to the built-in field.

> [!NOTE]
> A Kirby custom field requires **both** the PHP definition (backend) AND the panel Vue component (frontend). Without either half, the panel will show "field type X does not exist".

### Examples

**Search works with substrings and every word must match:**

| Query | Janet Simkin | Janet Wilson | Simon Simkin |
|-------|--------------|--------------|-------------|
| "janet" | ✓ | ✓ | ✗ |
| "simkin" | ✓ | ✗ | ✓ |
| "janet simkin" | ✓ | ✗ | ✗ |
| "ja sim" | ✓ | ✗ | ✗ |
| "janet wil" | ✗ | ✓ | ✗ |

**Short tokens are ignored:**

- Query "a jane" → treated as "jane" (drops "a")
- Query "j s" → returns all users (no tokens >= 2 characters)

### When to use

Use `usernamesearch` when:
- Your site has 500+ users and full-name search is a common panel task
- You want to reduce false positives from first-name-only or last-name-only matches
- Your users have short, common names that would generate many OR-based false positives

You can safely use it alongside the standard `users` field — the choice is per-field, so different page types can use different picker strategies.
