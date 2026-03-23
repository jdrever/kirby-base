<?php

declare(strict_types=1);

// Static, cacheable HTML - JS will hydrate the active state from localStorage
?>
<span class="colour-mode-selector">
    <button type="button" class="colour-mode-btn" data-colour-mode="light"><?=t('Light')?></button>
    |
    <button type="button" class="colour-mode-btn" data-colour-mode="dark"><?=t('Dark')?></button>
    |
    <button type="button" class="colour-mode-btn" data-colour-mode="auto"><?=t('Auto')?></button>
</span>

