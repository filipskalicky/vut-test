<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * News items with a public visibility window.
 *
 * Datetimes are compared in PHP (Y-m-d H:i:s) so the same query works on
 * MariaDB and on the SQLite in-memory database used by PHPUnit.
 */
class NewsModel extends Model
{
    protected $table         = 'news';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'content',
        'visible_from',
        'visible_to',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $beforeInsert  = ['normalizeVisibilityWindow'];
    protected $beforeUpdate  = ['normalizeVisibilityWindow'];

    /**
     * Validation used by admin create/update.
     *
     * @var array<string, string>
     */
    protected $validationRules = [
        'title'        => 'required|max_length[255]',
        'content'      => 'required|valid_editorjs',
        'visible_from' => 'required|valid_datetime',
        'visible_to'   => 'permit_empty|valid_datetime|date_on_or_after[visible_from]',
    ];

    /**
     * Czech messages shown next to the admin form fields.
     *
     * @var array<string, array<string, string>>
     */
    protected $validationMessages = [
        'title' => [
            'required'   => 'Nadpis je povinný.',
            'max_length' => 'Nadpis může mít nejvýše 255 znaků.',
        ],
        'content' => [
            'required'       => 'Obsah aktuality je povinný.',
            'valid_editorjs' => 'Obsah aktuality není ve správném formátu nebo je prázdný.',
        ],
        'visible_from' => [
            'required'       => 'Datum a čas, odkdy se má aktualita zobrazovat, jsou povinné.',
            'valid_datetime' => 'Zadejte platné datum a čas.',
        ],
        'visible_to' => [
            'valid_datetime'   => 'Zadejte platné datum a čas.',
            'date_on_or_after' => 'Konec zobrazení nesmí být dřívější než začátek.',
        ],
    ];

    public const DEFAULT_PER_PAGE = 5;

    /**
     * Admin listing default. The public list always uses DEFAULT_PER_PAGE.
     */
    public const ADMIN_DEFAULT_PER_PAGE = 10;

    /**
     * Allowed listing page sizes for the admin overview.
     * The public list always uses DEFAULT_PER_PAGE and ignores per_page.
     *
     * @var list<int>
     */
    public const PER_PAGE_OPTIONS = [5, 10, 20, 50];

    public const DEFAULT_SORT = 'published';

    public const DEFAULT_DIR = 'desc';

    /**
     * @var list<string>
     */
    public const SORT_FIELDS = ['published', 'title', 'created', 'updated'];

    /**
     * @var array<string, string>
     */
    public const SORT_COLUMNS = [
        'title'     => 'title',
        'published' => 'visible_from',
        'created'   => 'created_at',
        'updated'   => 'updated_at',
    ];

    /**
     * @var list<string>
     */
    public const STATUS_OPTIONS = ['scheduled', 'visible', 'expired'];

    /**
     * Accept only the allowed page sizes; anything else falls back to the default.
     */
    public static function resolvePerPage(mixed $value): int
    {
        $perPage = (int) $value;

        return in_array($perPage, self::PER_PAGE_OPTIONS, true)
            ? $perPage
            : self::ADMIN_DEFAULT_PER_PAGE;
    }

    /**
     * Admin list sort field: publication, title, created or updated.
     */
    public static function resolveSort(mixed $value): string
    {
        $sort = strtolower(trim((string) $value));

        return in_array($sort, self::SORT_FIELDS, true)
            ? $sort
            : self::DEFAULT_SORT;
    }

    /**
     * Admin list sort direction.
     */
    public static function resolveDir(mixed $value): string
    {
        return strtolower(trim((string) $value)) === 'asc' ? 'asc' : self::DEFAULT_DIR;
    }

