<?php
$hasMoney = isset($extra_attributes) && (strpos($extra_attributes, 'money') !== false);
$isDate   = isset($type) && $type === 'date';
$hasIcon  = $hasMoney || $isDate;
?>
<div class="divfield col-sm-3 mb-2">
    <div class="<?= $hasIcon ? 'input-group' : 'form-floating' ?>" id='group_<?= $name ?>'>
        <?php if ($hasMoney): ?>
            <span class="input-group-text" style="background-color: #e9ecef; border-color: #dee2e6;">
                <i class="bi bi-cash-coin" style="color: #28a745;"></i>
            </span>
        <?php elseif ($isDate): ?>
            <span class="input-group-text" style="background-color: #e9ecef; border-color: #dee2e6;">
                <i class="bi bi-calendar-event" style="color: #007bff;"></i>
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