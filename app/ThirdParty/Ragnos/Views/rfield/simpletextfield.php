<?php
$hasMoney = isset($extra_attributes) && (strpos($extra_attributes, 'money') !== false);
$isDate   = isset($type) && $type === 'date';
$hasIcon  = $hasMoney || $isDate;
?>
<div class="divfield col-sm-3 mb-2">
    <div class="<?= $hasIcon ? 'input-group' : 'form-floating' ?>" id='group_<?= $name ?>'>
        <?php if ($hasMoney): ?>
            <span class="input-group-text bg-body-secondary border-end-0 text-success">
                <i class="bi bi-cash-coin fs-5"></i>
            </span>
        <?php elseif ($isDate): ?>
            <span class="input-group-text bg-body-secondary border-end-0 text-primary">
                <i class="bi bi-calendar-event fs-5"></i>
            </span>
        <?php endif; ?>
        <div class="form-floating" style="flex: 1;">
            <?php
            $data = [
                'name'             => $name,
                'label'            => $label,
                'value'            => $value,
                'placeholder'      => $label,
                'type'             => $type ?? 'text',
                'extra_attributes' => $extra_attributes ?? '',
            ];
            ?>
            <?= view('App\ThirdParty\Ragnos\Views\rfield/inputfield', $data); ?>
            <label for="<?= $name ?>"><?= $label ?></label>
        </div>
    </div>
</div>