    /**
     * Admin visibility-status filter. Empty means every state.
     *
     * @return list<string>
     */
    public static function resolveStatuses(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[,\s]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_intersect(
            self::STATUS_OPTIONS,
            array_map(static fn (mixed $item): string => strtolower(trim((string) $item)), $value),
        ));
    }

    /**
     * Query params that keep admin search, sort and status across GET forms.
     *
     * @param list<string> $statuses
     *
     * @return array<string, string>
     */
    public static function listingQueryParams(
        string $search = '',
        string $sort = self::DEFAULT_SORT,
        string $dir = self::DEFAULT_DIR,
        array $statuses = [],
    ): array {
        $params = [];

        if ($search !== '') {
            $params['q'] = $search;
        }

        if ($sort !== self::DEFAULT_SORT) {
            $params['sort'] = $sort;
        }

        if ($dir !== self::DEFAULT_DIR) {
            $params['dir'] = $dir;
        }

        if ($statuses !== []) {
            $params['status'] = implode(',', $statuses);
        }

        return $params;
    }

    /**
     * Trim the listing search query and cap its length.
     */
    public static function resolveSearch(mixed $value): string
    {
        $query = trim((string) $value);
        if ($query === '') {
            return '';
        }

        return mb_strlen($query) > 100 ? mb_substr($query, 0, 100) : $query;
    }

    /**
     * LIKE fragments so Czech-formatted dates also match stored Y-m-d values.
     *
     * @return list<string>
     */
    public static function searchDateTerms(string $query): array
    {
        $query = trim(preg_replace('/\s+/u', ' ', $query) ?? $query);
        if ($query === '') {
            return [];
        }

        $terms = [$query];

        $patterns = [
            '/^(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})\s+(\d{1,2}):(\d{2})$/' => static fn (array $m): string => sprintf(
                '%04d-%02d-%02d %02d:%02d',
                (int) $m[3],
                (int) $m[2],
                (int) $m[1],
                (int) $m[4],
                (int) $m[5],
            ),
            '/^(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})$/' => static fn (array $m): string => sprintf(
                '%04d-%02d-%02d',
                (int) $m[3],
                (int) $m[2],
                (int) $m[1],
            ),
            '/^(\d{1,2})\.\s*(\d{1,2})\.?$/' => static fn (array $m): string => sprintf(
                '-%02d-%02d',
                (int) $m[2],
                (int) $m[1],
            ),
            '/^(\d{1,2})\.\s*(\d{4})$/' => static fn (array $m): string => sprintf(
                '%04d-%02d',
                (int) $m[2],
                (int) $m[1],
            ),
        ];

        foreach ($patterns as $regex => $builder) {
            if (preg_match($regex, $query, $matches) === 1) {
                $terms[] = $builder($matches);

                break;
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * Public list: newest first, only items visible today, paginated.
     *
     * An item is visible when visible_from <= now AND
     * (visible_to IS NULL OR visible_to >= now).
     *
     * @return list<array<string, mixed>>
     */
    public function getPublicPaginated(int $perPage = self::DEFAULT_PER_PAGE, ?int $page = null, string $search = ''): array
    {
        $now = $this->now();

        return $this
            ->where('visible_from <=', $now)
            ->groupStart()
                ->where('visible_to', null)
                ->orWhere('visible_to >=', $now)
            ->groupEnd()
            ->applySearch($search, false)
            ->orderBy('visible_from', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate($perPage, 'default', $page);
    }

    /**
     * Single public item, or null when it is missing or outside the visibility window.
     *
     * @return array<string, mixed>|null
     */
    public function findPublicById(int $id): ?array
    {
        $item = $this->find($id);

        if ($item === null || ! $this->isCurrentlyVisible($item)) {
            return null;
        }

        return $item;
    }

    /**
     * Visible items for sitemap.xml (id + timestamps only).
     *
     * @return list<array<string, mixed>>
     */
    public function getPublicSitemapEntries(): array
    {
        $now = $this->now();

        return $this
            ->select('id, visible_from, updated_at')
            ->where('visible_from <=', $now)
            ->groupStart()
                ->where('visible_to', null)
                ->orWhere('visible_to >=', $now)
            ->groupEnd()
            ->orderBy('visible_from', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Admin list: every item, paginated, with optional search, sort and status filter.
     *
     * @param list<string> $statuses
     *
     * @return list<array<string, mixed>>
     */
    public function getAllPaginated(
        int $perPage = self::ADMIN_DEFAULT_PER_PAGE,
        ?int $page = null,
        string $search = '',
        string $sort = self::DEFAULT_SORT,
        string $dir = self::DEFAULT_DIR,
        array $statuses = [],
    ): array {
        return $this
            ->applySearch($search, true)
            ->applyStatusFilter($statuses)
            ->applyListingOrder($sort, $dir)
            ->paginate($perPage, 'default', $page);
    }

    /**
     * Admin list: every item, same newest-first order.
     *
     * @return list<array<string, mixed>>
     */
    public function getAllSorted(): array
    {
        return $this
            ->orderBy('visible_from', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Match the query against the title and visibility dates.
     */
    protected function applySearch(string $search, bool $includeVisibleTo): self
    {
        $search = trim($search);
        if ($search === '') {
            return $this;
        }

        $this->groupStart();
        $this->like('title', $search);

        foreach (self::searchDateTerms($search) as $term) {
            $this->orLike('visible_from', $term);
            if ($includeVisibleTo) {
                $this->orLike('visible_to', $term);
            }
        }

        $this->groupEnd();

        return $this;
    }

    /**
     * Limit the admin list to selected visibility states.
     *
     * @param list<string> $statuses
     */
    protected function applyStatusFilter(array $statuses): self
    {
        $statuses = array_values(array_intersect(self::STATUS_OPTIONS, $statuses));
        if ($statuses === [] || count($statuses) === 3) {
            return $this;
        }

        $now     = $this->now();
        $started = false;

        $this->groupStart();

        if (in_array('scheduled', $statuses, true)) {
            $this->where('visible_from >', $now);
            $started = true;
        }

        if (in_array('visible', $statuses, true)) {
            if ($started) {
                $this->orGroupStart();
            } else {
                $this->groupStart();
            }

            $this
                ->where('visible_from <=', $now)
                ->groupStart()
                    ->where('visible_to', null)
                    ->orWhere('visible_to >=', $now)
                ->groupEnd()
                ->groupEnd();
            $started = true;
        }

        if (in_array('expired', $statuses, true)) {
            if ($started) {
                $this->orGroupStart();
            } else {
                $this->groupStart();
            }

            $this
                ->where('visible_from <=', $now)
                ->where('visible_to <', $now)
                ->groupEnd();
        }

        $this->groupEnd();

        return $this;
    }

    /**
     * Admin list order by the chosen column, then id as a tie-breaker.
     */
    protected function applyListingOrder(string $sort, string $dir): self
    {
        $column    = self::SORT_COLUMNS[$sort] ?? 'visible_from';
        $direction = $dir === 'asc' ? 'ASC' : 'DESC';

        return $this
            ->orderBy($column, $direction)
            ->orderBy('id', $direction);
    }

    /**
     * Whether the row would appear on the public page right now.
     *
     * @param array<string, mixed> $row
     */
    public function isCurrentlyVisible(array $row, ?string $now = null): bool
    {
        return $this->visibilityState($row, $now) === 'visible';
    }

    /**
     * Public visibility: visible now, scheduled in the future, or already expired.
     *
     * @param array<string, mixed> $row
     *
     * @return 'expired'|'scheduled'|'visible'
     */
    public function visibilityState(array $row, ?string $now = null): string
    {
        $now  ??= $this->now();
        $from = normalize_app_datetime((string) ($row['visible_from'] ?? '')) ?? '';
        $to   = normalize_app_datetime(isset($row['visible_to']) ? (string) $row['visible_to'] : null);

        if ($from === '' || $from > $now) {
            return $from !== '' && $from > $now ? 'scheduled' : 'expired';
        }

        if ($to !== null && $to < $now) {
            return 'expired';
        }

        return 'visible';
    }

    /**
     * Current datetime in the application timezone (Europe/Prague).
     */
    public function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Store picker values as Y-m-d H:i:s. Date-only input becomes midnight.
     *
     * @param array<string, mixed> $eventData
     *
     * @return array<string, mixed>
     */
    protected function normalizeVisibilityWindow(array $eventData): array
    {
        foreach (['visible_from', 'visible_to'] as $field) {
            if (! array_key_exists($field, $eventData['data'])) {
                continue;
            }

            $value = $eventData['data'][$field];
            if ($value === null || $value === '') {
                $eventData['data'][$field] = null;

                continue;
            }

            $normalized = normalize_app_datetime((string) $value);
            if ($normalized !== null) {
                $eventData['data'][$field] = $normalized;
            }
        }

        return $eventData;
    }
}
