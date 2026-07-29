<?php

namespace App\Service\PostHistory;

/**
 * Laravel's PostHistoryService was not migrated to Symfony
 * It simply created histories when a post variant content was updated
 * and trimmed when there are more than 25 entries
 *
 * we need a better system: https://github.com/hyvor/blogs/issues/355
 * @codeCoverageIgnore
 */
class PostHistoryService {}
