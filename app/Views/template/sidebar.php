<?php
use App\ThirdParty\Ragnos\Controllers\Ragnos;
$auth         = service('Admin_aut');
$ragnosConfig = config('RagnosConfig');
?>

<!-- Brand Logo -->
<div class="sidebar-brand">
    <a href="<?= site_url() ?>" class="brand-link">
        <span class="brand-text font-weight-light">
            <?= $ragnosConfig->Ragnos_all_to_uppercase ? strtoupper($ragnosConfig->Ragnos_application_title) : $ragnosConfig->Ragnos_application_title; ?>
        </span>
    </a>
</div>

<div class="sidebar-wrapper">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul id="sidebarTree" class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu"
            data-accordion="false">

            <?php foreach (service('menu')->getSidebarMenu() as $item): ?>
                <?php if (isset($item['divider'])): ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                <?php elseif (isset($item['children'])): ?>
                    <li class="nav-item">
                        <a class="nav-link">
                            <i class="<?= $item['icon'] ?>"></i>
                            <p>
                                <?= $item['title'] ?>
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php foreach ($item['children'] as $child): ?>
                                <li class="nav-item">
                                    <a href="<?= $child['url'] ?>" class="nav-link">
                                        <i class="bi <?= $child['icon'] ?> nav-icon"></i>
                                        <p><?= $child['title'] ?></p>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $item['url'] ?>">
                            <i class="bi <?= $item['icon'] ?>"></i>
                            <p><?= $item['title'] ?></p>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>

        </ul>
    </nav>
</div>